<?php

namespace AdventOfCode\Year2016;

/**
 * Day 03: Squares With Three Sides
 */
class Day03 {
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
	 * Part 1: Count valid triangles by rows.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$count = 0;

		foreach ( $this->data as [ $a, $b, $c ] ) {
			if ( $a + $b > $c && $a + $c > $b && $b + $c > $a ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Part 2: Count valid triangles reading by columns in groups of 3 rows.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$count = 0;

		for ( $i = 0; $i < count( $this->data ); $i += 3 ) {
			for ( $col = 0; $col < 3; $col++ ) {
				$a = $this->data[ $i ][ $col ];
				$b = $this->data[ $i + 1 ][ $col ];
				$c = $this->data[ $i + 2 ][ $col ];

				if ( $a + $b > $c && $a + $c > $b && $b + $c > $a ) {
					$count++;
				}
			}
		}

		return $count;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? "/data/day-03-test{$this->part}.txt" : '/data/day-03.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			return array_map( 'intval', preg_split( '/\s+/', trim( $line ) ) );
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
	$day03  = new Day03( $test, $part );
	$result = $day03->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 2,
			'real' => 1050,
		],
		2 => [
			'test' => 6,
			'real' => 1921,
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
