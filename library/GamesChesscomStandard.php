<?php
require_once 'GamesChesscomStandardSimple.php';

class GamesChesscomStandard extends GamesChesscomStandardSimple {

  public static $objTranslateSquare = array(
    'a' => 'a1',
    'b' => 'b1',
    'c' => 'c1',
    'd' => 'd1',
    'e' => 'e1',
    'f' => 'f1',
    'g' => 'g1',
    'h' => 'h1',
    'i' => 'a2',
    'j' => 'b2',
    'k' => 'c2',
    'l' => 'd2',
    'm' => 'e2',
    'n' => 'f2',
    'o' => 'g2',
    'p' => 'h2',
    'q' => 'a3',
    'r' => 'b3',
    's' => 'c3',
    't' => 'd3',
    'u' => 'e3',
    'v' => 'f3',
    'w' => 'g3',
    'x' => 'h3',
    'y' => 'a4',
    'z' => 'b4',
    'A' => 'c4',
    'B' => 'd4',
    'C' => 'e4',
    'D' => 'f4',
    'E' => 'g4',
    'F' => 'h4',
    'G' => 'a5',
    'H' => 'b5',
    'I' => 'c5',
    'J' => 'd5',
    'K' => 'e5',
    'L' => 'f5',
    'M' => 'g5',
    'N' => 'h5',
    'O' => 'a6',
    'P' => 'b6',
    'Q' => 'c6',
    'R' => 'd6',
    'S' => 'e6',
    'T' => 'f6',
    'U' => 'g6',
    'V' => 'h6',
    'W' => 'a7',
    'X' => 'b7',
    'Y' => 'c7',
    'Z' => 'd7',
    '0' => 'e7',
    '1' => 'f7',
    '2' => 'g7',
    '3' => 'h7',
    '4' => 'a8',
    '5' => 'b8',
    '6' => 'c8',
    '7' => 'd8',
    '8' => 'e8',
    '9' => 'f8',
    '!' => 'g8',
    '?' => 'h8'
  );

  public static $objPointValue = array(
    'P' => 1,
    'N' => 3,
    'B' => 3,
    'R' => 5,
    'Q' => 9
  );

  public function _takePiece($square) {
    if(isset($this->_pieces[$this->_board[$square]])) {
      $piece = $this->_board[$square];
      $color = $this->_getColor($piece);
      $pieceChar = GamesChesscomStandard::GetPieceChar($piece);

      $this->_pieces[$this->_board[$square]] = false;
    }
  }

  public function GetCaptured() {
    $objCapturedList = array(
      'W' =>
          array(
              'P' => 8,
              'B' => 2,
              'N' => 2,
              'Q' => 1,
              'R' => 2,
          ),
      'B' =>
          array(
              'P' => 8,
              'B' => 2,
              'N' => 2,
              'Q' => 1,
              'R' => 2,
          )
    );

    foreach($this->_pieces as $strPieceDetailed => $strLocation) {
      if($strLocation) {
        $strColor = substr($strPieceDetailed,0,1);
        $strPiece = substr($strPieceDetailed,1,1);
        if($strPiece != 'K') {
          if($objCapturedList[$strColor][$strPiece] > 0) {
            $objCapturedList[$strColor][$strPiece]--;
          } else {
            $objCapturedList[$strColor]['P']--;
          }
        }
      }
    }

    return $objCapturedList;
  }

  public function BuildGame($objGame) {
    $this->resetGame($objGame->InitialSetup, ($objGame->GameTypeId == GameType::CodeToId('chess960')));

    $strMoveList = $objGame->MoveText;
    while(strlen($strMoveList) > 0) {
      $strCurrentMove = substr($strMoveList, 0, 2);
      $strMoveFromSquare = GamesChesscomStandard::GetMoveFromSquare($strCurrentMove);
      $strMoveToSquare = GamesChesscomStandard::GetMoveToSquare($strCurrentMove);
      if($this->isPawn($this->_board[$strMoveFromSquare])) {
        $strPromotionPiece = GamesChesscomStandard::GetPromotionPiece($strCurrentMove);
      }
      $result = $this->moveSquare($strMoveFromSquare, $strMoveToSquare, $strPromotionPiece);
      $strMoveList = substr($strMoveList,2);
    }

  }

