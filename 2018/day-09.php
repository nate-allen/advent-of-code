<?php

namespace AdventOfCode\Year2018;

// Increase memory limit for part 2 with 7+ million marbles
ini_set( 'memory_limit', '512M' );

/**
 * Day 09: Marble Mania
 */
class Day09 {
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
	 * Part 1: Find the winning Elf's score after all marbles are placed.
	 *
	 * Simulates the circular marble game where players take turns placing marbles.
	 * Special scoring occurs when a marble is a multiple of 23.
	 *
	 * @return integer The highest score among all players.
	 */
	private function solve_part_1(): int {
		$players     = $this->data['players'];
		$last_marble = $this->data['last_marble'];

		return $this->play_game( $players, $last_marble );
	}

	/**
	 * Part 2: Find the winning Elf's score with 100x more marbles.
	 *
	 * Uses the same algorithm as part 1, but with the last marble value multiplied by 100.
	 * Requires efficient O(1) data structure to handle the large number of marbles.
	 *
	 * @return integer The highest score among all players.
	 */
	private function solve_part_2(): int {
		$players     = $this->data['players'];
		$last_marble = $this->data['last_marble'] * 100;

		return $this->play_game( $players, $last_marble );
	}

	/**
	 * Plays the marble game
	 *
	 * @param int $players     Number of players.
	 * @param int $last_marble Last marble value to place.
	 *
	 * @return int The highest score among all players.
	 */
	private function play_game( int $players, int $last_marble ): int {
		// Initialize: create list with single marble 0
		$circle = new \SplDoublyLinkedList();
		$circle->push( 0 );
		$scores = array_fill( 0, $players, 0 );

		// Process each marble from 1 to last_marble
		for ( $marble = 1; $marble <= $last_marble; $marble++ ) {
			$player = ($marble - 1) % $players;

			if ( $marble % 23 === 0 ) {
				// Special case: player scores this marble
				$scores[ $player ] += $marble;

				// Rotate 7 positions counter-clockwise
				for ( $i = 0; $i < 7; $i++ ) {
					$circle->unshift( $circle->pop() );
				}

				// Remove and score the marble at the end
				$removed_value      = $circle->pop();
				$scores[ $player ] += $removed_value;

				// Rotate -1 (counter-clockwise: move 1 from front to back)
				$circle->push( $circle->shift() );
			} else {
				// Normal case: place marble between 1 and 2 positions clockwise from current
				// Rotate -1 (counter-clockwise: move 1 from front to back)
				$circle->push( $circle->shift() );

				// Append new marble to the end
				$circle->push( $marble );
			}
		}

		return max( $scores );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * Extracts the number of players and the last marble value from the input line.
	 * Format: "X players; last marble is worth Y points"
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array with 'players' and 'last_marble' keys.
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-09-test.txt' : '/data/day-09.txt';
		$line = trim( file_get_contents( __DIR__ . $file ) );

		// Parse: "X players; last marble is worth Y points"
		if ( preg_match( '/(\d+) players; last marble is worth (\d+) points/', $line, $matches ) ) {
			return [
				'players'    => (int) $matches[1],
				'last_marble' => (int) $matches[2],
			];
		}

		throw new \RuntimeException( 'Failed to parse input data.' );
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
	$day09  = new Day09( $test, $part );
	$result = $day09->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 37305,
			'real' => 424639,
		],
		2 => [
			'test' => 320997431,
			'real' => 3516007333,
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
