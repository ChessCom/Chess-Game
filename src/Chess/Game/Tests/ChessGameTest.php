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

    public function testBlankBoardFen()
    {
        $this->game->blankBoard();
        $this->assertEquals('8/8/8/8/8/8/8/8 w KQkq - 1 1', $this->game->renderFen());
    }

    /**
     * Test a complete game
     *
     * This game was taken from ChessTempo.com
     * @link http://chesstempo.com/gamedb/game/184599
     */
    public function testCompleteGame()
    {
        $endFen = '1r2r1k1/5pp1/3q4/2n1p3/R1P1P1Pp/4BP2/6BP/R3Q1K1 b - - 0 36';

        $moves = array(
            1   => array('white' => 'Nf3',  'black' => 'Nf6'),
            2   => array('white' => 'c4',   'black' => 'e6'),
            3   => array('white' => 'Nc3',  'black' => 'Bb4'),
            4   => array('white' => 'Qc2',  'black' => 'O-O'),
            5   => array('white' => 'a3',   'black' => 'Bxc3'),
            6   => array('white' => 'Qxc3', 'black' => 'b6'),
            7   => array('white' => 'b4',   'black' => 'd6'),
            8   => array('white' => 'Bb2',  'black' => 'Bb7'),
            9   => array('white' => 'g3',   'black' => 'c5'),
            10  => array('white' => 'Bg2',  'black' => 'Nbd7'),
            11  => array('white' => 'O-O',  'black' => 'Rc8'),
            12  => array('white' => 'd3',   'black' => 'Re8'),
            13  => array('white' => 'e4',   'black' => 'a6'),
            14  => array('white' => 'Qb3',  'black' => 'b5'),
            15  => array('white' => 'Nd2',  'black' => 'Rb8'),
            16  => array('white' => 'Rfc1', 'black' => 'Ba8'),
            17  => array('white' => 'Qd1',  'black' => 'Qe7'),
            18  => array('white' => 'cxb5', 'black' => 'axb5'),
            19  => array('white' => 'Nb3',  'black' => 'e5'),
            20  => array('white' => 'f3',   'black' => 'h5'),
            21  => array('white' => 'bxc5', 'black' => 'dxc5'),
            22  => array('white' => 'a4',   'black' => 'h4'),
            23  => array('white' => 'g4',   'black' => 'c4'),
            24  => array('white' => 'dxc4', 'black' => 'bxa4'),
            25  => array('white' => 'Ba3',  'black' => 'Qd8'),
            26  => array('white' => 'Nc5',  'black' => 'Bc6'),
            27  => array('white' => 'Nxa4', 'black' => 'Nh7'),
            28  => array('white' => 'Nc5',  'black' => 'Ng5'),
            29  => array('white' => 'Nxd7', 'black' => 'Bxd7'),
            30  => array('white' => 'Rc3',  'black' => 'Qa5'),
            31  => array('white' => 'Rd3',  'black' => 'Ba4'),
            32  => array('white' => 'Qe1',  'black' => 'Qa6'),
            33  => array('white' => 'Bc1',  'black' => 'Ne6'),
            34  => array('white' => 'Rda3', 'black' => 'Nc5'),
            35  => array('white' => 'Be3',  'black' => 'Qd6'),
            36  => array('white' => 'Rxa4'),
        );

        $this->game->resetGame();

        foreach ($moves as $playerMoves) {
            foreach ($playerMoves as $move) {
                $this->game->moveSAN($move);
            }
        }

        $this->assertEquals($endFen, $this->game->renderFen());
    }

    public function testNewChess960GameRenderedFen()
    {
        $this->game->resetGame(false, true);
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', $this->game->renderFen());
    }

    public function testNewStandardGameFen()
    {
        $this->game->resetGame();
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', $this->game->renderFen());
    }

    public function testNewStandardGameWithProvidedFenRenderedFen()
    {
        $providedFen = 'rnbqkbnr/pp1ppppp/8/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq - 1 2';

        $this->game->resetGame($providedFen);
        $this->assertEquals($providedFen, $this->game->renderFen());
    }

    public function testNewStandardGameRenderedFenWithoutEnPassant()
    {
        $this->game->resetGame();
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq', $this->game->renderFen(true, false));
    }

    public function testNewStandardGameRenderedFenWithoutMoves()
    {
        $this->game->resetGame();
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq -', $this->game->renderFen(false));
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

    public function testValidMovesFromStartingFen()
    {
        $startFen   = 'rn3b1N/pp2k2p/4p2q/1NQ5/3P4/8/PPP3PP/5RK1 b - - 1 1';
        $endFen     = 'rnQ2b1N/pp2kR1p/4p2q/1N6/3P4/8/PPP3PP/6K1 b - - 7 4';
        $moves      = array('Kd8', 'Qc7', 'Ke8', 'Qc8', 'Ke7', 'Rf7');

        $this->game->resetGame($startFen);

        foreach ($moves as $move) {
            $this->game->moveSAN($move);
        }

        $this->assertEquals($endFen, $this->game->renderFen());
    }
}
