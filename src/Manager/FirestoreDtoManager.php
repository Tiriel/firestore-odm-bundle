<?php

namespace Tiriel\FirestoreOdmBundle\Manager;

use Symfony\Component\Uid\Uuid;
use Tiriel\FirestoreOdmBundle\Dto\Interface\PersistableDtoInterface;
use Tiriel\FirestoreOdmBundle\Exception\EntryNotFoundFirestoreException;
use Tiriel\FirestoreOdmBundle\Exception\NonUniqueEntryFirestoreException;
use Tiriel\FirestoreOdmBundle\Manager\Interface\DtoManagerInterface;
use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Kreait\Firebase\Contract\Firestore;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

abstract class FirestoreDtoManager implements DtoManagerInterface
{
    protected CollectionReference $collection;

    public function __construct(
        protected NormalizerInterface&DenormalizerInterface $normalizer,
        Firestore $firestore
    ) {
        $this->collection = $firestore->database()->collection($this->getClass());
    }

    public function get(string $id): ?PersistableDtoInterface
    {
        $doc = $this->collection->document($id)->snapshot();

        if (!$doc->exists()) {
            throw new EntryNotFoundFirestoreException($id, $this->getClass());
        }

        return $this->normalizer->denormalize(
            $doc->data(),
            static::getClass(),
        );
    }

    public function search(array $criteria): iterable
    {
        $query = $this->collection;
        if (!is_array(current($criteria))) {
            $criteria = [$criteria];
        }

        foreach ($criteria as $criterion) {
            if (3 !== \count($criterion)) {
                continue;
            }
            [$path, $operator, $value] = $criterion;
            $query = $query->where($path, $operator, $value);
        }

        $docs = $query->documents()->getIterator()->getArrayCopy();

        return $this->normalizer->denormalize(
            array_map(fn(DocumentSnapshot $doc) => $doc->data(), $docs),
            static::getClass().'[]'
        );
    }

    public function getList(): iterable
    {
        $docs = $this->collection->documents()->getIterator()->getArrayCopy();

        return $this->normalizer->denormalize(
            array_map(fn(DocumentSnapshot $doc) => $doc->data(), $docs),
            static::getClass().'[]',
            'array'
        );
    }

    public function create(PersistableDtoInterface $dto): void
    {
        $setId = \Closure::fromCallable(fn() => $this->id = Uuid::v7());
        $setId->call($dto);

        if ($this->collection->document($dto->getId())->snapshot()->exists()) {
            throw new NonUniqueEntryFirestoreException($dto->getId(), $this->getClass());
        }

        $this->collection->document($dto->getId())->set($this->normalizer->normalize($dto, 'array'));
    }

    public function update(PersistableDtoInterface $dto): void
    {
        if (!$this->collection->document($dto->getId())->snapshot()->exists()) {
            throw new EntryNotFoundFirestoreException($dto->getId(), $this->getClass());
        }

        $this->collection->document($dto->getId())->set($this->normalizer->normalize($dto, 'array'));
    }

    public function remove(PersistableDtoInterface $dto): void
    {
        if (!$this->collection->document($dto->getId())->snapshot()->exists()) {
            throw new EntryNotFoundFirestoreException($dto->getId(), $this->getClass());
        }

        $this->collection->document($dto->getId())->delete();
    }

    public function count(): int
    {
        return $this->collection->count();
    }

    abstract public function getClass(): string;
}