  public function BuildGameFromPiotrMoveList($strPiotrMoveList, $strInitialSetup = null, $blnChess960 = false) {
    $this->resetGame($strInitialSetup, $blnChess960);

    $strMoveList = $strPiotrMoveList;
    while(strlen($strMoveList) > 0) {
      $strCurrentMove = substr($strMoveList, 0, 2);
      $strMoveFromSquare = GamesChesscomStandard::GetMoveFromSquare($strCurrentMove);
      $strMoveToSquare = GamesChesscomStandard::GetMoveToSquare($strCurrentMove);
      if($this->isPawn($this->_board[$strMoveFromSquare])) {
        $strPromotionPiece = GamesChesscomStandard::GetPromotionPiece($strCurrentMove);
      }
      $result = $this->moveSquare($strMoveFromSquare, $strMoveToSquare, $strPromotionPiece);
      if($result !== true) {
        return false;
      }
      $strMoveList = substr($strMoveList,2);
    }
    return true;
  }

  public function BuildCmPosition($objPosition) {
    $this->resetGame($objPosition->Fen);
  }

  public static function GetMoveFromSquare($strMove) {
    $strMoveFromChar = substr($strMove,0,1);

    return self::$objTranslateSquare[$strMoveFromChar];
  }

  public static function GetMoveToSquare($strMove) {
    $strMoveToChar = substr($strMove,1);

    $objPromotionCharList = array('(','^',')','[','_',']','@','#','$','{','~','}');
    $objPromotionLeftList = array('(','[','@','{');
    $objPromotionRightList = array(')',']','$','}');

    if(!in_array($strMoveToChar,$objPromotionCharList)) {
      return self::$objTranslateSquare[$strMoveToChar];
    } else {
      $strMoveFromSquare = self::GetMoveFromSquare($strMove);
      $intRank = substr($strMoveFromSquare,1);
      $strColumn = substr($strMoveFromSquare,0,1);

      if($intRank == 2) {
        $intNewRank = 1;
      } else {
        $intNewRank = 8;
      }

      if(in_array($strMoveToChar,$objPromotionLeftList)) {
        switch($strColumn) {
          case "b":
            $strMoveToColumn = 'a';
            break;
          case "c":
            $strMoveToColumn = 'b';
            break;
          case "d":
            $strMoveToColumn = 'c';
            break;
          case "e":
            $strMoveToColumn = 'd';
            break;
          case "f":
            $strMoveToColumn = 'e';
            break;
          case "g":
            $strMoveToColumn = 'f';
            break;
          case "h":
            $strMoveToColumn = 'g';
            break;
        }
      } elseif(in_array($strMoveToChar,$objPromotionRightList)) {
        switch($strColumn) {
          case "a":
            $strMoveToColumn = 'b';
            break;
          case "b":
            $strMoveToColumn = 'c';
            break;
          case "c":
            $strMoveToColumn = 'd';
            break;
          case "d":
            $strMoveToColumn = 'e';
            break;
          case "e":
            $strMoveToColumn = 'f';
            break;
          case "f":
            $strMoveToColumn = 'g';
            break;
          case "g":
            $strMoveToColumn = 'h';
            break;
        }
      } else {
        $strMoveToColumn = $strColumn;
      }
      return ($strMoveToColumn . $intNewRank);
    }
  }

  public static function GetPromotionPiece($strMove) {
    //We assume that checking to be sure that a pawn was moved was already done before this function is called.

    $objPromotionCharList = array('(','^',')','[','_',']','@','#','$','{','~','}');
    $objPromotionLeftList = array('(','[','@','{');
    $objPromotionRightList = array(')',']','$','}');

    $objPromotionPieceMap = array(
      '(' => 'N',
      '^' => 'N',
      ')' => 'N',
      '[' => 'R',
      '_' => 'R',
      ']' => 'R',
      '@' => 'B',
      '#' => 'B',
      '$' => 'B',
      '{' => 'Q',
      '~' => 'Q',
      '}' => 'Q'
    );

    $strMoveToChar = substr($strMove,1);
    $strMoveToSquare = self::GetMoveToSquare($strMove);
    $intRank = substr($strMoveToSquare,1);

    if($intRank != 1 && $intRank != 8) {
      return false;
    } elseif(!in_array($strMoveToChar,$objPromotionCharList)) {
      return 'Q';
    } else {
      return $objPromotionPieceMap[$strMoveToChar];
    }
  }

