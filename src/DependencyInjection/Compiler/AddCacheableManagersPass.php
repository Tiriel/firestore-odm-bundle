<?php

namespace TirielFirestoreOdmBundle\DependencyInjection\Compiler;

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
        foreach ($container->findTaggedServiceIds('firestorm_odm.manager') as $id => $tags) {
            /** @var FirestoreDtoManager $id */
            $tags['firestorm_odm.manager'][0]['dto'] = $id::getClass();

            $container->register($id, $id)
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setTags($tags)
                ;
        }
    }
}
