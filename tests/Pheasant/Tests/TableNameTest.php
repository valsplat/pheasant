<?php

namespace Pheasant\Tests;

use Pheasant\Database\Mysqli\TableName;

class TableNameTest extends MysqlTestCase
{
    public function testParsingAFullyQualifiedTableName()
    {
        $tablename = new TableName('mydatabase.llamas');

        $this->assertEquals('mydatabase', $tablename->database);
        $this->assertEquals('llamas', $tablename->table);
        $this->assertEquals('mydatabase.llamas', (string) $tablename);
        $this->assertEquals('`mydatabase`.`llamas`', $tablename->quoted());
    }

    public function testParsingATableName()
    {
        $tablename = new TableName('llamas');

        $this->assertEquals(null, $tablename->database);
        $this->assertEquals('llamas', $tablename->table);
        $this->assertEquals('llamas', (string) $tablename);
        $this->assertEquals('`llamas`', $tablename->quoted());
    }
}
