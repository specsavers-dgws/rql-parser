<?php
namespace Mrix\Rql\Parser;

use Mrix\Rql\Parser\Exception\SyntaxErrorException;

/**
 * Parser
 */
class Parser
{
    /**
     * @var TokenParserInterface[]
     */
    protected $tokenParsers = [];

    /**
     * @param TokenParserInterface $tokenParser
     * @return void
     */
    public function addTokenParser(TokenParserInterface $tokenParser)
    {
        $this->tokenParsers[] = $tokenParser;
    }

    /**
     * @return TokenParserInterface[]
     */
    public function getTokenParsers()
    {
        return $this->tokenParsers;
    }

    /**
     * @param TokenStream $tokenStream
     * @return Query
     * @throws SyntaxErrorException
     */
    public function parse(TokenStream $tokenStream)
    {
        $query = $this->createQuery();
        while (!$tokenStream->isEnd()) {
            $query->addNode($this->subparse($tokenStream));
            $tokenStream->nextIf(Token::T_AMPERSAND);
        }

        return $query;
    }

    /**
     * @param TokenStream $tokenStream
     * @return AbstractNode
     * @throws SyntaxErrorException
     */
    protected function subparse(TokenStream $tokenStream)
    {
        $token = $tokenStream->getCurrent();
        foreach ($this->tokenParsers as $tokenParser) {
            if ($tokenParser->supports($token)) {
                return $tokenParser->parse($tokenStream);
            }
        }

        throw new SyntaxErrorException(
            sprintf(
                'Unexpected token "%s" (%s) at position %d',
                $token->getValue(),
                $token->getName(),
                $token->getPosition()
            )
        );
    }

    /**
     * @return Query
     */
    protected function createQuery()
    {
        return new Query();
    }
}
