<?php

namespace KleeGroup\FranceConnectBundle\Controller;

use KleeGroup\FranceConnectBundle\Manager\ContextService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
    
    /**
     * @return object|\Symfony\Bridge\Monolog\Logger
     */
    private function getLogger()
    {
        return $this->get('logger');
    }
    
    /**
     * @return ContextService|object
     */
    private function getFCService()
    {
        return $this->get('france_connect.service.context');
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
        $json = $this->getFCService()->getUserInfo($getParams);
        $route_name = $this->getParameter('france_connect.result');
        
        return $this->redirectToRoute($route_name, ['json' => urlencode($json)]);
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
