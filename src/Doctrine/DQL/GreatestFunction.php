<?php

namespace B4nan\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode;
use Doctrine\ORM\Query\AST\Node;
use Doctrine\ORM\Query\Lexer;
use Doctrine\ORM\Query\Parser;
use Doctrine\ORM\Query\SqlWalker;

/**
 * @author adamek
 */
class GreatestFunction extends FunctionNode
{

	/** @var Node */
	private $firstExpression;

	/** @var Node */
	private $secondExpression;

	/**
	 * @param SqlWalker $sqlWalker
	 * @return string
	 * @throws \Doctrine\ORM\Query\AST\ASTException
	 */
	public function getSql(SqlWalker $sqlWalker)
	{
		return sprintf(
			'GREATEST(%s, %s)',
			$this->firstExpression->dispatch($sqlWalker),
			$this->secondExpression->dispatch($sqlWalker)
		);
	}

	/**
	 * @param Parser $parser
	 */
	public function parse(Parser $parser)
	{
		$parser->match(Lexer::T_IDENTIFIER);
		$parser->match(Lexer::T_OPEN_PARENTHESIS);
		$this->firstExpression = $parser->ArithmeticPrimary();
		$parser->match(Lexer::T_COMMA);
		$this->secondExpression = $parser->ArithmeticPrimary();
		$parser->match(Lexer::T_CLOSE_PARENTHESIS);
	}
}

