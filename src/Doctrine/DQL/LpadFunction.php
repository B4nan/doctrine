<?php

namespace B4nan\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * LeftPad ::= "LPAD" "(" ArithmeticPrimary "," ArithmeticPrimary "," StringPrimary ")"
 * @author adamek
 */
class LpadFunction extends FunctionNode
{

	/** @var Node */
	public $stringExpression = NULL;

	/** @var Node */
	public $lengthExpression = NULL;

	/** @var Node */
	public $padStringExpression = NULL;

	/**
	 * @param Parser $parser
	 */
	public function parse(Parser $parser)
	{
		$parser->match(Lexer::T_IDENTIFIER);
		$parser->match(Lexer::T_OPEN_PARENTHESIS);
		$this->stringExpression = $parser->ArithmeticPrimary();
		$parser->match(Lexer::T_COMMA);
		$this->lengthExpression = $parser->ArithmeticPrimary();
		$parser->match(Lexer::T_COMMA);
		$this->padStringExpression = $parser->StringPrimary();
		$parser->match(Lexer::T_CLOSE_PARENTHESIS);
	}

	/**
	 * @param SqlWalker $sqlWalker
	 * @return string
	 */
	public function getSql(SqlWalker $sqlWalker)
	{
		return 'LPAD(' .
			$this->stringExpression->dispatch($sqlWalker) . ', ' .
			$this->lengthExpression->dispatch($sqlWalker) . ', ' .
			$this->padStringExpression->dispatch($sqlWalker) .
		')';
	}
}
