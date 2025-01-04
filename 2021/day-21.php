<?php

namespace AdventOfCode\Year2021;

/**
 * Day 21: Dirac Dice
 */
class Day21 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var integer
	 */
	private int $part;

	/**
	 * Whether to use the test data.
	 *
	 * @var bool
	 */
	private bool $is_test;

	/**
	 * The players with their starting position, current position and score.
	 *
	 * @var array
	 */
	private array $players;

	/**
	 * Distribution of 3-dice sums (3 to 9) and how many ways they can be rolled.
	 *
	 * @var array
	 */
	private array $roll_distribution = [
		3 => 1,
		4 => 3,
		5 => 6,
		6 => 7,
		7 => 6,
		8 => 3,
		9 => 1,
	];

	/**
	 * A cache to store previously computed results of a given state.
	 *
	 * Format: [ $stateKey ] = [ p1Wins, p2Wins ];
	 *
	 * @var array
	 */
	private array $cache = [];

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->players = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return integer
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Play a deterministic game using a 100-sided die. Return the product of the losing player's score and the
	 *         total number of die rolls.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$player = $this->players;

		$dice   = 0;
		$turn   = 1;
		$rolled = 0;

		while ( true ) {
			// Roll the dice three times
			$roll = [];
			for ( $i = 1; $i <= 3; $i++ ) {
				$roll[$i] = ++$dice;
				$rolled++;
				// Reset dice to 0 after hitting 100, so next ++ makes it 1
				if ( $dice === 100 ) {
					$dice = 0;
				}
			}

			// Sum the dice rolls
			$rolls = array_sum( $roll );

			// Move current player
			$space = ( $player[$turn]['pos'] + $rolls ) % 10;
			if ( $space === 0 ) {
				$space = 10;
			}

			$player[$turn]['pos']   = $space;
			$player[$turn]['score'] += $space;

			if ( $player[$turn]['score'] >= 1000 ) {
				break;
			}

			$turn = ( $turn === 1 ) ? 2 : 1;
		}

		$loser = ( $turn === 1 ) ? 2 : 1;

		return $player[$loser]['score'] * $rolled;
	}

	/**
	 * Part 2: Play a quantum game of Dirac Dice with a three-sided die. Return the number of universes in which the
	 * 	       player who wins more often does so.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		[$player_1, $player_2] = $this->quantum_play( $this->players[1]['start'], 0, $this->players[2]['start'], 0, 1 );

		return max( $player_1, $player_2 );
	}

	/**
	 * Recursive function to explore all quantum dice outcomes, using cache to avoid duplicate calculations.
	 *
	 * Returns an array of the number of player 1 wins and player 2 wins.
	 *
	 * @param integer $p1_position Player 1's current position.
	 * @param integer $p1_score    Player 1's current score.
	 * @param integer $p2_position Player 2's current position.
	 * @param integer $p2_score    Player 2's current score.
	 * @param integer $turn        Whose turn it is (1 or 2).
	 *
	 * @return array
	 */
	private function quantum_play( int $p1_position, int $p1_score, int $p2_position, int $p2_score, int $turn ): array {
		// Check if either player has won
		if ( $p1_score >= 21 ) {
			return [ 1, 0 ]; // p1 wins, p2 wins
		}
		if ( $p2_score >= 21 ) {
			return [ 0, 1 ]; // p1 wins, p2 wins
		}

		// Key for the current state.
		// Format: p1Pos,p1Score,p2Pos,p2Score,turn
		$state_key = sprintf( '%d,%d,%d,%d,%d', $p1_position, $p1_score, $p2_position, $p2_score, $turn );

		// If we've seen this state before, return from cache.
		if ( isset( $this->cache[ $state_key ] ) ) {
			return $this->cache[ $state_key ];
		}

		$total_p1_wins = 0;
		$total_p2_wins = 0;

		// Explore each possible roll sum with its frequency.
		foreach ( $this->roll_distribution as $roll_sum => $frequency ) {

			if ( $turn === 1 ) {
				// Compute new position
				$new_p1_pos = ( $p1_position + $roll_sum ) % 10;
				$new_p1_pos = ( $new_p1_pos === 0 ) ? 10 : $new_p1_pos;

				// Compute new score
				$new_p1_score = $p1_score + $new_p1_pos;

				// Recursively get the results for the next turn (Player 2).
				[ $sub_p1_wins, $sub_p2_wins ] = $this->quantum_play( $new_p1_pos, $new_p1_score, $p2_position, $p2_score, 2 );
			} else {
				// It's Player 2's turn
				$new_p2_pos = ( $p2_position + $roll_sum ) % 10;
				$new_p2_pos = ( $new_p2_pos === 0 ) ? 10 : $new_p2_pos;

				$new_p2_score = $p2_score + $new_p2_pos;

				[ $sub_p1_wins, $sub_p2_wins ] = $this->quantum_play( $p1_position, $p1_score, $new_p2_pos, $new_p2_score, 1 );
			}

			$total_p1_wins += $sub_p1_wins * $frequency;
			$total_p2_wins += $sub_p2_wins * $frequency;
		}

		$this->cache[ $state_key ] = [ $total_p1_wins, $total_p2_wins ];

		return $this->cache[ $state_key ];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file     = $test ? '/data/day-21-test.txt' : '/data/day-21.txt';
		$lines    = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$positions = [];

		foreach ( $lines as $line ) {
			if ( preg_match( '/(\d+)$/', $line, $matches ) ) {
				$positions[] = (int) $matches[1];
			}
		}

		return [
			1 => [
				'start' => $positions[0],
				'pos'   => $positions[0],
				'score' => 0,
			],
			2 => [
				'start' => $positions[1],
				'pos'   => $positions[1],
				'score' => 0,
			],
		];
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool    $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day21  = new Day21( $test, $part );
	$result = $day21->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 739785,
			'real' => 742257,
		],
		2 => [
			'test' => 444356092776315,
			'real' => 93726416205179,
		],
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values[ $part ]['test'] : $expected_values[ $part ]['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$part = (int) trim( readline( 'Which part do you want to run? (1/2): ' ) );
	if ( ! in_array( $part, [ 1, 2 ], true ) ) {
		echo 'Invalid part. Please enter 1 or 2.' . PHP_EOL;
		continue;
	}

	while ( true ) {
		$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
		if ( in_array( $test, [ 'y', 'n' ], true ) ) {
			$test_mode = $test === 'y';
			run_part( $part, $test_mode );
			break 2;
		}
		echo 'Invalid input. Please enter y or n.' . PHP_EOL;
	}
}
