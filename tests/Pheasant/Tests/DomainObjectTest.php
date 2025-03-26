<?php

namespace Pheasant\Tests;

use Pheasant\Tests\Examples\Animal;
use Pheasant\Tests\Examples\AnimalWithNameDefault;
use Pheasant\Tests\Examples\AnotherAnimal;
use Pheasant\Tests\Examples\Order;

class DomainObjectTest extends MysqlTestCase
{
    public function setUp()
    {
        parent::setUp();

        $migrator = new \Pheasant\Migrate\Migrator();
        $migrator
            ->destroy(Animal::schema())
            ->initialize(Animal::schema())
            ->destroy(Order::schema())
            ->initialize(Order::schema())
        ;
    }

    public function testLoad()
    {
        $animal = new Animal();
        $animal->load([
            'type' => 'walrus',
            'name' => 'Frank the Walrus',
        ]);

        $this->assertEquals($animal->type, 'walrus');
        $this->assertEquals($animal->name, 'Frank the Walrus');
    }

    public function testFilteredLoad()
    {
        $animal = new Animal();
        $animal->load([
            'type' => 'walrus',
            'name' => 'Frank the Walrus',
            'inject' => 'Bobby tables; DROP ALL TABLES',
        ], ['type', 'name']);

        $this->assertCount(2, $animal->changes());
        $this->assertEquals($animal->type, 'walrus');
        $this->assertEquals($animal->name, 'Frank the Walrus');
        $this->assertFalse(isset($animal->inject));
    }

    public function testDefaultProperties()
    {
        $animal = new Animal();
        $this->assertEquals($animal->type, 'llama');
        $this->assertEquals($animal->toArray(),
            ['id' => null, 'type' => 'llama', 'name' => null, 'meta' => null]);

        $llama = new Animal(['type' => 'llama']);
        $frog = new Animal(['type' => 'frog']);

        $this->assertTrue($llama->equals($animal));
        $this->assertFalse($llama->equals($frog));
    }

    public function testImportUsesDefaultProperties()
    {
        $animals = Animal::import([
            ['name' => 'Larry Llama'],
        ]);

        $this->assertEquals('llama', $animals[0]->type);
    }

    public function testPropertyIsset()
    {
        $animal = new Animal(['name' => 'bob']);

        $this->assertTrue(isset($animal->type));
        $this->assertTrue(isset($animal->name));

        $this->assertFalse(isset($animal->unknown));
    }

    /**
     * @expectedException \Pheasant\Exception
     */
    public function testGettingUnknownProperty()
    {
        $animal = Animal::import([['type' => 'Hippo']]);
        $animal[0]->unknownKey;
    }

    /**
     * @expectedException \Pheasant\Exception
     */
    public function testSavingUnknownProperty()
    {
        // try non-saved objects
        $another = new Animal();
        $another->unknown;
        $instance->save();
    }

    public function testInitializeDefaults()
    {
        $animal = new AnotherAnimal();
        $animal->type = 'llama';
        $animal->save();

        $this->assertEquals($animal->type, 'llama');
        $this->assertEquals($animal->tableName(), 'animal');
    }

    public function testCountIsConsistent()
    {
        $animal = Animal::import([
            ['type' => 'Hippo'],
            ['type' => 'Cat'],
            ['type' => 'Llama'],
            ['type' => 'Raptor'],
        ]);

        $awesome = Animal::find("type = 'Cat' or type = 'Llama'");
        $this->assertEquals($awesome->count(), 2);

        $scary = Animal::find('type = ?', 'Raptor');
        $this->assertEquals($scary->count(), 1);
        $this->assertEquals($awesome->count(), 2);
        $this->assertEquals($awesome[1]->type, 'Llama');
        $this->assertEquals($scary[0]->type, 'Raptor');
        $this->assertEquals($awesome[0]->type, 'Cat');
    }

    public function testIssue11DefaultValuesArePersistedInDatabase()
    {
        $animal = new AnimalWithNameDefault(['type' => 'horse']);

        $this->assertEquals($animal->name, 'blargh');
        $animal->save();

        $this->assertRowCount(1, $this->connection()->table('animal')->query([
            'id' => $animal->id,
            'type' => 'horse',
            'name' => 'blargh',
        ]));

        $horse = AnimalWithNameDefault::byId(1);
        $this->assertEquals($horse->name, 'blargh');
    }

    public function testObjectTransaction()
    {
        $animal = new Animal(['type' => 'frog']);

        $animal->transaction(function ($animal) {
            $animal->save();
        });

        $this->assertCount(1, Animal::findByType('frog'));
    }

    public function testObjectTransactionNotExecuting()
    {
        $this->assertCount(0, Animal::findByType('llama'));

        $t = \Pheasant::transaction(function () {
            $animal = new Animal(['type' => 'llama']);
            $animal->save();
        }, false);

        $this->assertCount(0, Animal::findByType('llama'));

        $t->execute();
        $this->assertCount(1, Animal::findByType('llama'));
    }

    public function testReloadWithoutClosure()
    {
        $llama = Animal::create(['type' => 'llama']);

        // update data in background
        $this->connection()->execute('UPDATE animal SET name="Frank" WHERE id=1');

        $this->assertEquals(null, $llama->name);
        $llama->reload();

        $this->assertEquals('Frank', $llama->name);
    }

    public function testIssetWithBooleanValues()
    {
        $llama = Animal::create(['name' => false]);

        $this->assertTrue(isset($llama->name));
    }

    public function testArrayAccess()
    {
        $llama = Animal::create(['name' => 'Frank']);
        $this->assertTrue(isset($llama['name']));
        $this->assertEquals('Frank', $llama['name']);

        $llama = Animal::create(['name' => null]);
        $this->assertTrue(isset($llama['name']));
        $this->assertNull($llama['name']);

        $llama['name'] = 'Joe';
        $this->assertEquals('Joe', $llama['name']);
    }

    public function testSettingTheSameValueDoesntTriggerChanged()
    {
        $animal = new Animal(['type' => 'horse']);
        $animal->save();
        $animal->load(['type' => 'horse']);
        $this->assertCount(0, $animal->changes());
    }

    public function testChanged()
    {
        $animal = new Animal(['type' => 'horse']);
        $animal->save();
        $animal->load(['type' => 'frog']);
        $this->assertEquals(['type' => 'frog'], $animal->changes());
    }

    public function testStringCoercion()
    {
        $llama = Animal::create(['id' => 123]);
        $this->assertEquals('Pheasant\Tests\Examples\Animal[id=123]', (string) $llama);
    }

    public function testOverridenProperties()
    {
        $counter = 0;
        $llama = Animal::create(['id' => 123]);
        $llama->override('type', function () use (&$counter) {
            ++$counter;

            return 'llama' . $counter;
        });

        $this->assertEquals('llama1', $llama->type);
        $this->assertEquals('llama2', $llama->type);
        $this->assertEquals('llama3', $llama->type);
    }

    public function testDomainObjectsWithReservedNames()
    {
        $order = Order::create(['id' => 1]);
        $this->assertNotNull($order);
        $this->assertEquals(1, Order::all()->count());
    }
}
