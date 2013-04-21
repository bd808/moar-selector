<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * Thrown to indicate that a parsing error occurred.
 *
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
class ParseException extends \UnexpectedValueException {

  /**
   * Error offset in parse stream.
   * @var int
   */
  protected $offset;

  /**
   * Constructor.
   *
   * @param string $msg Error message
   * @param int $offset Offset from start of parse stream where error was
   * detected.
   */
  public function __construct ($msg, $offset) {
    parent::__construct($msg);
    $this->offset = $offset;
  }

  /**
   * Returns the position where the error was found.
   *
   * @return int Error offset in parse stream
   */
  public function getErrorOffset () {
    return $this->offset;
  }

} //end ParseException
