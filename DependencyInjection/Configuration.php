<?php

namespace KleeGroup\FranceConnectBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('france_connect');

        $rootNode->children()
            ->scalarNode('client_id')
                ->info('Client id provided by FranceConnect.')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->scalarNode('client_secret')
                ->info('Client secret provided by FranceConnect.')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->scalarNode('provider_base_url')
                ->info('FranceConnect base url.')
                ->isRequired()
                ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($value) {
                        return !filter_var($value, FILTER_VALIDATE_URL) && strcmp(substr($value, -1),'/') !== 0;
                    })
                    ->thenInvalid("%s is not a valid URL. The URL must end with a slash")
                ->end()
            ->end()
            ->scalarNode('proxy_host')
                ->info('Proxy.')
                ->treatNullLike('')
            ->end()
            ->scalarNode('post_logout_route')
                ->info('Post logout route redirect used by FranceConnect.')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
            ->arrayNode('scopes')
                ->info('Scopes desired.')
                ->treatNullLike(array('openid'))
                ->prototype('scalar')->end()
                ->defaultValue(array('openid'))
            ->end()
            ->scalarNode('result_route')
                ->info('Route name for treatment.')
                ->isRequired()
                ->cannotBeEmpty()
            ->end()
        ->end();

        return $treeBuilder;
    }

}
