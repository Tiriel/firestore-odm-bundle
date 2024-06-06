<?php

namespace Tiriel\FirestoreOdmBundle\Enum;

enum OrderDir: string
{
    case Asc = 'ASC';
    case Desc = 'DESC';

    public function label(): string
    {
        return match ($this) {
            self::Asc => 'ASC',
            self::Desc => 'DESC',
        };
    }
}
