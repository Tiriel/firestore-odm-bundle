<?php

namespace Tiriel\FirestoreOdmBundle\Exception;

class MalformedCriteriaFirestoreException extends FirestoreException
{
    public function __construct(array $criteria)
    {
        parent::__construct("The given criteria does not match specifications", ['criteria' => $criteria]);
    }
}
