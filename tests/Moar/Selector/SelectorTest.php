<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
class SelectorTest extends \PHPUnit_Framework_TestCase {

  public function test_member () {
    $sel = new Selector('a');
    $graph = new graphClass;
    $graph->a = 'property a';

    $this->assertSame($graph->a, $sel->select($graph));
    $this->assertFalse($sel->failed());
    $this->assertNull($sel->getError());
  }

  public function test_simple_path () {
    $sel = new Selector('a.b.c.d');
    $graph = new graphClass;
    $graph->a->b->c->d = 'property d';

    $this->assertSame($graph->a->b->c->d, $sel->select($graph));
  }

  public function test_complex_path () {
    $sel = new Selector('{"a"}.{"b\""}.{"\'"}.{"d"}');
    $graph = new graphClass;
    // yup, php is this crazy.
    $graph->a->{'b"'}->{"'"}->d = 'property d';

    $this->assertSame($graph->a->{'b"'}->{"'"}->d, $sel->select($graph));
  }

  public function test_array_index () {
    $sel = new Selector('[0]');
    $graph = array('item 0');

    $this->assertSame($graph[0], $sel->select($graph));
  }

  public function test_array_index_chain () {
    $sel = new Selector('[0][1][2][3]');
    $graph = array(array(0, array(0, 1, array(0, 1, 2, "property 3",),),),);

    $this->assertSame($graph[0][1][2][3], $sel->select($graph));
  }

  public function test_rule () {
    $sel = new Selector('[b = 1]');
    $graph = array(
        new graphClass,
        new graphClass,
        new graphClass,
      );
    $graph[0]->b = 0;
    $graph[1]->b = 1;
    $graph[2]->b = 2;

    // selectors always return arrays
    $this->assertSame(array($graph[1]), $sel->select($graph));
  }

  public function test_complicated () {
    $sel = new Selector(
        'a.{"b"}[1][d.{"e"}[0].f = "something"][0].c.foo');
    $graph = new graphClass;
    $grandchild = new graphClass;
    $grandchild->f = 'something';
    $child = new graphClass;
    $child->d->e = array($grandchild);
    $child->c->foo = "this is the value selected";
    $graph->a->b = array(0, array(0, 1, 2, $child),);

    $this->assertSame($child->c->foo, $sel->select($graph));
  }

  /**
   * @expectedException Moar\Selector\ParseException
   * @expectedExceptionMessage Member name expected
   */
  public function test_malformed () {
    $sel = new Selector('"foo"');
  } //end test_malformed

  public function test_suppress_exceptions () {
    $sel = new Selector('a["some funky label"]');
    $sel->throwErrors(false);

    $graph = new graphClass;
    $graph->a = array();

    $this->assertNull($sel->select($graph));
    $this->assertTrue($sel->failed());
    $this->assertNotNull($sel->getError());
  }

} //end SelectorTest

class graphClass {
  public function __get ($name) {
    $this->{$name} = new self;
    return $this->{$name};
  }
}
