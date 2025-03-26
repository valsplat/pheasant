<?php

namespace Pheasant\Tests;

use Pheasant\Tests\Examples\Person;
use Pheasant\Types\SequenceType;
use Pheasant\Types\StringType;

class DomainObjectSequenceTest extends MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $table = $this->table('person', [
            'personid' => new SequenceType(),
            'name' => new StringType(),
        ]);
    }

    public function testSequencePrimaryKey()
    {
        $person = new Person();
        $person->save();

        $this->assertEquals(1, $person->personid);

        $person->name = 'Frank';
        $person->save();

        $this->assertEquals(1, $person->personid);
        $this->assertEquals('Frank', $person->name);
    }

    public function testSequenceFaileWhenManuallySet()
    {
        $person = new Person();
        $person->personid = 24;
        $person->save();

        // FIXME: is this desired behaviour?
        $this->assertEquals(1, $person->personid);
    }
}
