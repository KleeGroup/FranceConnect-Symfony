<?php

namespace KleeGroup\FranceConnectBundle\Controller;

use KleeGroup\FranceConnectBundle\Manager\ContextServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class FranceConnectController
 *
 * @package KleeGroup\FranceConnectBundle\Controller
 * @Route("/france-connect")
 */
class FranceConnectController extends AbstractController
{
    private LoggerInterface $logger;
    private ContextServiceInterface $contextService;

    public function __construct(LoggerInterface $logger, ContextServiceInterface $contextService)
    {
        $this->logger = $logger;
        $this->contextService = $contextService;
    }
    
    /**
     * @Route("/login_fc", methods="GET")
     * @return RedirectResponse
     */
    public function loginAction( ): Response
    {
        $this->logger->debug('Generating a URL to get the authorization code.');
        $url = $this->contextService->generateAuthorizationURL();
        
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
        $this->logger->debug('Callback intercept.');
        $getParams = $request->query->all();
        $this->contextService->getUserInfo($getParams);

        if ($this->getParameter('france_connect.result_type') === 'route') {
            $redirection = $this->redirectToRoute($this->getParameter('france_connect.result_value'));
        } else {
            $redirection = $this->redirect($this->getParameter('france_connect.result_value'));
        }

        return $redirection;
    }
    
    /**
     * @Route("/logout_fc")
     * @return RedirectResponse
     */
    public function logoutAction()
    {
        $this->logger->debug('Get Logout URL.');
        $url = $this->contextService->generateLogoutURL();
        
        return $this->redirect($url);
    }
}
