<?php

namespace Tiriel\FirestoreOdmBundle\Query;

use Google\Cloud\Core\Iterator\ItemIterator;
use Google\Cloud\Core\Iterator\PageIterator;
use Google\Cloud\Firestore\CollectionReference;
use Google\Cloud\Firestore\DocumentReference;
use Google\Cloud\Firestore\DocumentSnapshot;
use Google\Cloud\Firestore\Query;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;
use Tiriel\FirestoreOdmBundle\Dto\Interface\PersistableDtoInterface;
use Tiriel\FirestoreOdmBundle\Exception\EntryNotFoundFirestoreException;

class HydratorCollectionReference extends CollectionReference
{
    public function __construct(
        protected Query $inner,
        protected readonly NormalizerInterface&DenormalizerInterface $normalizer,
        protected readonly string $className,
    )
    {
    }

    public function documents(array $options = []): array
    {
        $docs = $this->inner->documents($options)->getIterator()->getArrayCopy();

        return $this->normalizer->denormalize(
            array_map(fn(DocumentSnapshot $doc) => $doc->data(), $docs),
            $this->className.'[]',
            'array',
            $options,
        );
    }

    public function document($documentId, array $options = []): PersistableDtoInterface
    {
        $doc = $this->inner->document($documentId)->snapshot();

        if (!$doc->exists()) {
            throw new EntryNotFoundFirestoreException($documentId, $this->className);
        }

        return $this->normalizer->denormalize(
            $doc->data(),
            $this->className,
            null,
            $options
        );
    }

    public function exists(int|string $documentId): bool
    {
        return $this->inner->document($documentId)->snapshot()->exists();
    }

    public function delete(int|string $documentId): void
    {
        $this->inner->document($documentId)->delete();
    }

    public function set(PersistableDtoInterface $dto): void
    {
        $this->inner->document($dto->getId())->set($this->normalizer->normalize($dto, 'array'));
    }

    public function name(): string
    {
        return $this->inner->name();
    }

    public function path(): string
    {
        return $this->inner->path();
    }

    public function id(): string
    {
        return $this->inner->id();
    }

    public function newDocument(): PersistableDtoInterface
    {
        $doc = $this->inner->document(Uuid::v7());

        if ($doc->snapshot()->exists()) {
            $doc = $this->inner->document(Uuid::v7());
        }

        return $this->normalizer->denormalize(
            $doc->snapshot()->data(),
            $this->className
        );
    }

    public function add(array $fields = [], array $options = []): PersistableDtoInterface
    {
        $dto = $this->newDocument();
        $access = PropertyAccess::createPropertyAccessor();

        foreach ($fields as $field => $value) {
            $access->setValue($dto, $field, $value);
        }

        $this->set($dto);

        return $dto;
    }

    public function listDocuments(array $options = []): \Traversable
    {
        return new ItemIterator(
            new PageIterator(
                fn(DocumentReference $doc) => $this->normalizer->denormalize($doc->snapshot()->data(), $this->className),
                [$this->inner, 'listDocuments'],
                $options,
                ['resultLimit' => $options['resultLimit']]
            ),
        );
    }

    public function parent(): DocumentReference
    {
        return $this->inner->parent();
    }

    public function count(array $options = [])
    {
        return $this->inner->count($options);
    }

    public function sum(string $field, array $options = [])
    {
        return $this->inner->sum($field, $options);
    }

    public function avg(string $field, array $options = [])
    {
        return $this->inner->avg($field, $options);
    }

    public function addAggregation($aggregate)
    {
        return $this->inner->addAggregation($aggregate);
    }

    public function select(array $fieldPaths)
    {
        $this->inner = $this->inner->select($fieldPaths);

        return $this;
    }

    public function where($fieldPath, $operator = null, $value = null)
    {
        $this->inner = $this->inner->where($fieldPath, $operator, $value);

        return $this;
    }

    public function orderBy($fieldPath, $direction = self::DIR_ASCENDING)
    {
        $this->inner = $this->inner->orderBy($fieldPath, $direction);

        return $this;
    }

    public function limit($number)
    {
        $this->inner = $this->inner->limit($number);

        return $this;
    }

    public function limitToLast($number)
    {
        $this->inner = $this->inner->limitToLast($number);

        return $this;
    }

    public function offset($number)
    {
        $this->inner = $this->inner->offset($number);

        return $this;
    }

    public function startAt($fieldValues)
    {
        $this->inner = $this->inner->startAt($fieldValues);

        return $this;
    }

    public function startAfter($fieldValues)
    {
        $this->inner = $this->inner->startAfter($fieldValues);

        return $this;
    }

    public function endBefore($fieldValues)
    {
        $this->inner = $this->inner->endBefore($fieldValues);

        return $this;
    }

    public function endAt($fieldValues)
    {
        $this->inner = $this->inner->endAt($fieldValues);

        return $this;
    }

    public function queryHas($key)
    {
        return $this->inner->queryHas($key);
    }

    public function queryKey($key)
    {
        return $this->inner->queryKey($key);
    }
}
