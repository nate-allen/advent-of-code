<?php

namespace AdventOfCode\Year2016;

/**
 * Day 07: Internet Protocol Version 7
 */
class Day07 {
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
	 * Part 1: Count IPs that support TLS (have ABBA outside but not inside brackets).
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$count = 0;

		foreach ( $this->data as $ip ) {
			$has_abba_outside = false;
			$has_abba_inside  = false;

			foreach ( $ip['supernet'] as $seg ) {
				if ( $this->has_abba( $seg ) ) {
					$has_abba_outside = true;
				}
			}

			foreach ( $ip['hypernet'] as $seg ) {
				if ( $this->has_abba( $seg ) ) {
					$has_abba_inside = true;
				}
			}

			if ( $has_abba_outside && ! $has_abba_inside ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Part 2: Count IPs that support SSL (ABA in supernet with corresponding BAB in hypernet).
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$count = 0;

		foreach ( $this->data as $ip ) {
			$abas = [];

			foreach ( $ip['supernet'] as $seg ) {
				for ( $i = 0; $i < strlen( $seg ) - 2; $i++ ) {
					if ( $seg[ $i ] === $seg[ $i + 2 ] && $seg[ $i ] !== $seg[ $i + 1 ] ) {
						$abas[] = $seg[ $i ] . $seg[ $i + 1 ];
					}
				}
			}

			foreach ( $abas as $aba ) {
				$bab   = $aba[1] . $aba[0] . $aba[1];
				$found = false;

				foreach ( $ip['hypernet'] as $seg ) {
					if ( str_contains( $seg, $bab ) ) {
						$found = true;
						break;
					}
				}

				if ( $found ) {
					$count++;
					break;
				}
			}
		}

		return $count;
	}

	/**
	 * Checks if a string contains an ABBA pattern.
	 *
	 * @param string $s The string to check.
	 *
	 * @return bool
	 */
	private function has_abba( string $s ): bool {
		for ( $i = 0; $i < strlen( $s ) - 3; $i++ ) {
			if ( $s[ $i ] !== $s[ $i + 1 ] && $s[ $i ] === $s[ $i + 3 ] && $s[ $i + 1 ] === $s[ $i + 2 ] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? "/data/day-07-test{$this->part}.txt" : '/data/day-07.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			$parts    = preg_split( '/[\[\]]/', $line );
			$supernet = [];
			$hypernet = [];

			foreach ( $parts as $i => $part ) {
				if ( $i % 2 === 0 ) {
					$supernet[] = $part;
				} else {
					$hypernet[] = $part;
				}
			}

			return [
				'supernet' => $supernet,
				'hypernet' => $hypernet,
			];
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
	$day07  = new Day07( $test, $part );
	$result = $day07->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 2,
			'real' => 115,
		],
		2 => [
			'test' => 3,
			'real' => 231,
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
