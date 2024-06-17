<?php

namespace Tiriel\FirestoreOdmBundle\Manager\Interface;

use Tiriel\FirestoreOdmBundle\Pagination\Interface\PaginatorInterface;
use Tiriel\FirestoreOdmBundle\Dto\Interface\PersistableDtoInterface;
use Tiriel\FirestoreOdmBundle\Enum\OrderDir;
use Tiriel\FirestoreOdmBundle\Exception\EntryNotFoundFirestoreException;
use Tiriel\FirestoreOdmBundle\Exception\NonUniqueEntryFirestoreException;

interface DtoManagerInterface
{
    /**
     * Returns a single DTO matching the given $id
     *
     * @throws EntryNotFoundFirestoreException is the given id is not found
     */
    public function get(string $id, array $options = []): ?PersistableDtoInterface;

    /**
     * @return iterable the DTOs matching the given criteria
     * @param array $criteria
     * Can be a single array matching Google SDK's `where` method,
     * or an array of arrays:
     *
     * $criteria = ['name', '=', 'foo']
     * or
     * $criteria = [
     *      ['name', '=', 'foo'],
     *      ['createdAt', '>=', '01-01-1970'],
     *  ]
     */
    public function search(array $criteria): iterable;

    /**
     * @return iterable the full list of documents from the collection
     */
    public function getList(array $options = []): iterable;

    /**
     * @param int $limit the number of documents to include
     * @param int $page used to calculate the offset, times the limit
     * @return PaginatorInterface a paginated list of documents from the collection
     */
    public function getPaginatedList(int $limit, int $page = 1): PaginatorInterface;

    /**
     * @param int $limit the number of documents to include
     * @param string|null $startAfterId the last id of the previous result set
     * @return PaginatorInterface a paginated list of documents from the collection
     */
    public function getCursoredList(int $limit, ?string $startAfterId = null): PaginatorInterface;

    /**
     * Persists a new entry in Firestore and generates a new id
     * (Uuid v7 as of now)
     *
     * @throws NonUniqueEntryFirestoreException if the generated Uuid is not unique
     */
    public function create(PersistableDtoInterface $dto): void;

    /**
     * @param PersistableDtoInterface $dto to be updated in the collection
     * @throws EntryNotFoundFirestoreException if the DTO's id doesn't exist in the collection
     */
    public function update(PersistableDtoInterface $dto): void;

    /**
     * @param PersistableDtoInterface $dto to be removed from the collection
     * @throws EntryNotFoundFirestoreException if the DTO's id doesn't exist in the collection
     */
    public function remove(PersistableDtoInterface $dto): void;

    /**
     * @return int the full count of documents in the collection
     */
    public function count(): int;

    /**
     * @return string the classname of the DTO associated to this manager
     */
    public function getClass(): string;
}
