<?php

namespace KleeGroup\FranceConnectBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 *  Configuraiton of FranceConnect Bundle.
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('france_connect');
        $rootNode = \method_exists($treeBuilder, 'getRootNode') ? $treeBuilder->getRootNode() : $treeBuilder->root('france_connect');

        $rootNode->children()
            ->scalarNode('client_id')
                ->info('Client id provided by FranceConnect.')
                ->isRequired()
            ->end()
            ->scalarNode('client_secret')
                ->info('Client secret provided by FranceConnect.')
                ->isRequired()
            ->end()
            ->scalarNode('provider_base_url')
                ->info('FranceConnect base url.')
                ->isRequired()
            ->end()
            ->scalarNode('proxy_host')
                ->info('Proxy host.')
                ->treatNullLike('')
                ->defaultValue('')
            ->end()
            ->integerNode('proxy_port')
                ->info('Proxy host.')
                ->defaultValue(1080)
                ->treatNullLike(1080)
                ->defaultValue(null)
            ->end()
            ->enumNode('callback_type')
                ->info('Callback type redirect used by FranceConnect.')
                ->defaultValue('route')
                ->values(['route', 'url'])
            ->end()
            ->scalarNode('callback_value')
                ->info('Callback value redirect used by FranceConnect.')
                ->treatNullLike('kleegroup_franceconnect_franceconnect_check')
                ->defaultValue('kleegroup_franceconnect_franceconnect_check')
            ->end()
            ->enumNode('logout_type')
                ->info('Post logout type redirect used by FranceConnect.')
                ->defaultValue('route')
                ->values(['route', 'url'])
            ->end()
            ->scalarNode('logout_value')
                ->info('Post logout value redirect used by FranceConnect.')
                ->isRequired()
            ->end()
            ->enumNode('result_type')
                ->info('Post logout type redirect used by FranceConnect.')
                ->defaultValue('route')
                ->values(['route', 'url'])
            ->end()
            ->scalarNode('result_value')
                ->info('Route name for treatment.')
                ->isRequired()
            ->end()
            ->arrayNode('scopes')
                ->info('Scopes desired.')
                ->treatNullLike(array('openid'))
                ->prototype('scalar')->end()
                ->defaultValue(array('openid'))
            ->end()
            ->arrayNode('providers_keys')
                ->info('Providers keys. FranceConnectBundle will put FranceConnectToken on these firewalls')
                ->requiresAtLeastOneElement()
                ->scalarPrototype('scalar')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

}
