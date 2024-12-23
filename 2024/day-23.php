<?php

namespace AdventOfCode\Year2024;

/**
 * Day 23: TITLE HERE
 */
class Day23 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
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
	 * @return int|string
	 */
	public function run(): int|string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Find all the sets of three connected computers. How many contain a computer with a name that starts with t?
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$records = array_map( function ( $line ) {
			$pair = explode( '-', $line );
			sort( $pair ); // Sort the pair alphabetically

			return $pair;
		}, $this->data );

		$triangles = [];

		foreach ( $records as [$a, $b] ) {
			if ( str_starts_with( $a, 't' ) || str_starts_with( $b, 't' ) ) {
				foreach ( $records as [$c, $d] ) {
					if ( $b !== $c && $b !== $d ) {
						continue;
					}
					if ( $a === $c || $a === $d ) {
						continue;
					}

					foreach ( $records as [$e, $f] ) {
						if ( $e !== $a ) {
							continue;
						}
						if ( $b === $f ) {
							continue;
						}
						if ( $c !== $f && $d !== $f ) {
							continue;
						}

						$triangle = [ $a, $b, $f ];
						sort( $triangle );
						$triangles[ implode( '', $triangle ) ] = true;
					}
				}
			}
		}

		return count( $triangles );
	}

	/**
	 * Part 2: Get the password for the Lan Party.
	 *
	 * The password to get into the LAN party is the name of every computer at the LAN party, sorted alphabetically,
	 * then joined together with commas
	 *
	 * @return int
	 */
	private function solve_part_2(): string {
		$computers = [];
		$profiles  = [];

		foreach ( $this->data as $line ) {
			[ $a, $b ] = explode( '-', $line );
			$computers[ $a ] = true;
			$computers[ $b ] = true;
			$profiles[ $a ]  = array_merge( $profiles[ $a ] ?? [ $a ], [ $b ] );
			$profiles[ $b ]  = array_merge( $profiles[ $b ] ?? [ $b ], [ $a ] );
		}

		$stop     = false;
		$network  = array_keys( $computers );
		$password = '';

		foreach ( $network as $computer ) {
			if ( $stop ) {
				break;
			}

			foreach ( $profiles as $profile1 ) {
				if ( ! in_array( $computer, $profile1 ) ) {
					continue;
				}

				$clone = array_diff( $profile1, [ $computer ] );
				$count = 0;

				foreach ( $profiles as $profile2 ) {
					if ( empty( array_diff( $clone, $profile2 ) ) ) {
						$count ++;
					}
				}

				if ( $this->is_test && $count === 4 || ! $this->is_test && $count === 13 ) {
					sort( $clone );
					$password = implode( ',', $clone );
					$stop     = true;
					break;
				}
			}
		}

		return $password;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-23-test.txt' : '/data/day-23.txt';
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
	$day23  = new Day23( $test, $part );
	$result = $day23->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 7,
			'real' => 1108,
		],
		2 => [
			'test' => 'co,de,ka,ta',
			'real' => 'ab,cp,ep,fj,fl,ij,in,ng,pl,qr,rx,va,vf',
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
