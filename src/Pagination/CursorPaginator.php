<?php

namespace Tiriel\FirestoreOdmBundle\Pagination;

use Google\Cloud\Firestore\Query;

class CursorPaginator extends Paginator
{
    public function __construct(
        Query $manager,
        int $maxResults,
        protected int|string|null $startAfterId = null,
        protected array $options = []
    ) {
        parent::__construct($manager, $maxResults);
    }

    public function prepareIterator(): void
    {
        $this->startAfterId = end($this->elements)?->getId();
        reset($this->elements);
    }

    protected function prepareQuery(): void
    {
        $this->query->limit($this->maxResults);
        if (null !== $this->startAfterId) {
            $this->query->startAfter([$this->startAfterId]);
        }
    }
}
