<?php

namespace Pheasant\Tests;

use Pheasant\DomainObject;
use Pheasant\Mapper;
use Pheasant\Types;

class DiffTest extends MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        // set up a domain object
        $this->initialize('Pheasant\DomainObject', function ($builder, $pheasant) {
            $builder->properties([
                'id' => new Types\SequenceType(),
                'type' => new Types\StringType(128),
                'isllama' => new Types\BooleanType(['default' => true]),
                'timecreated' => new Types\DateTimeType(),
                'unixtime' => new Types\UnixTimestampType(),
            ]);

            $pheasant->register(DomainObject::className(), new Mapper\RowMapper('llamas'));
        });

        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator->destroy(DomainObject::schema())->initialize(DomainObject::schema());
    }

    public function testObjectEqualsItsself()
    {
        $o = DomainObject::create([]);
        $this->assertTrue($o->equals($o));
    }

    public function testTimestampsAreEqual()
    {
        $t = '1981-09-24';

        $o1 = new DomainObject([
            'timecreated' => new \DateTime($t), 'unixtime' => new \DateTime($t),
        ]);
        $o2 = new DomainObject([
            'timecreated' => new \DateTime($t), 'unixtime' => new \DateTime($t),
        ]);

        $this->assertTrue($o1->equals($o2));
    }

    public function testDiff()
    {
        $o1 = new DomainObject(['type' => 'cat']);
        $o2 = new DomainObject(['type' => 'hippo']);

        $this->assertEquals(['type'], $o1->diff($o2));
    }

    public function testDiffWithObjects()
    {
        $o1 = new DomainObject([
            'timecreated' => new \DateTime('2001-01-01'), 'unixtime' => new \DateTime('1981-09-24'),
        ]);
        $o2 = new DomainObject([
            'timecreated' => new \DateTime('2001-01-01'), 'unixtime' => new \DateTime('2022-11-03'),
        ]);

        $this->assertEquals(['unixtime'], $o1->diff($o2));
    }
}
