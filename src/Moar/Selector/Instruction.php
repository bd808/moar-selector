<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * Selector instructions are used to traverse an object graph.
 *
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
abstract class Instruction {

  /**
   * Apply instruction to graph.
   *
   * @param mixed $node Current graph node
   * @return mixed Next traversal value
   */
  abstract public function apply ($node);

} //end Instruction
