<?php

namespace Pheasant\Tests\Examples;

use Pheasant\DomainObject;
use Pheasant\Types;
use Pheasant\Types\StringType;

class UserPref extends DomainObject
{
    public function properties()
    {
        return [
            'userid' => new Types\IntegerType(13, 'primary'),
            'pref' => new StringType(),
            'value' => new StringType(),
        ];
    }

    public function relationships()
    {
        return [
            'User' => User::belongsTo('userid'),
        ];
    }
}
