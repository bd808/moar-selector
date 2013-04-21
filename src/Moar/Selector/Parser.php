<?php
/**
 * @package Moar\Selector
 */

namespace Moar\Selector;

/**
 * Parse a graph traversal statement into a list of executable instructions.
 *
 * The graph traversal language described here allows:
 * - selecting a member of an object
 * - selecting an array element by index
 * - selecting an array element by key
 * - selecting array elements by content
 * - any combination of the above actions
 *
 * Grammar (in psudo-EBNF notation):
 * <pre><code>
 *   statement        = selector | index, [ '.', selector ] ;
 *   selector         = member, { ( '.', member ) | index } ;
 *   member           = identifier | complex ;
 *   complex          = '{', ws, literal, ws, '}' ;
 *   identifier       = start_char, { ident_char } ;
 *   literal          = '"', { not_quote }, '"' |
 *                      '\'', { not_single_quote }, '\'' ;
 *   index            = '[', ws, expression, ws, ']' ;
 *   expression       = value | rule ;
 *   value            = number | literal ;
 *   number           = [ '-' ], { digit }, [ '.', { digit } ] ;
 *   rule             = selector, ws, op, ws, value ;
 *   ws               =  /\s*?/
 *   op               = '=' ;
 *   start_char       = /[a-zA-Z_\x7f-\xff]/ ;
 *   ident_char       = /[a-zA-Z0-9_\x7f-\xff]/ ;
 *   not_quote        = /[^"]/
 *   not_single_quote = /[^']/
 *   digit            = /[0-9]/ ;
 * </code></pre>
 *
 * Example paths:
 *  - foo
 *  - {"something"}
 *  - [1]
 *  - foo.bar
 *  - foo.bar[child="val"].baz
 *  - foo.bar[0]
 *  - foo.bar[0].baz
 *  - foo.bar["blah"]
 *  - foo.bar["blah"].baz
 *  - foo.bar["blah"][0]
 *  - foo.{"any random string"}
 *  - foo.{"any random string"}.bar
 *  - foo.{"any random string"}[sel="val"].bar
 *  - foo.{"any random string"}[{"blah"}.xyzzy="val"].bar
 *
 * @package Moar\Selector
 * @copyright 2013 Bryan Davis and contributors. All Rights Reserved.
 */
class Parser {

  /**
   * Regex for identifier start character.
   * @var string
   */
  const START_CHAR = '/[a-zA-Z_\x7f-\xff]/';

  /**
   * Regex for identifier body character.
   * @var string
   */
  const IDENT_CHAR = '/[a-zA-Z0-9_\x7f-\xff]/';

  /**
   * Regex for any character.
   * @var string
   */
  const ANY_CHAR = '/./';

  /**
   * Regex for a character that can start a number.
   * @var string
   */
  const START_NUMBER = '/[0-9\-\+]/';

  /**
   * Regex for a digit.
   * @var string
   */
  const DIGIT = '/[0-9]/';

  /**
   * Regex for any quote.
   * @var string
   */
  const ANY_QUOTE = '/["\']/';

  /**
   * Regex for whitespace.
   * @var string
   */
  const WHITESPACE = '/\s/';

  /**
   * Regex for rule operators.
   * @var string
   */
  const START_OPERATOR = '/=/';

  /**
   * Statement to parse.
   * @var string
   */
  protected $src;

  /**
   * Current parser position.
   * @var int
   */
  protected $ptr;

  /**
   * Maximum parser position.
   * @var int
   */
  protected $maxPtr;


  /**
   * Constructor.
   *
   * @param string $statement Statement to parse
   */
  public function __construct ($statement) {
    $this->src = $statement;
    $this->reset();
  }


  /**
   * Reset the parser.
   *
   * @return void
   */
  public function reset () {
    $this->ptr = 0;
    $this->maxPtr = mb_strlen($this->src);
  }


