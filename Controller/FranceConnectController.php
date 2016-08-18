<?php

namespace KleeGroup\FranceConnectBundle\Controller;

use KleeGroup\FranceConnectBundle\Manager\ContextService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Class FranceConnectController
 * @package KleeGroup\FranceConnectBundle\Controller
 * @Route("/france-connect")
 */
class FranceConnectController extends Controller
{

    /**
     * @Route("/login_fc", methods="GET")
     */
    public function loginAction(Request $request)
    {
        $logger = $this->get('logger');
        $logger->debug('Generating a URL to get the authorization code.');
        $url = $this->get('france_connect.service.context')->generateAuthorizationURL();
        return $this->redirect($url);
    }

    /**
     * @Route("/callback", methods="GET")
     */
    public function checkAction(Request $request)
    {
        $logger = $this->get('logger');
        $logger->debug('Callback intercept.');
        $getParams  = $request->query->all();
        $json       = $this->get('france_connect.service.context')->getUserInfo($getParams);
        $route_name = $this->getParameter('france_connect.result');
        return $this->redirectToRoute($route_name, ['json'=>urlencode($json)]);
    }

    /**
     * @Route("/logout_fc")
     */
    public function logoutAction()
    {
        $logger = $this->get('logger');
        $logger->debug('Get Logout URL.');
        $url = $this->get('france_connect.service.context')->generateLogoutURL();
        return $this->redirect($url);
    }


}
