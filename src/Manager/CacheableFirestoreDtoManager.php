<?php

namespace Tiriel\FirestoreOdmBundle\Manager;

use Tiriel\FirestoreOdmBundle\Dto\Interface\PersistableDtoInterface;
use Tiriel\FirestoreOdmBundle\Manager\Interface\DtoManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;
use Tiriel\FirestoreOdmBundle\Pagination\Interface\PaginatorInterface;

class CacheableFirestoreDtoManager implements DtoManagerInterface
{
    protected iterable $documents = [];

    protected array $searches = [];

    protected ?int $count = null;

    public function __construct(
        protected readonly DtoManagerInterface $inner,
    ) {
    }

    public function get(string $id, array $options = []): ?PersistableDtoInterface
    {
        if (array_key_exists($id, $this->documents)) {
            return $this->documents[$id];
        }

        return $this->documents[$id] = $this->inner->get($id, $options);
    }

    public function search(array $criteria): iterable
    {
        $key = serialize($criteria);
        if (array_key_exists($key, $this->searches)) {
            return array_filter(
                $this->documents,
                fn(PersistableDtoInterface $dto) => array_key_exists($dto->getId(), $this->searches[$key])
            );
        }

        foreach ($results = $this->inner->search($criteria) as $dto) {
            /** @var PersistableDtoInterface $dto */
            $this->searches[$key][] = $dto->getId();
        }

        return $results;
    }

    public function getList(array $options = []): iterable
    {
        if (\count($this->documents) === $this->count()) {
            return $this->documents;
        }

        foreach ($this->inner->getList($options) as $dto) {
            /** @var PersistableDtoInterface $dto */
            if (!isset($this->documents[(string) $dto->getId()])) {
                $this->documents[(string) $dto->getId()] = $dto;
            }
        }

        return $this->documents;
    }

    public function getPaginatedList(int $limit, int $page = 1, array $options = []): PaginatorInterface
    {
        return $this->inner->getPaginatedList($limit, $page, $options);
    }

    public function getCursoredList(int $limit, ?string $startAfterId = null, array $options = []): PaginatorInterface
    {
        return $this->inner->getPaginatedList($limit, $startAfterId, $options);
    }

    public function create(PersistableDtoInterface $dto): void
    {
        $this->inner->create($dto);

        $this->documents[(string) $dto->getId()] = $dto;
    }

    public function update(PersistableDtoInterface $dto): void
    {
        $this->inner->update($dto);

        if (isset($this->documents[$dto->getId()])) {
            $this->documents[(string) $dto->getId()] = $dto;
        }
    }

    public function remove(PersistableDtoInterface $dto): void
    {
        $this->inner->remove($dto);

        if (isset($this->documents[(string) $dto->getId()])) {
            unset($this->documents[(string) $dto->getId()]);
        }
    }

    public function count(): int
    {
        return $this->count ??= $this->inner->count();
    }

    public function getClass(): string
    {
        return $this->inner->getClass();
    }
}
