<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
class MemberInstructionTest extends \PHPUnit_Framework_TestCase {

  protected $fixture;

  public function setUp () {
    parent::setUp();
    $this->fixture = new \stdClass;
  }

  public function test_basic () {
    $this->fixture->member = new \stdClass;
    $inst = new MemberInstruction("member");
    $this->assertSame($this->fixture->member, $inst->apply($this->fixture));
  }

  public function test_complex () {
    $name = "some funky label";
    $this->fixture->{$name} = new \stdClass;
    $inst = new MemberInstruction($name);
    $this->assertSame($this->fixture->{$name}, $inst->apply($this->fixture));
  }

  /**
   * @expectedException Moar\Selector\UndefinedPropertyException
   * @expectedExceptionMessage Undefined property:
   */
  public function test_not_found () {
    $name = "some funky label";
    $inst = new MemberInstruction($name);
    $inst->apply($this->fixture);
  }

  public function test_magic_methods () {
    $this->fixture = new MagicMethods();

    $this->fixture->member = new \stdClass;
    $inst = new MemberInstruction("member");
    $this->assertSame($this->fixture->member, $inst->apply($this->fixture));

    $name = "some funky label";
    $this->fixture->{$name} = new \stdClass;
    $inst = new MemberInstruction($name);
    $this->assertSame($this->fixture->{$name}, $inst->apply($this->fixture));
  }

  /**
   * @expectedException Moar\Selector\UndefinedPropertyException
   * @expectedExceptionMessage Undefined property:
   */
  public function test_magic_methods_not_found () {
    $this->fixture = new MagicMethods();

    $name = "some funky label";
    $inst = new MemberInstruction($name);
    $inst->apply($this->fixture);
  }

} //end MemberInstructionTest

class MagicMethods {

  private $hidden = array();

  public function __get ($name) {
    return $this->hidden[$name];
  }

  public function __set ($name, $value) {
    $this->hidden[$name] = $value;
  }

  public function __isset ($name) {
    return isset($this->hidden[$name]);
  }
} //end MagicMethods
