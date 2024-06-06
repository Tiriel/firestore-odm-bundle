<?php

namespace Tiriel\FirestoreOdmBundle\Pagination;

use Google\Cloud\Firestore\Query;
use Tiriel\FirestoreOdmBundle\Pagination\Interface\PaginatorInterface;
use Traversable;

abstract class Paginator implements PaginatorInterface
{
    protected iterable $elements = [];

    protected ?int $count = null;

    public function __construct(
        protected readonly Query $query,
        protected readonly int $maxResults,
    )
    {
    }

    public function getIterator(): Traversable
    {
        $this->prepareQuery();

        $this->elements = $this->query->documents();
        $this->count = \count((array) $this->elements);

        $this->prepareIterator();

        return new \ArrayIterator((array) $this->elements);
    }

    /**
     * @inheritDoc
     */
    public function count(): int
    {
        if (null === $this->count) {
            $this->prepareQuery();
            $this->count = $this->query->count();
        }

        return $this->count();
    }

    abstract protected function prepareIterator(): void;

    abstract protected function prepareQuery(): void;
}
