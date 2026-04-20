<?php

namespace AdventOfCode\Year2016;

/**
 * Day 05: How About a Nice Game of Chess?
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
	 * Part 1: Find password from MD5 hashes starting with 5 zeroes.
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		$password = '';
		$index    = 0;

		while ( strlen( $password ) < 8 ) {
			$hash = md5( $this->data . $index );

			if ( str_starts_with( $hash, '00000' ) ) {
				$password .= $hash[5];
			}

			$index++;
		}

		return $password;
	}

	/**
	 * Part 2: Find password using position-based MD5 hash decoding.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		$password = array_fill( 0, 8, null );
		$found    = 0;
		$index    = 0;

		while ( $found < 8 ) {
			$hash = md5( $this->data . $index );

			if ( str_starts_with( $hash, '00000' ) ) {
				$pos = $hash[5];

				if ( ctype_digit( $pos ) && (int) $pos < 8 && $password[ (int) $pos ] === null ) {
					$password[ (int) $pos ] = $hash[6];
					$found++;
				}
			}

			$index++;
		}

		return implode( '', $password );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return string
	 */
	private function parse_data( bool $test ): string {
		$file = $test ? '/data/day-05-test.txt' : '/data/day-05.txt';

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
	$day05  = new Day05( $test, $part );
	$result = $day05->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => '18f47a30',
			'real' => '4543c154',
		],
		2 => [
			'test' => '05ace8e3',
			'real' => '1050cbbd',
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
