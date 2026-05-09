<?php

namespace AdventOfCode\Year2016;

/**
 * Day 16: Dragon Checksum
 */
class Day16 {
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
	 * The initial state string.
	 *
	 * @var string
	 */
	private string $data;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return string
	 */
	public function run(): string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Fill disk of length 272 and compute checksum.
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		$length = $this->is_test ? 20 : 272;

		return $this->fill_and_checksum( $this->data, $length );
	}

	/**
	 * Part 2: Fill disk of length 35651584 and compute checksum.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		return $this->fill_and_checksum( $this->data, 35651584 );
	}

	/**
	 * Fills the disk using dragon curve and computes the checksum.
	 *
	 * @param string $data   Initial state.
	 * @param int    $length Disk length.
	 *
	 * @return string The checksum.
	 */
	private function fill_and_checksum( string $data, int $length ): string {
		// Generate data using dragon curve until long enough.
		while ( strlen( $data ) < $length ) {
			$b    = strtr( strrev( $data ), '01', '10' );
			$data = $data . '0' . $b;
		}

		$data = substr( $data, 0, $length );

		// Compute checksum until odd length.
		while ( strlen( $data ) % 2 === 0 ) {
			$checksum = '';

			for ( $i = 0; $i < strlen( $data ); $i += 2 ) {
				$checksum .= $data[ $i ] === $data[ $i + 1 ] ? '1' : '0';
			}

			$data = $checksum;
		}

		return $data;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return string The initial state.
	 */
	private function parse_data( bool $test ): string {
		$file = $test ? '/data/day-16-test.txt' : '/data/day-16.txt';

		return trim( file_get_contents( __DIR__ . $file ) );
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
	$day16  = new Day16( $test, $part );
	$result = $day16->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => '01100',
			'real' => '10100101010101101',
		],
		2 => [
			'test' => 0,
			'real' => '01100001101101001',
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