  /**
   * Parse the statement.
   *
   * @return array Collection of path traversal instructions
   */
  public function parse () {
    $parts = array();
    while (!$this->atEnd()) {
      $parts[] = $this->nextInstruction();
    }
    return $parts;
  } //end parse


  /**
   * Get the next traversal instruction.
   *
   * @return Moar\Selector\Instruction Traversal instruction
   */
  public function nextInstruction () {
    if ($this->expect('[')) {
      return $this->parseIndex();

    } else {
      return $this->parseMember();
    }
  } //end end nextInstruction


  /**
   * Parse a member identifier.
   *
   * @return Moar\Selector\Instruction Traversal instruction
   */
  protected function parseMember () {
    if ($this->expect('.')) {
      $this->consume('.');
    }

    if ($this->expect('{')) {
      $this->consume('{');
      $this->consumeWhitespace();
      $str = $this->parseLiteral();
      $this->consumeWhitespace();
      $this->consume('}');
      return new MemberInstruction($str);

    } else {
      // normal identifier, ends at next period or bracket
      $str = $this->parseChar(self::START_CHAR);
      if (null === $str) {
        throw $this->makeException(
            "Member name expected, got {$this->peek(5)}");
      }
      $char = null;
      while (null !== ($char = $this->parseChar(self::IDENT_CHAR))) {
        $str .= $char;
      }
      return new MemberInstruction($str);
    }//end if
  } //end parseMember


  /**
   * Parse an array index instruction.
   *
   * @return Moar\Selector\Instruction Traversal instruction
   */
  protected function parseIndex () {
    $this->consume('[');
    $this->consumeWhitespace();
    $instruction = null;
    if ($this->expectMatch(self::ANY_QUOTE)) {
      $instruction = new IndexInstruction(
          $this->parseLiteral());

    } else if ($this->expectMatch(self::START_NUMBER)) {
      $instruction = new IndexInstruction(
          $this->parseNumber());

    } else {
      $instruction = $this->parseIndexRule();
    }
    $this->consumeWhitespace();
    $this->consume(']');

    return $instruction;
  } //end parseIndex


  /**
   * Parse an index selector rule.
   *
   * @return Moar\Selector\Instruction Traversal instruction
   */
  protected function parseIndexRule () {
    $rule = new IndexRule();
    $this->consumeWhitespace();

    while (!$this->expectMatch(self::START_OPERATOR)) {
      $rule->addInstruction($this->nextInstruction());
      $this->consumeWhitespace();
    }

    $opStart = $this->peek();
    switch ($opStart) {
      case '=':
        $op = $this->consume('=');
        break;
      default:
        throw $this->makeException("Expected operator, got {$this->peek(3)}");
        break;
    } //end switch operator
    $rule->operator($op);

    $this->consumeWhitespace();

    if ($this->expectMatch(self::ANY_QUOTE)) {
      $rule->value($this->parseLiteral());

    } else if ($this->expectMatch(self::START_NUMBER)) {
      $rule->value($this->parseNumber());

    } else {
        throw $this->makeException("Expected value, got {$this->peek(3)}");
    }

    $this->consumeWhitespace();

    return $rule;
  } //end parseIndexRule


  /**
   * Parse a literal (quoted) string.
   * @return string Literal string
   */
  protected function parseLiteral () {
    $quote = $this->parseChar(self::ANY_QUOTE);
    if (null === $quote) {
      throw $this->makeException("Quote expected, got {$this->peek(5)}");
    }

    $str = '';
    while (!$this->expect($quote)) {
      $char = $this->parseChar();
      if (null === $char) {
        throw $this->makeException("Malformed quoted string: {$this->peek(3)}");
      }
      $str .= $char;
    }
    $this->consume($quote);

    return $str;
  } //end parseLiteral


