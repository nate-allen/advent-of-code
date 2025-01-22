<?php

namespace AdventOfCode\Year2020;

/**
 * Day 13: Shuttle Search
 */
class Day13 {
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
	 * Part 1: Find the ID of the earliest bus you can take to the airport multiplied by the number of minutes you'll
	 *         need to wait for that bus.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$earliest = PHP_INT_MAX;
		$bus_id   = 0;

		foreach ( $this->data['buses'] as $bus ) {
			// Calculate the wait time for the current bus
			$wait = $bus - ( $this->data['timestamp'] % $bus );

			// If the current wait time is less than the earliest recorded wait time, update the earliest time and bus ID.
			if ( $wait < $earliest ) {
				$earliest = $wait;
				$bus_id   = $bus;
			}
		}

		return $earliest * $bus_id;
	}

	/**
	 * Part 2: Find the earliest timestamp such that all of the listed bus IDs depart at offsets matching their
	 * 	       positions in the list.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$timestamp = 0;
		$step      = 1;

		foreach ( $this->data['buses'] as $offset => $bus ) {
			// Loop until the bus departs at the correct offset
			while ( ( $timestamp + $offset ) % $bus !== 0 ) {
				// Increment the timestamp by the current step
				$timestamp += $step;
			}

			// Update the step to be the product of the current step and the bus ID
			// This ensures that the next bus will also depart at the correct offset
			$step *= $bus;
		}

		return $timestamp;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-13-test.txt' : '/data/day-13.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$buses = array_filter(
			array_map(
				fn( $bus ) => $bus === 'x' ? null : (int) $bus,
				explode( ',', $lines[1] )
			),
			fn( $bus ) => $bus !== null
		);

		return [
			'timestamp' => (int) $lines[0],
			'buses'     => $buses,
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
	$day13  = new Day13( $test, $part );
	$result = $day13->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 295,
			'real' => 2092,
		],
		2 => [
			'test' => 1068781,
			'real' => 702970661767766,
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
