<?php

namespace AdventOfCode\Year2019;

/**
 * Day 05: Sunny with a Chance of Asteroids
 */
class Day05 {
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
	 * Part 1: Run the Intcode diagnostic program for the air conditioner with input 1.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->run_program( 1 );
	}

	/**
	 * Part 2: Runs the diagnostic program with input 5.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->run_program( 5 );
	}

	/**
	 * Runs the Intcode program with the given input.
	 *
	 * @param int $input The input value to use when an opcode 3 is encountered.
	 *
	 * @return int
	 */
	private function run_program( int $input ): int {
		$data    = $this->data;
		$pointer = 0;
		$outputs = [];

		while ( true ) {
			$instruction = $data[ $pointer ];
			$opcode      = $instruction % 100;
			$mode1       = intdiv( $instruction, 100 ) % 10;
			$mode2       = intdiv( $instruction, 1000 ) % 10;

			if ( $opcode === 99 ) {
				break;
			}

			switch ( $opcode ) {
				case 1:
					// Addition.
					$param1          = $data[ $pointer + 1 ];
					$param2          = $data[ $pointer + 2 ];
					$param3          = $data[ $pointer + 3 ];
					$val1            = ( $mode1 === 0 ) ? $data[ $param1 ] : $param1;
					$val2            = ( $mode2 === 0 ) ? $data[ $param2 ] : $param2;
					$data[ $param3 ] = $val1 + $val2;
					$pointer         += 4;
					break;

				case 2:
					// Multiplication.
					$param1          = $data[ $pointer + 1 ];
					$param2          = $data[ $pointer + 2 ];
					$param3          = $data[ $pointer + 3 ];
					$val1            = ( $mode1 === 0 ) ? $data[ $param1 ] : $param1;
					$val2            = ( $mode2 === 0 ) ? $data[ $param2 ] : $param2;
					$data[ $param3 ] = $val1 * $val2;
					$pointer         += 4;
					break;

				case 3:
					// Input.
					$param1          = $data[ $pointer + 1 ];
					$data[ $param1 ] = $input;
					$pointer         += 2;
					break;

				case 4:
					// Output.
					$param1    = $data[ $pointer + 1 ];
					$val       = ( $mode1 === 0 ) ? $data[ $param1 ] : $param1;
					$outputs[] = $val;
					$pointer   += 2;
					break;

				case 5:
					// Jump-if-true.
					$param1  = $data[ $pointer + 1 ];
					$param2  = $data[ $pointer + 2 ];
					$val1    = ( $mode1 === 0 ) ? $data[ $param1 ] : $param1;
					$val2    = ( $mode2 === 0 ) ? $data[ $param2 ] : $param2;
					$pointer = ( $val1 !== 0 ) ? $val2 : $pointer + 3;
					break;

				case 6:
					// Jump-if-false.
					$param1  = $data[ $pointer + 1 ];
					$param2  = $data[ $pointer + 2 ];
					$val1    = ( $mode1 === 0 ) ? $data[ $param1 ] : $param1;
					$val2    = ( $mode2 === 0 ) ? $data[ $param2 ] : $param2;
					$pointer = ( $val1 === 0 ) ? $val2 : $pointer + 3;
					break;

				case 7:
					// Less than.
					$param1          = $data[ $pointer + 1 ];
					$param2          = $data[ $pointer + 2 ];
					$param3          = $data[ $pointer + 3 ];
					$val1            = ( $mode1 === 0 ) ? $data[ $param1 ] : $param1;
					$val2            = ( $mode2 === 0 ) ? $data[ $param2 ] : $param2;
					$data[ $param3 ] = ( $val1 < $val2 ) ? 1 : 0;
					$pointer         += 4;
					break;

				case 8:
					// Equals.
					$param1          = $data[ $pointer + 1 ];
					$param2          = $data[ $pointer + 2 ];
					$param3          = $data[ $pointer + 3 ];
					$val1            = ( $mode1 === 0 ) ? $data[ $param1 ] : $param1;
					$val2            = ( $mode2 === 0 ) ? $data[ $param2 ] : $param2;
					$data[ $param3 ] = ( $val1 == $val2 ) ? 1 : 0;
					$pointer         += 4;
					break;
			}
		}

		// Return the final diagnostic code (the last output).
		return end( $outputs );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-05-test.txt' : '/data/day-05.txt';
		$lines = array_map( 'intval', explode( ",", trim( file_get_contents( __DIR__ . $file ) ) ) );

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
	$day05  = new Day05( $test, $part );
	$result = $day05->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 13818007,
			'real' => 15386262,
		],
		2 => [
			'test' => 3176266,
			'real' => 10376124,
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
