<?php
/**
 * Created by PhpStorm.
 * User: tveron
 * Date: 02/08/2016
 * Time: 12:07
 */

namespace KleeGroup\FranceConnectBundle\Tests\DependencyInjection;


use KleeGroup\FranceConnectBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function mustProvideConfiguration()
    {
        $emptyConfig = array();
        $this->expectException(InvalidConfigurationException::class);
        $this->processConfiguration($emptyConfig);
    }

    /**
     * @test
     */
    public function scopesAreNotRequired ()
    {
        $client_id         = '123456789';
        $client_secret     = '123456789';
        $provider_base_url = 'https://fcp.integ01.dev-franceconnect.fr/api/v1/';
        $result_route      = 'route';
        $post_logout_route = 'route';

        $config = array(
            'france_connect'=> array (
                'client_id'         => $client_id,
                'client_secret'     => $client_secret,
                'provider_base_url' => $provider_base_url,
                'result_route'      => $result_route,
                'post_logout_route' => $post_logout_route
            )
        );

        /**@var array  */
        $finalConfig = $this->processConfiguration($config);
        $this->assertArrayHasKey('scopes',$finalConfig);
        $scopes = $finalConfig['scopes'];
        $this->assertCount(1, $scopes);
        $this->assertSame('openid', $scopes[0]);
    }

    /**
     * @param array $configs
     * @return array
     */
    protected function processConfiguration(array $configs)
    {
        $configuration = new Configuration();
        $processor = new Processor();
        return $processor->processConfiguration($configuration, $configs);
    }
}