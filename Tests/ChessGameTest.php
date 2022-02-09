<?php

namespace Chess\Game;

use PHPUnit\Framework\TestCase;

class ChessGameTest extends TestCase
{
    /** @var ChessGame */
    private $game;

    public function setUp(): void
    {
        $this->game = new ChessGame();
    }

    public function testAddPiece()
    {
        $this->game->blankBoard();
        $this->game->addPiece('B', 'P', 'a7');
        $this->assertEquals('8/p7/8/8/8/8/8/8 w KQkq - 1 1', $this->game->renderFen());
    }

    public function testAddPiecePromotedPawnForBishopKnightRook()
    {
        foreach (array('B', 'N', 'R') as $piece) {
            $this->game->blankBoard();
            $this->game->addPiece('B', $piece, 'a8');
            $this->game->addPiece('B', $piece, 'h8');
            $this->assertTrue($this->game->addPiece('B', $piece, 'a1'));
        }
    }

    public function testAddPiecePromotedPawnForQueen()
    {
        $this->game->blankBoard();
        $this->game->addPiece('B', 'Q', 'd8');
        $this->assertTrue($this->game->addPiece('B', 'Q', 'a1'));
    }

    public function testAddPieceInvalidAddingKingWhenAlreadyExists()
    {
        $this->game->blankBoard();
        $this->game->addPiece('B', 'K', 'e8');
        $this->assertInstanceOf('PEAR_Error', $this->game->addPiece('B', 'K', 'a6'));
    }

    public function testAddPieceInvalidSquareParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->addPiece('W', 'P', 'g9'));
    }

    public function testAddPieceSquareInvalidBecauseSquareIsAlreadyOccupiedNonPawnStartingRank()
    {
        $this->game->blankBoard();
        $this->game->addPiece('B', 'Q', 'a8');
        $this->assertInstanceOf('PEAR_Error', $this->game->addPiece('B', 'K', 'a8'));
    }

    public function testAddPieceSquareInvalidBecauseSquareIsAlreadyOccupiedPawnStartingRank()
    {
        $this->game->blankBoard();
        $this->game->addPiece('B', 'P', 'a7');
        $this->assertInstanceOf('PEAR_Error', $this->game->addPiece('B', 'Q', 'a7'));
    }

    public function testBlankBoardFen()
    {
        $this->game->blankBoard();
        $this->assertEquals('8/8/8/8/8/8/8/8 w KQkq - 1 1', $this->game->renderFen());
    }

    public function testCastlingBlackFromTheKingSide()
    {
        $startFen = 'rnbqk2r/pppp1ppp/5n2/2b1p3/P1P1P1P1/8/1P1P1P1P/RNBQKBNR b KQkq a3 0 4';
        $endFen = 'rnbq1rk1/pppp1ppp/5n2/2b1p3/P1P1P1P1/8/1P1P1P1P/RNBQKBNR w KQ - 1 5';
        $moves = array('O-O');

        $this->game->resetGame($startFen);

        foreach ($moves as $move) {
            $this->game->moveSAN($move);
        }

        $this->assertEquals($endFen, $this->game->renderFen());
        $this->assertTrue($this->game->canCastleKingside());
    }

    public function testCastlingBlackFromTheQueenSide()
    {
        $startFen = 'r3kbnr/pp3ppp/n2pb3/q1p1p3/P3P1PP/1PPP4/5P2/RNBQKBNR b KQkq - 0 7';
        $endFen = '2kr1bnr/pp3ppp/n2pb3/q1p1p3/P3P1PP/1PPP4/5P2/RNBQKBNR w KQ - 1 8';
        $moves = array('O-O-O');

        $this->game->resetGame($startFen);

        foreach ($moves as $move) {
            $this->game->moveSAN($move);
        }

        $this->assertEquals($endFen, $this->game->renderFen());
        $this->assertTrue($this->game->canCastleQueenside());
    }

    public function testCastlingWhiteFromTheKingSide()
    {
        $startFen = 'rnbqkbnr/4pppp/8/pppp4/5PP1/5N1B/PPPPP2P/RNBQK2R w KQkq a6 0 5';
        $endFen = 'rnbqkbnr/4pppp/8/pppp4/5PP1/5N1B/PPPPP2P/RNBQ1RK1 b kq - 1 5';
        $moves = array('O-O');

        $this->game->resetGame($startFen);

        foreach ($moves as $move) {
            $this->game->moveSAN($move);
        }

        $this->assertEquals($endFen, $this->game->renderFen());
        $this->assertTrue($this->game->canCastleKingside());
    }

    public function testCastlingWhiteFromTheQueenSide()
    {
        $startFen = 'rnbqkbnr/3pppp1/p1p5/1p5p/3P1B2/2NQ4/PPP1PPPP/R3KBNR w KQkq - 0 5';
        $endFen = 'rnbqkbnr/3pppp1/p1p5/1p5p/3P1B2/2NQ4/PPP1PPPP/2KR1BNR b kq - 1 5';
        $moves = array('O-O-O');

        $this->game->resetGame($startFen);

        foreach ($moves as $move) {
            $this->game->moveSAN($move);
        }

        $this->assertEquals($endFen, $this->game->renderFen());
        $this->assertTrue($this->game->canCastleQueenside());
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
            1 => array('white' => 'Nf3', 'black' => 'Nf6'),
            2 => array('white' => 'c4', 'black' => 'e6'),
            3 => array('white' => 'Nc3', 'black' => 'Bb4'),
            4 => array('white' => 'Qc2', 'black' => 'O-O'),
            5 => array('white' => 'a3', 'black' => 'Bxc3'),
            6 => array('white' => 'Qxc3', 'black' => 'b6'),
            7 => array('white' => 'b4', 'black' => 'd6'),
            8 => array('white' => 'Bb2', 'black' => 'Bb7'),
            9 => array('white' => 'g3', 'black' => 'c5'),
            10 => array('white' => 'Bg2', 'black' => 'Nbd7'),
            11 => array('white' => 'O-O', 'black' => 'Rc8'),
            12 => array('white' => 'd3', 'black' => 'Re8'),
            13 => array('white' => 'e4', 'black' => 'a6'),
            14 => array('white' => 'Qb3', 'black' => 'b5'),
            15 => array('white' => 'Nd2', 'black' => 'Rb8'),
            16 => array('white' => 'Rfc1', 'black' => 'Ba8'),
            17 => array('white' => 'Qd1', 'black' => 'Qe7'),
            18 => array('white' => 'cxb5', 'black' => 'axb5'),
            19 => array('white' => 'Nb3', 'black' => 'e5'),
            20 => array('white' => 'f3', 'black' => 'h5'),
            21 => array('white' => 'bxc5', 'black' => 'dxc5'),
            22 => array('white' => 'a4', 'black' => 'h4'),
            23 => array('white' => 'g4', 'black' => 'c4'),
            24 => array('white' => 'dxc4', 'black' => 'bxa4'),
            25 => array('white' => 'Ba3', 'black' => 'Qd8'),
            26 => array('white' => 'Nc5', 'black' => 'Bc6'),
            27 => array('white' => 'Nxa4', 'black' => 'Nh7'),
            28 => array('white' => 'Nc5', 'black' => 'Ng5'),
            29 => array('white' => 'Nxd7', 'black' => 'Bxd7'),
            30 => array('white' => 'Rc3', 'black' => 'Qa5'),
            31 => array('white' => 'Rd3', 'black' => 'Ba4'),
            32 => array('white' => 'Qe1', 'black' => 'Qa6'),
            33 => array('white' => 'Bc1', 'black' => 'Ne6'),
            34 => array('white' => 'Rda3', 'black' => 'Nc5'),
            35 => array('white' => 'Be3', 'black' => 'Qd6'),
            36 => array('white' => 'Rxa4'),
        );

        $this->game->resetGame();

        foreach ($moves as $playerMoves) {
            foreach ($playerMoves as $move) {
                $this->game->moveSAN($move);
            }
        }

        $this->assertEquals($endFen, $this->game->renderFen());
    }

    /**
     * Test two knights that can both move to the same square, but one is pinned to the king.
     *
     * 1.e4 e5 2.Bc4 Nf6 3.d3 Nc6 4.Nc3 Bb4 5.Ne2
     */
    public function testAmbiguousKnightMoves()
    {
        $endFen = 'r1bqk2r/pppp1ppp/2n2n2/4p3/1b2P3/2NP4/PPP1NPPP/R1BQKB1R w KQkq - 3 5';

        $moves = array(
            1 => array('white' => 'e4', 'black' => 'e5'),
            3 => array('white' => 'Nc3', 'black' => 'Nf6'),
            4 => array('white' => 'd3', 'black' => 'Bb4'),
            5 => array('white' => 'Ne2', 'black' => 'Nc6'),
            6 => array('white' => 'Ng2'),
        );

        $this->game->resetGame();

        foreach ($moves as $playerMoves) {
            foreach ($playerMoves as $move) {
                $this->game->moveSAN($move);
            }
        }

        $this->assertEquals($endFen, $this->game->renderFen());
    }

    public function testGameOverDueToCheckmate()
    {
        $startFen = '3k2R1/8/3K4/8/8/8/8/8 b - -';

        $this->game->resetGame($startFen);
        $this->assertEquals('W', $this->game->gameOver());
    }

    public function testGameOverDueTo50MoveDraw()
    {
        $startFen = '7k/4N3/4NK2/5B2/8/8/8/r7 w - - 100 112';

        $this->game->resetGame($startFen);
        $this->assertFalse($this->game->gameOver());
        $this->assert50MoveDraw();
    }

    public function testGetDiagonalColor()
    {
        $this->game->resetGame();
        $this->assertEquals('B', $this->game->getDiagonalColor('d4'));
        $this->assertEquals('W', $this->game->getDiagonalColor('a8'));
    }

    public function testGetDiagonalColorInvalidSquareParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getDiagonalColor('SQUARE_X'));
    }

    public function testGetPieceLocationsColorBlack()
    {
        $this->game->blankBoard();
        $this->game->addPiece('W', 'K', 'a1');
        $this->game->addPiece('W', 'Q', 'h1');
        $this->game->addPiece('B', 'P', 'c7');

        $locations = $this->invokeMethod($this->game, 'getPieceLocations', array('B'));
        $this->assertEquals(1, count($locations));
        $this->assertTrue(in_array('c7', $locations));
    }

    public function testGetPieceLocationsColorWhite()
    {
        $this->game->blankBoard();
        $this->game->addPiece('W', 'K', 'a1');
        $this->game->addPiece('W', 'Q', 'h1');
        $this->game->addPiece('B', 'P', 'c7');

        $locations = $this->invokeMethod($this->game, 'getPieceLocations', array('W'));
        $this->assertEquals(2, count($locations));
        $this->assertTrue(in_array('a1', $locations));
        $this->assertTrue(in_array('h1', $locations));
    }

    public function testGetPieceLocationsInvalidColorParameterError()
    {
        $this->game->blankBoard();
        $this->game->addPiece('W', 'K', 'a1');
        $this->game->addPiece('W', 'Q', 'h1');
        $this->game->addPiece('B', 'P', 'c7');

        $error = $this->invokeMethod($this->game, 'getPieceLocations', array('COLOR_X'));
        $this->assertInstanceOf('PEAR_Error', $error);
    }

    public function testGetPieceLocationsNoColorSpecified()
    {
        $this->game->blankBoard();
        $this->game->addPiece('W', 'K', 'a1');
        $this->game->addPiece('W', 'Q', 'h1');
        $this->game->addPiece('B', 'P', 'c7');

        $locations = $this->invokeMethod($this->game, 'getPieceLocations');
        $this->assertEquals(2, count($locations));
        $this->assertTrue(in_array('a1', $locations));
        $this->assertTrue(in_array('h1', $locations));
    }

    public function testGetPossibleBishopMovesNoColorSpecified()
    {
        $this->game->resetGame();
        $this->game->moveSAN('e4');
        $this->game->moveSAN('c5');

        $locations = $this->game->getPossibleBishopMoves('f1');
        $this->assertEquals(5, count($locations));
        $this->assertTrue(in_array('e2', $locations));
        $this->assertTrue(in_array('d3', $locations));
        $this->assertTrue(in_array('c4', $locations));
        $this->assertTrue(in_array('b5', $locations));
        $this->assertTrue(in_array('a6', $locations));
    }

    public function testGetPossibleBishopMovesInvalidColorParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossibleBishopMoves('f1', 'COLOR_X'));
    }

    public function testGetPossibleBishopMovesInvalidSquareParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossibleBishopMoves('SQUARE_X'));
    }

    public function testGetPossibleKingMovesNoColorSpecified()
    {
        $this->game->resetGame();
        $this->game->moveSAN('e4');
        $this->game->moveSAN('c5');
        $locations = $this->game->getPossibleKingMoves('e1');

        $this->assertEquals(3, count($locations));
        $this->assertTrue(in_array('c1', $locations));
        $this->assertTrue(in_array('g1', $locations));
        $this->assertTrue(in_array('e2', $locations));
    }

    public function testGetPossibleKingMovesInvalidColorParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossibleKingMoves('e1', 'COLOR_X'));
    }

    public function testGetPossibleKingMovesInvalidSquareParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossibleKingMoves('SQUARE_X'));
    }

    public function testGetPossibleKnightMovesNoColorSpecified()
    {
        $this->game->resetGame();

        $locations = $this->game->getPossibleKnightMoves('g1');
        $this->assertEquals(2, count($locations));
        $this->assertTrue(in_array('f3', $locations));
        $this->assertTrue(in_array('h3', $locations));
    }

    public function testGetPossibleKnightMovesInvalidColorParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossibleKnightMoves('g1', 'COLOR_X'));
    }

    public function testGetPossibleKnightMovesInvalidSquareParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossibleKnightMoves('SQUARE_X'));
    }

    public function testGetPossibleMovesInvalidColorParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossibleMoves('P', 'a2', 'COLOR_X'));
    }

    public function testGetPossibleMovesInvalidPieceParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossibleMoves('PIECE_X', 'a2'));
    }

    public function testGetPossibleMovesInvalidSquareParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossibleMoves('P', 'SQUARE_X'));
    }

    public function testGetPossibleMovesNoColorSpecified()
    {
        $this->game->resetGame();
        $moves = $this->game->getPossibleMoves('P', 'a2');
        $this->assertEquals(2, count($moves));
        $this->assertTrue(in_array('a3', $moves));
        $this->assertTrue(in_array('a4', $moves));
    }

    public function testGetPossiblePawnMovesNoColorSpecified()
    {
        $this->game->resetGame();

        $locations = $this->game->getPossiblePawnMoves('h2');
        $this->assertEquals(2, count($locations));
        $this->assertTrue(in_array('h3', $locations));
        $this->assertTrue(in_array('h3', $locations));
    }

    public function testGetPossiblePawnMovesInvalidColorParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossiblePawnMoves('h2', 'COLOR_X'));
    }

    public function testGetPossiblePawnMovesInvalidSquareParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossiblePawnMoves('SQUARE_X'));
    }

    public function testGetPossiblePawnMovesEnPassantBlackSideOne()
    {
        $this->game->blankBoard();
        $this->game->addPiece('W', 'P', 'c2');
        $this->game->addPiece('B', 'P', 'd4');

        $this->game->moveSAN('c4');

        $locations = $this->game->getPossiblePawnMoves('d4');
        $this->assertEquals(2, count($locations));
        $this->assertTrue(in_array('c3', $locations));
        $this->assertTrue(in_array('d3', $locations));
    }

    public function testGetPossiblePawnMovesEnPassantBlackSideTwo()
    {
        $this->game->blankBoard();
        $this->game->addPiece('W', 'P', 'e2');
        $this->game->addPiece('B', 'P', 'd4');

        $this->game->moveSAN('e4');

        $locations = $this->game->getPossiblePawnMoves('d4');
        $this->assertEquals(2, count($locations));
        $this->assertTrue(in_array('d3', $locations));
        $this->assertTrue(in_array('e3', $locations));
    }

    public function testGetPossiblePawnMovesEnPassantWhiteSideOne()
    {
        $this->game->blankBoard();
        $this->game->addPiece('W', 'P', 'e4');
        $this->game->addPiece('B', 'P', 'd7');

        $this->game->moveSAN('e5');
        $this->game->moveSAN('d5');

        $locations = $this->game->getPossiblePawnMoves('e5');
        $this->assertEquals(2, count($locations));
        $this->assertTrue(in_array('d6', $locations));
        $this->assertTrue(in_array('e6', $locations));
    }

    public function testGetPossiblePawnMovesEnPassantWhiteSideTwo()
    {
        $this->game->blankBoard();
        $this->game->addPiece('W', 'P', 'e4');
        $this->game->addPiece('B', 'P', 'f7');

        $this->game->moveSAN('e5');
        $this->game->moveSAN('f5');

        $locations = $this->game->getPossiblePawnMoves('e5');
        $this->assertEquals(2, count($locations));
        $this->assertTrue(in_array('e6', $locations));
        $this->assertTrue(in_array('f6', $locations));
    }

    public function testGetPossibleRookMovesInvalidColorParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossibleRookMoves('h1', 'COLOR_X'));
    }

    public function testGetPossibleRookMovesInvalidSquareParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->getPossibleRookMoves('SQUARE_X'));
    }

    public function testGetPossibleRookMovesNoColorSpecified()
    {
        $this->game->resetGame();
        $this->game->moveSAN('h4');
        $this->game->moveSAN('c5');

        $locations = $this->game->getPossibleRookMoves('h1');
        $this->assertEquals(2, count($locations));
        $this->assertTrue(in_array('h2', $locations));
        $this->assertTrue(in_array('h3', $locations));
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

    public function testIsInBasicDrawKingBishopVersusKingBishopWithBishopsOnSameColor()
    {
        $startFen = '7B/8/8/8/8/6k1/1b6/5K2 w - -';

        $this->game->resetGame($startFen);
        $this->assertBasicDraw();
    }

    public function testIsInBasicDrawKingBishopVersusKingBishopWithBishopsOnDifferentColor()
    {
        $startFen = '8/8/8/6k1/1K6/8/2B4b/8 w - -';

        $this->game->resetGame($startFen);
        $this->assertBasicDraw();
    }

    public function testIsInBasicDrawKingVersusKing()
    {
        $startFen = '8/5k2/8/8/6K1/8/8/8 w - -';

        $this->game->resetGame($startFen);
        $this->assertBasicDraw();
    }

    public function testIsInBasicDrawKingVersusKingWithBishopOrKnight()
    {
        foreach (array('b', 'B', 'N', 'n') as $piece) {
            $startFen = sprintf('8/2%s2k2/8/8/6K1/8/8/8 w - -', $piece);

            $this->game->resetGame($startFen);
            $this->assertBasicDraw();
        }
    }

    public function testIsInBasicDrawKingAndTwoKnightsVsKing()
    {
        $startFen = '8/8/4K3/8/4k3/3nn3/8/8 w - -';

        $this->game->resetGame($startFen);
        $this->assertTrue($this->game->inBasicDraw());
    }

    public function testIsNotInBasicDrawWith4pieces()
    {
        $startFen = '8/8/6B1/3k4/8/4K3/5Q2/8 w - -';

        $this->game->resetGame($startFen);
        $this->assertFalse($this->game->inBasicDraw());
    }

    public function testIsInCheckmate()
    {
        $startFen = '3k2R1/8/3K4/8/8/8/8/8 b - -';

        $this->game->resetGame($startFen);
        $this->assertTrue($this->game->inCheckMate());
    }

    public function testIsInCheckmateInvalidColorParameterError()
    {
        $this->game->resetGame();
        $this->assertInstanceOf('PEAR_Error', $this->game->inCheckMate('COLOR_X'));
    }

    public function testIsInFiftyRuleDraw()
    {
        $startFen = '7k/4N3/4NK2/5B2/8/8/8/r7 w - - 100 112';

        $this->game->resetGame($startFen);
        $this->assert50MoveDraw();
    }

    /**
     * FEN for board setup from Karpov vs. Kasparov, Tilburg, 1991
     *
     * @link http://en.wikipedia.org/wiki/Fifty-move_rule#Karpov_vs._Kasparov
     */
    public function testIsInStalemate()
    {
        $startFen = '7k/5Q2/6K1/8/8/8/8/8 b - -';

        $this->game->resetGame($startFen);
        $this->assertStalemate();
    }

    /**
     * FEN for board setup and subsequent moves from Fischer versus Petrosian, 1971
     *
     * @link http://en.wikipedia.org/wiki/Threefold_repetition#Fischer_versus_Petrosian.2C_1971
     * @link http://chesstempo.com/gamedb/game/2695095
     */
    public function testIsInThreefoldRepetitionDraw()
    {
        $this->game->resetGame('8/pp3p1k/2p2q1p/3r1P1Q/5R2/7P/P1P2P2/7K w - - 1 30');

        $moves = array(
            30 => array('white' => 'Qe2', 'black' => 'Qe5'),
            31 => array('white' => 'Qh5', 'black' => 'Qf6'),
            32 => array('white' => 'Qe2', 'black' => 'Re5'),
            33 => array('white' => 'Qd3', 'black' => 'Rd5'),
            34 => array('white' => 'Qe2',),
        );

        foreach ($moves as $playerMoves) {
            foreach ($playerMoves as $move) {
                $this->game->moveSAN($move);
            }
        }

        $this->assertRepetitionDraw();
    }

    public function testIsNotInBasicDrawTooManyBlackBishops()
    {
        $startFen = '6B1/1b6/8/8/8/6k1/1b6/5K2 w - -';

        $this->game->resetGame($startFen);
        $this->assertFalse($this->game->inBasicDraw());
    }

    public function testIsNotInBasicDrawTooManyBlackPieces()
    {
        $startFen = '6B1/1p6/8/8/8/6k1/1b6/5K2 w - -';

        $this->game->resetGame($startFen);
        $this->assertFalse($this->game->inBasicDraw());
    }

    public function testIsNotInBasicDrawTooManyWhiteBishops()
    {
        $startFen = '6B1/1B6/8/8/8/6k1/1b6/5K2 w - -';

        $this->game->resetGame($startFen);
        $this->assertFalse($this->game->inBasicDraw());
    }

    public function testIsNotInBasicDrawTooManyWhitePieces()
    {
        $startFen = '6B1/1P6/8/8/8/6k1/1b6/5K2 w - -';

        $this->game->resetGame($startFen);
        $this->assertFalse($this->game->inBasicDraw());
    }

    public function testIsNotInFiftyRuleDraw()
    {
        $this->game->resetGame();
        $this->assertFalse($this->game->in50MoveDraw());
    }

    public function testIsNotGameOver()
    {
        $this->game->resetGame();
        $this->assertFalse($this->game->gameOver());
    }

    public function testIsNotInStalemateBecauseHasLegalMoves()
    {
        $startFen = 'rnbqkbnr/pp1ppppp/8/2p5/4P3/5N2/PPPP1PPP/RNBQKB1R b KQkq - 1 2';

        $this->game->resetGame($startFen);
        $this->assertFalse($this->game->inStaleMate());
    }

    public function testIsNotInStalemateBecauseIsInCheck()
    {
        $startFen = '8/8/2k4/8/8/8/2R4/4K3 b KQkq - 1 2';

        $this->game->resetGame($startFen);
        $this->assertFalse($this->game->inStaleMate());
    }

    public function testIsInNotThreefoldRepetitionDraw()
    {
        $this->game->resetGame();
        $this->assertFalse($this->game->inRepetitionDraw());
    }

    public function testIsNotPromoteMove()
    {
        $this->game->resetGame();
        $this->game->moveSAN('e4');
        $this->game->moveSAN('c5');
        $this->assertFalse($this->game->isPromoteMove('e4', 'e5'));
    }

    public function testIsNotPromoteMoveIllegalMove()
    {
        $this->game->blankBoard();
        $this->game->addPiece('W', 'B', 'a1');
        $this->game->addPiece('W', 'B', 'h1');
        $this->game->addPiece('W', 'P', 'b7');
        $this->assertFalse($this->game->isPromoteMove('b7', 'a7'));
    }

    public function testIsPromoteMove()
    {
        $this->game->blankBoard();
        $this->game->addPiece('W', 'B', 'a1');
        $this->game->addPiece('W', 'B', 'h1');
        $this->game->addPiece('W', 'P', 'b7');
        $this->assertTrue($this->game->isPromoteMove('b7', 'b8'));
    }

    public function testMoveList()
    {
        $moves = array('Nf3', 'Nf6', 'c4', 'e6', 'Nc3', 'Bb4');

        $this->game->resetGame();

        foreach ($moves as $move) {
            $this->game->moveSAN($move);
        }

        $moveList = $this->game->getMoveList();
        $this->assertTrue(is_array($moveList));
        $this->assertEquals(3, count($moveList));

        foreach ($moveList as $madeMoves) {
            $this->assertTrue(is_array($madeMoves));
            $this->assertEquals(2, count($madeMoves));
        }

        $this->assertEquals('Nf3', $moveList[1][0]);
        $this->assertEquals('Nf6', $moveList[1][1]);

        $this->assertEquals('c4', $moveList[2][0]);
        $this->assertEquals('e6', $moveList[2][1]);

        $this->assertEquals('Nc3', $moveList[3][0]);
        $this->assertEquals('Bb4', $moveList[3][1]);
    }

    public function testMoveListString()
    {
        $moves = array('Nf3', 'Nf6', 'c4', 'e6', 'Nc3');

        $this->game->resetGame();

        foreach ($moves as $move) {
            $this->game->moveSAN($move);
        }

        $moveList = $this->game->getMoveListString();
        $this->assertTrue(is_string($moveList));
        $this->assertEquals('1.Nf3 Nf6 2.c4 e6 3.Nc3', $moveList);
    }

    public function testMoveListWithChecks()
    {
        $moves = array('Rf1', 'Kb6', 'Rf6', 'Kb5', 'Rf5', 'Kb6');
        $startFen = '8/8/k7/8/8/8/4K3/4R3 w - -';

        $this->game->resetGame($startFen);

        foreach ($moves as $move) {
            $this->game->moveSAN($move);
        }

        $moveList = $this->game->getMoveList(true);
        $this->assertTrue(is_array($moveList));
        $this->assertEquals(3, count($moveList));

        foreach ($moveList as $madeMoves) {
            $this->assertTrue(is_array($madeMoves));
            $this->assertEquals(2, count($madeMoves));
        }

        $this->assertEquals('Rf1', $moveList[1][0]);
        $this->assertEquals('Kb6', $moveList[1][1]);

        $this->assertEquals('Rf6+', $moveList[2][0]);
        $this->assertEquals('Kb5', $moveList[2][1]);

        $this->assertEquals('Rf5+', $moveList[3][0]);
        $this->assertEquals('Kb6', $moveList[3][1]);
    }

    public function testMoveListStringWithChecks()
    {
        $moves = array('Rf1', 'Kb6', 'Rf6', 'Kb5', 'Rf5', 'Kb6');
        $startFen = '8/8/k7/8/8/8/4K3/4R3 w - -';

        $this->game->resetGame($startFen);

        foreach ($moves as $move) {
            $this->game->moveSAN($move);
        }

        $moveList = $this->game->getMoveListString(true);
        $this->assertTrue(is_string($moveList));
        $this->assertEquals('1.Rf1 Kb6 2.Rf6+ Kb5 3.Rf5+ Kb6', $moveList);
    }

    public function testNewChess960GameRenderedFen()
    {
        $this->game->resetGame(false, true);
        $this->assertEquals('rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR w KQkq - 0 1', $this->game->renderFen());
    }

    // XXX: Parsing a legitimate Chess960 FEN appears to be horked
    /**
     * Set up a new Chess960 board. FEN generated at lichness.org
     *
     * @link http://en.lichess.org
     */
//    public function testNewChess960GameRenderedFen()
//    {
//        $fen = 'nrnbbkqr/pppppppp/8/8/8/8/PPPPPPPP/NRNBBKQR w KQkq - 0 1 ';
//        $this->game->resetGame($fen, true);
//        $this->assertEquals($fen, $this->game->renderFen());
//    }

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

    public function testState()
    {
        $this->game->resetGame();

        $originalState = $this->game->getState();
        $this->game->moveSAN('e4');

        $newState = $this->game->getState();
        $this->assertTrue($newState === $this->game->getState());
        $this->assertFalse($newState === $originalState);

        $this->game->setState($originalState);
        $this->assertFalse($newState === $this->game->getState());
        $this->assertTrue($originalState === $this->game->getState());

        $this->game->commitTransaction();
        $this->assertTrue($originalState === $this->game->getState());
    }

    public function testToArray()
    {
        $piecesToSet = array(
            'a2' => array('color' => 'B', 'piece' => 'P'),
            'd1' => array('color' => 'B', 'piece' => 'Q'),
            'd8' => array('color' => 'W', 'piece' => 'Q'),
        );

        $this->game->blankBoard();

        foreach ($piecesToSet as $square => $data) {
            $this->game->addPiece($data['color'], $data['piece'], $square);
        }

        $gameBoardArray = $this->game->toArray();
        $this->assertTrue(is_array($gameBoardArray));
        $this->assertEquals(64, count($gameBoardArray));

        $referenceArray = array();

        foreach (array(1, 2, 3, 4, 5, 6, 7, 8) as $column) {
            foreach (array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h') as $rank) {
                $index = $rank . $column;

                if (array_key_exists($index, $piecesToSet)) {
                    $color = $piecesToSet[$index]['color'];
                    $piece = $piecesToSet[$index]['piece'];
                    $referenceArray[$index] = ('W' == $color) ? $piece : strtolower($piece);
                } else {
                    $referenceArray[$index] = false;
                }
            }
        }

        $this->assertEquals($referenceArray, $gameBoardArray);
    }

    public function testToMoveBlack()
    {
        $this->game->resetGame();
        $this->assertEquals('W', $this->game->toMove());
    }

    public function testToMoveWhite()
    {
        $this->game->resetGame();
        $this->game->moveSAN('e4');
        $this->assertEquals('B', $this->game->toMove());
    }

    public function testMovesShouldNotBeChangedAfterCheckingIfGameInStalemate()
    {
        $this->game->resetGame();
        $this->game->moveSAN('h3');
        $this->assertFalse($this->game->inStaleMate());
        $this->assertEquals(array(1 => array('h3')), $this->game->getMoveList());
    }

    public function testInitialSetupWithDuplicatedPiecesShouldBeValid()
    {
        $this->game->resetGame('nnnnknnn/pppppppp/8/8/8/8/PPPPPPPP/R1BQKB1R w KQ -');
        $this->assertTrue($this->game->moveSAN('b3'));
    }

    public function testGameShouldNotBeOverInClaimableDraw()
    {
        $moves = array('e4', 'e5', 'Nf3', 'Nc6', 'Ng1', 'Nb8', 'Nf3', 'Nc6', 'Ng1', 'Nb8', 'Nf3');
        $this->game->resetGame();
        foreach ($moves as $move) {
            $this->game->moveSAN($move);
        }

        $this->assertRepetitionDraw();
    }

    private function assertBasicDraw()
    {
        $this->assertTrue($this->game->inBasicDraw());
        $this->assertTrue($this->game->inForcedDraw());
        $this->assertTrue($this->game->inDraw());
        $this->assertFalse($this->game->inClaimableDraw());
        $this->assertNotFalse($this->game->gameOver());
    }

    private function assertStalemate()
    {
        $this->assertTrue($this->game->inStaleMate());
        $this->assertTrue($this->game->inForcedDraw());
        $this->assertTrue($this->game->inDraw());
        $this->assertFalse($this->game->inClaimableDraw());
        $this->assertNotFalse($this->game->gameOver());
    }

    private function assert50MoveDraw()
    {
        $this->assertTrue($this->game->in50MoveDraw());
        $this->assertTrue($this->game->inDraw());
        $this->assertTrue($this->game->inClaimableDraw());
        $this->assertFalse($this->game->inForcedDraw());
        $this->assertFalse($this->game->gameOver());
    }

    private function assertRepetitionDraw()
    {
        $this->assertTrue($this->game->inRepetitionDraw());
        $this->assertTrue($this->game->inDraw());
        $this->assertTrue($this->game->inClaimableDraw());
        $this->assertFalse($this->game->inForcedDraw());
        $this->assertFalse($this->game->gameOver());
    }

    public function testKingValidMovesWithMoveSAN()
    {
        $this->game->resetGame();
        $this->game->moveSAN('e4');
        $this->game->moveSAN('e5');
        $this->game->moveSAN('d4');
        $this->game->moveSAN('exd4');
        $this->game->moveSAN('Qxd4');
        $this->game->moveSAN('Nc6');
        $this->game->moveSAN('Qd1');
        $this->game->moveSAN('Bd6');
        $this->game->moveSAN('a3');
        $this->game->moveSAN('Nf6');
        $this->game->moveSAN('Nc3');
        $this->game->moveSAN('Nxe4');
        $this->game->moveSAN('Qe7');
        $this->game->moveSAN('Qe2');
        $this->assertTrue($this->game->isError($this->game->moveSAN('Kxe7')));
    }

    public function testKingValidMovesMakingMoves()
    {
        $this->game->resetGame();
        $this->game->moveSquare('e2', 'e4');
        $this->game->moveSquare('e7', 'e5');
        $this->game->moveSquare('d2', 'd4');
        $this->game->moveSquare('e5', 'd4');
        $this->game->moveSquare('d1', 'd4');
        $this->game->moveSquare('b8', 'c6');
        $this->game->moveSquare('d4', 'd1');
        $this->game->moveSquare('f8', 'd6');
        $this->game->moveSquare('a2', 'a3');
        $this->game->moveSquare('g8', 'f6');
        $this->game->moveSquare('b1', 'c3');
        $this->game->moveSquare('f6', 'e4');
        $this->game->moveSquare('c3', 'e4');
        $this->game->moveSquare('d8', 'e7');
        $this->game->moveSquare('d1', 'e2');
        $this->assertTrue($this->game->isError($this->game->moveSquare('e8', 'e7')));
    }

    public function testKingShouldNotTakeOwnPiece()
    {
        $this->game->resetGame('5b1r/1b2k1p1/2N1pn1p/BBP5/3P4/1PN1P3/1P3PPP/4K2R b K - 0 22');
        $this->assertTrue($this->game->isError($this->game->moveSquare('e7', 'e6')));
    }

    public function testHasMatingMaterialShouldReturnTrueOnStandardSetup()
    {
        $this->game->resetGame();
        $this->assertTrue($this->game->hasBlackMatingMaterial());
        $this->assertTrue($this->game->hasWhiteMatingMaterial());
    }

    public function testHasMatingMaterialShouldReturnTrueForBlackAndFalseForWhite()
    {
        $this->game->resetGame('8/8/8/8/5b2/8/4kpK1/8 b - - 1 86');
        $this->assertFalse($this->game->hasWhiteMatingMaterial());
        $this->assertTrue($this->game->hasBlackMatingMaterial());
    }

    public function testGameShouldEndInFiveFoldRepetition()
    {
        $moves = array(
            'Nc3',
            'Nc6',
            'Nb1',
            'Nb8',
            'Nc3',
            'Nc6',
            'Nb1',
            'Nb8',
            'Nc3',
            'Nc6',
            'Nb1',
            'Nb8',
            'Nc3',
            'Nc6',
            'Nb1',
            'Nb8',
            'Nc3',
            'Nc6',
            'Nb1',
            'Nb8',
            'Nc3',
            'Nc6'
        );
        $this->game->resetGame();
        foreach ($moves as $move) {
            $this->game->moveSAN($move);
        }
        $this->assertFiveFoldRepetitionDraw();
    }

    public function testNotInBasicDrawTwoWhiteVsTwoBlack()
    {
        $fen = '8/8/8/8/4k3/8/3Kn3/6Q1 w - -';
        $this->game->resetGame($fen);
        $this->assertFalse($this->game->inBasicDraw());
    }

    public function testInBasicDrawThreePiecesRemained()
    {
        $fen = '8/8/8/8/4k3/8/3K4/6n1 w - -';
        $this->game->resetGame($fen);
        $this->assertTrue($this->game->inBasicDraw());
    }

    private function assertFiveFoldRepetitionDraw()
    {
        $this->assertTrue($this->game->inRepetitionDraw());
        $this->assertTrue($this->game->inDraw());
        $this->assertFalse($this->game->inClaimableDraw());
        $this->assertTrue($this->game->inForcedDraw());
        $this->assertEquals('D', $this->game->gameOver());
    }

    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     */
    private function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
    }
}
