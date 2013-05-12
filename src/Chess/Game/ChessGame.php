<?php

namespace Chess\Game;

require_once('/usr/local/php5-20130210-223618/lib/php/PEAR.php');

/**
 * ABSTRACT parent class - use {@link Games_Chess_Standard} for a typical
 * chess game
 *
 * This class contains a few public methods that are the only thing most
 * users of the package will ever need.  Protected methods are available
 * for usage by child classes, and it is expected that all child classes
 * will implement certain protected methods used by the utility methods in
 * this class.
 *
 * Public API methods used are:
 *
 * Game-related methods
 *
 * - {@link resetGame()}: in order to start a new game (pass a FEN for a starting
 *   position)
 * - {@link blankBoard()}: in order to start with an empty chessboard
 * - {@link addPiece()}: Use to add pieces one at a time to the board
 * - {@link moveSAN()}: Use to move pieces based on their SAN (Qa3, exd5, etc.)
 * - {@link moveSquare()}: Use to move pieces based on their square (a2 -> a3
 *   for Qa3, e4 -> d5 for exd5, etc.)
 *
 * Game state methods:
 *
 * - {@link inCheck()}: Use to determine the presence of check
 * - {@link inCheckMate()}: Use to determine a won game
 * - {@link inStaleMate()}: Use to determine presence of stalemate draw
 * - {@link in50MoveDraw()}: Use to determine presence of 50-move rule draw
 * - {@link inRepetitionDraw()}: Use to determine presence of a draw by repetition
 * - {@link inStaleMate()}: Use to determine presence of stalemate draw
 * - {@link inDraw()}: Use to determine if any forced draw condition exists
 *
 * Game data methods:
 *
 * - {@link renderFen()}: Use to retrieve a FEN representation of the
 *   current chessboard position, in order to transfer to another chess program
 * - {@link toArray()}: Use to retrieve a literal representation of the
 *   current chessboard position, in order to display as HTML or some other
 *   format for the user
 * - {@link getMoveList()}: Use to retrieve the list of SAN moves for this game
 * @package Games_Chess
 */
class ChessGame
{

    /**#@+
     * Move constants
     */
    /**
     * Castling move (O-O or O-O-O)
     */
    const GAMES_CHESS_CASTLE = 1;

    /**
     * Pawn move (e4, e8=Q, exd5)
     */
    const GAMES_CHESS_PAWNMOVE = 2;

    /**
     * Piece move (Qa4, Nfe6, Bxe5, Re2xe6)
     */
    const GAMES_CHESS_PIECEMOVE = 3;

    /**
     * Special move type used in Wild23 like P@a4 (place a pawn at a4)
     */
    const GAMES_CHESS_PIECEPLACEMENT = 4;

    /**#@-*/

    /**#@+
     * Error Constants
     */
    /**
     * Invalid Standard Algebraic Notation was used
     */
    const GAMES_CHESS_ERROR_INVALID_SAN = 1;

    /**
     * The number of space-separated fields in a FEN passed to {@internal
     * {@link _parseFen()} through }} {@link resetGame()} was incorrect, should be 6
     */
     const GAMES_CHESS_ERROR_FEN_COUNT = 2;

    /**
     * A FEN containing multiple spaces in a row was parsed {@internal by
     * {@link _parseFen()}}}
     */
    const GAMES_CHESS_ERROR_EMPTY_FEN = 3;

    /**
     * Too many pieces were passed in for the chessboard to fit them in a FEN
     * {@internal passed to {@link _parseFen()}}}
     */
    const GAMES_CHESS_ERROR_FEN_TOOMUCH = 4;

    /**
     * The indicator of which side to move in a FEN was neither "w" nor "b"
     */
    const GAMES_CHESS_ERROR_FEN_TOMOVEWRONG = 5;

    /**
     * The list of castling indicators was too long (longest is KQkq) of a FEN
     */
    const GAMES_CHESS_ERROR_FEN_CASTLETOOLONG = 6;

    /**
     * Something other than K, Q, k or q was in the castling indicators of a FEN
     */
    const GAMES_CHESS_ERROR_FEN_CASTLEWRONG = 7;

    /**
     * The en passant square was neither "-" nor an algebraic square in a FEN
     */
    const GAMES_CHESS_ERROR_FEN_INVALID_EP = 8;

    /**
     * The ply count (number of half-moves) was not a number in a FEN
     */
    const GAMES_CHESS_ERROR_FEN_INVALID_PLY = 9;

    /**
     * The move count (pairs of white/black moves) was not a number in a FEN
     */
    const GAMES_CHESS_ERROR_FEN_INVALID_MOVENUMBER = 10;

    /**
     * An illegal move was attempted, the king is in check
     */
    const GAMES_CHESS_ERROR_IN_CHECK = 11;

    /**
     * Can't castle kingside, either king or rook has moved
     */
    const GAMES_CHESS_ERROR_CANT_CK = 12;

    /**
     * Can't castle kingside, pieces are in the way on the f and/or g files
     */
    const GAMES_CHESS_ERROR_CK_PIECES_IN_WAY = 13;

    /**
     * Can't castle kingside, either king or rook has moved
     */
    const GAMES_CHESS_ERROR_CANT_CQ = 14;

    /**
     * Can't castle queenside, pieces are in the way on the d, c and/or b files
     */
    const GAMES_CHESS_ERROR_CQ_PIECES_IN_WAY = 15;

    /**
     * Castling would place the king in check, which is illegal
     */
    const GAMES_CHESS_ERROR_CASTLE_WOULD_CHECK = 16;

    /**
     * Performing a requested move would place the king in check
     */
    const GAMES_CHESS_ERROR_MOVE_WOULD_CHECK = 17;

    /**
     * The requested move does not remove a check on the king
     */
    const GAMES_CHESS_ERROR_STILL_IN_CHECK = 18;

    /**
     * An attempt (however misguided) was made to capture one's own piece, illegal
     */
    const GAMES_CHESS_ERROR_CANT_CAPTURE_OWN = 19;

    /**
     * An attempt was made to capture a piece on a square that does not contain a piece
     */
    const GAMES_CHESS_ERROR_NO_PIECE = 20;

    /**
     * A attempt to move an opponent's piece was made, illegal
     */
    const GAMES_CHESS_ERROR_WRONG_COLOR = 21;

    /**
     * A request was made to move a piece from one square to another, but it can't
     * move to that square legally
     */
    const GAMES_CHESS_ERROR_CANT_MOVE_THAT_WAY = 22;

    /**
     * An attempt was made to add a piece to the chessboard, but there are too many
     * pieces of that type already on the chessboard
     */
    const GAMES_CHESS_ERROR_MULTIPIECE = 23;

    /**
     * An attempt was made to add a piece to the chessboard through the parsing of
     * a FEN, but there are too many pieces of that type already on the chessboard
     */
    const GAMES_CHESS_ERROR_FEN_MULTIPIECE = 24;

    /**
     * An attempt was made to add a piece to the chessboard on top of an existing piece
     */
    const GAMES_CHESS_ERROR_DUPESQUARE = 25;

    /**
     * An invalid piece indicator was used in a FEN
     */
    const GAMES_CHESS_ERROR_FEN_INVALIDPIECE = 26;

    /**
     * Not enough piece data was passed into the FEN to explain every square on the board
     */
    const GAMES_CHESS_ERROR_FEN_TOOLITTLE = 27;

    /**
     * Something other than "W" or "B" was passed to a method needing a color
     */
    const GAMES_CHESS_ERROR_INVALID_COLOR = 28;

    /**
     * Something that isn't SAN ([a-h][1-8]) was passed to a function requiring a
     * square location
     */
    const GAMES_CHESS_ERROR_INVALID_SQUARE = 29;

    /**
     * Something other than "P", "Q", "R", "B", "N" or "K" was passed to a method
     * needing a piece type
     */
    const GAMES_CHESS_ERROR_INVALID_PIECE = 30;

    /**
     * Something other than "Q", "R", "B", or "N" was passed to a method
     * needing a piece type for pawn promotion
     */
    const GAMES_CHESS_ERROR_INVALID_PROMOTE = 31;

    /**
     * SAN was passed in that is too ambiguous - multiple pieces could execute
     * the move, and no disambiguation (like Naf3 or Bf3xe4) was used
     */
    const GAMES_CHESS_ERROR_TOO_AMBIGUOUS = 32;

    /**
     * No piece of the current color can execute the SAN (as in, if Na3 is passed
     * in, but there are no knights that can reach a3
     */
    const GAMES_CHESS_ERROR_NOPIECE_CANDOTHAT = 33;

    /**
     * In loser's chess, and the current move does not capture a piece although
     * capture is possible.
     */
    const GAMES_CHESS_ERROR_MOVE_MUST_CAPTURE = 34;

    /**
     * When piece placement is attempted, but no pieces exist to be placed
     */
    const GAMES_CHESS_ERROR_NOPIECES_TOPLACE = 35;

    /**
     * When piece placement is attempted, but there is a piece on the desired square already
     */
    const GAMES_CHESS_ERROR_PIECEINTHEWAY = 36;

    /**
     * When a pawn placement on the first or back rank is attempted
     */
    const GAMES_CHESS_ERROR_CANT_PLACE_18 = 37;

    /**
     * For representing a game with fewer chars, we can use 2 chars for every possible chess move. we call this "piot move notation"
     */
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

  var $_moveFromSquare;

    /**
     * Used for transactions
     * @var array
     * @access private
     */
    var $_saveState = array();
    /**
     * @var array
     * @access private
     */
    var $_board;
    /**
     * @var string
     * @access private
     */
    var $_move = 'W';
    /**
     * @var integer
     * @access private
     */
    var $_moveNumber = 1;
    /**
     * Half-moves since last pawn move or capture
     * @var integer
     * @access private
     */
    var $_halfMoves = 1;
    /**
     * Square that an en passant can happen, or "-"
     * @var string
     * @access private
     */
    var $_enPassantSquare = '-';
    /**
     * Moves in SAN format for easy write-out to a PGN file
     *
     * The format is:
     * <pre>
     * array(
     *  movenumber => array(White move, Black move),
     *  movenumber => array(White move, Black move),
     * )
     * </pre>
     * @var array
     * @access private
     */
    var $_moves = array();
    /**
     * Moves in SAN format for easy write-out to a PGN file, with check/checkmate annotations appended
     *
     * The format is:
     * <pre>
     * array(
     *  movenumber => array(White move, Black move),
     *  movenumber => array(White move, Black move),
     * )
     * </pre>
     * @var array
     * @access private
     */
    var $_movesWithCheck = array();
    /**
     * Store every position from the game, used to determine draw by repetition
     *
     * If the exact same position is encountered three times, then it is a draw
     * @var array
     * @access private
     */
    var $_allFENs = array();
    /**#@+
     * Castling rights
     * @var boolean
     * @access private
     */
    var $_WCastleQ = true;
    var $_WCastleK = true;
    var $_BCastleQ = true;
    var $_BCastleK = true;
    /**#@-*/
    /**
     * Contents of the last move returned from {@link _parseMove()}, used to
     * process en passant.
     * @var false|array
     * @access private
     */
    var $_lastMove = false;

    var $_Chess960 = false;
    var $_QRookColumn = 'a';
    var $_KRookColumn = 'h';
    var $_KColumn = 'e';
    var $_QRookSet = false;

    var $objColumnToNumber = array(
      'a' => 1,
      'b' => 2,
      'c' => 3,
      'd' => 4,
      'e' => 5,
      'f' => 6,
      'g' => 7,
      'h' => 8
    );

    var $objNumberToColumn = array(
      1 => 'a',
      2 => 'b',
      3 => 'c',
      4 => 'd',
      5 => 'e',
      6 => 'f',
      7 => 'g',
      8 => 'h'
    );

    /**
     * Create a blank chessboard with no pieces on it
     */
    function blankBoard()
    {
        $this->_board = array();
        for ($j = 8; $j >= 1; $j--) {
            for ($i = ord('a'); $i <= ord('h'); $i++) {
                $this->_board[chr($i) . $j] = chr($i) . $j;
            }
        }

        $this->_pieces =
        array(
            'WR1' => false,
            'WN1' => false,
            'WB1' => false,
            'WQ' => false,
            'WK' => false,
            'WB2' => false,
            'WN2' => false,
            'WR2' => false,

            'WP1' => false,
            'WP2' => false,
            'WP3' => false,
            'WP4' => false,
            'WP5' => false,
            'WP6' => false,
            'WP7' => false,
            'WP8' => false,

            'BP1' => false,
            'BP2' => false,
            'BP3' => false,
            'BP4' => false,
            'BP5' => false,
            'BP6' => false,
            'BP7' => false,
            'BP8' => false,

            'BR1' => false,
            'BN1' => false,
            'BB1' => false,
            'BQ' => false,
            'BK' => false,
            'BB2' => false,
            'BN2' => false,
            'BR2' => false,
        );
    }

    /**
     * Create a new game with the starting position, or from the position
     * specified by $fen
     *
     * @param false|string
     * @return PEAR_Error|true returns any errors thrown by {@link _parseFen()}
     */
    function resetGame($fen = false, $blnChess960 = false)
    {
        $this->_Chess960 = $blnChess960;

        $this->_saveState = array();
        if (!$fen) {
            $this->_setupStartingPosition();
        } else {
            return $this->_parseFen($fen);
        }
        return true;
    }

