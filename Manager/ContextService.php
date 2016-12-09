<?php

namespace KleeGroup\FranceConnectBundle\Manager;


use KleeGroup\FranceConnectBundle\Manager\Exception\Exception;
use KleeGroup\FranceConnectBundle\Manager\Exception\SecurityException;
use KleeGroup\FranceConnectBundle\Security\Core\Authentication\Token\FranceConnectToken;
use KleeGroup\FranceConnectBundle\Security\Core\Authorization\Voter\FranceConnectAuthenticatedVoter;
use Namshi\JOSE\SimpleJWS;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;


/**
 * Class ContextService
 *
 * @package KleeGroup\FranceConnectBundle\Manager
 */
class ContextService implements ContextServiceInterface
{
    const OPENID_SESSION_TOKEN = "open_id_session_token";
    const OPENID_SESSION_NONCE = "open_id_session_nonce";
    const ID_TOKEN_HINT        = "open_id_token_hint";
    
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
     * @var string proxy host
     */
    private $proxyHost;
    
    /**
     * @var int proxy port
     */
    private $proxyPort;
    
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;
    
    /**
     * @var SessionAuthenticationStrategyInterface
     */
    private $sessionStrategy;
    
    /**
     * @var RequestStack
     */
    private $requestStack;
    
    /**
     * @var array
     */
    private $providersKeys;
    
    /**
     * ContextService constructor.
     *
     * @param SessionInterface $session   session manager
     * @param LoggerInterface  $logger    logger
     * @param string           $clientId  service identifier
     * @param string           $clientSecret
     * @param string           $fcBaseUrl FranceConnect base URL
     * @param array            $scopes    scopes
     * @param string           $proxy     proxy
     * @param string           $proxyPort proxyPort
     */
    public function __construct(
        SessionInterface $session,
        LoggerInterface $logger,
        RouterInterface $router,
        SessionAuthenticationStrategyInterface $sessionStrategy,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        $clientId,
        $clientSecret,
        $fcBaseUrl,
        array $scopes,
        string $proxy,
        int $proxyPort,
        $callbackRoute,
        $logoutRoute,
        array $providersKeys
    ) {
        $this->session = $session;
        $this->logger = $logger;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->fcBaseUrl = $fcBaseUrl;
        $this->scopes = $scopes;
        try {
            // ensure route exist
            $this->logoutUrl = $router->generate($logoutRoute, [], UrlGeneratorInterface::ABSOLUTE_URL);
            $this->callbackUrl = $router->generate($callbackRoute, [], UrlGeneratorInterface::ABSOLUTE_URL);
        } catch (RouteNotFoundException $ex) {
            throw new Exception("Route name is invalid", $ex);
        }
        $this->proxyPort = $proxyPort;
        $this->proxyHost = $proxy;
        $this->tokenStorage = $tokenStorage;
        $this->sessionStrategy = $sessionStrategy;
        $this->requestStack = $requestStack;
        $this->providersKeys = $providersKeys;
    }
    
