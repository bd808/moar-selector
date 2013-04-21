<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
class IndexRuleTest extends \PHPUnit_Framework_TestCase {

  protected $fixture;

  public function setUp () {
    parent::setUp();
    $this->fixture = array();
    $this->fixture[] = $this->makeNode('a', 1);
    $this->fixture[] = $this->makeNode('a', 2);
    $this->fixture[] = $this->makeNode('a', 2);
    $this->fixture[] = $this->makeNode('a', 3);
    $this->fixture[] = $this->makeNode('a', 3);
    $this->fixture[] = $this->makeNode('a', 3);
    $this->fixture[] = $this->makeNode('b', 1);
    $this->fixture[] = $this->makeNode('b', 2);
    $this->fixture[] = $this->makeNode('b', 2);
    $this->fixture[] = $this->makeNode('b', 3);
    $this->fixture[] = $this->makeNode('b', 3);
    $this->fixture[] = $this->makeNode('b', 3);
  }

  public function test_one_match () {
    $sel = new MemberInstruction("a");
    $rule = $this->makeRule($sel, '=', 1);
    $result = $rule->apply($this->fixture);

    $this->assertTrue(is_array($result), 'Expected an array');
    $this->assertEquals(1, count($result), 'Expected a single result');
    $this->assertEquals(1, $result[0]->a, 'Expected result with a=1');
  }

  public function test_many_match () {
    $sel = new MemberInstruction("b");
    $rule = $this->makeRule($sel, '=', 3);
    $result = $rule->apply($this->fixture);

    $this->assertTrue(is_array($result), 'Expected an array');
    $this->assertEquals(3, count($result), 'Expected 3 results');
    foreach ($result as $r) {
      $this->assertEquals(3, $r->b, 'Expected result with b=3');
    }
  }

  public function test_no_member_match () {
    $sel = new MemberInstruction("c");
    $rule = $this->makeRule($sel, '=', 1);
    $result = $rule->apply($this->fixture);

    $this->assertTrue(is_array($result), 'Expected an array');
    $this->assertEquals(0, count($result), 'Expected an empty result');
  }

  public function test_no_value_match () {
    $sel = new MemberInstruction("a");
    $rule = $this->makeRule($sel, '=', 4);
    $result = $rule->apply($this->fixture);

    $this->assertTrue(is_array($result), 'Expected an array');
    $this->assertEquals(0, count($result), 'Expected an empty result');
  }

  /**
   * @expectedException Moar\Selector\TypeException
   * @expectedExceptionMessage Expected array, got
   */
  public function test_node_not_array () {
    $sel = new MemberInstruction("a");
    $rule = $this->makeRule($sel, '=', 4);
    $result = $rule->apply("I'm not an object");
  }

  /**
   * Make a stdClass object having the given member and value.
   *
   * @param string $slot Member name
   * @param mixed $value member value
   * @return stdClass Object
   */
  protected function makeNode ($slot, $value) {
    $o = new \stdClass;
    $o->{$slot} = $value;
    return $o;
  } //end makeNode

  /**
   * Build a rule.
   *
   * @param array|Moar\Selector\Instruction $statements Node selector
   * @param string $op Operator
   * @param mixed $value Value to match
   */
  protected function makeRule ($statements, $op, $value) {
    if (!is_array($statements)) {
      $statements = array($statements);
    }
    $rule = new IndexRule();
    foreach ($statements as $stmt) {
      $rule->addInstruction($stmt);
    }
    $rule->operator($op);
    $rule->value($value);
    return $rule;
  } //end makeRule

} //end IndexRuleTest
