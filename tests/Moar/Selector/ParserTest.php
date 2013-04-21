<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
class ParserTest extends \PHPUnit_Framework_TestCase {

  public function test_member () {
    $p = new Parser('a');
    $stack = $p->parse();

    $this->assertEquals(1, count($stack));
    $this->assertinstanceOf('Moar\Selector\Instruction', $stack[0]);
    $this->assertinstanceOf('Moar\Selector\MemberInstruction', $stack[0]);
  }

  public function test_simple_path () {
    $p = new Parser('a.b.c.d');
    $stack = $p->parse();

    $this->assertEquals(4, count($stack));
    foreach ($stack as $inst) {
      $this->assertinstanceOf('Moar\Selector\Instruction', $inst);
      $this->assertinstanceOf('Moar\Selector\MemberInstruction', $inst);
    }
  }

  public function test_complex_path () {
    $p = new Parser('{"a"}.{"b\""}.{"\'"}.{"d"}');
    $stack = $p->parse();

    $this->assertEquals(4, count($stack));
    foreach ($stack as $inst) {
      $this->assertinstanceOf('Moar\Selector\Instruction', $inst);
      $this->assertinstanceOf('Moar\Selector\MemberInstruction', $inst);
    }
  }

  public function test_array_index () {
    $p = new Parser('[0]');
    $stack = $p->parse();

    $this->assertEquals(1, count($stack));
    $this->assertinstanceOf('Moar\Selector\Instruction', $stack[0]);
    $this->assertinstanceOf('Moar\Selector\IndexInstruction', $stack[0]);
  }

  public function test_array_index_chain () {
    $p = new Parser('[0][1][2][3]');
    $stack = $p->parse();

    $this->assertEquals(4, count($stack));
    foreach ($stack as $inst) {
      $this->assertinstanceOf('Moar\Selector\Instruction', $inst);
      $this->assertinstanceOf('Moar\Selector\IndexInstruction', $inst);
    }
  }

  public function test_rule () {
    $p = new Parser('[b = 1]');
    $stack = $p->parse();

    $this->assertEquals(1, count($stack));
    $this->assertinstanceOf('Moar\Selector\Instruction', $stack[0]);
    $this->assertinstanceOf('Moar\Selector\IndexRule', $stack[0]);
  }

  public function test_complicated () {
    $p = new Parser('a.{"b"}[1][d.{"e"}[0].f = "something"]["c"].foo');
    $stack = $p->parse();

    $this->assertEquals(6, count($stack));
    $expect = array(
        'Moar\Selector\MemberInstruction',
        'Moar\Selector\MemberInstruction',
        'Moar\Selector\IndexInstruction',
        'Moar\Selector\IndexRule',
        'Moar\Selector\IndexInstruction',
        'Moar\Selector\MemberInstruction',
        );
    foreach ($stack as $idx => $inst) {
      $this->assertinstanceOf('Moar\Selector\Instruction', $inst);
      $this->assertinstanceOf($expect[$idx], $inst);
    }
  }

  public function test_malformed () {
    $bad = array(
        '"bar"'                        => 0,
        "'bar'"                        => 0,
        'a.'                           => 2,
        '[a=~]'                        => 3,
        'a.{"b"}[1][ b = 1 ].foo"bar"' => 23,
        '['                            => 1,
        '["foo]'                       => 6,
        '{"foo}'                       => 6,
      );

    foreach ($bad as $sel => $loc) {
      $p = new Parser($sel);
      try {
        $stack = $p->parse();
        $this->fail("Expected Moar\Selector\ParseException for input: {$sel}");
      } catch (ParseException $expected) {
        $this->assertEquals($loc, $expected->getErrorOffset());
      }
    }
  } //end test_malformed

} //end ParserTest
