<?php

namespace AdventOfCode\Year2020;

/**
 * Day 08: Handheld Halting
 */
class Day08 {
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

	/**
	 * Cache for memoization.
	 *
	 * @var array
	 */
	private array $cache = [];

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
	 * Part 1: Run the boot code and return the value of the accumulator before an instruction is executed a second time.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		[ $accumulator ] = $this->run_program( $this->data );

		return $accumulator;
	}

	/**
	 * Part 2: Fix the program by changing exactly one jmp (to nop) or nop (to jmp) instruction. Return the value of the
	 *         accumulator after the program terminates.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->fix_program( $this->data );
	}

	/**
	 * Runs the boot code program and returns the value of the accumulator before an instruction is executed a second time.
	 *
	 * @param array $data The boot code program data.
	 *
	 * @return array
	 */
	private function run_program( array $data ): array {
		$accumulator = 0;
		$index       = 0;
		$visited     = [];
		$terminated  = false;

		while ( ! isset( $visited[ $index ] ) ) {
			if ( ! isset( $data[ $index ] ) ) {
				$terminated = true;
				break;
			}

			$visited[ $index ] = true;
			$operation        = key( $data[ $index ] );
			$value            = $data[ $index ][ $operation ];

			switch ( $operation ) {
				case 'acc':
					$accumulator += $value;
					$index ++;
					break;
				case 'jmp':
					$index += $value;
					break;
				case 'nop':
					$index ++;
					break;
			}
		}

		return [ $accumulator, $terminated ];
	}

	/**
	 * Fixes the boot code program by changing exactly one jmp (to nop) or nop (to jmp) instruction. Returns the value of
	 * the accumulator after the program terminates.
	 *
	 * @param array $data The boot code program data.
	 *
	 * @return integer
	 */
	private function fix_program( array $data ): int {
		$index = 0;
		$size  = count( $data );

		while ( $index < $size ) {
			$operation = key( $data[ $index ] );

			if ( $operation === 'acc' ) {
				$index ++;
				continue;
			}

			$original = $data[ $index ];
			$data[ $index ] = [ $operation === 'jmp' ? 'nop' : 'jmp' => $original[ $operation ] ];

			list( $accumulator, $terminated ) = $this->run_program( $data );

			if ( $terminated ) {
				return $accumulator;
			}

			$data[ $index ] = $original;
			$index ++;
		}

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
		$file  = $test ? '/data/day-08-test.txt' : '/data/day-08.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			// Split each line into operation and value
			list( $operation, $value ) = explode( ' ', $line );
			$value = (int) $value; // Convert value to an integer

			return [ $operation => $value ];
		}, $lines );
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
	$day08  = new Day08( $test, $part );
	$result = $day08->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 5,
			'real' => 1594,
		],
		2 => [
			'test' => 8,
			'real' => 758,
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