  public function GetCmMoveFromSanMove($strSanMove) {
    $this->startTransaction();

    if ($this->isError($parsedMove = $this->_parseMove($strSanMove))) {
      echo "Invalid SAN Move: $strSanMove";
      $this->rollbackTransaction();
      return false;
    }


    if ($this->isError($err = $this->_validMove($parsedMove))) {
      echo "Unable to validate SAN Move: $strSanMove";
      $this->rollbackTransaction();
      return false;
    }

    list($key, $parsedMove) = each($parsedMove);
    if ($key == GAMES_CHESS_CASTLE) {
      $a = ($parsedMove == 'Q') ? 'K' : 'Q';
      // clear castling rights
      $this->{'_' . $this->_move . 'Castle' . $parsedMove} = false;
      $this->{'_' . $this->_move . 'Castle' . $a} = false;
      $row = ($this->_move == 'W') ? 1 : 8;
      switch ($parsedMove) {
        case 'K' :
          if($this->_Chess960) {
              $strCmMove = $this->_KColumn . $row . $this->_KRookColumn . $row;
            } else {
                $strCmMove = 'e' . $row . 'g' . $row;
            }
        break;
        case 'Q' :
            if($this->_Chess960) {
                $strCmMove = $this->_KColumn . $row . $this->_QRookColumn . $row;
            } else {
                $strCmMove = 'e' . $row . 'c' . $row;
            }
        break;
      }
    } else {
      $movedfrom = $this->_getSquareFromParsedMove($parsedMove);
      $promote = isset($parsedMove['promote']) ? $parsedMove['promote'] : '';

      $strCmMove = '';
      $strCmMove .= $movedfrom . $parsedMove['square'];
      if($promote) {
        $strCmMove .= '=' . $promote;
      }
    }

    $this->rollbackTransaction();

    return $strCmMove;
  }

  public function GetSanMoveFromCmMove($strCmMove) {
    $strMoveFrom = substr($strCmMove,0,2);
    $strMoveTo = substr($strCmMove,2,2);
    if(strlen($strCmMove) > 4) {
      $strPromotion = substr($strCmMove,5,1);
    } else {
      $strPromotion = '';
    }
    $strMoveSan = $this->_convertSquareToSAN($strMoveFrom,$strMoveTo,$strPromotion);

    return $strMoveSan;
  }

  public static function GetCmMoveFromPiotrMove($strPiotrMove, $objChessGame) {
    $strMoveFromSquare = self::GetMoveFromSquare($strPiotrMove);
    $strMoveToSquare = self::GetMoveToSquare($strPiotrMove);
    $objMovePiece = $objChessGame->_squareToPiece($strMoveFromSquare);
    $strPromotionPiece = false;
    if($objMovePiece) {
      $strPiece = $objMovePiece['piece'];
      if(strtolower($strPiece) == 'p') {
        $strPromotionPiece = self::GetPromotionPiece($strPiotrMove);
      }
    }

    $strCmMove = $strMoveFromSquare . $strMoveToSquare;
    if($strPromotionPiece) {
      $strCmMove .= '=' . $strPromotionPiece;
    }

    return $strCmMove;
  }

  public static function GetMoveDataFromPiotrMove($strPiotrMove, $objChessGame) {
    $strMoveFromSquare = self::GetMoveFromSquare($strPiotrMove);
    $strMoveToSquare = self::GetMoveToSquare($strPiotrMove);
    $objMovePiece = $objChessGame->_squareToPiece($strMoveFromSquare);
    $strPromotionPiece = '';
    if($objMovePiece) {
      $strPiece = $objMovePiece['piece'];
      if(strtolower($strPiece) == 'p') {
        $strPromotionPiece = self::GetPromotionPiece($strPiotrMove);
      }
    }

    $objMoveData = array(
      'move_from' => $strMoveFromSquare,
      'move_to' => $strMoveToSquare,
      'promotion' => $strPromotionPiece
    );

    return $objMoveData;
  }

