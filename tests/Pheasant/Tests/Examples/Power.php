<?php

namespace Pheasant\Tests\Examples;

use Pheasant\DomainObject;
use Pheasant\Types\IntegerType;
use Pheasant\Types\SequenceType;
use Pheasant\Types\StringType;

class Power extends DomainObject
{
    public function properties()
    {
        return [
            'id' => new SequenceType(),
            'description' => new StringType(),
            'heroid' => new IntegerType(),
        ];
    }

    public function relationships()
    {
        return [
            'Hero' => Hero::belongsTo('heroid', 'id'),
        ];
    }
}
