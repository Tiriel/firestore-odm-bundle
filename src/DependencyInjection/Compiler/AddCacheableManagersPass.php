<?php

namespace Tiriel\FirestoreOdmBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Tiriel\FirestoreOdmBundle\Manager\CacheableFirestoreDtoManager;
use Tiriel\FirestoreOdmBundle\Manager\FirestoreDtoManager;
use Tiriel\FirestoreOdmBundle\Manager\Interface\DtoManagerInterface;

class AddCacheableManagersPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->getDefinitions() as $id => $def) {
            if (!is_subclass_of($id, FirestoreDtoManager::class)) {
                continue;
            }
            /** @var class-string<FirestoreDtoManager> $className */
            $className = $def->getClass();

            $container->register($id.'.cache')
                ->setClass(CacheableFirestoreDtoManager::class)
                ->setDecoratedService($id)
                ->setAutowired(true)
                ->addTag('firestore_odm.manager', ['dto' => $className::getClass()]);

            $parts = explode('\\', $className::getClass());
            $dtoName = \array_pop($parts);
            $container->registerAliasForArgument($id.'.cache', DtoManagerInterface::class, strtolower($dtoName).'Manager');
        }
    }
}
