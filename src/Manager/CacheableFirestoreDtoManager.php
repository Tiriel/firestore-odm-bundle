<?php

namespace App\Manager;

use Tiriel\FirestoreOdmBundle\Dto\Interface\PersistableDtoInterface;
use Tiriel\FirestoreOdmBundle\Manager\Interface\DtoManagerInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;
use Symfony\Component\DependencyInjection\Attribute\AutowireDecorated;

#[AsDecorator(FirestoreDtoManager::class)]
abstract class CacheableFirestoreDtoManager extends FirestoreDtoManager
{
    protected iterable $documents = [];

    protected array $searches = [];

    protected ?int $count = null;

    public function __construct(
        #[AutowireDecorated]
        protected readonly DtoManagerInterface $inner,
    ) {
    }

    public function get(string $id): ?PersistableDtoInterface
    {
        if (array_key_exists($id, $this->documents)) {
            return $this->documents[$id];
        }

        return $this->documents[$id] = $this->inner->get($id);
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

    public function getList(): iterable
    {
        if (\count($this->documents) === $this->count()) {
            return $this->documents;
        }

        foreach ($this->inner->getList() as $dto) {
            /** @var PersistableDtoInterface $dto */
            if (!isset($this->documents[$dto->getId()])) {
                $this->documents[$dto->getId()] = $dto;
            }
        }

        return $this->documents;
    }

    public function create(PersistableDtoInterface $dto): void
    {
        $this->inner->create($dto);

        $this->documents[$dto->getId()] = $dto;
    }

    public function update(PersistableDtoInterface $dto): void
    {
        $this->inner->update($dto);

        if (isset($this->documents[$dto->getId()])) {
            $this->documents[$dto->getId()] = $dto;
        }
    }

    public function remove(PersistableDtoInterface $dto): void
    {
        $this->inner->remove($dto);

        if (isset($this->documents[$dto->getId()])) {
            unset($this->documents[$dto->getId()]);
        }
    }

    public function count(): int
    {
        return $this->count ??= $this->inner->count();
    }
}
