<?php

namespace Pheasant\Tests\Examples;

use Pheasant\DomainObject;
use Pheasant\Types\SequenceType;
use Pheasant\Types\StringType;

class SecretIdentity extends DomainObject
{
    public function properties()
    {
        return [
            'id' => new SequenceType(),
            'realname' => new StringType(),
        ];
    }

    public function relationships()
    {
        return [
            'Hero' => Hero::hasOne('id', 'identityid'),
        ];
    }
}
