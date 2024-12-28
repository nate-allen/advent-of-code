<?php

namespace AdventOfCode\Year2021;

/**
 * Day 08: Seven Segment Search
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

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->parse_data( $this->is_test );
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
	 * Part 1: In the output values, how many times do digits 1, 4, 7, or 8 appear?
	 *
	 * 1 has 2 segments, 4 has 4, 7 has 3, and 8 has 7.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$total = 0;

		foreach ( $this->data as $line ) {
			$total += count( array_intersect( array_map( 'strlen', $line['values'] ), [ 2, 4, 3, 7 ] ) );
		}

		return $total;
	}

	/**
	 * Part 2: Determine all the wire/segment connections and decode the four-digit output values. Return their sum.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$total = 0;

		foreach ( $this->data as $line ) {
			$patterns = $line['patterns'];
			$output   = $line['values'];

			// Sort patterns for consistency
			$sorted_patterns = array_map( fn( $pattern ) => str_split( $pattern ), $patterns );
			array_walk( $sorted_patterns, fn( &$pattern ) => sort( $pattern ) );
			$sorted_patterns = array_map( fn( $pattern ) => implode( '', $pattern ), $sorted_patterns );

			// Determine the digits based on unique segment counts
			$digits = [];
			$len5   = []; // For 2, 3, 5
			$len6   = []; // For 0, 6, 9

			foreach ( $sorted_patterns as $pattern ) {
				switch ( strlen( $pattern ) ) {
					case 2:
						$digits[1] = $pattern;
						break;
					case 4:
						$digits[4] = $pattern;
						break;
					case 3:
						$digits[7] = $pattern;
						break;
					case 7:
						$digits[8] = $pattern;
						break;
					case 5:
						$len5[] = $pattern;
						break; // 2, 3, 5
					case 6:
						$len6[] = $pattern;
						break; // 0, 6, 9
				}
			}

			// Figure out the remaining digits
			// 3 is the only length-5 pattern that shares all segments with 1
			foreach ( $len5 as $key => $pattern ) {
				if ( $this->shares_segments( $pattern, $digits[1], 2 ) ) {
					$digits[3] = $pattern;
					unset( $len5[ $key ] );
					break;
				}
			}

			// 9 is the only length-6 pattern that shares all segments with 4
			foreach ( $len6 as $key => $pattern ) {
				if ( $this->shares_segments( $pattern, $digits[4], 4 ) ) {
					$digits[9] = $pattern;
					unset( $len6[ $key ] );
					break;
				}
			}

			// 0 is the remaining length-6 pattern that shares all segments with 7
			foreach ( $len6 as $key => $pattern ) {
				if ( $this->shares_segments( $pattern, $digits[7], 3 ) ) {
					$digits[0] = $pattern;
					unset( $len6[ $key ] );
					break;
				}
			}

			// 6 is the remaining length-6 pattern
			$digits[6] = array_pop( $len6 );

			// 5 is the only length-5 pattern that shares 5 segments with 6
			foreach ( $len5 as $key => $pattern ) {
				if ( $this->shares_segments( $pattern, $digits[6], 5 ) ) {
					$digits[5] = $pattern;
					unset( $len5[ $key ] );
					break;
				}
			}

			// 2 is the remaining length-5 pattern
			$digits[2] = array_pop( $len5 );

			// Flip the array for decoding
			$digit_map = array_flip( $digits );

			// Decode the output
			$decoded_value = '';
			foreach ( $output as $value ) {
				$sorted_value = str_split( $value );
				sort( $sorted_value );
				$decoded_value .= $digit_map[ implode( '', $sorted_value ) ];
			}

			$total += (int) $decoded_value;
		}

		return $total;
	}

	/**
	 * Count shared segments between two patterns.
	 *
	 * @param string  $pattern1
	 * @param string  $pattern2
	 * @param integer $expected
	 *
	 * @return bool
	 */
	private function shares_segments( string $pattern1, string $pattern2, int $expected ): bool {
		$shared = count( array_intersect( str_split( $pattern1 ), str_split( $pattern2 ) ) );

		return $shared === $expected;
	}


	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return void
	 */
	private function parse_data( bool $test ): void {
		$file  = $test ? '/data/day-08-test.txt' : '/data/day-08.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		foreach ( $lines as $line ) {
			$parts = explode( ' | ', $line );
			$this->data[] = [
				'patterns'  => explode( ' ', $parts[0] ),
				'values' => explode( ' ', $parts[1] ),
			];
		}
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
			'test' => 26,
			'real' => 288,
		],
		2 => [
			'test' => 61229,
			'real' => 0,
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
