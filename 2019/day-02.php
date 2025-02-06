<?php

namespace AdventOfCode\Year2019;

/**
 * Day 02: 1202 Program Alarm
 */
class Day02 {
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
	 * Part 1: Run the Intcode program and return the value at position 0 after execution.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$memory = $this->data;

		// Restore "1202 program alarm" state
		$memory[1] = 12;
		$memory[2] = 2;

		// Execute program
		$position = 0;
		while ( $memory[ $position ] !== 99 ) {
			$opcode = $memory[ $position ];

			$param1     = $memory[ $memory[ $position + 1 ] ] ?? 0;
			$param2     = $memory[ $memory[ $position + 2 ] ] ?? 0;
			$output_pos = $memory[ $position + 3 ] ?? 0;

			if ( $opcode === 1 ) {
				$memory[ $output_pos ] = $param1 + $param2;
			} elseif ( $opcode === 2 ) {
				$memory[ $output_pos ] = $param1 * $param2;
			}

			// Move to the next instruction
			$position += 4;
		}

		// Return the value at position 0
		return $memory[0];
	}

	/**
	 * Part 2: Find the noun and verb that cause the program to produce the output 19690720.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$target   = $this->is_test ? 2650 : 19690720;
		$original = $this->data;

		// Try every combination of noun and verb (each from 0 to 99).
		for ( $noun = 0; $noun <= 99; $noun ++ ) {
			for ( $verb = 0; $verb <= 99; $verb ++ ) {
				// Start with a fresh copy of the original memory for each attempt.
				$memory    = $original;
				$memory[1] = $noun;
				$memory[2] = $verb;

				$position = 0;
				while ( $memory[ $position ] !== 99 ) {
					$opcode = $memory[ $position ];

					$param1     = $memory[ $memory[ $position + 1 ] ] ?? 0;
					$param2     = $memory[ $memory[ $position + 2 ] ] ?? 0;
					$output_pos = $memory[ $position + 3 ] ?? 0;

					if ( $opcode === 1 ) {
						$memory[ $output_pos ] = $param1 + $param2;
					} elseif ( $opcode === 2 ) {
						$memory[ $output_pos ] = $param1 * $param2;
					}

					$position += 4;
				}

				// Check if the result matches the target output.
				if ( $memory[0] === $target ) {
					return 100 * $noun + $verb;
				}
			}
		}

		// No valid noun and verb found to produce the target output
		return 0;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-02-test.txt' : '/data/day-02.txt';

		return array_map( 'intval', explode( ",", trim( file_get_contents( __DIR__ . $file ) ) ) );
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
	$day02  = new Day02( $test, $part );
	$result = $day02->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 100,
			'real' => 4484226,
		],
		2 => [
			'test' => 253,
			'real' => 5696,
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
