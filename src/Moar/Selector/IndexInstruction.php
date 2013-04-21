<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * Selector instruction which retrieves data from an array.
 *
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
class IndexInstruction extends Instruction {

  /**
   * Array index.
   * @var mixed
   */
  protected $idx;


  /**
   * Constructor.
   *
   * @param mixed $name Index label
   */
  public function __construct ($name) {
    $this->idx = $name;
  }


  /**
   * Select array member by index.
   *
   * @param mixed $node Current graph node
   * @return mixed Next traversal value or null if not present
   * @throws UndefinedIndexException If array does not have
   * index.
   * @throws TypeException If node is not an array
   */
  public function apply ($node) {
    $arrayLike = is_array($node) || ($node instanceof \ArrayAccess);
    if (!$arrayLike) {
      throw new TypeException(
          "Expected array, got " . gettype($node));
    }

    if (array_key_exists($this->idx, $node) ||
        (($node instanceof \ArrayAccess) &&
          $node->offsetExists($this->idx))) {
      return $node[$this->idx];

    } else {
      throw new UndefinedIndexException(
          "Undefined index: {$this->idx}");
    }
  } //end apply

} //end IndexInstruction