    function _getSquareFromParsedMove($parsedmove, $color = null)
    {
        if (is_null($color)) {
            $color = $this->_move;
        }
        switch ($parsedmove['piece']) {
            case 'K' :
                if (in_array($parsedmove['square'],
                    $this->getPossibleKingMoves($this->_pieces[$color . 'K'], $color))) {
                    return $this->_pieces[$color . 'K'];
                }
            break;
            case 'Q' :
            case 'B' :
            case 'R' :
            case 'N' :
                if ($parsedmove['disambiguate']) {
                    if (strlen($parsedmove['disambiguate']) == 2) {
                        $square = $parsedmove['disambiguate'];
                    } elseif (is_numeric($parsedmove['disambiguate'])) {
                        $row = $parsedmove['disambiguate'];
                    } else {
                        $col = $parsedmove['disambiguate'];
                    }
                } else {
                    $others = array();
                    $others = $this->_getAllPieceSquares($parsedmove['piece'],
                                                         $color);
                    $disambiguate = '';
                    $ambiguous = array();
                    if (count($others)) {
                        foreach ($others as $square) {
                            if (in_array($parsedmove['square'],
                                    $this->getPossibleMoves($parsedmove['piece'],
                                                            $square,
                                                            $color))) {
                                // other pieces can move to this square - need to disambiguate
                                $ambiguous[] = $square;
                            }
                        }
                    }
                    if (count($ambiguous) > 1) {
                        $pieces = implode($ambiguous, ' ');
                        return $this->raiseError(
                            self::GAMES_CHESS_ERROR_TOO_AMBIGUOUS,
                            array('san' => $parsedmove['piece'] .
                                $parsedmove['disambiguate'] . $parsedmove['takes']
                                . $parsedmove['square'],
                                  'squares' => $pieces,
                                  'piece' => $parsedmove['piece']));
                    }
                    $square = $col = $row = null;
                }
                $potentials = array();
                foreach ($this->_pieces as $name => $value) {
                    if (!$value) {
                        continue;
                    }
                    if ($name{0} != $color) {
                        continue;
                    }
                    if (isset($square)) {
                        if ($name{1} == $parsedmove['piece'] &&
                              $value[0] == $square) {
                            return $square;
                        }
                        if ($name{1} == 'P' && $value[0] == $square &&
                              $value[1] == $parsedmove['piece']) {
                            return $square;
                        }
                    } elseif (isset($col)) {
                        if ($name{1} == $parsedmove['piece'] &&
                              $value{0} == $col) {
                            if (in_array($parsedmove['square'],
                                  $this->getPossibleMoves($parsedmove['piece'],
                                                            $value, $color))) {
                                $potentials[] = $value;
                            }
                        }
                        if ($name{1} == 'P' && $value[0]{0} == $col &&
                              $value[1] == $parsedmove['piece']) {
                            if (in_array($parsedmove['square'],
                                  $this->getPossibleMoves($parsedmove['piece'],
                                                            $value[0], $color))) {
                                $potentials[] = $value[0];
                            }
                        }
                    } elseif (isset($row)) {
                        if ($name{1} == $parsedmove['piece'] &&
                              $value{1} == $row) {
                            if (in_array($parsedmove['square'],
                                  $this->getPossibleMoves($parsedmove['piece'],
                                                            $value, $color))) {
                                $potentials[] = $value;
                            }
                        }
                        if ($name{1} == 'P' && $value[0]{1} == $row &&
                              $value[1] == $parsedmove['piece']) {
                            if (in_array($parsedmove['square'],
                                  $this->getPossibleMoves($parsedmove['piece'],
                                                            $value[0], $color))) {
                                $potentials[] = $value[0];
                            }
                        }
                    } else {
                        if ($name{1} == $parsedmove['piece']) {
                            if (in_array($parsedmove['square'],
                                  $this->getPossibleMoves($parsedmove['piece'],
                                                            $value, $color))) {
                                $potentials[] = $value;
                            }
                        } elseif ($name{1} == 'P' &&
                              $value[1] == $parsedmove['piece']) {
                            if (in_array($parsedmove['square'],
                                  $this->getPossibleMoves($parsedmove['piece'],
                                                            $value[0], $color))) {
                                $potentials[] = $value[0];
                            }
                        }
                    }
                }
                if (count($potentials) == 1) {
                    return $potentials[0];
                }
            break;
            case 'P' :
                if ($parsedmove['disambiguate']) {
                    $square = $parsedmove['disambiguate'] . $parsedmove['takesfrom'];
                } else {
                    $square = null;
                }
                if ($parsedmove['takesfrom']) {
                    $col = $parsedmove['takesfrom'];
                } else {
                    $col = null;
                }
                $potentials = array();
                foreach ($this->_pieces as $name => $value) {
                    if ($name{0} != $color) {
                        continue;
                    }
                    if (isset($square)) {
                        if ($name{1} == 'P' && $value[0] == $square && $value[1] == 'P') {
                            return $square;
                        }
                    } elseif (isset($col)) {
                        if ($name{1} == 'P' && $value[0]{0} == $col && $value[1] == 'P') {
                            if (in_array($parsedmove['square'],
                                  $this->getPossiblePawnMoves($value[0], $color))) {
                                $potentials[] = $value[0];
                            }
                        }
                    } else {
                        if ($name{1} == 'P' && $value[1] == 'P') {
                            if (in_array($parsedmove['square'],
                                  $this->getPossiblePawnMoves($value[0], $color))) {
                                $potentials[] = $value[0];
                            }
                        }
                    }
                }
                if (count($potentials) == 1) {
                    return $potentials[0];
                }
            break;
        }
        if ($parsedmove['piece'] == 'P') {
            $san = $parsedmove['takesfrom'] . $parsedmove['takes'] . $parsedmove['square'];
        } else {
            $san = $parsedmove['piece'] .
                           $parsedmove['disambiguate'] . $parsedmove['takes'] .
                           $parsedmove['square'];
        }
        return $this->raiseError(self::GAMES_CHESS_ERROR_NOPIECE_CANDOTHAT,
            array('san' => $san,
                  'color' => $color));
    }

    function inCheck($color)
    {
        $ret = array();
        $king_loc = $this->_pieces[$color . 'K'];
        $opposite_color = $color == 'W' ? 'B' : 'W';

        foreach ($this->_pieces as $name => $loc) {
            if ($name{0} == $opposite_color) {
                $enemy_loc = (is_array($loc) ? $loc[0] : $loc);
                if ($enemy_loc && $this->_isThreat($enemy_loc, $king_loc)) {
                    $ret[] = $enemy_loc;
                }
            }
        }

        if (!count($ret)) {
            return false;
        }
        if (count($ret) == 1) {
            return $ret[0];
        }
        return $ret;
    }

    function _isEmptySquare($square)
    {
        return $this->_board[$square] == $square;
    }

