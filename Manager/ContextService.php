<?php

namespace KleeGroup\FranceConnectBundle\Manager;


use KleeGroup\FranceConnectBundle\Manager\Exception\Exception;
use KleeGroup\FranceConnectBundle\Manager\Exception\InvalidArgumentException;
use KleeGroup\FranceConnectBundle\Manager\Exception\SecurityException;
use Namshi\JOSE\SimpleJWS;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;


/**
 * Class ContextService
 *
 * @package KleeGroup\FranceConnectBundle\Manager
 */
class ContextService implements ContextServiceInterface
{
    const OPENID_SESSION_TOKEN = "open_id_session_token";
    const OPENID_SESSION_NONCE = "open_id_session_nonce";
    const ID_TOKEN_HINT = "open_id_token_hint";
    
    /**
     * @var SessionInterface session manager
     */
    private $session;
    /**
     * @var LoggerInterface logger
     */
    private $logger;
    /**
     * @var string Identifier
     */
    private $clientId;
    /**
     * @var string Secret identifier
     */
    private $clientSecret;
    /**
     * @var string FranceConnect base URL
     */
    private $fcBaseUrl;
    /**
     * @var array scopes of data
     */
    private $scopes;
    /**
     * @var string callback URL
     */
    private $callbackUrl;
    /**
     * @var string logout URL
     */
    private $logoutUrl;
    
    /**
     * @var string proxy
     */
    private $proxy;
    
    /**
     * ContextService constructor.
     *
     * @param SessionInterface $session   session manager
     * @param LoggerInterface  $logger    logger
     * @param                  $clientId  service identifier
     * @param                  $clientSecret
     * @param                  $fcBaseUrl FranceConnect base URL
     * @param array            $scopes    scopes
     * @param                  $proxy     proxy
     */
    public function __construct(
        SessionInterface $session,
        LoggerInterface $logger,
        RouterInterface $router,
        $clientId,
        $clientSecret,
        $fcBaseUrl,
        array $scopes,
        $proxy,
        $callbackRoute,
        $logoutRoute
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->fcBaseUrl = $fcBaseUrl;
        $this->scopes = $scopes;
        try {
            $this->logoutUrl = $router->generate($logoutRoute, [],UrlGeneratorInterface::ABSOLUTE_URL );
            $this->callbackUrl = $router->generate($callbackRoute, [],UrlGeneratorInterface::ABSOLUTE_URL );
        } catch (RouteNotFoundException $ex) {
            throw new InvalidArgumentException("Route name is invalid", $ex);
        }
        $this->proxy = $proxy;
    }
    
    /**
     * @inheritdoc
     */
    public function generateAuthorizationURL()
    {
        $this->logger->debug('Set session tokens');
        $this->session->set(self::OPENID_SESSION_TOKEN, $this->getRandomToken());
        $this->session->set(self::OPENID_SESSION_NONCE, $this->getRandomToken());
        
        $this->logger->debug('Generate Query String.');
        $params = [
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'scope'         => implode(' ', $this->scopes),
            'redirect_uri'  => $this->callbackUrl,
            'nonce'         => $this->session->get(self::OPENID_SESSION_NONCE),
            'state'         => urlencode('token={'.$this->session->get(self::OPENID_SESSION_TOKEN).'}'),
        ];
        
        return $this->fcBaseUrl.'authorize?'.http_build_query($params);
    }
    
    /**
     * Generate random string.
     *
     * @return string
     */
    private function getRandomToken()
    {
        return sha1(mt_rand(0, mt_getrandmax()));
    }
    
    /**
     * Returns data provided by FranceConnect.
     *
     * @param array $params query string parameter
     * @return string data provided by FranceConnect (json)
     * @throws Exception General exception
     * @throws SecurityException An exception may be thrown if a security check has failed
     */
    public function getUserInfo(array $params)
    {
        $this->logger->debug('Get User Info.');
        if (array_key_exists("error", $params)) {
            $this->logger->error(
                $params["error"].array_key_exists("error_description", $params) ? $params["error_description"] : ''
            );
            throw new Exception('FranceConnect error => '.$params["error"]);
        }
        
        $this->verifyState($params['state']);
        $accessToken = $this->getAccessToken($params['code']);
        $userInfo = $this->getInfos($accessToken);
        $user_info['access_token'] = $accessToken;
        
        return json_encode($userInfo, true);
    }
    
