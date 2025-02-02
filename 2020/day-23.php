<?php

namespace AdventOfCode\Year2020;

/**
 * Day 23: Crab Cups
 */
class Day23 {
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
	 * Part 1: Play the game for 100 moves and return the labels on the cups after cup 1.
	 *
	 * @return integer
	 */
	/**
	 * Part 1: Play the game for 100 moves and return the labels on the cups after cup 1.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$cups    = $this->data;
		$min_cup = min( $cups );
		$max_cup = max( $cups );

		// Start with the cup at index 0.
		$current_index = 0;

		for ( $move = 1; $move <= 100; $move ++ ) {
			$current_cup = $cups[ $current_index ];

			// Pick up three cups immediately clockwise
			if ( count( $cups ) - ( $current_index + 1 ) >= 3 ) {
				// The next three cups lie in one continuous block.
				$picked = array_splice( $cups, $current_index + 1, 3 );
			} else {
				// We need to pick up some cups from the end and some from the beginning.
				$picked     = array_splice( $cups, $current_index + 1 );
				$num_needed = 3 - count( $picked );
				$picked     = array_merge( $picked, array_splice( $cups, 0, $num_needed ) );
			}

			// Select destination cup
			$destination = $current_cup - 1;
			while ( ! in_array( $destination, $cups, true ) ) {
				$destination --;
				if ( $destination < $min_cup ) {
					$destination = $max_cup;
				}
			}

			// Place the picked up cups immediately after the destination cup
			$dest_index = array_search( $destination, $cups );
			array_splice( $cups, $dest_index + 1, 0, $picked );

			// Select new current cup
			$current_index = array_search( $current_cup, $cups );
			$current_index = ( $current_index + 1 ) % count( $cups );
		}

		// Build the final answer
		$index_1    = array_search( 1, $cups );
		$result     = '';
		$cups_count = count( $cups );

		for ( $i = 1; $i < $cups_count; $i ++ ) {
			$result .= $cups[ ( $index_1 + $i ) % $cups_count ];
		}

		return (int) $result;
	}

	/**
	 * Part 2: Simulate the game with one million cups and ten million moves. Multiply the labels on the two cups
	 *         clockwise of cup 1.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$initial_cups = $this->data;

		$total_cups  = 1_000_000;
		$total_moves = 10_000_000;

		// next[$cup] will hold the label of the cup clockwise of $cup.
		$next = [];
		$prev = null;

		// For the initial cups from the input.
		foreach ( $initial_cups as $cup ) {
			if ( $prev !== null ) {
				$next[ $prev ] = $cup;
			}
			$prev = $cup;
		}

		// Fill in the rest of the cups up to $total_cups.
		for ( $cup = max( $initial_cups ) + 1; $cup <= $total_cups; $cup ++ ) {
			$next[ $prev ] = $cup;
			$prev          = $cup;
		}

		// Complete the circle by linking the last cup to the first cup.
		$next[ $prev ] = $initial_cups[0];

		// Set the current cup to the first cup in the input.
		$current_cup = $initial_cups[0];

		// Pre-calculate the maximum label.
		$max_label = $total_cups;

		// Perform the moves.
		for ( $move = 1; $move <= $total_moves; $move ++ ) {
			// Pick up the three cups clockwise of the current cup.
			$pick1 = $next[ $current_cup ];
			$pick2 = $next[ $pick1 ];
			$pick3 = $next[ $pick2 ];

			// Remove the three picked cups from the circle.
			$next[ $current_cup ] = $next[ $pick3 ];

			// Select destination cup.
			$destination = $current_cup - 1;
			if ( $destination < 1 ) {
				$destination = $max_label;
			}

			// If the destination is in the picked cups, decrement.
			while ( $destination === $pick1 || $destination === $pick2 || $destination === $pick3 ) {
				$destination --;
				if ( $destination < 1 ) {
					$destination = $max_label;
				}
			}

			// Insert the picked cups immediately clockwise of the destination cup.
			$temp                 = $next[ $destination ];
			$next[ $destination ] = $pick1;
			$next[ $pick3 ]       = $temp;

			// Select the new current cup.
			$current_cup = $next[ $current_cup ];
		}

		// After all moves, the two cups immediately clockwise of cup 1.
		$cup1 = $next[1];
		$cup2 = $next[ $cup1 ];

		// Multiply their labels together.
		return $cup1 * $cup2;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-23-test.txt' : '/data/day-23.txt';

		return array_map( 'intval', str_split( trim( file_get_contents( __DIR__ . $file ) ) ) );
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
	$day23  = new Day23( $test, $part );
	$result = $day23->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 67384529,
			'real' => 95648732,
		],
		2 => [
			'test' => 149245887792,
			'real' => 192515314252,
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
