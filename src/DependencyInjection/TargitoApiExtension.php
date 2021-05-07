<?php

namespace Targito\Bundle\Api\DependencyInjection;

use LogicException;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Targito\Api\TargitoApi;

class TargitoApiExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yaml');
        $loader->load('autowiring.yaml');

        $configs = $this->processConfiguration(new Configuration(), $configs);

        $this->configureServices($configs, $container);
    }

    private function configureServices(array $configs, ContainerBuilder $container): void
    {
        $defaultCredentialsService = $configs['credentials']['type'];
        if ($defaultCredentialsService === 'environment' || $defaultCredentialsService === 'explicit') {
            $defaultCredentialsService = "targito_api.credentials.${defaultCredentialsService}";
        }
        $defaultCredentialsServiceDefinition = $container->getDefinition($defaultCredentialsService);
        if ($defaultCredentialsService === 'targito_api.credentials.explicit') {
            $accountId = $configs['credentials']['account_id'];
            $apiPassword = $configs['credentials']['api_password'];
            if ($accountId === null || $apiPassword === null) {
                throw new LogicException("When using the 'explicit' credentials type, account_id and api_password must be defined");
            }
            $defaultCredentialsServiceDefinition->setArguments([
                $configs['credentials']['account_id'],
                $configs['credentials']['api_password'],
            ]);
        } elseif ($defaultCredentialsService === 'targito_api.credentials.environment') {
            $defaultCredentialsServiceDefinition->setArguments([
                $configs['credentials']['account_id_env_name'],
                $configs['credentials']['api_password_env_name'],
            ]);
        }
        $container->setAlias('targito_api.credentials.default', $defaultCredentialsService);

        $defaultHttpService = $configs['http_request'];
        if ($defaultHttpService === null) {
            $defaultHttpService = extension_loaded('curl') ? 'targito_api.http.curl' : 'targito_api.http.stream';
        }
        $container->setAlias('targito_api.http.default', $defaultHttpService);

        $apiUrl = $configs['api_url'] ?? TargitoApi::API_URL;

        $targitoApiServiceDefinition = $container->getDefinition('targito_api.api');
        $targitoApiServiceDefinition->setArgument('$apiUrl', $apiUrl);

        foreach ($container->findTaggedServiceIds('targito_api.endpoint') as $serviceId => $tags) {
            $endpointDefinition = $container->getDefinition($serviceId);
            $endpointDefinition->setArgument('$apiUrl', $apiUrl);
        }
    }
}
