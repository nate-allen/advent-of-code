<?php

namespace AdventOfCode\Year2020;

/**
 * Day 22: Crab Combat
 */
class Day22 {
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
	 * Parsed data from the input file.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
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
	 * Part 1: Play a game of Combat and calculate the winning player's score.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$players = $this->data;

		while ( ! empty( $players[0] ) && ! empty( $players[1] ) ) {
			// Draw the top cards
			$card_1 = array_shift( $players[0] );
			$card_2 = array_shift( $players[1] );

			// Determine round winner
			if ( $card_1 > $card_2 ) {
				$players[0][] = $card_1;
				$players[0][] = $card_2;
			} else {
				$players[1][] = $card_2;
				$players[1][] = $card_1;
			}
		}

		// Determine overall winner
		$winner = ! empty( $players[0] ) ? 0 : 1;

		// Calculate final score
		$score = 0;
		$deck  = array_reverse( $players[ $winner ] ); // Reverse the deck

		foreach ( $deck as $index => $card ) {
			$score += ( $card * ( $index + 1 ) );
		}

		return $score;
	}

	/**
	 * Part 2: Play a recursive game of Combat and calculate the winning player's score.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		[ , $winning_deck ] = $this->play_recursive_combat( $this->data );

		$score = 0;
		$deck  = array_reverse( $winning_deck );

		foreach ( $deck as $index => $card ) {
			$score += ( $card * ( $index + 1 ) );
		}

		return $score;
	}

	/**
	 * Plays a recursive game of Combat and returns the winner and their deck.
	 *
	 * @param array $players The current decks of both players.
	 *
	 * @return array
	 */
	private function play_recursive_combat( array $players ): array {
		$previous_rounds = [];

		while ( ! empty( $players[0] ) && ! empty( $players[1] ) ) {
			// Check for a previous round match to prevent infinite recursion
			$round_key = implode( ',', $players[0] ) . '|' . implode( ',', $players[1] );

			if ( isset( $previous_rounds[ $round_key ] ) ) {
				return [ 0, $players[0] ];
			}

			$previous_rounds[ $round_key ] = true;

			$card_1 = array_shift( $players[0] );
			$card_2 = array_shift( $players[1] );

			if ( count( $players[0] ) >= $card_1 && count( $players[1] ) >= $card_2 ) {
				// Start a sub-game
				[ $round_winner, ] = $this->play_recursive_combat( [
					array_slice( $players[0], 0, $card_1 ),
					array_slice( $players[1], 0, $card_2 )
				] );
			} else {
				// Standard combat rule applies
				$round_winner = $card_1 > $card_2 ? 0 : 1;
			}

			// Winner gets both cards (their card first)
			if ( $round_winner === 0 ) {
				$players[0][] = $card_1;
				$players[0][] = $card_2;
			} else {
				$players[1][] = $card_2;
				$players[1][] = $card_1;
			}
		}

		return ! empty( $players[0] ) ? [ 0, $players[0] ] : [ 1, $players[1] ];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file    = $test ? '/data/day-22-test.txt' : '/data/day-22.txt';
		$players = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$cards = [];

		foreach ( $players as $player ) {
			$lines = explode( "\n", trim( $player ) );
			array_shift( $lines ); // Remove the first line (player number)
			$cards[] = array_map( 'intval', $lines );
		}

		return $cards;
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day22  = new Day22( $test, $part );
	$result = $day22->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 306,
			'real' => 33098,
		],
		2 => [
			'test' => 291,
			'real' => 35055,
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
