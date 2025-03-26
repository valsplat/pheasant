<?php

namespace Pheasant\Tests\Examples;

use Pheasant\DomainObject;
use Pheasant\Types\SequenceType;

class Order extends DomainObject
{
    public function properties()
    {
        return [
            'id' => new SequenceType(),
        ];
    }
}
