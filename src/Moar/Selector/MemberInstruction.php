<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * Selector instruction which selects a member from an object.
 *
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
class MemberInstruction extends Instruction {

  /**
   * Member to select.
   * @var string
   */
  protected $member;


  /**
   * Constructor.
   *
   * @param string $name Member name
   */
  public function __construct ($name) {
    $this->member = $name;
  }


  /**
   * Select member property value from an object.
   *
   * @param mixed $node Current graph node
   * @return mixed Next traversal value
   * @throws Moar\Selector\UndefinedPropertyException If object does not have
   * member.
   * @throws Moar\Selector\TypeException If node is not an object
   */
  public function apply ($node) {
    if (!is_object($node)) {
      throw new TypeException(
          "Expected object, got " . gettype($node));
    }

    // The isset() test allows `__isset()` magic overloading.
    // `__get()` magic without `__isset()` can still sneak by, but including
    // just a check for `__get()` could trigger errors that we can't trap.
    // We can get a false negative for an object with `__get()`/`__isset()`
    // having a null value for the member.
    if (property_exists($node, $this->member) ||
        isset($node->{$this->member})) {
      return $node->{$this->member};

    } else {
      throw new UndefinedPropertyException(
          "Undefined property: " . get_class($node) . "::{$this->member}");
    }
  } //end apply

} //end MemberInstruction
