<?php

namespace B4nan\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode,
	Doctrine\ORM\Query\Lexer,
	Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\SqlWalker;

/**
 * @author adamek
 */
class RepeatFunction extends FunctionNode
{

	/** @var Node */
	public $char = NULL;

	/** @var Node */
	public $times = NULL;

	/**
	 * @param Parser $parser
	 */
	public function parse(Parser $parser)
	{
		$parser->match(Lexer::T_IDENTIFIER);
		$parser->match(Lexer::T_OPEN_PARENTHESIS);
		$this->char = $parser->ScalarExpression();
		$parser->match(Lexer::T_COMMA);
		$this->times = $parser->ScalarExpression();
		$parser->match(Lexer::T_CLOSE_PARENTHESIS);
	}

	/**
	 * @param SqlWalker $sqlWalker
	 * @return string
	 */
	public function getSql(SqlWalker $sqlWalker)
	{
		return sprintf('REPEAT(%s, %s)', $this->char->dispatch($sqlWalker), $this->times->dispatch($sqlWalker));
	}

}
