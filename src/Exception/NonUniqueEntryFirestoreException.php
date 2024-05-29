<?php

namespace Tiriel\FirestoreOdmBundle\Exception;

class NonUniqueEntryFirestoreException extends FirestoreException
{
    public function __construct(string $id, string $collection)
    {
        parent::__construct("The provided identifier already exists in collection", [
            'id' => $id,
            'collection' => $collection,
        ]);
    }
}
