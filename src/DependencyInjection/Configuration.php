<?php

namespace Targito\Bundle\Api\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('targito_api');
        /** @var ArrayNodeDefinition $rootNode */
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->addDefaultsIfNotSet()
            ->children()
                ->arrayNode('credentials')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('type')
                            ->info("Can be 'environment', 'explicit' or any service implementing Targito\Api\Credentials\CredentialsInterface")
                            ->defaultValue('environment')
                        ->end()
                        ->scalarNode('account_id')
                            ->info("The account id that is used when type is 'explicit'")
                            ->defaultNull()
                        ->end()
                        ->scalarNode('api_password')
                            ->info("The api password that is used when type is 'explicit'")
                            ->defaultNull()
                        ->end()
                        ->scalarNode('account_id_env_name')
                            ->info("The account id environment variable name when type is 'environment'")
                            ->defaultValue('TARGITO_ACCOUNT_ID')
                        ->end()
                        ->scalarNode('api_password_env_name')
                            ->info("The api password environment variable name when type is 'environment'")
                            ->defaultValue('TARGITO_API_PASSWORD')
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('http_request')
                    ->info("The service that will be used as the http request (must implement Targito\\Api\\Http\\HttpRequestInterface).\nDefaults to null which means autodetect between default curl and stream based implementations.")
                    ->defaultNull()
                ->end()
                ->scalarNode('api_url')
                    ->info('The api url (including version) to issue requests to. Can be null which means to use default.')
                    ->defaultNull()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
