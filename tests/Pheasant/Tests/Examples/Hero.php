<?php

namespace Pheasant\Tests\Examples;

use Pheasant\DomainObject;
use Pheasant\Types\IntegerType;
use Pheasant\Types\SequenceType;
use Pheasant\Types\StringType;

class Hero extends DomainObject
{
    public function properties()
    {
        return [
            'id' => new SequenceType(),
            'alias' => new StringType(),
            'identityid' => new IntegerType(),
        ];
    }

    public function relationships()
    {
        return [
            'Powers' => Power::hasMany('id', 'heroid'),
            'SecretIdentity' => SecretIdentity::belongsTo('identityid', 'id', true),
        ];
    }

    public static function createHelper($alias, $identity, $powers = [])
    {
        $hero = new Hero(['alias' => $alias]);
        $hero->save();

        $identity = new SecretIdentity(['realname' => $identity]);
        $hero->SecretIdentity = $identity;
        $identity->save();

        foreach ($powers as $power) {
            $power = new Power(['description' => $power]);
            $hero->Powers[] = $power;
            $power->save();
        }

        $hero->save();

        return $hero;
    }
}
