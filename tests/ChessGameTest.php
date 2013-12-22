<?php
use Chess\Game\ChessGame;

error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
require_once __DIR__. '/../ChessGame.php';
require_once 'PEAR.php';

class ChessGameTest extends PHPUnit_Framework_TestCase
{
    private $game;

    public function setUp()
    {
        parent::setUp();
        $this->game = new ChessGame();
    }

    /**
     * @dataProvider dataProvider
     */
    public function testValidMove($fen, $moves)
    {
        $this->game->resetGame($fen);

//        Should not be any exception here
        foreach ($moves as $objMoveData) {
            if ($objMoveData['white']) {
                $strCleanMove = $this->cleanSANMove($objMoveData['white']);
                $this->game->moveSAN($strCleanMove);
            }

            if ($objMoveData['black']) {
                $strCleanMove = $this->cleanSANMove($objMoveData['black']);
                $this->game->moveSAN($strCleanMove);
            }
        }
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
//    TODO: add more combinations if needed
    public function dataProvider()
    {
        return array(
            array(
                "rn3b1N/pp2k2p/4p2q/1NQ5/3P4/8/PPP3PP/5RK1 b - - 1 1",
                array(
                    1 => array("black" => "Kd8"),
                    2 => array("white" => "Qc7+", "black" => "Ke8"),
                    3 => array("white" => "Qc8+", "black" => "Ke7"),
                    4 => array("white" => "Rf7#"),
                ),
            ),
        );
    }

    protected function cleanSANMove($strSanMove)
    {
        return trim(preg_replace("/[^a-zA-Z0-9\-]/", "", $strSanMove));
    }
}
