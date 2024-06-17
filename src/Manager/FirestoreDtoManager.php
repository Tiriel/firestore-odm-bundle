<?php

namespace Tiriel\FirestoreOdmBundle\Manager;

use Google\Cloud\Firestore\FirestoreClient;
use Google\Cloud\Firestore\Query;
use Symfony\Component\Uid\Uuid;
use Tiriel\FirestoreOdmBundle\Pagination\CursorPaginator;
use Tiriel\FirestoreOdmBundle\Pagination\Interface\PaginatorInterface;
use Tiriel\FirestoreOdmBundle\Dto\Interface\PersistableDtoInterface;
use Tiriel\FirestoreOdmBundle\Exception\EntryNotFoundFirestoreException;
use Tiriel\FirestoreOdmBundle\Exception\NonUniqueEntryFirestoreException;
use Tiriel\FirestoreOdmBundle\Manager\Interface\DtoManagerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tiriel\FirestoreOdmBundle\Pagination\OffsetPaginator;
use Tiriel\FirestoreOdmBundle\Query\HydratorCollectionReference;

abstract class FirestoreDtoManager implements DtoManagerInterface
{
    public const DTO_CLASS = 'REPLACE_ME';

    protected Query $collection;

    public function __construct(
        protected NormalizerInterface&DenormalizerInterface $normalizer,
        FirestoreClient $firestoreClient
    ) {
        $this->collection = new HydratorCollectionReference(
            $firestoreClient->collection($this->getClass()),
            $this->normalizer,
            static::getClass()
        );
    }

    public function get(string $id, array $options = []): ?PersistableDtoInterface
    {
        return $this->collection->document($id, $options);
    }

    public function search(array $criteria): iterable
    {
        if (!is_array(current($criteria))) {
            $criteria = [$criteria];
        }

        foreach ($criteria as $criterion) {
            if (3 !== \count($criterion)) {
                continue;
            }
            [$path, $operator, $value] = $criterion;
            $this->collection->where($path, $operator, $value);
        }

        return $this->collection->documents();
    }

    public function getList(array $options = []): iterable
    {
        return $this->collection->documents($options);
    }

    public function getPaginatedList(int $limit, int $page = 1, array $options = []): PaginatorInterface
    {
        return new OffsetPaginator($this->collection, $limit, $page, $options);
    }

    public function getCursoredList(int $limit, int|string|null $startAfterId = null, array $options = []): PaginatorInterface
    {
        return new CursorPaginator($this->collection, $limit, $startAfterId, $options);
    }

    public function create(PersistableDtoInterface $dto): void
    {
        $setId = (fn() => $this->id = Uuid::v7())(...);
        $setId->call($dto);

        if ($this->collection->exists($dto->getId())) {
            throw new NonUniqueEntryFirestoreException($dto->getId(), $this->getClass());
        }

        $this->collection->set($dto);
    }

    public function update(PersistableDtoInterface $dto): void
    {
        if (!$this->collection->exists($dto->getId())) {
            throw new EntryNotFoundFirestoreException($dto->getId(), $this->getClass());
        }

        $this->collection->set($dto);
    }

    public function remove(PersistableDtoInterface $dto): void
    {
        if (!$this->collection->exists($dto->getId())) {
            throw new EntryNotFoundFirestoreException($dto->getId(), $this->getClass());
        }

        $this->collection->delete($dto->getId());
    }

    public function count(): int
    {
        return $this->collection->count();
    }

    public function getClass(): string
    {
        return static::DTO_CLASS;
    }
}