    function _isThreat($from, $to)
    {
        $x1 = ord($from{0}) - ord('a');
        $y1 = ord($from{1}) - ord('1');
        $x2 = ord($to{0}) - ord('a');
        $y2 = ord($to{1}) - ord('1');

        $piece = $this->_squareToPiece($from);
        switch ($piece['piece']) {
        case 'P':
            if ($piece['color'] == 'W') {
                return ($x2 == $x1 - 1 || $x2 == $x1 + 1) && $y2 == $y1 + 1;
            }
            if ($piece['color'] == 'B') {
                return ($x2 == $x1 - 1 || $x2 == $x1 + 1) && $y2 == $y1 - 1;
            }
            return false;
        case 'R':
            return $this->_isRookThreat($x1, $y1, $x2, $y2);
        case 'N':
            return abs($x1 - $x2) == 1 && abs($y1 - $y2) == 2 || abs($x1 - $x2) == 2 && abs($y1 - $y2) == 1;
        case 'B':
            return $this->_isBishopThreat($x1, $y1, $x2, $y2);
        case 'Q':
            return $this->_isBishopThreat($x1, $y1, $x2, $y2) || $this->_isRookThreat($x1, $y1, $x2, $y2);
        case 'K':
            return abs($x1 - $x2) <= 1 && abs($y1 - $y2) <= 1;
        }
    }
    function _isRookThreat($x1, $y1, $x2, $y2)
    {
        if ($y1 == $y2) {
            for ($x = min($x1, $x2) + 1; $x < max($x1, $x2); $x++) {
                if (!$this->_isEmptySquare(chr($x + ord('a')) . chr($y1 + ord('1')))) {
                    return false;
                }
            }
            return true;
        }
        if ($x1 == $x2) {
            for ($y = min($y1, $y2) + 1; $y < max($y1, $y2); $y++) {
                if (!$this->_isEmptySquare(chr($x1 + ord('a')) . chr($y + ord('1')))) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }
    function _isBishopThreat($x1, $y1, $x2, $y2)
    {
        if (abs($x1 - $x2) == abs($y1 - $y2)) {
            $dx = $x1 < $x2? 1: -1;
            $dy = $y1 < $y2? 1: -1;
            $x = $x1 + $dx;
            $y = $y1 + $dy;
            while ($x != $x2) {
                if (!$this->_isEmptySquare(chr($x + ord('a')) . chr($y + ord('1')))) {
                    return false;
                }
                $x += $dx;
                $y += $dy;
            }
            return true;
        }
        return false;
    }

    function _getPossibleChecks($color)
    {
        $ret = array();
        foreach ($this->_pieces as $name => $loc) {
            if (!$loc) {
                continue;
            }
            if ($name{0} == $color) {
                if ($name{1} == 'P') {
                    $ret[$name] = $this->getPossibleMoves($loc[1], $loc[0], $color, false);
                } else {
                    $ret[$name] = $this->getPossibleMoves($name{1}, $loc, $color, false);
                }
            }
        }
        return $ret;
    }
    function _getAllPieceSquares($piece, $color, $exclude = null)
    {
        $ret = array();
        foreach ($this->_pieces as $name => $loc) {
            if (!$loc) {
                continue;
            }
            if ($name{0} != $color) {
                continue;
            }
            if ($name{1} == 'P') {
                if ($loc[1] != $piece || $loc[0] == $exclude) {
                    continue;
                } else {
                    $ret[] = $loc[0];
                    continue;
                }
            }
            if ($loc == $exclude) {
                continue;
            }
            if ($name{1} != $piece) {
                continue;
            }
            $ret[] = $loc;
        }
        return $ret;
    }

    function _getKing($color = null)
    {
        if (!is_null($color)) {
            return $this->_pieces[$color . 'K'];
        } else {
            return $this->_pieces[$this->_move . 'K'];
        }
    }

    /**
     * Get the location of a piece
     *
     * This does NOT take an algebraic square as the argument, but the contents
     * of _board[algebraic square]
     * @param string
     * @return string|array
     * @access protected
     */
    function _getPiece($piecename)
    {
        return is_array($this->_pieces[$piecename]) ?
            $this->_pieces[$piecename][0] :
            $this->_pieces[$piecename];
    }

    function _takePiece($piece)
    {
        if (isset($this->_pieces[$this->_board[$piece]])) {
            $this->_pieces[$this->_board[$piece]] = false;
        }
    }

    /**
     * Move a piece from one square to another, disregarding any existing pieces
     *
     * {@link _takePiece()} should always be used prior to this method.  No
     * validation is performed
     * @param string [a-h][1-8] square the piece resides on
     * @param string [a-h][1-8] square the piece moves to
     * @param string Piece to promote to if this is a promotion move
     */
    function _movePiece($from, $to, $promote = '')
    {
        if (isset($this->_pieces[$this->_board[$from]])) {
            $newto = $this->_pieces[$this->_board[$from]];
            if (is_array($newto)) {
                $newto[0] = $to;
                if ($promote && ($to{1} == '8' || $to{1} == '1')) {
                    $newto[1] = $promote;
                }
            } else {
                $newto = $to;
            }
            $this->_pieces[$this->_board[$from]] = $newto;
        }
    }

    /**
     * Translate an algebraic coordinate into the color and name of a piece,
     * or false if no piece is on that square
     * @return false|array Format array('color' => B|W, 'piece' => P|R|Q|N|K|B)
     * @param string [a-h][1-8]
     * @access protected
     */
    function _squareToPiece($square)
    {
        if ($this->_board[$square] != $square) {
            $piece = $this->_board[$square];
            if ($piece{1} == 'P') {
                $color = $piece{0};
                $piece = $this->_pieces[$piece][1];
            } else {
                $color = $piece{0};
                $piece = $piece{1};
            }
            return array('color' => $color, 'piece' => $piece);
        } else {
            return false;
        }
    }
    /**
     * Make a move from a Standard Algebraic Notation (SAN) format
     *
     * SAN is just a normal chess move like Na4, instead of the English Notation,
     * like NR4
     * @param string
     * @return true|PEAR_Error
     */
    function moveSAN($move, $from = null)
    {
        if (!is_array($this->_board)) {
            $this->resetGame();
        }
        if (!$this->isError($parsedMove = $this->_parseMove($move))) {
            if (!$this->isError($err = $this->_validMove($parsedMove, $from))) {
                list($key, $parsedMove) = each($parsedMove);
                $this->_moves[$this->_moveNumber][($this->_move == 'W') ? 0 : 1] = $move;
                $oldMoveNumber = $this->_moveNumber;
                $this->_moveNumber += ($this->_move == 'W') ? 0 : 1;
                $this->_halfMoves++;
                if ($key == self::GAMES_CHESS_CASTLE) {
                    $a = ($parsedMove == 'Q') ? 'K' : 'Q';
                    // clear castling rights
                    $this->{'_' . $this->_move . 'Castle' . $parsedMove} = false;
                    $this->{'_' . $this->_move . 'Castle' . $a} = false;
                    $row = ($this->_move == 'W') ? 1 : 8;
                    switch ($parsedMove) {
                        case 'K' :
                          if("g$row" == $this->_KRookColumn . $row && "f$row" == $this->_KColumn . $row) {
                            //if king and rook are just swapping squares, need special function so pieces dont capture each other
                            $this->_swapSquares($this->_KColumn . $row, $this->_KRookColumn . $row);
                          } elseif("g$row" == $this->_KRookColumn . $row) {
                            //the king is moving to where the rook is, so lets move the rook first to avoid piece capture
                            $this->_moveAlgebraic($this->_KRookColumn . $row, "f$row");
                            $this->_moveAlgebraic($this->_KColumn . $row, "g$row");
                          } else {
                            $this->_moveAlgebraic($this->_KColumn . $row, "g$row");
                            $this->_moveAlgebraic($this->_KRookColumn . $row, "f$row");
                          }
                          $this->_moveFromSquare = $this->_KColumn . $row;
                          $this->_lastMove['square'] = "g$row";
                        break;
                        case 'Q' :
                          if("c$row" == $this->_QRookColumn . $row && "d$row" == $this->_KColumn . $row) {
                            //if king and rook are just swapping squares, need special function so pieces dont capture each other
                            $this->_swapSquares($this->_KColumn . $row, $this->_QRookColumn . $row);
                          } elseif("c$row" == $this->_QRookColumn . $row) {
                            //the king is moving to where the rook is, so lets move the rook first to avoid piece capture
                            $this->_moveAlgebraic($this->_QRookColumn . $row, "d$row");
                            $this->_moveAlgebraic($this->_KColumn . $row, "c$row");
                          } else {
                            $this->_moveAlgebraic($this->_KColumn . $row, "c$row");
                            $this->_moveAlgebraic($this->_QRookColumn . $row, "d$row");
                          }
                          $this->_moveFromSquare = $this->_KColumn . $row;
                          $this->_lastMove['square'] = "c$row";
                        break;
                    }
                    $this->_enPassantSquare = '-';
                } else {
                    $movedfrom = $from? $from: $this->_getSquareFromParsedMove($parsedMove);
                    $this->_moveFromSquare = $movedfrom;
                    $this->_lastMove = $parsedMove;
                    $promote = isset($parsedMove['promote']) ?
                        $parsedMove['promote'] : '';
                    $this->_moveAlgebraic($movedfrom, $parsedMove['square'], $promote);
                    if ($parsedMove['takes']) {
                        $this->_halfMoves = 0;
                    }
                    if ($parsedMove['piece'] == 'P') {
                        $this->_halfMoves = 0;
                        $this->_enPassantSquare = '-';
                        if (in_array($movedfrom{1} - $parsedMove['square']{1},
                              array(2, -2))) {
                            $direction = ($this->_move == 'W' ? 1 : -1);
                            $this->_enPassantSquare = $parsedMove['square']{0} .
                                ($parsedMove['square']{1} - $direction);
                        }
                    } else {
                        $this->_enPassantSquare = '-';
                    }
                    if ($parsedMove['piece'] == 'K') {
                        $this->{'_' . $this->_move . 'CastleQ'} = false;
                        $this->{'_' . $this->_move . 'CastleK'} = false;
                    }
                    if ($parsedMove['piece'] == 'R') {
                        if ($movedfrom{0} == $this->_QRookColumn && (($this->_move == 'B' && $movedfrom{1} == '8') || ($this->_move == 'W' && $movedfrom{1} == '1'))) {
                          $this->{'_' . $this->_move . 'CastleQ'} = false;
                        }
                        if ($movedfrom{0} == $this->_KRookColumn && (($this->_move == 'B' && $movedfrom{1} == '8') || ($this->_move == 'W' && $movedfrom{1} == '1'))) {
                          $this->{'_' . $this->_move . 'CastleK'} = false;
                        }
                    }
                    if ($parsedMove['square'] == ($this->_QRookColumn . '1')) {
                      $this->_WCastleQ = false;
                    } elseif($parsedMove['square'] == ($this->_KRookColumn . '1')) {
                      $this->_WCastleK = false;
                    } elseif($parsedMove['square'] == ($this->_QRookColumn . '8')) {
                      $this->_BCastleQ = false;
                    } elseif($parsedMove['square'] == ($this->_KRookColumn . '8')) {
                      $this->_BCastleK = false;
                    }
                }
                $moveWithCheck = $move;
                if ($checkingSquares = $this->inCheck(($this->_move == 'W') ? 'B' : 'W')) {
                    if ($this->inCheckMate(($this->_move == 'W') ? 'B' : 'W', $checkingSquares)) {
                        $moveWithCheck .= '#';
                    } else {
                        $moveWithCheck .= '+';
                    }
                }
                $this->_movesWithCheck[$oldMoveNumber][($this->_move == 'W') ? 0 : 1] = $moveWithCheck;
                $this->_move = ($this->_move == 'W' ? 'B' : 'W');

                // increment the position counter for this position
                $x = $this->renderFen(false);
                if (!isset($this->_allFENs[$x])) {
                    $this->_allFENs[$x] = 0;
                }
                $this->_allFENs[$x]++;

                return true;
            } else {
                return $err;
            }
        } else {
            return $parsedMove;
        }
    }

    /**
     * Move a piece from one square to another, and mark the old square as empty
     *
     * @param string [a-h][1-8] square to move from
     * @param string [a-h][1-8] square to move to
     * @param string piece to promote to, if this is a promotion move
     * @return true|PEAR_Error
     */
    function moveSquare($from, $to, $promote = '')
    {
        $move = $this->_convertSquareToSAN($from, $to, $promote);

        if ($this->isError($move)) {
            return $move;
        } else {
            return $this->moveSAN($move, $from);
        }
    }

    /**
     * Get the list of moves in Standard Algebraic Notation
     *
     * Can be used to populate a PGN file.
     * @param boolean If true, then moves that check will be postfixed with "+" and checkmate with "#"
     *                as in Nf3+ or Qxg7#
     * @return array
     */
    function getMoveList($withChecks = false)
    {
        if ($withChecks) {
            return $this->_movesWithCheck;
        }
        return $this->_moves;
    }

    /**
     * Get the list of moves in Standard Algebraic Notation as a string
     *
     * Can be used to populate a PGN file.
     * @param boolean If true, then moves that check will be postfixed with "+" and checkmate with "#"
     *                as in Nf3+ or Qxg7#
     * @return array
     */
    function getMoveListString($withChecks = false) {
      if ($withChecks) {
        $objMoveList = $this->_movesWithCheck;
      } else {
        $objMoveList = $this->_moves;
      }
      $strMoveList = "";
      $intCount=0;
      foreach($objMoveList as $key => $objMove) {
        if($intCount != 0) {
          $strMoveList .= ' ';
        }
        $strMoveList .= $key . '.';
        if($objMove[0]) {
          $strMoveList .= $objMove[0];
        } else {
          $strMoveList .= '..';
        }
        if($objMove[1]) {
          $strMoveList .= ' ' . $objMove[1];
        }
        $intCount++;
      }
      return $strMoveList;
    }

    /**
     * @return W|B|D|false winner of game, or draw, or false if still going
     */
    function gameOver()
    {
        $opposite = $this->_move == 'W' ? 'B' : 'W';
        if ($this->inCheckmate()) {
            return $opposite;
        }
        if ($this->inDraw()) {
            return 'D';
        }
        return false;
    }

    /**
     * Determine whether a side is in checkmate
     * @param W|B color of side to check, defaults to the current side
     * @return boolean
     * @throws self::GAMES_CHESS_ERROR_INVALID_COLOR
     */
    function inCheckMate($color = null, $checkingSquares = null)
    {
        if (is_null($color)) {
            $color = $this->_move;
        }
        $color = strtoupper($color);
        if (!in_array($color, array('W', 'B'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_COLOR,
                array('color' => $color));
        }
        if (!($checking = $checkingSquares)) {
            if (!($checking = $this->inCheck($color))) {
                return false;
            }
        }
        $moves = $this->getPossibleKingMoves($king = $this->_getKing($color), $color);
        foreach ($moves as $escape) {
            $this->startTransaction();
            $this->_move = $color;

            try {
                $this->moveSquare($king, $escape);
            } catch (\Exception $e) {
                //do nothing
            }

            $this->_move = $color;
            $stillchecked = $this->inCheck($color);
            $this->rollbackTransaction();
            if (!$stillchecked) {
                return false;
            }
        }
        // if we're in double check, and the king can't move, that's checkmate
        if (is_array($checking) && count($checking) > 1) {
            return true;
        }
        $squares = $this->_getPathToKing($checking, $king);
        if ($this->_interposeOrCapture($squares, $color)) {
            return false;
        }
        return true;
    }

    /**
     * Determine whether a side is in stalemate
     * @param W|B color of the side to look at, defaults to the current side
     * @return boolean
     * @throws self::GAMES_CHESS_ERROR_INVALID_COLOR
     */
    function inStaleMate($color = null)
    {
        if (is_null($color)) {
            $color = $this->_move;
        }
        $color = strtoupper($color);
        if (!in_array($color, array('W', 'B'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_COLOR,
                array('color' => $color));
        }
        if ($this->inCheck($color)) {
            return false;
        }
        $moves = $this->_getPossibleChecks($color);
        foreach($moves as $name => $canmove) {
            if (count($canmove)) {
                $a = $this->_getPiece($name);
                foreach($canmove as $move) {
                    $this->startTransaction();
                    $this->_move = $color;

                    if (! $this->isError($this->moveSquare($a, $move))) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Determines the presence of a forced draw
     * @param W|B
     * @return boolean
     */
    function inDraw($color = null)
    {
        return $this->inStaleMate($color) ||
               $this->inRepetitionDraw() ||
               $this->in50MoveDraw() ||
               $this->inBasicDraw();
    }

    /**
     * Determine whether draw by repetition has happened
     *
     * From FIDE rules:
     * <pre>
     * 10.10
     *
     * The game is drawn, upon a claim by the player having the move, when the
     * same position, for the third time:
     * (a) is about to appear, if he first writes the move on his
     *     scoresheet and declares to the arbiter his intention of making
     *     this move; or
     * (b) has just appeared, the same player having the move each time.
     *
     * The position is considered the same if pieces of the same kind and
     * colour occupy the same squares, and if all the possible moves of
     * all the pieces are the same, including the rights to castle [at
     * some future time] or to capture a pawn "en passant".
     * </pre>
     *
     * This class determines draw by comparing FENs rendered after every move
     * @return boolean
     */
    function inRepetitionDraw()
    {
        $fen = $this->renderFen(false);
        if (isset($this->_allFENs[$fen]) && $this->_allFENs[$fen] >= 3) {
            return true;
        }
        return false;
    }

    /**
     * Determine whether any pawn move or capture has occurred in the past 50 moves
     * @return boolean
     */
    function in50MoveDraw()
    {
        return $this->_halfMoves >= 100;
    }

    /**
     * Determine the presence of a basic draw as defined by FIDE rules
     *
     * The rule states:
     * <pre>
     * 10.4
     *
     * The game is drawn when one of the following endings arises:
     * (a) king against king;
     * (b) king against king with only bishop or knight;
     * (c) king and bishop against king and bishop, with both bishops
     *     on diagonals of the same colour.
     * </pre>
     * @return boolean
     */
    function inBasicDraw()
    {
        $pieces = $this->_getPieceTypes();
        $blackpieces = array_keys($pieces['B']);
        $whitepieces = array_keys($pieces['W']);

        if (count($blackpieces) > 2 || count($whitepieces) > 2) {
          return false;
        } elseif(array_key_exists('B', $pieces['W']) && is_array($pieces['W']['B']) && count($pieces['W']['B']) > 1) {
          return false;
        } elseif(array_key_exists('B', $pieces['B']) && is_array($pieces['B']['B']) && count($pieces['B']['B']) > 1) {
          return false;
        }

        if (count($blackpieces) == 1) {
            if (count($whitepieces) == 1) {
                return true;
            }
            // XXX: The following if/else block appears to be unreachable due to the if/elseif/elseif block above
            if ($whitepieces[0] == 'K') {
                if (in_array($whitepieces[1], array('N', 'B'))) {
                  if(is_array($pieces['W']['N']) && count($pieces['W']['N']) > 1) {
                    return false;
                  } elseif(is_array($pieces['W']['B']) && count($pieces['W']['B']) > 1) {
                    return false;
                  } else {
                    return true;
                  }
                } else {
                    return false;
                }
            } else {
                if (in_array($whitepieces[0], array('N', 'B'))) {
                  if(array_key_exists('N', $pieces['W']) && is_array($pieces['W']['N']) && count($pieces['W']['N']) > 1) {
                    return false;
                  } elseif(array_key_exists('B', $pieces['W']) && is_array($pieces['W']['B']) && count($pieces['W']['B']) > 1) {
                    return false;
                  } else {
                    return true;
                  }
                } else {
                    return false;
                }
            }
        }

        if (count($whitepieces) == 1) {
            if (count($blackpieces) == 1) {
                return true;
            }
            // XXX: The following if/else block appears to be unreachable due to the if/elseif/elseif block above
            if ($blackpieces[0] == 'K') {
                if (in_array($blackpieces[1], array('N', 'B'))) {
                  if(is_array($pieces['B']['N']) && count($pieces['B']['N']) > 1) {
                    return false;
                  } elseif(is_array($pieces['B']['B']) && count($pieces['B']['B']) > 1) {
                    return false;
                  } else {
                    return true;
                  }
                } else {
                    return false;
                }
            } else {
                if (in_array($blackpieces[0], array('N', 'B'))) {
                  if(array_key_exists('N', $pieces['B']) && is_array($pieces['B']['N']) && count($pieces['B']['N']) > 1) {
                    return false;
                  } elseif(array_key_exists('B', $pieces['B']) && is_array($pieces['B']['B']) && count($pieces['B']['B']) > 1) {
                    return false;
                  } else {
                    return true;
                  }
                } else {
                    return false;
                }
            }
        }
        $wpindex = ($whitepieces[0] == 'K') ? 1 : 0;
        $bpindex = ($blackpieces[0] == 'K') ? 1 : 0;
        if ($whitepieces[$wpindex] == 'B' && $blackpieces[$bpindex] == 'B') {
            // bishops of same color?
            if ($pieces['B']['B'][0] == $pieces['W']['B'][0]) {
                return true;
            }
        }
        return false;
    }

    function _renderFen()
    {
        $fen = '';
        $ws = 0;
        $saverow = '8';
        foreach ($this->_board as $square => $piece) {
            if ($square{1} != $saverow) {
                // if we have just moved to the next rank,
                // output any whitespace, and a '/'
                if ($ws) {
                    $fen .= $ws;
                }
                $fen .= '/';
                $ws = 0;
                $saverow = $square{1};
            }
            if ($square == $piece) {
                // increment whitespace - no piece on this square
                $ws++;
            } else {
                // add any whitespace and reset
                if ($ws) {
                    $fen .= $ws;
                }
                $ws = 0;
                if (is_array($this->_pieces[$piece])) {
                    // add pawns/promoted pawns
                    $p = ($piece{0} == 'W') ? $this->_pieces[$piece][1] :
                        strtolower($this->_pieces[$piece][1]);
                } else {
                    // add pieces
                    $p = ($piece{0} == 'W') ? $piece{1} : strtolower($piece{1});
                }
                $fen .= $p;
            }
        }
        // add any trailing whitespace
        if ($ws) {
            $fen .= $ws;
        }
        return $fen;
    }


    /**
     * render the FEN notation for the current board
     * @param boolean private parameter, used to determine whether to include
     *                move number/ply count - this is used to keep track of
     *                positions for draw detection
     * @return string
     */
    function renderFen($include_moves = true, $include_en_passant = true)
    {
        $fen = $this->_renderFen() . ' ';

        // render who's to move
        $fen .= strtolower($this->_move) . ' ';

        // render castling rights
        if (!$this->_WCastleQ && !$this->_WCastleK && !$this->_BCastleQ
              && !$this->_BCastleK) {
            $fen .= '- ';
        } else {
            if ($this->_WCastleK) {
              if($this->_Chess960) {
                if($this->_KRookColumn == 'h') {
                  $fen .= 'K';
                } else {
                  $fen .= strtoupper($this->_KRookColumn);
                }
              } else {
                $fen .= 'K';
              }
            }
            if ($this->_WCastleQ) {
              if($this->_Chess960) {
                if($this->_QRookColumn == 'a') {
                  $fen .= 'Q';
                } else {
                  $fen .= strtoupper($this->_QRookColumn);
                }
              } else {
                $fen .= 'Q';
              }
            }
            if ($this->_BCastleK) {
              if($this->_Chess960) {
                if($this->_KRookColumn == 'h') {
                  $fen .= 'k';
                } else {
                  $fen .= $this->_KRookColumn;
                }
              } else {
                $fen .= 'k';
              }
            }
            if ($this->_BCastleQ) {
              if($this->_Chess960) {
                if($this->_QRookColumn == 'a') {
                  $fen .= 'q';
                } else {
                  $fen .= $this->_QRookColumn;
                }
              } else {
                $fen .= 'q';
              }
            }
            $fen .= ' ';
        }

        if(!$include_en_passant) {
          return trim($fen);
        }

        // render en passant square
        $fen .= $this->_enPassantSquare;

        if (!$include_moves) {
            return trim($fen);
        }

        // render half moves since last pawn move or capture
        $fen .=  ' ' . $this->_halfMoves . ' ';

        // render move number
        $fen .= $this->_moveNumber;
        return $fen;
    }

    /**
     * Add a piece to the chessboard
     *
     * Must be overridden in child classes
     * @abstract
     * @param W|B Color of piece
     * @param P|N|K|Q|R|B Piece type
     * @param string algebraic location of piece
     */
    function addPiece($color, $type, $square)
    {
        if (!isset($this->_board[$square])) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_SQUARE,
                array('square' => $square));
        }
        if ($this->_board[$square] != $square) {
            $dpiece = $this->_board[$square];
            if ($dpiece{1} == 'P') {
                $dpiece = $this->_pieces[$dpiece][1];
            } else {
                $dpiece = $dpiece{1};
            }
            return $this->raiseError(self::GAMES_CHESS_ERROR_DUPESQUARE,
                array('piece' => $type, 'dpiece' => $dpiece, 'square' => $square));
        }
        switch ($type) {
            case 'B' :
            case 'N' :
            case 'R' :
                $piece_name = $color . $type;
                if (!$this->_pieces[$piece_name . '1']) {
                    $this->_board[$square] = $piece_name . '1';
                    $this->_pieces[$piece_name . '1'] = $square;
                } elseif (!$this->_pieces[$piece_name . '2']) {
                    $this->_board[$square] = $piece_name . '2';
                    $this->_pieces[$piece_name . '2'] = $square;
                } else {
                    // handle promoted pawns
                    for ($col = 1; $col <= 8; $col++) {
                        if (!$this->_pieces[$color . 'P' . $col]) {
                            $this->_pieces[$color . 'P' . $col] =
                                array($square, $type);
                            $this->_board[$square] = $color . 'P' . $col;
                            break 2;
                        }
                    }
                    return $this->raiseError(self::GAMES_CHESS_ERROR_MULTIPIECE,
                        array('color' => $color, 'piece' => $type));

                }
            break;
            case 'Q' :
                $piece_name = $color . 'Q';
                if (!$this->_pieces[$piece_name]) {
                    $this->_board[$square] = $piece_name;
                    $this->_pieces[$piece_name] = $square;
                } else {
                    // handle promoted pawns
                    for ($col = 1; $col <= 8; $col++) {
                        if (!$this->_pieces[$color . 'P' . $col]) {
                            $this->_pieces[$color . 'P' . $col] =
                                array($square, 'Q');
                            $this->_board[$square] = $color . 'P' . $col;
                            break 2;
                        }
                    }
                    return $this->raiseError(self::GAMES_CHESS_ERROR_MULTIPIECE,
                        array('color' => $color, 'piece' => $type));
                }
            break;
            case 'P' :
                // handle regular pawns
                for ($col = 1; $col <= 8; $col++) {
                    if (!$this->_pieces[$color . 'P' . $col]) {
                        $this->_pieces[$color . 'P' . $col] =
                            array($square, 'P');
                        $this->_board[$square] = $color . 'P' . $col;
                        break 2;
                    }
                }
                return $this->raiseError(self::GAMES_CHESS_ERROR_MULTIPIECE,
                    array('color' => $color, 'piece' => $type));
            break;
            case 'K' :
                if (!$this->_pieces[$color . 'K']) {
                    $this->_pieces[$color . 'K'] = $square;
                    $this->_board[$square] = $color . 'K';
                } else {
                    return $this->raiseError(self::GAMES_CHESS_ERROR_MULTIPIECE,
                        array('color' => $color, 'piece' => $type));
                }
            break;
        }
        return true;
    }


    /**
     * Generate a representation of the chess board and pieces for use as a
     * direct translation to a visual chess board
     *
     * Must be overridden in child classes
     * @return array
     * @abstract
     */
    function toArray()
    {
        $ret = array();
        foreach ($this->_board as $square => $piece) {
            if ($piece == $square) {
                $ret[$square] = false;
                continue;
            }
            $lower = $piece{0};
            if (is_array($this->_pieces[$piece])) {
                $piece = $this->_pieces[$piece][1];
            } else {
                $piece = $piece{1};
            }
            if ($lower == 'B') {
                $piece = strtolower($piece);
            }
            $ret[$square] = $piece;
        }
        uksort($ret, array($this, '_sortToArray'));
        return $ret;
    }

    /**
     * Sort two algebraic coordinates for easy display by foreach() iteration
     * @param string
     * @param string
     * @access private
     */
    function _sortToArray($a, $b)
    {
        if ($a == $b) {
            return 0;
        }
        if ($a{1} == $b{1}) {
            return strnatcmp($a{0}, $b{0});
        }
        if ($a{0} == $b{0}) {
            return strnatcmp($b{1}, $a{1});
        }
        if ($b{1} > $a{1}) {
            return 1;
        }
        if ($a{1} > $b{1}) {
            return -1;
        }
    }


    /**
     * Determine whether moving a piece from one square to another requires
     * a pawn promotion
     * @param string [a-h][1-8] location of the piece to move
     * @param string [a-h][1-8] place to move the piece to
     * @return boolean true if the move represented by moving from $from to $to
     *                 is a pawn promotion move
     */
    function isPromoteMove($from, $to)
    {
        $test = $this->_convertSquareToSAN($from, $to);
        if ($this->isError($test)) {
            return false;
        }
        if (strpos($test, '=Q') !== false) {
            return true;
        }
        return false;
    }

    /**
     * @return W|B return the color of the side to move (white or black)
     */
    function toMove()
    {
        return $this->_move;
    }

    /**
     * Determine legality of kingside castling
     * @return boolean
     */
    function canCastleKingside()
    {
        return $this->{'_' . $this->_move . 'CastleK'};
    }


    /**
     * Determine legality of queenside castling
     * @return boolean
     */
    function canCastleQueenside()
    {
        return $this->{'_' . $this->_move . 'CastleQ'};
    }

    /**
     * Move a piece from one square to another, and mark the old square as empty
     *
     * NO validation is performed, use {@link moveSquare()} for validation.
     *
     * @param string [a-h][1-8] square to move from
     * @param string [a-h][1-8] square to move to
     * @param string piece to promote to, if this is a promotion move
     * @access protected
     */
    function _moveAlgebraic($from, $to, $promote = '')
    {
      if($from == $to) {
        //sometimes in chess960 castling, the piece doesnt actually move. if we try to move it, it'll leave the board field blank
        return;
      }

      if ($to == $this->_enPassantSquare && $this->isPawn($this->_board[$from])) {
          $rank = ($to{1} == '3') ? '4' : '5';
          // this piece was just taken
          $this->_takePiece($to{0} . $rank);
          $this->_board[$to{0} . $rank] = $to{0} . $rank;
      }
      if ($this->_board[$to] != $to) {
          // this piece was just taken
          $this->_takePiece($to);
      }
      // mark the piece as moved
      $this->_movePiece($from, $to, $promote);
      $this->_board[$to] = $this->_board[$from];
      $this->_board[$from] = $from;
    }

    function _swapSquares($from, $to)
    {
      if($from == $to) {
        //sometimes in chess960 castling, the piece doesnt actually move. if we try to move it, it'll leave the board field blank
        return;
      }

      $strPiece = $this->_board[$to];

      // mark the piece as moved
      $this->_movePiece($from, $to);
      $this->_board[$to] = $this->_board[$from];
      $this->_board[$from] = $from;

      if($strPiece == $to) {
        return; //no piece to swap
      } else {
        $this->_board[$from] = $strPiece;
        $this->_pieces[$strPiece] = $from;
      }
    }

    /**
     * Parse out the segments of a move (minus any annotations)
     * @param string
     * @return array
     * @access protected
     */
    function _parseMove($move)
    {
        if ($move == 'O-O') {
            return array(self::GAMES_CHESS_CASTLE => 'K');
        }
        if ($move == 'O-O-O') {
            return array(self::GAMES_CHESS_CASTLE => 'Q');
        }
        // pawn moves
        if (is_string($move) && preg_match('/^P?(([a-h])([1-8])?(x))?([a-h][1-8])(=?([QRNB]))?$/', $move, $match)) {
            if ($match[2]) {
                $takesfrom = $match[2]{0};
            } else {
                $takesfrom = '';
            }
            $res = array(
                'takesfrom' => $takesfrom,
                'takes' => $match[4],
                'disambiguate' => '',
                'square' => $match[5],
                'promote' => '',
                'piece' => 'P',
            );
            if (isset($match[7])) {
                $res['promote'] = $match[7];
            }
            return array(self::GAMES_CHESS_PAWNMOVE => $res);
        // piece moves
        } elseif (is_string($move) && preg_match('/^(K)(x)?([a-h][1-8])$/', $move, $match)) {
            $res = array(
                'takesfrom' => false,
                'piece' => $match[1],
                'disambiguate' => '',
                'takes' => $match[2],
                'square' => $match[3],
            );
            return array(self::GAMES_CHESS_PIECEMOVE => $res);
        } elseif (is_string($move) && preg_match('/^([QRBN])([a-h]|[1-8]|[a-h][1-8])?(x)?([a-h][1-8])$/', $move, $match)) {
            $res = array(
                'takesfrom' => false,
                'piece' => $match[1],
                'disambiguate' => $match[2],
                'takes' => $match[3],
                'square' => $match[4],
            );
            return array(self::GAMES_CHESS_PIECEMOVE => $res);
        } elseif (is_string($move) && preg_match('/^([QRBN])@([a-h][1-8])$/', $move, $match)) {
            $res = array(
                'piece' => $match[1],
                'square' => $match[2],
            );
            return array(self::GAMES_CHESS_PIECEPLACEMENT => $res);
        // error
        } elseif (is_string($move) && preg_match('/^([P])@([a-h][2-7])$/', $move, $match)) {
            $res = array(
                'piece' => $match[1],
                'square' => $match[2],
            );
            return array(self::GAMES_CHESS_PIECEPLACEMENT => $res);
        // error
        } elseif (is_string($move) && preg_match('/^([P])@([a-h][18])$/', $move, $match)) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_CANT_PLACE_18, array('san' => $move));
        // error
        } else {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_SAN,
                array('pgn' => $move));
        }
    }


    /**
     * Set up the board with the starting position
     *
     * Must be overridden in child classes
     * @abstract
     * @access protected
     */
    function _setupStartingPosition()
    {
        $this->_board = array(
'a8' => 'BR1', 'b8' => 'BN1', 'c8' => 'BB1', 'd8' => 'BQ', 'e8' => 'BK', 'f8' => 'BB2', 'g8' => 'BN2', 'h8' => 'BR2',
'a7' => 'BP1', 'b7' => 'BP2', 'c7' => 'BP3', 'd7' => 'BP4', 'e7' => 'BP5', 'f7' => 'BP6', 'g7' => 'BP7', 'h7' => 'BP8',
'a6' => 'a6', 'b6' => 'b6', 'c6' => 'c6', 'd6' => 'd6', 'e6' => 'e6', 'f6' => 'f6', 'g6' => 'g6', 'h6' => 'h6',
'a5' => 'a5', 'b5' => 'b5', 'c5' => 'c5', 'd5' => 'd5', 'e5' => 'e5', 'f5' => 'f5', 'g5' => 'g5', 'h5' => 'h5',
'a4' => 'a4', 'b4' => 'b4', 'c4' => 'c4', 'd4' => 'd4', 'e4' => 'e4', 'f4' => 'f4', 'g4' => 'g4', 'h4' => 'h4',
'a3' => 'a3', 'b3' => 'b3', 'c3' => 'c3', 'd3' => 'd3', 'e3' => 'e3', 'f3' => 'f3', 'g3' => 'g3', 'h3' => 'h3',
'a2' => 'WP1', 'b2' => 'WP2', 'c2' => 'WP3', 'd2' => 'WP4', 'e2' => 'WP5', 'f2' => 'WP6', 'g2' => 'WP7', 'h2' => 'WP8',
'a1' => 'WR1', 'b1' => 'WN1', 'c1' => 'WB1', 'd1' => 'WQ', 'e1' => 'WK', 'f1' => 'WB2', 'g1' => 'WN2', 'h1' => 'WR2',
        );
        $this->_halfMoves = 0;
        $this->_moveNumber = 1;
        $this->_move = 'W';
        $this->_WCastleQ = true;
        $this->_WCastleK = true;
        $this->_BCastleQ = true;
        $this->_BCastleK = true;
        $this->_enPassantSquare = '-';
        $this->_pieces =
        array(
            'WR1' => 'a1',
            'WN1' => 'b1',
            'WB1' => 'c1',
            'WQ' => 'd1',
            'WK' => 'e1',
            'WB2' => 'f1',
            'WN2' => 'g1',
            'WR2' => 'h1',

            'WP1' => array('a2', 'P'),
            'WP2' => array('b2', 'P'),
            'WP3' => array('c2', 'P'),
            'WP4' => array('d2', 'P'),
            'WP5' => array('e2', 'P'),
            'WP6' => array('f2', 'P'),
            'WP7' => array('g2', 'P'),
            'WP8' => array('h2', 'P'),

            'BP1' => array('a7', 'P'),
            'BP2' => array('b7', 'P'),
            'BP3' => array('c7', 'P'),
            'BP4' => array('d7', 'P'),
            'BP5' => array('e7', 'P'),
            'BP6' => array('f7', 'P'),
            'BP7' => array('g7', 'P'),
            'BP8' => array('h7', 'P'),

            'BR1' => 'a8',
            'BN1' => 'b8',
            'BB1' => 'c8',
            'BQ' => 'd8',
            'BK' => 'e8',
            'BB2' => 'f8',
            'BN2' => 'g8',
            'BR2' => 'h8',
        );
    }


    /**
     * Parse a Forsyth-Edwards Notation (FEN) chessboard position string, and
     * set up the chessboard with this position
     * @param string
     * @access private
     */
    function _parseFen($fen)
    {
        $splitfen = explode(' ', $fen);
        if (count($splitfen) == 3) {
          $fen .= ' - 0 1';
        } elseif(count($splitfen) == 4) {
          $fen .= ' 0 1';
        } elseif(count($splitfen) == 5) {
          $fen .= ' 1';
        } elseif(count($splitfen) != 6) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_COUNT,
                array('fen' => $fen, 'sections' => count($splitfen)));
        }

        $splitfen = explode(' ', $fen);
        foreach($splitfen as $index => $test) {
            if ($test == '') {
                return $this->raiseError(self::GAMES_CHESS_ERROR_EMPTY_FEN,
                    array('fen' => $fen, 'section' => $index));
            }
        }

        $this->blankBoard();
        $loc = 'a8';
        $idx = 0;
        $FEN = $splitfen[0];

        // parse position section
        while ($idx < strlen($FEN)) {
            $c = $FEN{$idx};
            switch ($c) {
                case "K" :
                  if($this->_Chess960) {
                    $this->_KColumn = $loc{0};
                  }
                case "R" :
                case "Q" :
                case "B" :
                case "N" :
                case "P" :
                    try {
                        $err = $this->addPiece('W', $c, $loc);
                    } catch (\Exception $e) {
                        //do nothing
                    }

                    if ($this->isError($err)) {
                        if ($err->getCode() == self::GAMES_CHESS_ERROR_MULTIPIECE) {
                            return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_MULTIPIECE,
                            array('fen' => $fen, 'color' => 'W', 'piece' => $c));
                        } else {
                            return $err;
                        }
                    }
                break;
                case "r" :
                  if($this->_Chess960) {
                    if(!$this->_QRookSet) {
                      $this->_QRookColumn = $loc{0};
                      $this->_QRookSet = true;
                    } else {
                      $this->_KRookColumn = $loc{0};
                    }
                  }
                case "k" :
                case "q" :
                case "b" :
                case "n" :
                case "p" :
                    try {
                        $err = $this->addPiece('B', strtoupper($c), $loc);
                    } catch (\Exception $e) {
                        //do nothing
                    }

                    if ($this->isError($err)) {
                        if ($err->getCode() == self::GAMES_CHESS_ERROR_MULTIPIECE) {
                            return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_MULTIPIECE,
                            array('fen' => $fen, 'color' => 'B', 'piece' => $c));
                        } else {
                            return $err;
                        }
                    }
                break;

                case "1" :
                case "2" :
                case "3" :
                case "4" :
                case "5" :
                case "6" :
                case "7" :
                case "8" :
                    $loc{0} = chr(ord($loc{0}) + ($c - 1));
                break;
                case "/" :
                    $loc{1} = $loc{1} - 1;
                    $loc{0} = 'a';
                    $idx++;
                    continue 2;
                break;
                default :
                    return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_INVALIDPIECE,
                        array('fen' => $fen, 'fenchar' => $c));
                break;
            }
            $idx++;
            $loc{0} = chr(ord($loc{0}) + 1);
            if (ord($loc{0}) > ord('h')) {
                if (strlen($FEN) > $idx && $FEN{$idx} != '/') {
                    return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_TOOMUCH,
                        array('fen' => $fen));
                }
            }
        }
        if ($loc != 'i1') {
            return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_TOOLITTLE,
                array('fen' => $fen));
        }

        // parse who's to move
        if (!in_array($splitfen[1], array('w', 'b', 'W', 'B'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_TOMOVEWRONG,
                array('fen' => $fen, 'tomove' => $splitfen[1]));
        }
        $this->_move = strtoupper($splitfen[1]);

        // parse castling rights
        if (strlen($splitfen[2]) > 4) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_CASTLETOOLONG,
                array('fen' => $fen, 'castle' => $splitfen[2]));
        }
        $this->_WCastleQ = false;
        $this->_WCastleK = false;
        $this->_BCastleQ = false;
        $this->_BCastleK = false;
        if ($splitfen[2] != '-') {
            for ($i = 0; $i < 4; $i++) {
                if ($i >= strlen($splitfen[2])) {
                    continue;
                }
                switch ($splitfen[2]{$i}) {
                    case 'K' :
                        $this->_WCastleK = true;
                    break;
                    case 'Q' :
                        $this->_WCastleQ = true;
                    break;
                    case 'k' :
                        $this->_BCastleK = true;
                    break;
                    case 'q' :
                        $this->_BCastleQ = true;
                    break;
                    default:
                        return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_CASTLEWRONG,
                            array('fen' => $fen, 'castle' => $splitfen[2]{$i}));
                    break;
                }
            }
        }

        // parse en passant square
        $this->_enPassantSquare = '-';
        if ($splitfen[3] != '-') {
            if (!preg_match('/^[a-h][36]$/', $splitfen[3])) {
                return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_INVALID_EP,
                    array('fen' => $fen, 'enpassant' => $splitfen[3]));
            }
            $this->_enPassantSquare = $splitfen[3];
        }

        // parse half moves since last pawn move or capture
        if (!is_numeric($splitfen[4])) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_INVALID_PLY,
                array('fen' => $fen, 'ply' => $splitfen[4]));
        }
        $this->_halfMoves = $splitfen[4];

        // parse move number
        if (!is_numeric($splitfen[5])) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_FEN_INVALID_MOVENUMBER,
                array('fen' => $fen, 'movenumber' => $splitfen[5]));
        }
        $this->_moveNumber = $splitfen[5];
        return true;
    }

    /**
     * Validate a move
     * @param array parsed move array from {@link _parsedMove()}
     * @return true|PEAR_Error
     * @throws self::GAMES_CHESS_ERROR_IN_CHECK
     * @throws self::GAMES_CHESS_ERROR_CANT_CK
     * @throws self::GAMES_CHESS_ERROR_CK_PIECES_IN_WAY
     * @throws self::GAMES_CHESS_ERROR_CANT_CQ
     * @throws self::GAMES_CHESS_ERROR_CQ_PIECES_IN_WAY
     * @throws self::GAMES_CHESS_ERROR_CASTLE_WOULD_CHECK
     * @throws self::GAMES_CHESS_ERROR_CANT_CAPTURE_OWN
     * @throws self::GAMES_CHESS_ERROR_STILL_IN_CHECK
     * @throws self::GAMES_CHESS_ERROR_MOVE_WOULD_CHECK
     * @access protected
     */
    function _validMove($move, $from = null)
    {
        list($type, $info) = each($move);

        $this->startTransaction();
        $valid = false;
        switch ($type) {
            case self::GAMES_CHESS_CASTLE :
                if ($this->inCheck($this->_move)) {
                    $this->rollbackTransaction();
                    return $this->raiseError(self::GAMES_CHESS_ERROR_IN_CHECK);
                }
                if ($this->_move == 'W') {
                  $intRow = 1;
                } else {
                  $intRow = 8;
                }

                if ($info == 'K') {
                  //CASTLING KINGSIDE

                  if (!$this->{'_' . $this->_move . 'CastleK'}) {
                      $this->rollbackTransaction();
                      return $this->raiseError(self::GAMES_CHESS_ERROR_CANT_CK);
                  }

                  //find left most column (either kings starting point or rooks ending point)
                  if($this->objColumnToNumber[$this->_KColumn] < $this->objColumnToNumber['f']) {
                    $strLeftColumn = $this->_KColumn;
                  } else {
                    $strLeftColumn = 'f';
                  }

                  //find right most column (either rooks starting point or kings ending point)
                  if($this->objColumnToNumber[$this->_KRookColumn] > $this->objColumnToNumber['g']) {
                    $strRightColumn = $this->_KRookColumn;
                  } else {
                    $strRightColumn = 'g';
                  }

                  for($i=$this->objColumnToNumber[$strLeftColumn]; $i <= $this->objColumnToNumber[$strRightColumn]; $i++) {
                    if($this->objNumberToColumn[$i] == $this->_KColumn || $this->objNumberToColumn[$i] == $this->_KRookColumn) {
                      //The column we're checking is the king or rook column, so ignore
                      continue;
                    }
                    if ($this->_board[$this->objNumberToColumn[$i] . $intRow] != ($this->objNumberToColumn[$i] . $intRow)) {
                      //there is a piece in the way!
                      $this->rollbackTransaction();
                      return $this->raiseError(self::GAMES_CHESS_ERROR_CK_PIECES_IN_WAY);
                    }
                  }

                  $strFinalKingColumn = 'g';
                } else {
                  //CASTLING QUEENSIDE

                  if (!$this->{'_' . $this->_move . 'CastleQ'}) {
                      $this->rollbackTransaction();
                      return $this->raiseError(self::GAMES_CHESS_ERROR_CANT_CK);
                  }

                  //find left most column (either rooks starting point or kings ending point)
                  if($this->objColumnToNumber[$this->_QRookColumn] < $this->objColumnToNumber['c']) {
                    $strLeftColumn = $this->_QRookColumn;
                  } else {
                    $strLeftColumn = 'c';
                  }

                  //find right most column (either kings starting point or rooks ending point)
                  if($this->objColumnToNumber[$this->_KColumn] > $this->objColumnToNumber['d']) {
                    $strRightColumn = $this->_KColumn;
                  } else {
                    $strRightColumn = 'd';
                  }

                  for($i=$this->objColumnToNumber[$strLeftColumn]; $i <= $this->objColumnToNumber[$strRightColumn]; $i++) {
                    if($this->objNumberToColumn[$i] == $this->_KColumn || $this->objNumberToColumn[$i] == $this->_QRookColumn) {
                      //The column we're checking is the king or rook column, so ignore
                      continue;
                    }
                    if ($this->_board[$this->objNumberToColumn[$i] . $intRow] != ($this->objNumberToColumn[$i] . $intRow)) {
                      //there is a piece in the way!
                      $this->rollbackTransaction();
                      return $this->raiseError(self::GAMES_CHESS_ERROR_CK_PIECES_IN_WAY);
                    }
                  }

                  $strFinalKingColumn = 'c';
                }

                $kingsquares = array();
                if($this->objColumnToNumber[$this->_KColumn] > $this->objColumnToNumber[$strFinalKingColumn]) {
                  //king walking left

                  for($i = ($this->objColumnToNumber[$this->_KColumn] - 1); $i >= $this->objColumnToNumber[$strFinalKingColumn]; $i--) {
                    $kingsquares[] = ($this->objNumberToColumn[$i] . $intRow);
                  }
                } elseif($this->objColumnToNumber[$this->_KColumn] < $this->objColumnToNumber[$strFinalKingColumn]) {
                  //king walking right

                  for($i = ($this->objColumnToNumber[$this->_KColumn] + 1); $i <= $this->objColumnToNumber[$strFinalKingColumn]; $i++) {
                    $kingsquares[] = ($this->objNumberToColumn[$i] . $intRow);
                  }
                }

                $on = ($this->_KColumn . $intRow);

                // check every square the king could move to and make sure
                // we wouldn't be in check
                foreach ($kingsquares as $square) {
                    $this->_moveAlgebraic($on, $square);
                    if ($this->inCheck($this->_move)) {
                        $this->rollbackTransaction();
                        return $this->raiseError(self::GAMES_CHESS_ERROR_CASTLE_WOULD_CHECK);
                    }
                    $on = $square;
                }
                $valid = true;
            break;
            case self::GAMES_CHESS_PIECEMOVE :
            case self::GAMES_CHESS_PAWNMOVE :
                if (!$this->isError($piecesq = $from? $from: $this->_getSquareFromParsedMove($info))) {
                    $colorMoving = $this->_move;
                    // $wasinCheck = $this->inCheck($colorMoving);  //KK
                    $piece = $this->_board[$info['square']];
                    if ($info['takes'] && $this->_board[$info['square']] ==
                          $info['square']) {
                        if (!($info['square'] == $this->_enPassantSquare &&
                              $info['piece'] == 'P')) {
                            return $this->raiseError(self::GAMES_CHESS_ERROR_NO_PIECE,
                                array('square' => $info['square']));
                        }
                    }
                    $this->_moveAlgebraic($piecesq, $info['square']);
                    $valid = !$this->inCheck($colorMoving);
                    // if ($wasinCheck && !$valid) {
                        // $this->rollbackTransaction();
                        // return $this->raiseError(self::GAMES_CHESS_ERROR_STILL_IN_CHECK);
                    // } elseif (!$valid) {
                    if (!$valid) {
                        $this->rollbackTransaction();
                        return $this->raiseError(self::GAMES_CHESS_ERROR_MOVE_WOULD_CHECK);
                    }
                } else {
                    $this->rollbackTransaction();
                    return $piecesq;
                }
            break;
        }
        $this->rollbackTransaction();
        return $valid;
    }

    /**
     * Convert a starting and ending algebraic square into SAN
     * @access protected
     * @param string [a-h][1-8] square piece is on
     * @param string [a-h][1-8] square piece moves to
     * @param string Q|R|B|N
     * @return string|PEAR_Error
     * @throws self::GAMES_CHESS_ERROR_INVALID_PROMOTE
     * @throws self::GAMES_CHESS_ERROR_INVALID_SQUARE
     * @throws self::GAMES_CHESS_ERROR_NO_PIECE
     * @throws self::GAMES_CHESS_ERROR_WRONG_COLOR
     * @throws self::GAMES_CHESS_ERROR_CANT_MOVE_THAT_WAY
     */
    function _convertSquareToSAN($from, $to, $promote = '')
    {
        if ($promote == '') {
            $promote = 'Q';
        }
        $promote = strtoupper($promote);
        if (!in_array($promote, array('Q', 'B', 'N', 'R'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_PROMOTE,
                array('piece' => $promote));
        }
        $SAN = '';
        if (!preg_match('/^[a-h][1-8]$/', $from)) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_SQUARE,
                array('square' => $from));
        }
        if (!preg_match('/^[a-h][1-8]$/', $to)) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_SQUARE,
                array('square' => $to));
        }
        $piece = $this->_squareToPiece($from);
        if (!$piece) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_NO_PIECE,
                array('square' => $from));
        }
        if ($piece['color'] != $this->_move) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_WRONG_COLOR,
                array('square' => $from));
        }

        if ($piece['piece'] == 'K') {
          if((($to == ($this->_QRookColumn . (($this->_move == 'B')?'8':'1'))) && $this->{'_' . $this->_move . 'CastleQ'}) || (($to == ($this->_KRookColumn . (($this->_move == 'B')?'8':'1'))) && $this->{'_' . $this->_move . 'CastleK'}) || !in_array($to, $this->_getKingSquares($from))) {
            // this is a castling attempt
            if($this->objColumnToNumber[$from{0}] < $this->objColumnToNumber[$to{0}]) {
                return 'O-O';
            } else {
                return 'O-O-O';
            }
          }
        } else {
          $moves = $this->getPossibleMoves($piece['piece'], $from, $piece['color']);    //KK start: optimize: no need to get all possible moves
          if (!in_array($to, $moves)) {
              return $this->raiseError(self::GAMES_CHESS_ERROR_CANT_MOVE_THAT_WAY,
                  array('from' => $from, 'to' => $to));
          }  //KK end
        }
        $others = array();
        if ($piece['piece'] != 'K' && $piece['piece'] != 'P') {
            $others = $this->_getAllPieceSquares($piece['piece'],
                                                 $piece['color'], $from);
        }
        $disambiguate = '';
        $ambiguous = array();
        if (count($others)) {
            foreach ($others as $square) {
                if (in_array($to, $this->getPossibleMoves($piece['piece'], $square,     //KK again: no need to get all possible moves
                                                          $piece['color']))) {
                    // other pieces can move to this square - need to disambiguate
                    $ambiguous[] = $square;
                }
            }
        }
        if (count($ambiguous) == 1) {
            if ($ambiguous[0]{0} != $from{0}) {
                $disambiguate = $from{0};
            } elseif ($ambiguous[0]{1} != $from{1}) {
                $disambiguate = $from{1};
            } else {
                $disambiguate = $from;
            }
        } elseif (count($ambiguous)) {
            $disambiguate = $from;
        }
        if ($piece['piece'] == 'P') {
            if ($from{0} != $to{0}) {
                $SAN = $from{0};
            }
        } else {
            $SAN = $piece['piece'];
        }
        $SAN .= $disambiguate;

        if ($this->_board[$to] != $to) {
            $SAN .= 'x';
        } else {
            if ($piece['piece'] == 'P' && $to == $this->_enPassantSquare) {
                $SAN .= 'x';
            }
        }
        $SAN .= $to;
        if ($piece['piece'] == 'P' && ($to{1} == '1' || $to{1} == '8')) {
            $SAN .= '=' . $promote;
        }
        return $SAN;
    }

    /**
     * Get a list of all possible theoretical squares a piece of this nature
     * and color could move to with the current board and game setup.
     *
     * This method will return all valid moves without determining the presence
     * of check
     * @param K|P|Q|R|B|N Piece name
     * @param string [a-h][1-8] algebraic location of the piece
     * @param B|W color of the piece
     * @param boolean Whether to return shortcut king moves for castling
     * @return array|PEAR_Error
     * @throws self::GAMES_CHESS_ERROR_INVALID_COLOR
     * @throws self::GAMES_CHESS_ERROR_INVALID_SQUARE
     * @throws self::GAMES_CHESS_ERROR_INVALID_PIECE
     */
    function getPossibleMoves($piece, $square, $color = null, $returnCastleMoves = true)
    {
        if (is_null($color)) {
            $color = $this->_move;
        }
        $color = strtoupper($color);
        if (!in_array($color, array('W', 'B'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_COLOR,
                array('color' => $color));
        }
        if (!preg_match('/^[a-h][1-8]$/', $square)) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_SQUARE,
                array('square' => $square));
        }
        $piece = strtoupper($piece);
        if (!in_array($piece, array('K', 'Q', 'B', 'N', 'R', 'P'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_PIECE,
                array('piece' => $piece));
        }
        switch ($piece) {
            case 'K' :
                return $this->getPossibleKingMoves($square, $color, $returnCastleMoves);
            break;
            case 'Q' :
                return $this->getPossibleQueenMoves($square, $color);
            break;
            case 'B' :
                return $this->getPossibleBishopMoves($square, $color);
            break;
            case 'N' :
                return $this->getPossibleKnightMoves($square, $color);
            break;
            case 'R' :
                return $this->getPossibleRookMoves($square, $color);
            break;
            case 'P' :
                return $this->getPossiblePawnMoves($square, $color);
            break;
        }
    }

    /**
     * Get the set of squares that are diagonals from this square on an empty board.
     *
     * WARNING: assumes valid input
     * @param string [a-h][1-8]
     * @param boolean if true, simply returns an array of all squares
     * @return array Format:
     *
     * <pre>
     * array(
     *   'NE' => array(square, square),
     *   'NW' => array(square, square),
     *   'SE' => array(square, square),
     *   'SW' => array(square, square)
     * )
     * </pre>
     *
     * Think of the diagonal directions as on a map.  squares are listed with
     * closer squares first
     */
    function _getDiagonals($square, $returnFlatArray = false)
    {
        $nw = ($square{0} != 'a') && ($square{1} != '8');
        $ne = ($square{0} != 'h') && ($square{1} != '8');
        $sw = ($square{0} != 'a') && ($square{1} != '1');
        $se = ($square{0} != 'h') && ($square{1} != '1');
        if ($nw) {
            $nw = array();
            $i = $square;
            while(ord($i{0}) > ord('a') && ord($i{1}) < ord('8')) {
                $i{0} = chr(ord($i{0}) - 1);
                $i{1} = chr(ord($i{1}) + 1);
                $nw[] = $i;
            }
        }
        if ($ne) {
            $ne = array();
            $i = $square;
            while(ord($i{0}) < ord('h') && ord($i{1}) < ord('8')) {
                $i{0} = chr(ord($i{0}) + 1);
                $i{1} = chr(ord($i{1}) + 1);
                $ne[] = $i;
            }
        }
        if ($sw) {
            $sw = array();
            $i = $square;
            while(ord($i{0}) > ord('a') && ord($i{1}) > ord('1')) {
                $i{0} = chr(ord($i{0}) - 1);
                $i{1} = chr(ord($i{1}) - 1);
                $sw[] = $i;
            }
        }
        if ($se) {
            $se = array();
            $i = $square;
            while(ord($i{0}) < ord('h') && ord($i{1}) > ord('1')) {
                $i{0} = chr(ord($i{0}) + 1);
                $i{1} = chr(ord($i{1}) - 1);
                $se[] = $i;
            }
        }
        if ($returnFlatArray) {
            if (!$nw) {
                $nw = array();
            }
            if (!$sw) {
                $sw = array();
            }
            if (!$ne) {
                $ne = array();
            }
            if (!$se) {
                $se = array();
            }
            return array_merge($ne, array_merge($nw, array_merge($se, $sw)));
        }
        return array('NE' => $ne, 'NW' => $nw, 'SE' => $se, 'SW' => $sw);
    }

    /**
     * Get the set of squares that are diagonals from this square on an empty board.
     *
     * WARNING: assumes valid input
     * @param string [a-h][1-8]
     * @param boolean if true, simply returns an array of all squares
     * @return array Format:
     *
     * <pre>
     * array(
     *   'N' => array(square, square),
     *   'E' => array(square, square),
     *   'S' => array(square, square),
     *   'W' => array(square, square)
     * )
     * </pre>
     *
     * Think of the horizontal directions as on a map.  squares are listed with
     * closer squares first
     * @access protected
     */
    function _getRookSquares($square, $returnFlatArray = false)
    {
        $n = ($square{1} != '8');
        $e = ($square{0} != 'h');
        $s = ($square{1} != '1');
        $w = ($square{0} != 'a');
        if ($n) {
            $n = array();
            $i = $square;
            while(ord($i{1}) < ord('8')) {
                $i{1} = chr(ord($i{1}) + 1);
                $n[] = $i;
            }
        }
        if ($e) {
            $e = array();
            $i = $square;
            while(ord($i{0}) < ord('h')) {
                $i{0} = chr(ord($i{0}) + 1);
                $e[] = $i;
            }
        }
        if ($s) {
            $s = array();
            $i = $square;
            while(ord($i{1}) > ord('1')) {
                $i{1} = chr(ord($i{1}) - 1);
                $s[] = $i;
            }
        }
        if ($w) {
            $w = array();
            $i = $square;
            while(ord($i{0}) > ord('a')) {
                $i{0} = chr(ord($i{0}) - 1);
                $w[] = $i;
            }
        }
        if ($returnFlatArray) {
            if (!$n) {
                $n = array();
            }
            if (!$s) {
                $s = array();
            }
            if (!$e) {
                $e = array();
            }
            if (!$w) {
                $w = array();
            }
            return array_merge($n, array_merge($s, array_merge($e, $w)));
        }
        return array('N' => $n, 'E' => $e, 'S' => $s, 'W' => $w);
    }

    /**
     * Get all the squares a queen could go to on a blank board
     *
     * WARNING: assumes valid input
     * @return array combines contents of {@link _getRookSquares()} and
     *               {@link _getDiagonals()}
     * @param string [a-h][1-8]
     * @param boolean if true, simply returns an array of all squares
     * @access protected
     */
    function _getQueenSquares($square, $returnFlatArray = false)
    {
        return array_merge($this->_getRookSquares($square, $returnFlatArray),
                           $this->_getDiagonals($square, $returnFlatArray));
    }

    /**
     * Get all the squares a knight could move to on an empty board
     *
     * WARNING: assumes valid input
     * @param string [a-h][1-8]
     * @param boolean if true, simply returns an array of all squares
     * @return array Returns an array of all the squares organized by compass
     *               point, that a knight can go to.  These squares may be indexed
     *               by any of WNW, NNW, NNE, ENE, ESE, SSE, SSW or WSW, unless
     *               $returnFlatArray is true, in which case an array of squares
     *               is returned
     * @access protected
     */
    function _getKnightSquares($square, $returnFlatArray = false)
    {
        $squares = array();
        // west-northwest square
        if (ord($square{0}) > ord('b') && $square{1} < 8) {
            $squares['WNW'] = chr(ord($square{0}) - 2) . ($square{1} + 1);
        }
        // north-northwest square
        if (ord($square{0}) > ord('a') && $square{1} < 7) {
            $squares['NNW'] = chr(ord($square{0}) - 1) . ($square{1} + 2);
        }
        // north-northeast square
        if (ord($square{0}) < ord('h') && $square{1} < 7) {
            $squares['NNE'] = chr(ord($square{0}) + 1) . ($square{1} + 2);
        }
        // east-northeast square
        if (ord($square{0}) < ord('g') && $square{1} < 8) {
            $squares['ENE'] = chr(ord($square{0}) + 2) . ($square{1} + 1);
        }
        // east-southeast square
        if (ord($square{0}) < ord('g') && $square{1} > 1) {
            $squares['ESE'] = chr(ord($square{0}) + 2) . ($square{1} - 1);
        }
        // south-southeast square
        if (ord($square{0}) < ord('h') && $square{1} > 2) {
            $squares['SSE'] = chr(ord($square{0}) + 1) . ($square{1} - 2);
        }
        // south-southwest square
        if (ord($square{0}) > ord('a') && $square{1} > 2) {
            $squares['SSW'] = chr(ord($square{0}) - 1) . ($square{1} - 2);
        }
        // west-southwest square
        if (ord($square{0}) > ord('b') && $square{1} > 1) {
            $squares['WSW'] = chr(ord($square{0}) - 2) . ($square{1} - 1);
        }
        if ($returnFlatArray) {
            return array_values($squares);
        }
        return $squares;
    }

    /**
     * Get a list of all the squares a king could castle to on an empty board
     *
     * WARNING: assumes valid input
     * @param string [a-h][1-8]
     * @return array
     * @access protected
     * @since 0.7alpha
     */
    function _getCastleSquares($square)
    {
        $ret = array();
        if ($this->_move == 'W') {
            if ($square == 'e1' && $this->_WCastleK) {
                $ret[] = 'g1';
            }
            if ($square == 'e1' && $this->_WCastleQ) {
                $ret[] = 'c1';
            }

        } else {
            if ($square == 'e8' && $this->_BCastleK) {
                $ret[] = 'g8';
            }
            if ($square == 'e8' && $this->_BCastleQ) {
                $ret[] = 'c8';
            }
        }
        return $ret;
    }

    /**
     * Get a list of all the squares a king could move to on an empty board
     *
     * WARNING: assumes valid input
     * @param string [a-h][1-8]
     * @return array
     * @access protected
     */
    function _getKingSquares($square)
    {
        $squares = array();
        if (ord($square{0}) - ord('a')) {
            $squares[] = chr(ord($square{0}) - 1) . $square{1};
            if ($square{1} < 8) {
                $squares[] = chr(ord($square{0}) - 1) . ($square{1} + 1);
            }
            if ($square{1} > 1) {
                $squares[] = chr(ord($square{0}) - 1) . ($square{1} - 1);
            }
        }
        if (ord($square{0}) - ord('h')) {
            $squares[] = chr(ord($square{0}) + 1) . $square{1};
            if ($square{1} < 8) {
                $squares[] = chr(ord($square{0}) + 1) . ($square{1} + 1);
            }
            if ($square{1} > 1) {
                $squares[] = chr(ord($square{0}) + 1) . ($square{1} - 1);
            }
        }
        if ($square{1} > 1) {
            $squares[] = $square{0} . ($square{1} - 1);
        }
        if ($square{1} < 8) {
            $squares[] = $square{0} . ($square{1} + 1);
        }
        return $squares;
    }

    /**
     * Get the location of all pieces on the board of a certain color
     *
     * Default is the color that is about to move
     * @param W|B
     * @return array|PEAR_Error
     * @throws self::GAMES_CHESS_ERROR_INVALID_COLOR
     */
    function getPieceLocations($color = null)
    {
        if (is_null($color)) {
            $color = $this->_move;
        }
        $color = strtoupper($color);
        if (!in_array($color, array('W', 'B'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_COLOR,
                array('color' => $color));
        }
        return $this->_getAllPieceLocations($color);
    }

    /**
     * Get the location of every piece on the board of color $color
     * @param W|B color of pieces to check
     * @return array
     * @abstract
     * @access protected
     */
    function _getAllPieceLocations($color)
    {
        $ret = array();
        foreach ($this->_pieces as $name => $loc) {
            if ($name{0} == $color) {
                $where =  (is_array($loc) ? $loc[0] : $loc);
                if ($where) {
                    $ret[] = $where;
                }
            }
        }
        return $ret;
    }

    /**
     * Get all legal Knight moves (checking of the king is not taken into account)
     * @param string [a-h][1-8] Location of piece
     * @param W|B color of piece, or null to use current piece to move
     * @return array
     */
    function getPossibleKnightMoves($square, $color = null)
    {
        if (is_null($color)) {
            $color = $this->_move;
        }
        $color = strtoupper($color);
        if (!in_array($color, array('W', 'B'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_COLOR,
                array('color' => $color));
        }
        if (!preg_match('/^[a-h][1-8]$/', $square)) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_SQUARE,
                array('square' => $square));
        }
        $allmoves = $this->_getKnightSquares($square);
        $mypieces = $this->getPieceLocations($color);
        return array_values(array_diff($allmoves, $mypieces));
    }

    /**
     * Get all legal Bishop moves (checking of the king is not taken into account)
     * @param string [a-h][1-8] Location of piece
     * @param W|B color of piece, or null to use current piece to move
     * @return array
     */
    function getPossibleBishopMoves($square, $color = null)
    {
        if (is_null($color)) {
            $color = $this->_move;
        }
        $color = strtoupper($color);
        if (!in_array($color, array('W', 'B'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_COLOR,
                array('color' => $color));
        }
        if (!preg_match('/^[a-h][1-8]$/', $square)) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_SQUARE,
                array('square' => $square));
        }
        $allmoves = $this->_getDiagonals($square);
        $mypieces = $this->getPieceLocations($color);
        foreach($mypieces as $loc) {
            // go through the diagonals, and remove squares behind our own pieces
            // and also remove the piece's square
            // as bishops cannot pass through any pieces.
            if (is_array($allmoves['NW']) && in_array($loc, $allmoves['NW'])) {
                $pos = array_search($loc, $allmoves['NW']);
                $allmoves['NW'] = array_slice($allmoves['NW'], 0, $pos);
            }
            if (is_array($allmoves['NE']) && in_array($loc, $allmoves['NE'])) {
                $pos = array_search($loc, $allmoves['NE']);
                $allmoves['NE'] = array_slice($allmoves['NE'], 0, $pos);
            }
            if (is_array($allmoves['SE']) && in_array($loc, $allmoves['SE'])) {
                $pos = array_search($loc, $allmoves['SE']);
                $allmoves['SE'] = array_slice($allmoves['SE'], 0, $pos);
            }
            if (is_array($allmoves['SW']) && in_array($loc, $allmoves['SW'])) {
                $pos = array_search($loc, $allmoves['SW']);
                $allmoves['SW'] = array_slice($allmoves['SW'], 0, $pos);
            }
        }
        $enemypieces = $this->getPieceLocations($color == 'W' ? 'B' : 'W');
        foreach($enemypieces as $loc) {
            // go through the diagonals, and remove squares behind enemy pieces
            // and include the piece's square, since we can capture it
            // but bishops cannot pass through any pieces.
            if (is_array($allmoves['NW']) && in_array($loc, $allmoves['NW'])) {
                $pos = array_search($loc, $allmoves['NW']);
                $allmoves['NW'] = array_slice($allmoves['NW'], 0, $pos + 1);
            }
            if (is_array($allmoves['NE']) && in_array($loc, $allmoves['NE'])) {
                $pos = array_search($loc, $allmoves['NE']);
                $allmoves['NE'] = array_slice($allmoves['NE'], 0, $pos + 1);
            }
            if (is_array($allmoves['SE']) && in_array($loc, $allmoves['SE'])) {
                $pos = array_search($loc, $allmoves['SE']);
                $allmoves['SE'] = array_slice($allmoves['SE'], 0, $pos + 1);
            }
            if (is_array($allmoves['SW']) && in_array($loc, $allmoves['SW'])) {
                $pos = array_search($loc, $allmoves['SW']);
                $allmoves['SW'] = array_slice($allmoves['SW'], 0, $pos + 1);
            }
        }
        $newmoves = array();
        foreach($allmoves as $key => $value) {
            if (!$value) {
                continue;
            }
            $newmoves = array_merge($newmoves, $value);
        }
        return array_values(array_diff($newmoves, $mypieces));
    }

    /**
     * Get all legal Rook moves (checking of the king is not taken into account)
     * @param string [a-h][1-8] Location of piece
     * @param W|B color of piece, or null to use current piece to move
     * @return array
     */
    function getPossibleRookMoves($square, $color = null)
    {
        if (is_null($color)) {
            $color = $this->_move;
        }
        $color = strtoupper($color);
        if (!in_array($color, array('W', 'B'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_COLOR,
                array('color' => $color));
        }
        if (!preg_match('/^[a-h][1-8]$/', $square)) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_SQUARE,
                array('square' => $square));
        }
        $allmoves = $this->_getRookSquares($square);
        $mypieces = $this->getPieceLocations($color);
        foreach($mypieces as $loc) {
            // go through the rook squares, and remove squares behind our own pieces
            // and also remove the piece's square
            // as rooks cannot pass through any pieces.
            if (is_array($allmoves['N']) && in_array($loc, $allmoves['N'])) {
                $pos = array_search($loc, $allmoves['N']);
                $allmoves['N'] = array_slice($allmoves['N'], 0, $pos);
            }
            if (is_array($allmoves['E']) && in_array($loc, $allmoves['E'])) {
                $pos = array_search($loc, $allmoves['E']);
                $allmoves['E'] = array_slice($allmoves['E'], 0, $pos);
            }
            if (is_array($allmoves['S']) && in_array($loc, $allmoves['S'])) {
                $pos = array_search($loc, $allmoves['S']);
                $allmoves['S'] = array_slice($allmoves['S'], 0, $pos);
            }
            if (is_array($allmoves['W']) && in_array($loc, $allmoves['W'])) {
                $pos = array_search($loc, $allmoves['W']);
                $allmoves['W'] = array_slice($allmoves['W'], 0, $pos);
            }
        }
        $enemypieces = $this->getPieceLocations($color == 'W' ? 'B' : 'W');
        foreach($enemypieces as $loc) {
            // go through the rook squares, and remove squares behind enemy pieces
            // and include the piece's square, since we can capture it
            // but rooks cannot pass through any pieces.
            if (is_array($allmoves['N']) && in_array($loc, $allmoves['N'])) {
                $pos = array_search($loc, $allmoves['N']);
                $allmoves['N'] = array_slice($allmoves['N'], 0, $pos + 1);
            }
            if (is_array($allmoves['E']) && in_array($loc, $allmoves['E'])) {
                $pos = array_search($loc, $allmoves['E']);
                $allmoves['E'] = array_slice($allmoves['E'], 0, $pos + 1);
            }
            if (is_array($allmoves['S']) && in_array($loc, $allmoves['S'])) {
                $pos = array_search($loc, $allmoves['S']);
                $allmoves['S'] = array_slice($allmoves['S'], 0, $pos + 1);
            }
            if (is_array($allmoves['W']) && in_array($loc, $allmoves['W'])) {
                $pos = array_search($loc, $allmoves['W']);
                $allmoves['W'] = array_slice($allmoves['W'], 0, $pos + 1);
            }
        }
        $newmoves = array();
        foreach($allmoves as $key => $value) {
            if (!$value) {
                continue;
            }
            $newmoves = array_merge($newmoves, $value);
        }
        return array_values(array_diff($newmoves, $mypieces));
    }

    /**
     * Get all legal Queen moves (checking of the king is not taken into account)
     * @param string [a-h][1-8] Location of piece
     * @param W|B color of piece, or null to use current piece to move
     * @return array
     */
    function getPossibleQueenMoves($square, $color = null)
    {
        $a = $this->getPossibleRookMoves($square, $color);
        if ($this->isError($a)) {
            return $a;
        }
        $b = $this->getPossibleBishopMoves($square, $color);
        if ($this->isError($b)) {
            return $b;
        }
        return array_merge($a, $b);
    }

    /**
     * Get all legal Pawn moves (checking of the king is not taken into account)
     * @param string [a-h][1-8] Location of piece
     * @param W|B color of piece, or null to use current piece to move
     * @return array
     */
    function getPossiblePawnMoves($square, $color = null, $enpassant = null)
    {
        if (is_null($color)) {
            $color = $this->_move;
        }
        $color = strtoupper($color);
        if (!in_array($color, array('W', 'B'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_COLOR,
                array('color' => $color));
        }
        if (!preg_match('/^[a-h][1-8]$/', $square)) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_SQUARE,
                array('square' => $square));
        }
        if (is_null($enpassant)) {
            $enpassant = $this->_enPassantSquare;
        }
        $mypieces = $this->getPieceLocations($color);
        $enemypieces = $this->getPieceLocations($color == 'W' ? 'B' : 'W');
        $allmoves = array();
        if ($color == 'W') {
            $dbl = '2';
            $direction = 1;
            // en passant calculation
            if ($square{1} == '5' && in_array(ord($enpassant{0}) - ord($square{0}),
                                              array(1, -1))) {
                if (in_array(chr(ord($square{0}) - 1) . 5,
                             $enemypieces)) {
                    $allmoves[] = chr(ord($square{0}) - 1) . 6;
                }
                if (in_array(chr(ord($square{0}) + 1) . 5,
                             $enemypieces)) {
                    $allmoves[] = chr(ord($square{0}) + 1) . 6;
                }
            }
        } else {
            $dbl = '7';
            $direction = -1;
            // en passant calculation
            if ($square{1} == '4' && in_array(ord($enpassant{0}) - ord($square{0}),
                                              array(1, -1))) {
                if (in_array(chr(ord($square{0}) - 1) . 4,
                             $enemypieces)) {
                    $allmoves[] = chr(ord($square{0}) - 1) . 3;
                }
                if (in_array(chr(ord($square{0}) + 1) . 4,
                             $enemypieces)) {
                    $allmoves[] = chr(ord($square{0}) + 1) . 3;
                }
            }
        }
        if (!in_array($square{0} . ($square{1} + $direction), $mypieces) &&
            !in_array($square{0} . ($square{1} + $direction), $enemypieces))
        {
            $allmoves[] = $square{0} . ($square{1} + $direction);
        }
        if (count($allmoves) && $square{1} == $dbl) {
            if (!in_array($square{0} . ($square{1} + 2 * $direction), $mypieces) &&
                !in_array($square{0} . ($square{1} + 2 * $direction), $enemypieces))
            {
                $allmoves[] = $square{0} . ($square{1} + 2 * $direction);
            }
        }
        if (in_array(chr(ord($square{0}) - 1) . ($square{1} + $direction),
                     $enemypieces)) {
            $allmoves[] = chr(ord($square{0}) - 1) . ($square{1} + $direction);
        }
        if (in_array(chr(ord($square{0}) + 1) . ($square{1} + $direction),
                     $enemypieces)) {
            $allmoves[] = chr(ord($square{0}) + 1) . ($square{1} + $direction);
        }
        return $allmoves;
    }

    /**
     * Get all legal King moves (checking of the king is not taken into account)
     * @param string [a-h][1-8] Location of piece
     * @param W|B color of piece, or null to use current piece to move
     * @return array
     * @since 0.7alpha castling is possible by moving the king to the destination square
     */
    function getPossibleKingMoves($square, $color = null, $returnCastleMoves = true)
    {
        if (is_null($color)) {
            $color = $this->_move;
        }
        $color = strtoupper($color);
        if (!in_array($color, array('W', 'B'))) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_COLOR,
                array('color' => $color));
        }
        if (!preg_match('/^[a-h][1-8]$/', $square)) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_SQUARE,
                array('square' => $square));
        }
        $newret = $castleret = array();
        $ret = $this->_getKingSquares($square);
        if ($returnCastleMoves) {
            $castleret = $this->_getCastleSquares($square);
        }
        $mypieces = $this->getPieceLocations($color);
        foreach ($ret as $square) {
            if (!in_array($square, $mypieces)) {
                $newret[] = $square;
            }
        }
        return array_merge($newret, $castleret);
    }

    /**
     * Return the color of a square (black or white)
     * @param string [a-h][1-8]
     * @access protected
     * @return B|W
     */
    function _getDiagonalColor($square)
    {
        $map = array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5, 'f' => 6,
            'g' => 7, 'h' => 8);
        $rank = $map[$square{0}];
        $file = $square{1};
        $color = ($rank + $file) % 2;
        return $color ? 'W' : 'B';
    }

    function getDiagonalColor($square)
    {
        if (!preg_match('/^[a-h][1-8]$/', $square)) {
            return $this->raiseError(self::GAMES_CHESS_ERROR_INVALID_SQUARE,
                array('square' => $square));
        }
        return $this->_getDiagonalColor($square);
    }

    /**
     * Get all the squares between an attacker and the king where another
     * piece can interpose, or capture the checking piece
     *
     * @param string algebraic square of the checking piece
     * @param string algebraic square of the king
     */
    function _getPathToKing($checkee, $king)
    {
        if ($this->_isKnight($this->_board[$checkee])) {
            return array($checkee);
        } else {
            $path = array();
            // get all the paths
            $kingpaths = $this->_getQueenSquares($king);
            foreach ($kingpaths as $subpath) {
                if (!$subpath) {
                    continue;
                }
                if (in_array($checkee, $subpath)) {
                    foreach ($subpath as $square) {
                        $path[] = $square;
                        if ($square == $checkee) {
                            return $path;
                        }
                    }
                }
            }
        }
    }

    /**
     * @param integer error code from {@link Chess.php}
     * @param array associative array of additional error message data
     * @uses \PEAR::raiseError()
     * @return PEAR_Error
     */
    function raiseError($code, $extra = array())
    {
        //Do NOT F with this please. throwing exception here will break this whole library
        $pear = new \PEAR();
        return $pear->raiseError($this->getMessage($code, $extra), $code,
            null, null, $extra);
    }

    /**
     * Get an error message from the code
     *
     * Future versions of this method will be multi-language
     * @return string
     * @param integer Error code
     * @param array extra information to pass for error message creation
     */
    function getMessage($code, $extra)
    {
        $messages = array(
            self::GAMES_CHESS_ERROR_INVALID_SAN =>
                '"%pgn%" is not a valid algebraic move',
            self::GAMES_CHESS_ERROR_FEN_COUNT =>
                'Invalid FEN - "%fen%" has %sections% fields, 6 is required',
            self::GAMES_CHESS_ERROR_EMPTY_FEN =>
                'Invalid FEN - "%fen%" has an empty field at index %section%',
            self::GAMES_CHESS_ERROR_FEN_TOOMUCH =>
                'Invalid FEN - "%fen%" has too many pieces for a chessboard',
            self::GAMES_CHESS_ERROR_FEN_TOMOVEWRONG =>
                'Invalid FEN - "%fen%" has invalid to-move indicator, must be "w" or "b"',
            self::GAMES_CHESS_ERROR_FEN_CASTLETOOLONG =>
                'Invalid FEN - "%fen%" the castling indicator (KQkq) is too long',
            self::GAMES_CHESS_ERROR_FEN_CASTLEWRONG =>
                'Invalid FEN - "%fen%" the castling indicator "%castle%" is invalid',
            self::GAMES_CHESS_ERROR_FEN_INVALID_EP =>
                'Invalid FEN - "%fen%" the en passant square indicator "%enpassant%" is invalid',
            self::GAMES_CHESS_ERROR_FEN_INVALID_PLY =>
                'Invalid FEN - "%fen%" the half-move ply count "%ply%" is not a number',
            self::GAMES_CHESS_ERROR_FEN_INVALID_MOVENUMBER =>
                'Invalid FEN - "%fen%" the move number "%movenumber%" is not a number',
            self::GAMES_CHESS_ERROR_IN_CHECK =>
                'The king is in check and that move does not prevent check',
            self::GAMES_CHESS_ERROR_CANT_CK =>
                'Can\'t castle kingside, either the king or rook has moved',
            self::GAMES_CHESS_ERROR_CK_PIECES_IN_WAY =>
                'Can\'t castle kingside, pieces are in the way',
            self::GAMES_CHESS_ERROR_CANT_CQ =>
                'Can\'t castle queenside, either the king or rook has moved',
            self::GAMES_CHESS_ERROR_CQ_PIECES_IN_WAY =>
                'Can\'t castle queenside, pieces are in the way',
            self::GAMES_CHESS_ERROR_CASTLE_WOULD_CHECK =>
                'Can\'t castle, it would put the king in check',
            self::GAMES_CHESS_ERROR_MOVE_WOULD_CHECK =>
                'That move would put the king in check',
            self::GAMES_CHESS_ERROR_STILL_IN_CHECK =>
                'The move does not remove the check on the king',
            self::GAMES_CHESS_ERROR_CANT_CAPTURE_OWN =>
                'Cannot capture your own pieces',
            self::GAMES_CHESS_ERROR_NO_PIECE =>
                'There is no piece on square %square%',
            self::GAMES_CHESS_ERROR_WRONG_COLOR =>
                'The piece on %square% is not your piece',
            self::GAMES_CHESS_ERROR_CANT_MOVE_THAT_WAY =>
                'The piece on %from% cannot move to %to%',
            self::GAMES_CHESS_ERROR_MULTIPIECE =>
                'Too many %color% %piece%s',
            self::GAMES_CHESS_ERROR_FEN_MULTIPIECE =>
                'Invalid FEN - "%fen%" Too many %color% %piece%s',
            self::GAMES_CHESS_ERROR_DUPESQUARE =>
                '%dpiece% already occupies square %square%, cannot be replaced by %piece%',
            self::GAMES_CHESS_ERROR_FEN_INVALIDPIECE =>
                'Invalid FEN - "%fen%" the character "%fenchar%" is not a valid piece, separator or number',
            self::GAMES_CHESS_ERROR_FEN_TOOLITTLE =>
                'Invalid FEN - "%fen%" has too few pieces for a chessboard',
            self::GAMES_CHESS_ERROR_INVALID_COLOR =>
                '"%color%" is not a valid piece color, try W or B',
            self::GAMES_CHESS_ERROR_INVALID_SQUARE =>
                '"%square%" is not a valid square, must be between a1 and h8',
            self::GAMES_CHESS_ERROR_INVALID_PIECE =>
                '"%piece%" is not a valid piece, must be P, Q, R, N, K or B',
            self::GAMES_CHESS_ERROR_INVALID_PROMOTE =>
                '"%piece%" is not a valid promotion piece, must be Q, R, N or B',
            self::GAMES_CHESS_ERROR_TOO_AMBIGUOUS =>
                '"%san%" does not resolve ambiguity between %piece%s on %squares%',
            self::GAMES_CHESS_ERROR_NOPIECE_CANDOTHAT =>
                'There are no %color% pieces on the board that can do "%san%"',
            self::GAMES_CHESS_ERROR_MOVE_MUST_CAPTURE =>
                'Capture is possible, "%san%" does not capture',
            self::GAMES_CHESS_ERROR_NOPIECES_TOPLACE =>
                'There are no captured %color% %piece%s available to place',
            self::GAMES_CHESS_ERROR_PIECEINTHEWAY =>
                'There is already a piece on %square%, cannot place another there',
            self::GAMES_CHESS_ERROR_CANT_PLACE_18 =>
                'Placing a piece on the first or back rank is illegal (%san%)',
        );
        $message = $messages[$code];
        foreach ($extra as $key => $value) {
            if (strpos($key, 'piece') !== false) {
                switch(strtoupper($value)) {
                    case 'R' :
                        $value = 'Rook';
                    break;
                    case 'Q' :
                        $value = 'Queen';
                    break;
                    case 'P' :
                        $value = 'Pawn';
                    break;
                    case 'B' :
                        $value = 'Bishop';
                    break;
                    case 'K' :
                        $value = 'King';
                    break;
                    case 'N' :
                        $value = 'Knight';
                    break;
                }
            }
            if ($key == 'color') {
                switch($value) {
                    case 'W' :
                        $value = 'White';
                    break;
                    case 'B' :
                        $value = 'Black';
                    break;
                }
            }
            if(is_string($message) && is_string($key) && is_string($value)) {
                $message = @str_replace('%'.$key.'%', $value, $message);
            }
        }
        if(is_string($message)) {
            return $message;
        } else {
            return "Error moving pieces";
        }
    }

    /**
     * Determines whether the data returned from a method is a PEAR-related
     * error class
     * @param mixed
     * @return boolean
     */
    function isError($err)
    {
        return is_a($err, 'PEAR_Error');
    }

    /**
     * Begin a chess piece transaction
     *
     * Transactions are used to attempt moves that may be revoked later, especially
     * in methods like {@link inCheckMate()}
     */
    function startTransaction()
    {
        $state = get_object_vars($this);
        unset($state['_saveState']);
        if (!is_array($this->_saveState)) {
            $this->_saveState = array();
        }
        array_push($this->_saveState, $state);
    }

    /**
     * Set the state of the chess game
     *
     * WARNING: this resets the state without any validation.
     * @param array
     */
    function setState($state)
    {
        foreach($state as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * Get the current state of the chess game
     *
     * Use this in conjunction with setState
     * @param array
     */
    function getState()
    {
        return get_object_vars($this);
    }

    /**
     * Remove any possibility of undo.
     */
    function commitTransaction()
    {
        array_pop($this->_saveState);
    }

    /**
     * Undo any changes to state since {@link startTransaction()} was first used
     */
    function rollbackTransaction()
    {
        $vars = array_pop($this->_saveState);
        foreach($vars as $name => $value) {
            $this->$name = $value;
        }
    }

    /**
     * Determine whether a piece name is a bishop
     *
     * This does NOT take an algebraic square as the argument, but the contents
     * of _board[algebraic square]
     * @param string
     * @return boolean
     * @access protected
     */
    function isBishop($piecename)
    {
        return $piecename{1} == 'B' ||
            ($piecename{1} == 'P' &&
                $this->_pieces[$piecename][1] == 'B');
    }

    /**
     * Determine whether a piece name is a rook
     *
     * This does NOT take an algebraic square as the argument, but the contents
     * of _board[algebraic square]
     * @param string
     * @return boolean
     * @access protected
     */
    function isRook($piecename)
    {
        return $piecename{1} == 'R' ||
            ($piecename{1} == 'P' &&
                $this->_pieces[$piecename][1] == 'R');
    }

    /**
     * Determine whether a piece name is a pawn
     *
     * This does NOT take an algebraic square as the argument, but the contents
     * of _board[algebraic square]
     * @param string
     * @return boolean
     * @access protected
     */
    function isPawn($piecename)
    {
        return $piecename{1} == 'P' &&
                $this->_pieces[$piecename][1] == 'P';
    }

    /**
     * Determine whether a piece name is a king
     *
     * This does NOT take an algebraic square as the argument, but the contents
     * of _board[algebraic square]
     * @param string
     * @return boolean
     * @access protected
     */
    function isKing($piecename)
    {
        return $piecename{1} == 'K';
    }


    function _isKnight($piecename)
    {
        return $piecename{1} == 'N' ||
            ($piecename{1} == 'P' &&
                $this->_pieces[$piecename][1] == 'N');
    }

    function _interposeOrCapture($squares, $color)
    {
      if($this->_enPassantSquare != '-' && !in_array($this->_enPassantSquare,$squares)) {
        $squares[] = $this->_enPassantSquare;
      }

        foreach ($this->_pieces as $name => $value) {
            if (!$value) {
                continue;
            }
            if ($name{0} != $color) {
                continue;
            }
            if ($name{1} == 'K') {
                continue;
            }
            if (is_array($value)) {
                $name = $value[1];
                $value = $value[0];
            } else {
                $name = $name{1};
            }
            $allmoves = $this->getPossibleMoves($name, $value, $color);

            foreach($squares as $square) {
                if (in_array($square, $allmoves)) {
                    // try the move, see if we're still in check
                    // if so, then the piece is pinned and cannot move
                    $this->startTransaction();
                    $this->_move = $color;

                    try {
                        $ret = $this->moveSquare($value, $square);
                    } catch (\Exception $e) {
                        //do nothing
                    }

                    $this->_move = $color;
                    $stillchecked = $this->inCheck($color);
                    $this->rollbackTransaction();
                    if (!$stillchecked) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Retrieve the color of a piece from its name
     *
     * Game-specific method of retrieving the color of a piece
     * @access protected
     */
    function _getColor($name)
    {
        return $name{0};
    }

    /**
     * Get a list of all pieces on the board organized by the type of piece,
     * and the color of the square the piece is on.
     *
     * Used to determine basic draw conditions
     * @return array Format:
     *
     * <pre>
     * array(
     *   // white pieces
     *   'W' => array('B' => array('W', 'B'), // all bishops
     *                'K' => array('W'),...
     *               ),
     *   // black pieces
     *   'B' => array('Q' => array('B'), // all queens
     *                'K' => array('W'),... // king is on white square
     * </pre>
     * @access protected
     */
    function _getPieceTypes()
    {
        $ret = array('W' => array(), 'B' => array());
        foreach($this->_pieces as $name => $loc) {
            if (!$loc) {
                continue;
            }
            $type = $name{1};
            if (is_array($loc)) {
                $type = $loc[1];
                $loc = $loc[0];
            }
            $ret[$name{0}][$type][] = $this->_getDiagonalColor($loc);
        }
        return $ret;
    }
}