  /**
   * Parse a numeric value.
   *
   * @return number Number
   */
  protected function parseNumber () {
    $str = '';
    // optional negation
    if ($this->expect('-')) {
      $str .= $this->consume('-');
    }
    // 0-N digits
    while (is_numeric($this->peek())) {
      $str .= $this->consume(1);
    }
    // optional fraction
    if ($this->expect('.')) {
      $str .= $this->consume('.');
      // 0-N digits
      while (is_numeric($this->peek())) {
        $str .= $this->consume(1);
      }
    }
    $asFloat = floatval($str);
    $asInt = intval($str);

    if ($asFloat == $asInt) {
      return $asInt;
    } else {
      return $asFloat;
    }
  } //end parseNumber


  /**
   * Parse the next character in the stream.
   *
   * @param string $onlyMatch Regex that character must match
   * @return string Next character or null if regex fails
   */
  protected function parseChar ($onlyMatch = self::ANY_CHAR) {
    if ('\\' === $this->peek()) {
      $this->consume('\\');
    }
    if ($this->expectMatch($onlyMatch)) {
      return $this->consume(1);
    } else {
      return null;
    }
  } //end parseChar


  /**
   * Are we at the end of the input?
   *
   * @return bool True if at end, false otherwise
   */
  public function atEnd () {
    return $this->ptr >= $this->maxPtr;
  }


  /**
   * Peek at the input stream.
   *
   * @param int|string $len How far to look ahead
   * @param int|string $offset How far to skip before looking
   * @return string Input chunk
   */
  protected function peek ($len = 1, $offset = 0) {
    if ($this->atEnd()) {
      return '';
    }
    if (is_string($len)) {
      $len = mb_strlen($len);
    }
    if (is_string($offset)) {
      $offset = mb_strlen($offset);
    }
    return mb_substr($this->src, $this->ptr + $offset, $len);
  } //end peek


  /**
   * Does the given string come next in the input stream?
   *
   * @param string $str String to expect
   * @param int|string $offset How far to skip before looking
   * @return bool True if expected string is in input, false otherwise
   */
  protected function expect ($str, $offset = 0) {
    if ($this->atEnd()) {
      return false;
    }
    return $this->peek($str, $offset) === $str;
  } //end expect


  /**
   * Does the given pattern come next in the input stream?
   *
   * @param string $pattern Regex or single char to match
   * @param int $len How far to look ahead
   * @param int $offset How far to skip before looking
   * @return bool True if expected pattern matches input, false otherwise
   */
  protected function expectMatch ($pattern, $len = 1, $offset = 0) {
    return preg_match($pattern, $this->peek($len, $offset)) === 1;
  } //end peekMatch


  /**
   * Consume and return the next N chars or expected string.
   *
   * @param int|string $what Number of chars to consume or string to expect
   * @return string Consumed chars
   * @throws Exception If max length is exceeded or expected string not
   * matched
   */
  protected function consume ($what = 1) {
    if (is_string($what)) {
      $len = mb_strlen($what);
      if ($what !== mb_substr($this->src, $this->ptr, $len)) {
        throw $this->makeException("Expected {$what}, got {$this->peek(5)}");
      }
      $this->ptr += $len;
      return $what;

    } else {
      if ($this->ptr + $what > $this->maxPtr) {
        throw $this->makeException(
            "Tried to consume {$what} chars, exceeded limit");
      }
      $chunk = mb_substr($this->src, $this->ptr, $what);
      $this->ptr += $what;
      return $chunk;
    }
  } //end consume


  /**
   * Consume any number of whitespace characters from the input stream.
   * @return void
   */
  protected function consumeWhitespace () {
    while ($this->expectMatch(self::WHITESPACE)) {
      $this->consume();
    }
  } //end consumeWhitespace


  /**
   * Build and return a Moar\Selector\ParseException with the current parser
   * position.
   *
   * @param string $msg Error message
   * @return Moar\Selector\ParseException Exception
   */
  protected function makeException ($msg) {
    $e = new ParseException($msg, $this->ptr);
    return $e;
  } //end makeException

} //end Parser
