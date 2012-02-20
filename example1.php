<?php

    require('library/GamesChesscomStandard.php');

    $startTime = microtime(true);

    $moveList = 'mCYIgvZRfH5ZegWOHZ6ZlBIBdB!TcM3VMT2Tbs0Sad?!sm46kA90jr7GBlGldlZQmw!EpxE!vB89BQ6Qfd92gp!6wmXHAHOHmBQcdc6cBHcQiy29pw98wE8ZnDQIHBVNEFTLFNLCNE1LENIJNUCultJBtuBDowDCuvRJxFJBFNCuvDuwU1BtNVwxDB0RyGSKGOxVBtKCOWV?tx?41ULDULCu';

    $chessGame = new GamesChesscomStandard();
    $chessGame->resetGame();

    while(strlen($moveList) > 0) {
      $currentMove = substr($moveList, 0, 2);
      $strMoveFromSquare = GamesChesscomStandard::GetMoveFromSquare($currentMove);
      $strMoveToSquare = GamesChesscomStandard::GetMoveToSquare($currentMove);
      if($chessGame->isPawn($chessGame->_board[$strMoveFromSquare])) {
        $strPromotionPiece = GamesChesscomStandard::GetPromotionPiece($currentMove);
      }
      $result = $chessGame->moveSquare($strMoveFromSquare, $strMoveToSquare, $strPromotionPiece);
      $moveList = substr($moveList,2);
    }

    $endTime = microtime(true);

    echo "Processing Time: " . ($endTime - $startTime) . " seconds\n";

