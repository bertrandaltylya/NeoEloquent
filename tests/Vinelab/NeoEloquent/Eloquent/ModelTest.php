<?php

namespace Vinelab\NeoEloquent\Tests\Eloquent;

use Mockery as M;
use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;
use Vinelab\NeoEloquent\Tests\TestCase;

class Model extends NeoEloquent
{
}

class Labeled extends NeoEloquent
{
    protected $label = 'Labeled';
}

class Table extends NeoEloquent
{
    protected $table = 'Table';
}

class ModelTest extends TestCase
{
    public function tearDown()
    {
        M::close();

        parent::tearDown();
    }

    public function testDefaultNodeLabel()
    {
        $m = new Model();

        $label = $m->getDefaultNodeLabel();

        // By default the label should be the concatenation of the class's namespace
        $this->assertEquals('VinelabNeoEloquentTestsEloquentModel', reset($label));
    }

    public function testOverriddenNodeLabel()
    {
        $m = new Labeled();

        $label = $m->getDefaultNodeLabel();

        $this->assertEquals('Labeled', reset($label));
    }

    public function testLabelBackwardCompatibilityWithTable()
    {
        $m = new Table();

        $label = $m->nodeLabel();

        $this->assertEquals('Table', reset($label));
    }

    public function testSettingLabelAtRuntime()
    {
        $m = new Model();

        $m->setLabel('Padrouga');

        $label = $m->getDefaultNodeLabel();

        $this->assertEquals('Padrouga', reset($label));
    }

    public function testDifferentTypesOfLabelsAlwaysLandsAnArray()
    {
        $m = new Model();

        $m->setLabel(array('User', 'Fan'));
        $label = $m->getDefaultNodeLabel();
        $this->assertEquals(array('User', 'Fan'), $label);

        $m->setLabel(':User:Fan');
        $label = $m->getDefaultNodeLabel();
        $this->assertEquals(array('User', 'Fan'), $label);

        $m->setLabel('User:Fan:Maker:Baker');
        $label = $m->getDefaultNodeLabel();
        $this->assertEquals(array('User', 'Fan', 'Maker', 'Baker'), $label);
    }

    public function testGettingEloquentBuilder()
    {
        $m = new Model();

        $builder = $m->newEloquentBuilder(M::mock('Vinelab\NeoEloquent\Query\Builder'));

        $this->assertInstanceOf('Vinelab\NeoEloquent\Eloquent\Builder', $builder);
    }

    public function testAddLabels()
    {
        //create a new model object
        $m = new Labeled();
        $m->setLabel(array('User', 'Fan')); //set some labels
        $m->save();
        //get the node id, we need it to verify if the label is actually added in graph
        $id = $m->id;

        //add the label
        $m->addLabels(array('Superuniqelabel1'));

        $labels = $this->getNodeLabels($id);

        $this->assertTrue(in_array('Superuniqelabel1', $labels));
    }

    public function testDropLabels()
    {
        //create a new model object
        $m = new Labeled();
        $m->setLabel(array('User', 'Fan', 'Superuniqelabel2')); //set some labels
        $m->save();
        //get the node id, we need it to verify if the label is actually added in graph
        $id = $m->id;

        //drop the label
        $m->dropLabels(array('Superuniqelabel2'));
        $this->assertFalse(in_array('Superuniqelabel2', $this->getNodeLabels($id)));
    }
}
