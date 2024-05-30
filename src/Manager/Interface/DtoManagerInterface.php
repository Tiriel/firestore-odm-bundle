<?php

namespace Tiriel\FirestoreOdmBundle\Manager\Interface;

use Tiriel\FirestoreOdmBundle\Dto\Interface\PersistableDtoInterface;

interface DtoManagerInterface
{
    public function get(string $id): ?PersistableDtoInterface;

    public function search(array $criteria): iterable;

    public function getList(): iterable;

    public function create(PersistableDtoInterface $dto): void;

    public function update(PersistableDtoInterface $dto): void;

    public function remove(PersistableDtoInterface $dto): void;

    public function count(): int;

    public function getClass(): string;
}