    /**
     * Check state parameter for security reason.
     *
     * @param $state
     * @throws SecurityException
     */
    private function verifyState($state)
    {
        $this->logger->debug('Verify parameter state.');
        $state = urldecode($state);
        $stateArray = [];
        parse_str($state, $stateArray);
        $token = $stateArray['token'];
        $token = preg_replace('~{~', '', $token, 1);
        $token = preg_replace('~}~', '', $token, 1);
        
        if ($token != $this->session->get(self::OPENID_SESSION_TOKEN)) {
            $this->logger->error('The value of the parameter STATE is not equal to the one which is expected');
            throw new SecurityException("The token is invalid.");
        }
    }
    
    /**
     * Get Access Token.
     *
     * @param string authorization code
     * @return string access token
     * @throws SecurityException
     * @throws Exception
     */
    private function getAccessToken($code)
    {
        $this->logger->debug('Get Access Token.');
        $curlWrapper = new CurlWrapper($this->proxy);
        $post_data = [
            "grant_type"    => "authorization_code",
            "code"          => $code,
            "redirect_uri"  => $this->callbackUrl,
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
        ];
        
        $this->logger->debug('POST Data to FranceConnect.');
        $curlWrapper->setPostDataUrlEncode($post_data);
        $token_url = $this->fcBaseUrl.'token';
        $result = $curlWrapper->get($token_url);
        
        // check status code
        if ($curlWrapper->getHTTPCode() != 200) {
            if (!$result) {
                $this->logger->error("Curl Error : ".$curlWrapper->getLastError());
                throw new Exception($curlWrapper->getLastError());
            }
            $result_array = json_decode($result, true);
            $description = array_key_exists("error_description",$result_array) ? $result_array["error_description"] : '';
            $this->logger->error(
                $result_array["error"] . $description
            );
            throw new Exception("FranceConnect Error => ".$result_array['error']);
        }
        
        $result_array = json_decode($result, true);
        $id_token = $result_array['id_token'];
        $this->session->set(self::ID_TOKEN_HINT, $id_token);
        $all_part = explode(".", $id_token);
        $header = json_decode(base64_decode($all_part[0]), true);
        $payload = json_decode(base64_decode($all_part[1]), true);
        
        // check nonce parameter
        if ($payload['nonce'] != $this->session->get(self::OPENID_SESSION_NONCE)) {
            $this->logger->error('The value of the parameter NONCE is not equal to the one which is expected');
            throw new SecurityException("The nonce parameter is invalid");
        }
        // verify the signature of jwt
        $this->logger->debug('Check JWT signature.');
        $jws = SimpleJWS::load($id_token);
        if (!$jws->verify($this->clientSecret)) {
            $this->logger->error('The signature of the JWT is not valid.');
            throw new SecurityException("JWS is invalid");
        }
        
        $this->session->remove(self::OPENID_SESSION_NONCE);
        
        return $result_array['access_token'];
    }
    
    /**
     * Last call to FranceConnect to get data.
     *
     * @param $accessToken
     * @return mixed
     * @throws Exception
     */
    private function getInfos($accessToken)
    {
        $this->logger->debug('Get Infos.');
        $curlWrapper = new CurlWrapper($this->proxy);
        $curlWrapper->addHeader("Authorization", "Bearer $accessToken");
        $userInfoUrl = $this->fcBaseUrl."userinfo";
        $result = $curlWrapper->get($userInfoUrl);
        
        if ($curlWrapper->getHTTPCode() != 200) {
            if (!$result) {
                $messageErreur = $this->curlWrapper->getLastError();
            } else {
                $result_array = json_decode($result, true);
                $messageErreur = $result_array['error'];
            }
            $this->logger->error($messageErreur);
            throw new Exception("Erreur lors de la récupération des infos sur le serveur OpenID : ".$messageErreur);
        }
        
        return json_decode($result, true);
    }
    
    /**
     * @inheritdoc
     */
    public function generateLogoutURL()
    {
        $this->logger->debug('Generate Query String.');
        $params = [
            'post_logout_redirect_uri' => $this->logoutUrl,
            'id_token_hint'            => $this->session->get(self::ID_TOKEN_HINT),
        ];
        
        $this->logger->debug('Remove session token');
        $this->session->clear();
        
        return $this->fcBaseUrl.'logout?'.http_build_query($params);
    }
    
}