<?php

namespace Tiriel\FirestoreOdmBundle;

use Google\Cloud\Firestore\FirestoreClient;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use Tiriel\FirestoreOdmBundle\DependencyInjection\Compiler\AddCacheableManagersPass;
use Tiriel\FirestoreOdmBundle\Factory\FirestoreFactory;

class TirielFirestoreOdmBundle extends AbstractBundle
{
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');

        if (\is_string($config['service_account']) && str_contains($config['service_account'], 'env(')) {
            $config['service_account'] = $builder->resolveEnvPlaceholders($config['service_account']);
        }

        $config['service_account'] = \is_array($config['service_account'])
            ?: $builder->getParameterBag()->resolveValue($config['service_account']);

        $id = sprintf("firestore_odm.%s.firestore_client", $config['project_name']);
        $builder->register($id, FirestoreClient::class)
            ->setFactory([FirestoreFactory::class, 'create'])
            ->addArgument($config)
            ->setPublic(false)
        ;

        $builder->setAlias(FirestoreClient::class, $id);
    }

    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddCacheableManagersPass());
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->scalarNode('project_name')
                    ->info('Your Google Cloud project name')
                ->end()
                ->variableNode('service_account')
                    ->info("The path to your service account's credentials file, or the credentials as an array.")
                    ->example('"%env(json:file:GC_CREDENTIALS)%", or "%kernel.project_dir%/config/secrets/my_project-123456.json", or service_account: type: "service_account"...')
                    ->validate()
                        ->ifTrue(static fn($v) => !\is_string($v) && !\is_array($v))
                        ->thenInvalid("Service account credentials must be give, either as a file path or an array.")
                    ->end()
                ->end()
                ->scalarNode('database_uri')
                    ->info("Only use if your database's url cannot be guessed from your service account file")
                ->end()
            ->end()
        ;
    }
}
