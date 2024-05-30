<?php

namespace Tiriel\FirestoreOdmBundle\ValueResolver;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedLocator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Tiriel\FirestoreOdmBundle\Dto\Interface\PersistableDtoInterface;
use Tiriel\FirestoreOdmBundle\Manager\FirestoreDtoManager;

class FirestoreDtoValueResolver implements ValueResolverInterface
{
    public function __construct(
        #[TaggedLocator('firestore_odm.manager', indexAttribute: 'dto')]
        protected readonly ContainerInterface $locator,
    ) {
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $argType = $argument->getType();
        if (
            !$argType
            || !is_subclass_of($argType, PersistableDtoInterface::class)
        ) {
            return [];
        }

        $value = null;
        foreach ($request->attributes->keys() as $key) {
            if (property_exists($argType, $key)) {
                $value = $request->attributes->get($key);
            }
        }

        if (!is_string($value)) {
            return [];
        }

        /** @var FirestoreDtoManager $manager */
        $manager = $this->locator->get($argType);

        return [$manager->get($value)];
    }
}
