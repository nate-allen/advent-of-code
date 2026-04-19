<?php

namespace AdventOfCode\Year2016;

/**
 * Day 04: Security Through Obscurity
 */
class Day04 {
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
	 * Part 1: Sum sector IDs of real rooms.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$sum = 0;

		foreach ( $this->data as $room ) {
			if ( $this->is_real_room( $room ) ) {
				$sum += $room['sector'];
			}
		}

		return $sum;
	}

	/**
	 * Part 2: Find sector ID of the room storing North Pole objects.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		foreach ( $this->data as $room ) {
			if ( ! $this->is_real_room( $room ) ) {
				continue;
			}

			$decrypted = '';
			$shift     = $room['sector'] % 26;

			foreach ( str_split( $room['name'] ) as $ch ) {
				if ( $ch === '-' ) {
					$decrypted .= ' ';
				} else {
					$decrypted .= chr( ( ord( $ch ) - ord( 'a' ) + $shift ) % 26 + ord( 'a' ) );
				}
			}

			if ( str_contains( $decrypted, 'northpole' ) ) {
				return $room['sector'];
			}
		}

		return 0;
	}

	/**
	 * Checks if a room is real by validating its checksum.
	 *
	 * @param array $room The room data.
	 *
	 * @return bool
	 */
	private function is_real_room( array $room ): bool {
		$counts = array_count_values( str_split( str_replace( '-', '', $room['name'] ) ) );
		uksort( $counts, function ( $a, $b ) use ( $counts ) {
			return $counts[ $b ] <=> $counts[ $a ] ?: $a <=> $b;
		} );

		return substr( implode( '', array_keys( $counts ) ), 0, 5 ) === $room['checksum'];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-04-test.txt' : '/data/day-04.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			preg_match( '/^(.+)-(\d+)\[([a-z]+)\]$/', $line, $m );

			return [
				'name'     => $m[1],
				'sector'   => (int) $m[2],
				'checksum' => $m[3],
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
	$day04  = new Day04( $test, $part );
	$result = $day04->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 1514,
			'real' => 245102,
		],
		2 => [
			'test' => 0,
			'real' => 324,
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
