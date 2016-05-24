<?php

namespace B4nan\Doctrine\DQL;

use Doctrine\ORM\Query\AST\Functions\FunctionNode,
	Doctrine\ORM\Query\Lexer,
	Doctrine\ORM\Query\Parser;

/**
 * @author adamek
 */
class CollateFunction extends FunctionNode
{

	public $expressionToCollate = NULL;

	/** @var string */
	public $collation = NULL;

	/**
	 * @param Parser $parser
	 */
	public function parse(Parser $parser)
	{
		$parser->match(Lexer::T_IDENTIFIER);
		$parser->match(Lexer::T_OPEN_PARENTHESIS);
		$this->expressionToCollate = $parser->StringPrimary();

		$parser->match(Lexer::T_COMMA);

		$parser->match(Lexer::T_IDENTIFIER);
		$lexer = $parser->getLexer();
		$this->collation = $lexer->token['value'];

		$parser->match(Lexer::T_CLOSE_PARENTHESIS);
	}

	/**
	 * @param \Doctrine\ORM\Query\SqlWalker $sqlWalker
	 * @return string
	 */
	public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
	{
		return sprintf('%s COLLATE %s', $this->expressionToCollate->dispatch($sqlWalker), $this->collation);
	}

}
