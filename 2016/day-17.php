<?php

namespace AdventOfCode\Year2016;

/**
 * Day 17: Two Steps Forward
 */
class Day17 {
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
	 * The passcode.
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
	 * Part 1: Find shortest path to the vault.
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		// BFS to find shortest path to (3,3).
		$queue = [ [ 0, 0, '' ] ];
		$head  = 0;
		$dirs  = [ [ 'U', 0, -1 ], [ 'D', 0, 1 ], [ 'L', -1, 0 ], [ 'R', 1, 0 ] ];

		while ( $head < count( $queue ) ) {
			[ $x, $y, $path ] = $queue[ $head++ ];

			if ( $x === 3 && $y === 3 ) {
				return $path;
			}

			$hash = md5( $this->data . $path );

			foreach ( $dirs as $i => [ $dir, $dx, $dy ] ) {
				$nx = $x + $dx;
				$ny = $y + $dy;

				if ( $nx < 0 || $nx > 3 || $ny < 0 || $ny > 3 ) {
					continue;
				}

				if ( strpos( 'bcdef', $hash[ $i ] ) !== false ) {
					$queue[] = [ $nx, $ny, $path . $dir ];
				}
			}
		}

		return '';
	}

	/**
	 * Part 2: Find length of the longest path to the vault.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		$queue   = [ [ 0, 0, '' ] ];
		$head    = 0;
		$dirs    = [ [ 'U', 0, -1 ], [ 'D', 0, 1 ], [ 'L', -1, 0 ], [ 'R', 1, 0 ] ];
		$longest = 0;

		while ( $head < count( $queue ) ) {
			[ $x, $y, $path ] = $queue[ $head++ ];

			if ( $x === 3 && $y === 3 ) {
				$longest = max( $longest, strlen( $path ) );
				continue;
			}

			$hash = md5( $this->data . $path );

			foreach ( $dirs as $i => [ $dir, $dx, $dy ] ) {
				$nx = $x + $dx;
				$ny = $y + $dy;

				if ( $nx < 0 || $nx > 3 || $ny < 0 || $ny > 3 ) {
					continue;
				}

				if ( strpos( 'bcdef', $hash[ $i ] ) !== false ) {
					$queue[] = [ $nx, $ny, $path . $dir ];
				}
			}
		}

		return (string) $longest;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return string The passcode.
	 */
	private function parse_data( bool $test ): string {
		$file = $test ? '/data/day-17-test.txt' : '/data/day-17.txt';

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
	$day17  = new Day17( $test, $part );
	$result = $day17->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 'DDRRRD',
			'real' => 'DRLRDDURDR',
		],
		2 => [
			'test' => 370,
			'real' => 500,
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
