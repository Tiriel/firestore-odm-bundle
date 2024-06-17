<?php

namespace Tiriel\FirestoreOdmBundle\Pagination;

use Google\Cloud\Firestore\Query;

class OffsetPaginator extends Paginator
{
    protected int $page;

    public function __construct(
        Query $manager,
        int $maxResults,
        int $page = 1,
        array $options = [],
    ) {
        parent::__construct($manager, $maxResults, $options);
        $this->page = $page;
    }

    protected function prepareIterator(): void
    {
        $this->page++;
    }

    protected function prepareQuery(): void
    {
        $this->query = $this->query
            ->limit($this->maxResults)
            ->offset($this->maxResults * ($this->page - 1));
    }
}
