<?php

namespace KleeGroup\FranceConnectBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class FranceConnectExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $loaderXml = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loaderXml->load('services.xml');

        $container->setParameter('france_connect.client_id', $config['client_id']);
        $container->setParameter('france_connect.client_secret', $config['client_secret']);
        $container->setParameter('france_connect.provider_base_url', $config['provider_base_url']);
        $container->setParameter('france_connect.callback_url', $config['callback_url']);
        $container->setParameter('france_connect.logout_url', $config['post_logout_redirect_uri']);
        $container->setParameter('france_connect.scopes', $config['scopes']);
        $container->setParameter('france_connect.result_route', $config['result_route']);
        $container->setParameter('france_connect.proxy', $config['proxy_host']);
    }
}
