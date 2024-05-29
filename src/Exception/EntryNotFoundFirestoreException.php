<?php

namespace Tiriel\FirestoreOdmBundle\Exception;

class EntryNotFoundFirestoreException extends FirestoreException
{
    public function __construct(string $id, string $collection)
    {
        parent::__construct("The entry with provided id was not found in the collection", [
            'id' => $id,
            'collection' => $collection,
        ]);
    }
}
