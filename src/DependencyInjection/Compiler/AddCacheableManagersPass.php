<?php

namespace TirielFirestoreOdmBundle\DependencyInjection\Compiler;

use App\Manager\FirestoreDtoManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AddCacheableManagersPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        foreach ($container->findTaggedServiceIds('app.firestore_manager') as $id => $tags) {
            /** @var FirestoreDtoManager $id */
            $tags['app.firestore_manager'][0]['dto'] = $id::getClass();

            $container->register($id, $id)
                ->setAutowired(true)
                ->setAutoconfigured(true)
                ->setTags($tags)
                ;
        }
    }
}
