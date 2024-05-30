<?php

namespace Tiriel\FirestoreOdmBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tiriel\FirestoreOdmBundle\Manager\FirestoreDtoManager;

class AddCacheableManagersPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('firestore_odm.manager') as $id => $tags) {
            $def = $container->getDefinition($id);
            /** @var class-string<FirestoreDtoManager> $className */
            $className = $def->getClass();
            $tags['firestorm_odm.manager'][0]['dto'] = $className::getClass();

            $container->register($id, $className)
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setTags($tags)
                ;
        }
    }
}
