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
                ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($p) { return !is_string($p); })
                    ->thenInvalid('The parameter "client_id" must be a string.')
                ->end()
            ->end()
            ->scalarNode('client_secret')
                ->info('Client secret provided by FranceConnect.')
                ->isRequired()
                ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($p) { return !is_string($p); })
                    ->thenInvalid('The parameter "client_secret" must be a string.')
                ->end()
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
            ->scalarNode('callback_type')
                ->info('Callback type redirect used by FranceConnect.')
                ->treatNullLike('route')
                ->defaultValue('route')
                ->validate()
                    ->ifTrue(function ($p) { return !in_array($p, ['route','url'], true); })
                    ->thenInvalid('The parameter "callback_type" must be "route" or "url".')
                ->end()
            ->end()
            ->scalarNode('callback_value')
                ->info('Callback value redirect used by FranceConnect.')
                ->treatNullLike('kleegroup_franceconnect_franceconnect_check')
                ->defaultValue('kleegroup_franceconnect_franceconnect_check')
                ->validate()
                    ->ifTrue(function ($p) { return !is_string($p); })
                    ->thenInvalid('The parameter "logout_value" must be a string.')
                ->end()
            ->end()
            ->scalarNode('logout_type')
                ->info('Post logout type redirect used by FranceConnect.')
                ->treatNullLike('route')
                ->defaultValue('route')
                ->validate()
                    ->ifTrue(function ($p) { return !in_array($p, ['route','url'], true); })
                    ->thenInvalid('The parameter "logout_type" must be "route" or "url".')
                ->end()
            ->end()
            ->scalarNode('logout_value')
                ->info('Post logout value redirect used by FranceConnect.')
                ->isRequired()
                ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($p) { return !is_string($p); })
                    ->thenInvalid('The parameter "logout_value" must be a string.')
                ->end()
            ->end()
            ->scalarNode('result_type')
                ->info('Post logout type redirect used by FranceConnect.')
                ->treatNullLike('route')
                ->defaultValue('route')
                ->validate()
                    ->ifTrue(function ($p) { return !in_array($p, ['route','url'], true); })
                    ->thenInvalid('The parameter "result_type" must be "route" or "url".')
                ->end()
            ->end()
            ->scalarNode('result_value')
                ->info('Route name for treatment.')
                ->isRequired()
                ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($p) { return !is_string($p); })
                    ->thenInvalid('The parameter "result_value" must be a string.')
                ->end()
            ->end()
            ->arrayNode('scopes')
                ->info('Scopes desired.')
                ->treatNullLike(array('openid'))
                ->prototype('scalar')->end()
                ->defaultValue(array('openid'))
            ->end()
            ->arrayNode('providers_keys')
                ->info('Providers keys. FranceConnectBundle will put FranceConnectToken on these firewalls')
                ->cannotBeEmpty()
                ->prototype('scalar')->end()
            ->end()
        ->end();

        return $treeBuilder;
    }

}
