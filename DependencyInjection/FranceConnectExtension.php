<?php

namespace KleeGroup\FranceConnectBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Routing\RouterInterface;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class FranceConnectExtension extends Extension
{
    const CALLBACK_ROUTE = "kleegroup_franceconnect_franceconnect_check";
    
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        
        $loaderXml = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loaderXml->load('services.xml');
        
        $container->setParameter('france_connect.client_id', $config['client_id']);
        $container->setParameter('france_connect.client_secret', $config['client_secret']);
        $container->setParameter('france_connect.provider_base_url', $config['provider_base_url']);
        
        $container->setParameter('france_connect.callback_route', self::CALLBACK_ROUTE);
        $container->setParameter('france_connect.logout_route', $config['post_logout_route']);
        $container->setParameter('france_connect.result', $config['result_route']);
        
        $container->setParameter('france_connect.scopes', $config['scopes']);
        $container->setParameter('france_connect.providers_keys', $config['providers_keys']);
        
        $container->setParameter('france_connect.proxy_host', $config['proxy_host']);
        $container->setParameter('france_connect.proxy_port', $config['proxy_port']);
    }
}
