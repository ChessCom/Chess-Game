<?php

namespace Chess\Game\Tests;

use Chess\Game\ChessGame;

class ChessGameTest extends \PHPUnit_Framework_TestCase
{
    /** @var ChessGame */
    private $game;

    public function setUp()
    {
        $this->game = new ChessGame();
    }

    /*
     * According to the Wikipedia page describing FEN notation, segment 5 (Half-Move clock)
     * is the number of half moves since the last pawn advance or capture. This means that
     * after either of those events, the clock should be reset to 0. The game library
     * presently resets to one, thus the counter is off by one.
     *
     * The following sequence of moves and the resulting FEN is provided on the linked
     * page and was used to verify the bug's existence and subsequent fix.
     *
     * @link http://en.wikipedia.org/wiki/Forsyth-Edwards_Notation
     */
    public function testHalfMoveBugFix()
    {
        $this->game->resetGame();
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', $this->game->renderFen());

        $this->game->moveSAN('e4');
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/4P3/8/PPPP1PPP/RNBQKBNR b KQkq e3 0 1', $this->game->renderFen());

        $this->game->moveSAN('c5');
        $this->assertEquals('rnbqkbnr/pp1ppppp/8/2p5/4P3/8/PPPP1PPP/RNBQKBNR w KQkq c6 0 2', $this->game->renderFen());

        $this->game->moveSAN('Nf3');
        $this->assertEquals('rnbqkbnr/pp1ppppp/8/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq - 1 2', $this->game->renderFen());
    }
}
