<?php

namespace KleeGroup\FranceConnectBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/coobook/bundles/extension.html
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

        foreach ($config as $key => $value) {
            $container->setParameter('france_connect.'.$key, $value);
        }
    }
}
