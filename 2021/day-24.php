<?php

namespace AdventOfCode\Year2021;

/**
 * Day 24: Arithmetic Logic Unit
 */
class Day24 {
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
	 * Part 1: Find the largest valid submarine model number.
	 *
	 * The submarine's MONAD (Model Number Automatic Detector) validates 14-digit numbers using a complex ALU program.
	 * This part identifies the largest valid model number such that the program leaves the value 0 in the z variable.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		[ $max_model_number, ] = $this->find_model_numbers();

		return (int) implode( '', $max_model_number );
	}

	/**
	 * Part 2: Find the smallest valid submarine model number.
	 *
	 * Similar to part 1, this part identifies the smallest valid model number such that the ALU program
	 * leaves the value 0 in the z variable, adhering to the same MONAD validation rules.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		[ , $min_model_number ] = $this->find_model_numbers();

		return (int) implode( '', $min_model_number );
	}

	/**
	 * Determine the largest and smallest valid model numbers.
	 *
	 * @return array An array containing the max and min model numbers.
	 */
	private function find_model_numbers(): array {
		$instructions = $this->data;
		$stack        = [];
		$relations    = [];

		// Loop through the 14 blocks of instructions
		for ( $i = 0; $i < 14; $i ++ ) {
			$base_index = $i * 18;
			$divisor    = (int) explode( ' ', $instructions[ $base_index + 4 ] )[2];
			$offset_x   = (int) explode( ' ', $instructions[ $base_index + 5 ] )[2];
			$offset_y   = (int) explode( ' ', $instructions[ $base_index + 15 ] )[2];

			if ( $divisor === 1 ) {
				// Push to stack when divisor is 1
				$stack[] = [ $i, $offset_y ];
			} else {
				// Pop from stack and determine relationships
				[ $prev_index, $prev_offset ] = array_pop( $stack );
				$relations[] = [ $i, $prev_index, $prev_offset + $offset_x ];
			}
		}

		$max_model_number = $min_model_number = array_fill( 0, 14, 0 );

		// Calculate max and min model numbers based on relationships
		foreach ( $relations as [$current, $previous, $delta] ) {
			if ( $delta > 0 ) {
				$max_model_number[ $current ]  = 9;
				$max_model_number[ $previous ] = 9 - $delta;
				$min_model_number[ $current ]  = 1 + $delta;
				$min_model_number[ $previous ] = 1;
			} else {
				$max_model_number[ $current ]  = 9 + $delta;
				$max_model_number[ $previous ] = 9;
				$min_model_number[ $current ]  = 1;
				$min_model_number[ $previous ] = 1 - $delta;
			}
		}

		return [ $max_model_number, $min_model_number ];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-24-test.txt' : '/data/day-24.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
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
	$day24  = new Day24( $test, $part );
	$result = $day24->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 29599469991739,
			'real' => 92969593497992,
		],
		2 => [
			'test' => 17153114691118,
			'real' => 81514171161381,
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
