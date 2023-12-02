<?php
/**
 * Day 2: Rock Paper Scissors
 *
 * Rules: Winning earns 6 points, a tie earns 3 points, and losing earns 0 points. Bonus points are also awarded based
 * on the shape that was played. Rock earns 1 point, paper earns 2 points, and scissors earns 3 points.
 */

/**
 * Part 1: Calculate points for each player.
 *
 * @return void
 */
function part_1() {
	$strategies = get_strategies();
	$score = 0;
	$choices = array(
		'opponent' => array(
			'A' => 'Rock',
			'B' => 'Paper',
			'C' => 'Scissors',
		),
		'player' => array(
			'X' => 'Rock',
			'Y' => 'Paper',
			'Z' => 'Scissors',
		),
	);

	foreach ( $strategies as $strategy ) {
		$move = explode( ' ', $strategy );
		$opponent = $move[0];
		$player = $move[1];
		$opponentChoice = $choices['opponent'][ $opponent ];
		$playerChoice = $choices['player'][ $player ];

		$score += get_score( $opponentChoice, $playerChoice );
	}

	echo $score . PHP_EOL;
}

/**
 * Part 2: Determine if you should win, lose, or tie the game.
 *
 * @return void
 */
function part_2() {
	$strategies = get_strategies();
	$score      = 0;
	$choices    = array(
		'opponent' => array(
			'A' => 'Rock',
			'B' => 'Paper',
			'C' => 'Scissors',
		),
		'player'   => array(
			'X' => 'Lose',
			'Y' => 'Draw',
			'Z' => 'Win',
		),
	);

	foreach ( $strategies as $strategy ) {
		$move           = explode( ' ', $strategy );
		$opponent       = $move[0];
		$player         = $move[1];
		$opponentChoice = $choices['opponent'][ $opponent ];
		$playerChoice   = $choices['player'][ $player ];

		// Determine which move the player should make.
		if ( 'Lose' === $playerChoice ) {
			$playerChoice = get_move( $opponentChoice, true );
		} elseif ( 'Draw' === $playerChoice ) {
			$playerChoice = $opponentChoice;
		} elseif ( 'Win' === $playerChoice ) {
			$playerChoice = get_move( $opponentChoice );
		}

		$score += get_score( $opponentChoice, $playerChoice );
	}

	echo $score . PHP_EOL;
}

/**
 * Returns an array of strategies.
 *
 * @return array
 */
function get_strategies() {
	$strategy = trim( file_get_contents( __DIR__ . '/data/day-02.txt' ) );

	return explode( "\n", $strategy );
}

/**
 * Returns the score for a given move.
 *
 * @param string $opponentChoice The opponent's move.
 * @param string $playerChoice The player's move.
 *
 * @return int
 */
function get_score( $opponentChoice, $playerChoice ) {
	$score = 0;

	$score_map = array(
		'Rock' => array(
			'Rock' => 4,
			'Paper' => 8,
			'Scissors' => 3,
		),
		'Paper' => array(
			'Rock' => 1,
			'Paper' => 5,
			'Scissors' => 9,
		),
		'Scissors' => array(
			'Rock' => 7,
			'Paper' => 2,
			'Scissors' => 6,
		),
	);

	$score += $score_map[ $opponentChoice ][ $playerChoice ];

	return $score;
}

/**
 * Returns the move that beats the given move. If $lose is true, returns the move that loses to the given move.
 *
 * @param $opponentChoice string The opponent's move.
 * @param $lose bool Whether to return the move that loses to the given move.
 *
 * @return int|string
 */
function get_move( $opponentChoice, $lose = false ) {
	$hands = array(
		"Rock" => "Paper",
		"Paper" => "Scissors",
		"Scissors" => "Rock"
	);

	if ( $lose ) {
		$hands = array_flip( $hands );
	}

	return $hands[ $opponentChoice ];
}