  public static function GetPiotrMoveFromCmMove($strCmMove) {
    //strCmMove takes the form of d7d8=Q, and we need to convert that into Piotr's move format
    $objReverseTranslateSquare = array_flip(self::$objTranslateSquare);

    $strMoveFromChar = $objReverseTranslateSquare[substr($strCmMove,0,2)];
    if(strlen($strCmMove) > 4) {
      $strPromotionPiece = substr($strCmMove,5,1);

      $strFromColumn = substr($strCmMove,0,1);
      $strToColumn = substr($strCmMove,2,1);

      if($strFromColumn < $strToColumn) {
        $strPromoteDirection = 'right';
      } elseif($strFromColumn > $strToColumn) {
        $strPromoteDirection = 'left';
      } else {
        $strPromoteDirection = 'straight';
      }

      switch($strPromotionPiece) {
        case "Q":
        case "q":
          switch($strPromoteDirection) {
            case "right":
              $strMoveToChar = '}';
              break;
            case "left":
              $strMoveToChar = '{';
              break;
            default:
              $strMoveToChar = '~';
              break;
          }
          break;
        case "R":
        case "r":
          switch($strPromoteDirection) {
            case "right":
              $strMoveToChar = ']';
              break;
            case "left":
              $strMoveToChar = '[';
              break;
            default:
              $strMoveToChar = '_';
              break;
          }
          break;
        case "B":
        case "b":
          switch($strPromoteDirection) {
            case "right":
              $strMoveToChar = '$';
              break;
            case "left":
              $strMoveToChar = '@';
              break;
            default:
              $strMoveToChar = '#';
              break;
          }
          break;
        case "N":
        case "n":
          switch($strPromoteDirection) {
            case "right":
              $strMoveToChar = ')';
              break;
            case "left":
              $strMoveToChar = '(';
              break;
            default:
              $strMoveToChar = '^';
              break;
          }
          break;
      }
    } else {
      $strMoveToChar = $objReverseTranslateSquare[substr($strCmMove,2,2)];
    }

    $strPiotrMove = $strMoveFromChar . $strMoveToChar;

    return $strPiotrMove;
  }

  public static function GetPieceChar($piece) {
    return substr($piece,1,1);
  }

  public function GetCapturedPoints() {
    $objPointTotals = array(0,0);

    $objCapturedList = $this->GetCaptured();

    foreach($objCapturedList['W'] as $pieceChar => $intCount) {
      $objPointTotals[0] += (GamesChesscomStandard::$objPointValue[$pieceChar] * $intCount);
    }

    foreach($objCapturedList['B'] as $pieceChar => $intCount) {
      $objPointTotals[1] += (GamesChesscomStandard::$objPointValue[$pieceChar] * $intCount);
    }

    return $objPointTotals;
  }

  public static function GetCmSquareHash($strHyperPosition) {
    //Hyper Position is a very funky ChessMentor native string
    //0 = nothing
    //1 = Non key piece
    //2 = Key square (if a piece is in this square, it is also a key piece
    //3 = Key square with a NON key piece in it
    //The string reads from a8 - h8 ... a1 - h1
    $objCmSquareHash = array();
    $objRowList = preg_split("/\//",$strHyperPosition); //Row 8 is first here
    foreach($objRowList as $key => $strColumns) {
      $intCurrentRow = 8 - $key;
      for($i=0; $i<8; $i++) {
        switch($i) {
          case 0:
            $strCurrentColumn = 'a';
            break;
          case 1:
            $strCurrentColumn = 'b';
            break;
          case 2:
            $strCurrentColumn = 'c';
            break;
          case 3:
            $strCurrentColumn = 'd';
            break;
          case 4:
            $strCurrentColumn = 'e';
            break;
          case 5:
            $strCurrentColumn = 'f';
            break;
          case 6:
            $strCurrentColumn = 'g';
            break;
          case 7:
            $strCurrentColumn = 'h';
            break;
        }
        $objCmSquareHash[$strCurrentColumn][$intCurrentRow] = substr($strColumns,$i,1);
      }
    }

    return $objCmSquareHash;
  }

  public function GetKeyPieces($strHyperPosition) {
    if(false === (strpos($strHyperPosition,'1'))) {
      return array();
    }

    $objCmSquareHash = GamesChesscomStandard::GetCmSquareHash($strHyperPosition);

    $objKeyPieceSquareList = array();
    foreach($this->_pieces as $strPieceDetailed => $strLocation) {
      if($strLocation) {
        if(is_array($strLocation)) {
          //its a pawn
          $strBoardLocation = $strLocation[0];
        } else {
          $strBoardLocation = $strLocation;
        }

        $strColumn = substr($strBoardLocation,0,1);
        $strRow = substr($strBoardLocation,1,1);

        if($objCmSquareHash[$strColumn][$strRow] == 0 || $objCmSquareHash[$strColumn][$strRow] == 2) {
          $objKeyPieceSquareList[] = $strBoardLocation;
        }
      }
    }

    return $objKeyPieceSquareList;
  }

