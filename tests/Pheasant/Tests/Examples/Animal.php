<?php

namespace Pheasant\Tests\Examples;

use Pheasant\DomainObject;
use Pheasant\Mapper\RowMapper;
use Pheasant\Types;

class Animal extends DomainObject
{
    public static function initialize($builder, $pheasant)
    {
        $pheasant
            ->register(__CLASS__, new RowMapper('animal'));

        $builder
            ->properties([
                'id' => new Types\IntegerType(11, 'primary auto_increment'),
                'type' => new Types\StringType(255, 'required default=llama'),
                'name' => new Types\StringType(255),
                'meta' => new Types\JsonType(),
            ]);
    }

    public static function scopes()
    {
        return [
            'frogs' => function ($chain) { return $chain->filter('type = ?', 'frog'); },
            'by_type' => function ($chain, $type) { return $chain->filter('type = ?', $type); },
        ];
    }
}
