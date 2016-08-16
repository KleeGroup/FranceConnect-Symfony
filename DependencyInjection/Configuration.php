<?php

namespace KleeGroup\FranceConnectBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 *  Configuraiton of FranceConnect Bundle.
 *
 * <b>Example:</b>
 *
 *  # Configuration FranceConnectBundle
 *   france_connect:
 *   #Ids
 *   client_id: 'b8a8fdf9fe4e6f469086e825c21aed3116b9cc3eafe90a4c553678c92bdc9835'
 *   client_secret: 'f2fa587128d3fa75167a79327bdd4ebaf5db6b60aeadd8ea173631879697100b'
 *   #FranceConnect base URL
 *   provider_base_url: 'https://fcp.integ01.dev-franceconnect.fr/api/v1/'
 *   # Callback URL
 *   callback_url: 'http://127.0.0.1/login_fc'
 *   # data
 *   scopes:
 *   - 'openid'
 *   - 'profile'
 *   result_route: 'app.retour_fc'
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
            ->scalarNode('callback_url')
                ->info('Callback url used by FranceConnect.')
                ->isRequired()
                ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($value) {
                        return !filter_var($value, FILTER_VALIDATE_URL);
                    })
                    ->thenInvalid("%s is not a valid URL.")
                ->end()
            ->end()
    
            ->scalarNode('proxy_host')
                ->info('Proxy.')
                ->treatNullLike('')
            ->end()
            ->scalarNode('post_logout_redirect_uri')
                ->info('POST LOGOUT REDIRECT URI used by FranceConnect.')
                ->isRequired()
                ->cannotBeEmpty()
                ->validate()
                    ->ifTrue(function ($value) {
                        return !filter_var($value, FILTER_VALIDATE_URL);
                    })
                     ->thenInvalid("%s is not a valid URL.")
                ->end()
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
