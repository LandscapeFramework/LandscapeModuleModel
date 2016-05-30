<?php
namespace Test;

require('DatabaseModel.php');
use Landscape\DatabaseModel;
use \DateTime;

class Dataclass extends DatabaseModel
{
    public $keys = ["name" => "Landscape\TextField", "count" => "Landscape\NumberField", "time" => "Landscape\TimeField", "chk" => "Landscape\BoolField"];
}

class Linkclass extends DatabaseModel
{
    public $keys = ["name" => "Landscape\TextField", "link" => "Landscape\LinkField:Test\Dataclass"];
}

class DatabaseModelTest  extends \PHPUnit_Framework_TestCase
{
    public function testDatabase()
    {
        $now = new DateTime('now');
        $x = new Dataclass();
        $x->set('name', "myself");
        $x->set('count', 5);
        $x->set('time', $now);
        $x->set('chk', 1);
        $x->save();

        $y = new Linkclass();
        $y->set('name', "hey");
        $y->set('link', $x);
        $y->save();

        $id = $x->get('ID');
        $x = NULL;

        $x = new Dataclass('find', 'ID='.strval($id));
        $this->assertEquals($x->get('ID'), $id);
        $this->assertEquals($x->get('name'), "myself");
        $this->assertEquals($x->get('count'), 5);
        $this->assertEquals($x->get('time', true), $now);
        $this->assertEquals($x->get('time'), $now->getTimestamp());
        $this->assertEquals($x->get('chk', true), true);
        $this->assertEquals($x->get('chk'), 1);

        $x->set('name', 'other');
        $x->save();
        $x = NULL;

        $x = new Dataclass('find', 'ID='.strval($id));
        $this->assertEquals($x->get('ID'), $id);
        $this->assertEquals($x->get('name'), "other");
        $this->assertEquals($x->get('count'), 5);

        $list = $x->findRelatives("Test\Linkclass");
        $this->assertEquals(sizeof($list), 1);
        $this->assertEquals($list[0]->get('name'), "hey");

        $x = NULL;

        $x = new Dataclass('controler');
        $listall = $x->queryAll();
        $this->assertTrue(sizeof($listall) > 0);

        $y = $listall[0]; // get first item (should be the only one)
        $y->delete();

        $listall = $x->queryAll();
        $this->assertTrue(sizeof($listall) == 0);

    }
}
?>
