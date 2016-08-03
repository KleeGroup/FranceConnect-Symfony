<?php

/**
 * Created by PhpStorm.
 * User: tveron
 * Date: 02/08/2016
 * Time: 15:20
 */
class ContextServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $session;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $logger;

    /**
     * @var \KleeGroup\FranceConnectBundle\Manager\ContextService
     */
    private $context;

    private $clientId = '123456789123456789';

    private $clientSecret = 'mon_secret123456';

    private $fcBase = 'https://fcp.integ01.dev-franceconnect.fr/api/v1/';

    private $callbackUrl = 'http://127.0.0.1:8000/france-connect/callback';

    private $resultRoute  = 'route';

    private $scopes = array('openid');

    public function setUp()
    {
        $this->session = $this->getMockBuilder(\Symfony\Component\HttpFoundation\Session\SessionInterface::class)->getMock();
        $this->logger = $this->getMockBuilder(\Psr\Log\LoggerInterface::class)->getMock();

        $this->context = new \KleeGroup\FranceConnectBundle\Manager\ContextService($this->session, $this->logger, $this->clientId,
            $this->clientSecret, $this->fcBase, $this->scopes, $this->callbackUrl );
    }

    /**
     * @test
     */
    public function generateAuthorizationURL()
    {
        $randState = 'az:kjflkanga,hgm,a';

        $this->session
            ->expects($this->exactly(2))
            ->method('set')
            ->withConsecutive(
                [\KleeGroup\FranceConnectBundle\Manager\ContextService::OPENID_SESSION_TOKEN, $this->anything()],
                [\KleeGroup\FranceConnectBundle\Manager\ContextService::OPENID_SESSION_NONCE, $this->anything()]
            );

        $this->session
            ->method('get')
            ->withAnyParameters()
            ->willReturn($randState);

        $url = $this->context->generateAuthorizationURL();
        parse_str(parse_url($url,PHP_URL_QUERY ),$array);

        $this->assertArrayHasKey('response_type',$array);
        $this->assertSame('code', $array['response_type']);

        $this->assertArrayHasKey('scope',$array);
        $this->assertSame($this->scopes[0], $array['scope']);

        $this->assertArrayHasKey('redirect_uri',$array);
        $this->assertSame($this->callbackUrl, $array['redirect_uri']);

        $this->assertArrayHasKey('state',$array);
        $this->assertArrayHasKey('nonce',$array);
    }

}