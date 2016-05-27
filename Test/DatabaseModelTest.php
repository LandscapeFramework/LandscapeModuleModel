<?php
namespace Test;

require('DatabaseModel.php');
use Landscape\DatabaseModel;

class Dataclass extends DatabaseModel
{
    public $keys = ["name" => "Landscape\TextField", "count" => "Landscape\NumberField"];
}

class DatabaseModelTest  extends \PHPUnit_Framework_TestCase
{
    public function testDatabase()
    {
        $x = new Dataclass();
        $x->set('name', "myself");
        $x->set('count', 5);
        $x->save();

        $id = $x->get('ID');
        $x = NULL;

        $x = new Dataclass('find', 'ID='.strval($id));
        $this->assertEquals($x->get('ID'), $id);
        $this->assertEquals($x->get('name'), "myself");
        $this->assertEquals($x->get('count'), 5);

        $x->set('name', 'other');
        $x->save();
        $x = NULL;

        $x = new Dataclass('find', 'ID='.strval($id));
        $this->assertEquals($x->get('ID'), $id);
        $this->assertEquals($x->get('name'), "other");
        $this->assertEquals($x->get('count'), 5);

        $x = NULL;
    }
}
?>
