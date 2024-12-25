<?php

namespace AdventOfCode\Year2024;

/**
 * Day 25: Code Chronicle
 */
class Day25 {
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
	 * Part 1: How many unique lock/key pairs fit together without overlapping in any column?
	 *
	 * @return int
	 */
	public function run(): int {
		$total = 0;

		// Iterate over all unique pairs of blocks
		for ( $i = 0; $i < count( $this->data ); $i ++ ) {
			for ( $j = $i + 1; $j < count( $this->data ); $j ++ ) {
				if ( $this->fit( $this->data[ $i ], $this->data[ $j ] ) ) {
					$total ++;
				}
			}
		}

		return $total;
	}

	/**
	 * Determines if two blocks fit together without overlap.
	 *
	 * @param array $block1 The first block.
	 * @param array $block2 The second block.
	 *
	 * @return bool
	 */
	private function fit( array $block1, array $block2 ): bool {
		$rows = count( $block1 );
		$cols = count( $block1[0] );

		for ( $r = 0; $r < $rows; $r ++ ) {
			for ( $c = 0; $c < $cols; $c ++ ) {
				if ( $block1[ $r ][ $c ] === '#' && $block2[ $r ][ $c ] === '#' ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Parses the puzzle input data into 2D blocks.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-25-test.txt' : '/data/day-25.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$blocks        = [];
		$current_block = [];

		foreach ( $lines as $line ) {
			if ( trim( $line ) === '' ) {
				if ( ! empty( $current_block ) ) {
					$blocks[]      = array_map( 'str_split', $current_block );
					$current_block = [];
				}
			} else {
				$current_block[] = $line;
			}
		}

		if ( ! empty( $current_block ) ) {
			$blocks[] = array_map( 'str_split', $current_block );
		}

		return $blocks;
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day25  = new Day25( $test, $part );
	$result = $day25->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 3,
			'real' => 3317,
		]
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
	$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
	if ( in_array( $test, [ 'y', 'n' ], true ) ) {
		$test_mode = $test === 'y';
		run_part( 1, $test_mode );
		break;
	}
	echo 'Invalid input. Please enter y or n.' . PHP_EOL;
}
