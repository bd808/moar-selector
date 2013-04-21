<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * Select a target value from an object, object graph or array.
 *
 * The selector is configured using a language that uses periods (`.`) to
 * indicate member selection and brackets (`[]`) for array indexing. It also
 * provides a CSS3-inspired mechanism to select array members tht have content
 * matching a specified value. See {@link Moar\Selector\Parser} for full
 * language grammar and examples.
 *
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
class Selector {

  /**
   * Instructions for this selector.
   *
   * @var array Array of Moar\Selector\Instruction
   */
  protected $instructions;

  /**
   * Should we throw exceptions when they are encountered or not?
   *
   * @var bool
   */
  protected $throwExceptions = false;

  /**
   * Last error seen by this selector.
   *
   * @var Moar\Selector\TraversalException
   */
  protected $lastError;

  /**
   * Constructor.
   *
   * @param string $statement Selector statement
   * @throws Moar\Selector\ParseException If parsing fails
   */
  public function __construct ($statement) {
    $parser = new Parser($statement);
    $this->instructions = $parser->parse();
  }

  /**
   * Should this instance throw traversal errors when they are encountered or
   * not?
   *
   * @param bool $b Flag
   * @return void
   */
  public function throwErrors ($b) {
    $this->throwExceptions = (bool) $b;
  }

  /**
   * Apply selector to a node and return selected value.
   *
   * @param mixed $node Graph node
   * @return mixed Selected value or null if not found
   * @throws Moar\Selector\TraversalException If traversal fails and object
   * is configured to throw errors.
   * @see getError()
   * @see failed()
   */
  public function select ($node) {
    $this->lastError = null;

    foreach ($this->instructions as $inst) {
      try {
        $node = $inst->apply($node);

      } catch (TraversalException $e) {
        $this->lastError = $e;

        if ($this->throwExceptions) {
          throw $e;

        } else {
          return null;
        }
      }
    }

    return $node;
  } //end select

  /**
   * Get the last error.
   *
   * @return Moar\Selector\TraversalException Last error
   */
  public function getError () {
    return $this->lastError;
  }

  /**
   * Did the last traversal fail?
   *
   * @return bool True if most recient traversal failed, false otherwise
   */
  public function failed () {
    return null !== $this->lastError;
  }

} //end Selector
