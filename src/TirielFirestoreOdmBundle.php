<?php

namespace Tiriel\FirestoreOdmBundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use TirielFirestoreOdmBundle\DependencyInjection\Compiler\AddCacheableManagersPass;

class TirielFirestoreOdmBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new AddCacheableManagersPass());
    }
}
