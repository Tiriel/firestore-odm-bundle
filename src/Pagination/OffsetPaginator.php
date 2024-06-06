<?php

namespace Tiriel\FirestoreOdmBundle\Pagination;

use Google\Cloud\Firestore\Query;
use Tiriel\FirestoreOdmBundle\Manager\Interface\DtoManagerInterface;

class OffsetPaginator extends Paginator
{
    public function __construct(
        Query $manager,
        int $maxResults,
        protected int $page = 1,
    ) {
        parent::__construct($manager, $maxResults);
    }

    protected function prepareIterator(): void
    {
        $this->page++;
    }

    protected function prepareQuery(): void
    {
        $this->query
            ->limit($this->maxResults)
            ->offset($this->maxResults * ($this->page - 1));
    }
}
