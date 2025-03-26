<?php

namespace Pheasant\Tests\Examples;

use Pheasant\DomainObject;
use Pheasant\Types\SequenceType;
use Pheasant\Types\StringType;

class User extends DomainObject
{
    public function properties()
    {
        return [
            'userid' => new SequenceType(),
            'firstname' => new StringType(),
            'lastname' => new StringType(),
            'group' => new StringType(),
        ];
    }

    public function relationships()
    {
        return [
            'UserPrefs' => UserPref::hasMany('userid'),
        ];
    }
}
