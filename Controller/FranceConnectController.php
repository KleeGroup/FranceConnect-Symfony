<?php

namespace KleeGroup\FranceConnectBundle\Controller;

use KleeGroup\FranceConnectBundle\Manager\ContextService;
use KleeGroup\FranceConnectBundle\Manager\ContextServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class FranceConnectController
 *
 * @package KleeGroup\FranceConnectBundle\Controller
 * @Route("/france-connect")
 */
class FranceConnectController extends Controller
{
    /** @var LoggerInterface */
    private $logger;

    /** @var ContextServiceInterface */
    private $contextService;

    public function __construct(LoggerInterface $logger, ContextServiceInterface $contextService)
    {
        $this->logger = $logger;
        $this->contextService = $contextService;
    }
    
    /**
     * @return object|\Symfony\Bridge\Monolog\Logger
     */
    private function getLogger()
    {
        return $this->logger;
    }
    
    /**
     * @return ContextService|object
     */
    private function getFCService()
    {
        return $this->contextService;
    }
    
    /**
     * @Route("/login_fc", methods="GET")
     * @return RedirectResponse
     */
    public function loginAction( )
    {
        $logger = $this->getLogger();
        $logger->debug('Generating a URL to get the authorization code.');
        $url = $this->getFCService()->generateAuthorizationURL();
        
        return $this->redirect($url);
    }
    
    /**
     * @Route("/callback", methods="GET")
     * @param Request $request
     *
     * @return RedirectResponse
     */
    public function checkAction(Request $request)
    {
        $logger = $this->getLogger();
        $logger->debug('Callback intercept.');
        $getParams = $request->query->all();
        $this->getFCService()->getUserInfo($getParams);

        switch ($this->getParameter('france_connect.result_type')) {
            case 'route' :
                $redirection = $this->redirectToRoute($this->getParameter('france_connect.result_value'));
                break;
            default :
                $redirection = $this->redirect($this->getParameter('france_connect.result_value'));
                break;
        }

        return $redirection;
    }
    
    /**
     * @Route("/logout_fc")
     * @return RedirectResponse
     */
    public function logoutAction()
    {
        $logger = $this->getLogger();
        $logger->debug('Get Logout URL.');
        $url = $this->getFCService()->generateLogoutURL();
        
        return $this->redirect($url);
    }
    
    
}
