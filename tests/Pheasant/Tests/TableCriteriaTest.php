<?php

namespace Pheasant\Tests;

use Pheasant;
use Pheasant\Types;

class TableCriteriaTest extends MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->table = $this->table('user', [
            'userid' => new Types\IntegerType(8, 'primary auto_increment'),
            'firstname' => new Types\StringType(),
            'lastname' => new Types\StringType(),
        ]);

        $this->table->insert(['firstname' => 'Llama', 'lastname' => 'Herder']);
        $this->table->insert(['firstname' => 'Action', 'lastname' => 'Hero']);
        $this->assertRowCount(2, 'select * from user');
    }

    public function testWhereWithBindParameters()
    {
        $criteria = $this->table->where('firstname=?', 'Llama');

        $this->assertEquals("(firstname='Llama')", (string) $criteria);
        $this->assertEquals($criteria->count(), 1);
    }

    public function testWhereWithArray()
    {
        $criteria = $this->table->where(['firstname' => 'Llama']);
        $this->assertEquals("(`firstname`='Llama')", $criteria->toSql());
        $this->assertEquals(1, $criteria->count(), 1);
    }

    public function testWhereWithCriteria()
    {
        $criteria = $this->table->where(new Pheasant\Query\Criteria(['firstname' => 'Llama']));
        $this->assertEquals("(`firstname`='Llama')", $criteria->toSql());
        $this->assertEquals(1, $criteria->count());
    }

    public function testUpdateByCriteria()
    {
        $criteria = $this->table->where('firstname=?', 'Llama')->update(['firstname' => 'Alpaca']);
        $this->assertRowCount(1, "select * from user where `firstname`='Alpaca'");
    }

    public function testDeleteByCriteria()
    {
        $criteria = $this->table->where('firstname=?', 'Llama')->delete();
        $this->assertRowCount(0, "select * from user where firstname='Llamas'");
    }
}
