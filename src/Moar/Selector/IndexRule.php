<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * Selector instruction which examines a target array and selects elements
 * that satisfy the comparison operation.
 *
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
class IndexRule extends Instruction {

  /**
   * Left hand side of comparison.
   * Used to find the value from each array member that will be compared.
   *
   * @var array Array of Instructions
   */
  protected $lhs = array();

  /**
   * Comparison operation.
   * @var string
   */
  protected $op;

  /**
   * Right hand side of comparison.
   *
   * @var mixed
   */
  protected $rhs;


  /**
   * Add an instruction to the LHS selector chain.
   *
   * @param Instruction $stmt Selection instruction
   * @return void
   */
  public function addInstruction ($stmt) {
    $this->lhs[] = $stmt;
  }


  /**
   * Set the comparison operator.
   *
   * @param string $op Operator name
   * @return void
   */
  public function operator ($op) {
    $this->op = $op;
  }


  /**
   * Set the RHS of the comparison.
   *
   * @param mixed $val Comparison RHS
   * @return void
   */
  public function value ($val) {
    $this->rhs = $val;
  }


  /**
   * Evaluate an array element against this instruction's comparison.
   *
   * @param mixed $node Array element to compare
   * @return bool True if element matches, false otherwise
   */
  protected function checkMatch ($node) {
    // work our way down to the member to check
    foreach ($this->lhs as $path) {
      try {
        $node = $path->apply($node);
      } catch (TraversalException $e) {
        return false;
      }
    }

    switch ($this->op) {
      case '=':
        return $node == $this->rhs;
        break;

      default:
        throw new \RuntimeException(
            "Unsupported rule operator '{$this->op}'");
        break;
    } //end switch
  } //end checkMatch


  /**
   * Select array members matching our condition.
   *
   * @param mixed $node Current graph node
   * @return array Matched values (possibly empty)
   */
  public function apply ($node) {
    $arrayLike = is_array($node) || ($node instanceof ArrayAccess);
    if (!$arrayLike) {
      throw new TypeException(
          "Expected array, got " . gettype($node));
    }

    $matches = array();
    foreach ($node as $n) {
      if ($this->checkMatch($n)) {
        $matches[] = $n;
      }
    }

    return $matches;
  } //end apply

} //end IndexRule