  public function GetKeySquares($strHyperPosition) {
    if(false === (strpos($strHyperPosition,'2')) && false === (strpos($strHyperPosition,'3'))) {
      return array();
    }

    $objCmSquareHash = GamesChesscomStandard::GetCmSquareHash($strHyperPosition);

    $objKeyPieceSquareList = array();

    $objColumnList = array('a','b','c','d','e','f','g','h');

    for($i=0; $i<8; $i++) {
      for($j=1; $j<=8; $j++) {
        $intCmValue = $objCmSquareHash[$objColumnList[$i]][$j];
        if($intCmValue == 2 || $intCmValue == 3) {
          $objKeyPieceSquareList[] = $objColumnList[$i] . $j . '';
        }
      }
    }

    return $objKeyPieceSquareList;
  }

  public function GetHyperPositionString($objKeySquareList, $objKeyPieceList) {
    //Hyper Position is a very funky ChessMentor native string
    //0 = nothing if nothing is in the square, otherwise a key piece if a piece is in the square
    //1 = Non key piece
    //2 = Key square (if a piece is in this square, it is also a key piece
    //3 = Key square with a NON key piece in it
    //The string reads from a8 - h8 ... a1 - h1
    //Example: 10000000/11300100/00100112/02021020/00021200/00102001/11120110/00210010

    $objColumnList = array('a','b','c','d','e','f','g','h');

    $strHyperPosition = '';
    for($i=8; $i>=1; $i--) {
      if($i != 8) {
        $strHyperPosition .= '/';
      }

      for($j=0; $j<8; $j++) {
        $strSquare = $objColumnList[$j] . $i;

        $objMovePiece = $this->_squareToPiece($strSquare);
        if($objMovePiece === false) {
          $blnIsPiece = false;
        } else {
          $blnIsPiece = true;
        }

        if(in_array($strSquare,$objKeySquareList)) {
          $blnIsKeySquare = true;
        } else {
          $blnIsKeySquare = false;
        }

        if(in_array($strSquare,$objKeyPieceList)) {
          $blnIsKeyPiece= true;
        } else {
          $blnIsKeyPiece = false;
        }

        if($blnIsPiece) {
          if($blnIsKeySquare) {
            if($blnIsKeyPiece) {
              //Key piece and key square with a piece in it
              $intHyperValue = 2;
            } else {
              //Not key piece but key square with a piece in it
              $intHyperValue = 3;
            }
          } else {
            if($blnIsKeyPiece) {
              //Key piece, NOT a key square with a piece in it
              $intHyperValue = 0;
            } else {
              //not Key piece, not a key square with a piece in it
              $intHyperValue = 1;
            }
          }
        } else {
          if($blnIsKeySquare) {
            //Key square with OUT a piece in it
            $intHyperValue = 2;
          } else {
            //not a key anything with OUT a piece in it
            $intHyperValue = 0;
          }
        }

        $strHyperPosition .= $intHyperValue;
      }
    }

    return $strHyperPosition;
  }

  public static function IsSquareLight($strSquare) {
    $strColumn = substr($strSquare,0,1);
    switch($strColumn) {
      case 'a':
        $intColumnValue = 1;
        break;
      case 'b':
        $intColumnValue = 2;
        break;
      case 'c':
        $intColumnValue = 3;
        break;
      case 'd':
        $intColumnValue = 4;
        break;
      case 'e':
        $intColumnValue = 5;
        break;
      case 'f':
        $intColumnValue = 6;
        break;
      case 'g':
        $intColumnValue = 7;
        break;
      case 'h':
        $intColumnValue = 8;
        break;
    }
    $intRow = substr($strSquare,1,1);
    $intTotalValue = $intColumnValue + $intRow;
    if($intTotalValue % 2 == 1) {
      return true;
    } else {
      return false;
    }
  }

  public function GetLastMoveSAN() {
    if(count($this->_moves) == 0) {
      return false;
    }

    $objMoveList = $this->_moves;

    $objLastMove = array_pop($objMoveList);
    $strLastMove = array_pop($objLastMove);

    return $strLastMove;
  }

  public function resetGameAndMoves($strFen) {
    $this->resetGame($strFen);
    $this->_moves = array();
  }

  public function makeCmMove($strCmMove) {
    $strMoveFromSquare = substr($strCmMove, 0, 2);
    $strMoveToSquare = substr($strCmMove, 2, 2);
    if(strlen($strCmMove) > 4) {
      $strPromotionPiece = substr($strCmMove, -1);
    } else {
      $strPromotionPiece = '';
    }
    return $this->moveSquare($strMoveFromSquare,$strMoveToSquare,$strPromotionPiece);
  }

  public static function CleanSANMove($strSanMove) {
    return trim(preg_replace("/[^a-zA-Z0-9\-]/","",$strSanMove));
  }
}
?>