    /**
     * @inheritdoc
     */
    public function generateAuthorizationURL()
    {
        $this->logger->debug('Set session tokens');
        $this->session->set(static::OPENID_SESSION_TOKEN, $this->getRandomToken());
        $this->session->set(static::OPENID_SESSION_NONCE, $this->getRandomToken());
        
        $this->logger->debug('Generate Query String.');
        $params = [
            'response_type' => 'code',
            'client_id'     => $this->clientId,
            'scope'         => implode(' ', $this->scopes),
            'redirect_uri'  => $this->callbackUrl,
            'nonce'         => $this->session->get(static::OPENID_SESSION_NONCE),
            'state'         => urlencode('token={'.$this->session->get(static::OPENID_SESSION_TOKEN).'}'),
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
     *
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
        $userInfo['access_token'] = $accessToken;
        
        $token = new FranceConnectToken($userInfo,
            [
                FranceConnectAuthenticatedVoter::IS_FRANCE_CONNECT_AUTHENTICATED,
                AuthenticatedVoter::IS_AUTHENTICATED_ANONYMOUSLY,
            ]
        );
        $request = $this->requestStack->getCurrentRequest();
        
        if (null !== $request) {
            $this->sessionStrategy->onAuthentication($request, $token);
        }
        
        $this->tokenStorage->setToken($token);
        foreach ($this->providersKeys as $key) {
            $this->session->set('_security_'.$key, serialize($token));
        }
        
        
        return json_encode($userInfo, true);
    }
    
    /**
     * Check state parameter for security reason.
     *
     * @param $state
     *
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
        
        if ($token != $this->session->get(static::OPENID_SESSION_TOKEN)) {
            $this->logger->error('The value of the parameter STATE is not equal to the one which is expected');
            throw new SecurityException("The token is invalid.");
        }
    }
    
    /**
     * Get Access Token.
     *
     * @param string $code authorization code
     *
     * @return string access token
     * @throws SecurityException
     * @throws Exception
     */
    private function getAccessToken($code)
    {
        $this->logger->debug('Get Access Token.');
        $this->initRequest();
        $token_url = $this->fcBaseUrl.'token';
        $post_data = [
            "grant_type"    => "authorization_code",
            "redirect_uri"  => $this->callbackUrl,
            "client_id"     => $this->clientId,
            "client_secret" => $this->clientSecret,
            "code"          => $code,
        ];
        $this->logger->debug('POST Data to FranceConnect.');
        $this->setPostFields($post_data);
        $response = \Unirest\Request::post($token_url);
        
        // check status code
        if ($response->code != Response::HTTP_OK) {
            $result_array = $response->body;
            $description = array_key_exists(
                "error_description",
                $result_array
            ) ? $result_array["error_description"] : '';
            $this->logger->error(
                $result_array["error"].$description
            );
            throw new Exception("FranceConnectError".$response->code." msg = ".$response->raw_body);
        }
        
        $result_array = $response->body;
        $id_token = $result_array['id_token'];
        $this->session->set(static::ID_TOKEN_HINT, $id_token);
        $all_part = explode(".", $id_token);
        $payload = json_decode(base64_decode($all_part[1]), true);
        
        // check nonce parameter
        if ($payload['nonce'] != $this->session->get(static::OPENID_SESSION_NONCE)) {
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
        
        $this->session->remove(static::OPENID_SESSION_NONCE);
        
        return $result_array['access_token'];
    }
    
    /**
     * Prepare request.
     */
    private function initRequest()
    {
        \Unirest\Request::clearCurlOpts();
        \Unirest\Request::clearDefaultHeaders();
        // => jsonOpts équivaut à "json_decode($result, true)"
        \Unirest\Request::jsonOpts(true);
        if (!is_null($this->proxyHost) && !empty($this->proxyHost)) {
            \Unirest\Request::proxy($this->proxyHost, $this->proxyPort);
        }
    }
    
    /**
     * set post fields.
     *
     * @param array $post_data
     */
    private function setPostFields(array $post_data)
    {
        $pd = [];
        foreach ($post_data as $k => $v) {
            $pd[] = "$k=$v";
        }
        $pd = implode("&", $pd);
        \Unirest\Request::curlOpt(CURLOPT_POST, true);
        \Unirest\Request::curlOpt(CURLOPT_POSTFIELDS, $pd);
        \Unirest\Request::curlOpt(CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    }
    
    /**
     * Last call to FranceConnect to get data.
     *
     * @param $accessToken
     *
     * @return mixed
     * @throws Exception
     */
    private function getInfos($accessToken)
    {
        $this->logger->debug('Get Infos.');
        $this->initRequest();
        $headers = [
            "Authorization" => "Bearer $accessToken",
        ];
        $userInfoUrl = $this->fcBaseUrl."userinfo?schema=openid";
        $response = \Unirest\Request::get($userInfoUrl, $headers);
        if ($response->code != Response::HTTP_OK) {
            $result_array = $response->body;
            $messageErreur = $result_array['error'];
            $this->logger->error($messageErreur);
            throw new Exception("Erreur lors de la récupération des infos sur le serveur OpenID : ".$messageErreur);
        }
        
        return $response->body;
    }
    
    /**
     * @inheritdoc
     */
    public function generateLogoutURL()
    {
        $this->logger->debug('Generate Query String.');
        $params = [
            'post_logout_redirect_uri' => $this->logoutUrl,
            'id_token_hint'            => $this->session->get(static::ID_TOKEN_HINT),
        ];
        
        $this->logger->debug('Remove session token');
        $this->session->clear();
        
        return $this->fcBaseUrl.'logout?'.http_build_query($params);
    }
    
}