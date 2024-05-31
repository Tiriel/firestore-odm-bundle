<?php

namespace Tiriel\FirestoreOdmBundle\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;
use Tiriel\FirestoreOdmBundle\Dto\Interface\PersistableDtoInterface;

class DtoIdNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function __construct(
        private NormalizerInterface&DenormalizerInterface $normalizer,
    ) {
    }

    public function normalize($object, ?string $format = null, array $context = []): array
    {
        return $this->normalizer->normalize($object, $format, $context);
    }

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        return is_subclass_of($data, PersistableDtoInterface::class);
    }

    public function getSupportedTypes(?string $format): array
    {
        return [PersistableDtoInterface::class => true];
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $setter = \Closure::fromCallable(fn($id) => $this->id = Uuid::fromString($id));

        $dto = $this->normalizer->denormalize($data, $type, $format, $context);
        $setter->call($dto, $data['id']);

        return $dto;
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return is_array($data) && array_key_exists('id', $data) && is_subclass_of($type, PersistableDtoInterface::class);
    }
}
