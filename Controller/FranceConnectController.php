<?php

namespace KleeGroup\FranceConnectBundle\Controller;

use KleeGroup\FranceConnectBundle\Manager\ContextServiceInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class FranceConnectController
 *
 * @package KleeGroup\FranceConnectBundle\Controller
 * @Route("/france-connect")
 */
#[Route(path: '/france-connect')]
class FranceConnectController extends AbstractController
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly ContextServiceInterface $contextService
    )
    {
    }

    /**
     * @return RedirectResponse
     */
    #[Route(path: '/login_fc', methods: ['GET'])]
    public function loginAction(): Response
    {
        $this->logger->debug('Generating a URL to get the authorization code.');
        $url = $this->contextService->generateAuthorizationURL();

        return $this->redirect($url);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse
     */
    #[Route(path: '/callback', methods: ['GET'])]
    public function checkAction(Request $request) : RedirectResponse
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
     * @return RedirectResponse
     */
    #[Route(path: '/logout_fc')]
    public function logoutAction(): RedirectResponse
    {
        $this->logger->debug('Get Logout URL.');
        $url = $this->contextService->generateLogoutURL();

        return $this->redirect($url);
    }
}
