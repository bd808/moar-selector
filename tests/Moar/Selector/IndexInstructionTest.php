<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
class IndexInstructionTest extends \PHPUnit_Framework_TestCase {

  protected $fixture;

  public function setUp () {
    parent::setUp();
    $this->fixture = array();
  }

  public function test_numeric () {
    $this->fixture[] = new \stdClass;
    $inst = new IndexInstruction(0);
    $this->assertSame($this->fixture[0], $inst->apply($this->fixture));
  }

  public function test_string () {
    $this->fixture['zero'] = new \stdClass;
    $inst = new IndexInstruction('zero');
    $this->assertSame($this->fixture['zero'], $inst->apply($this->fixture));
  }

  /**
   * @expectedException Moar\Selector\UndefinedIndexException
   * @expectedExceptionMessage Undefined index:
   */
  public function test_not_found () {
    $name = "some funky label";
    $inst = new IndexInstruction($name);
    $inst->apply($this->fixture);
  }

  public function test_arrayaccess () {
    $this->fixture = new \ArrayObject();
    $this->fixture[] = new \stdClass;
    $this->fixture['zero'] = new \stdClass;

    $inst = new IndexInstruction(0);
    $this->assertSame($this->fixture[0], $inst->apply($this->fixture));

    $inst = new IndexInstruction('zero');
    $this->assertSame($this->fixture['zero'], $inst->apply($this->fixture));
  }

  /**
   * @expectedException Moar\Selector\UndefinedIndexException
   * @expectedExceptionMessage Undefined index:
   */
  public function test_arrayaccess_not_found () {
    $this->fixture = new \ArrayObject();
    $name = "some funky label";
    $inst = new IndexInstruction($name);
    $inst->apply($this->fixture);
  }

} //end IndexInstructionTest
