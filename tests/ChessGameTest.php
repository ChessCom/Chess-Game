<?php
/**
 * Created by Vadzim Lukiashka.
 * User: vadziml
 * Date: 1/9/13
 * Time: 12:10 AM
 */
error_reporting(E_ALL & ~E_STRICT & ~E_NOTICE);
require_once 'library/ChessGame.php';

class ChessGameTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider dataProvider
     */
    public function testValidMove($fen, $moves)
    {
        $objChessGame = new ChessGame();
        $objChessGame->resetGame($fen);

//        Should not be any exception here
        foreach ($moves as $objMoveData) {
            if ($objMoveData['white']) {
                $strCleanMove = $this->cleanSANMove($objMoveData['white']);
                $objChessGame->moveSAN($strCleanMove);
            }

            if ($objMoveData['black']) {
                $strCleanMove = $this->cleanSANMove($objMoveData['black']);
                $objChessGame->moveSAN($strCleanMove);
            }
        }
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
