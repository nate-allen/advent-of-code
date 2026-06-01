<?php

namespace AdventOfCode\Year2017;

/**
 * Day 10: Knot Hash
 */
class Day10 {
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
	 * Raw input string for part 2.
	 *
	 * @var string
	 */
	private string $raw;

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
			1 => (string) $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Multiply the first two numbers after processing knot hash lengths.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$size    = $this->is_test ? 5 : 256;
		$list    = range( 0, $size - 1 );
		$pos     = 0;
		$skip    = 0;

		foreach ( $this->data as $length ) {
			// Reverse 'length' elements starting at 'pos', wrapping around.
			for ( $i = 0; $i < intdiv( $length, 2 ); $i++ ) {
				$a = ( $pos + $i ) % $size;
				$b = ( $pos + $length - 1 - $i ) % $size;

				[ $list[ $a ], $list[ $b ] ] = [ $list[ $b ], $list[ $a ] ];
			}

			$pos = ( $pos + $length + $skip ) % $size;
			$skip++;
		}

		return $list[0] * $list[1];
	}

	/**
	 * Part 2: Compute the full Knot Hash as a hexadecimal string.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		$lengths = array_merge(
			array_map( 'ord', str_split( $this->raw ) ),
			[ 17, 31, 73, 47, 23 ]
		);

		$size = 256;
		$list = range( 0, $size - 1 );
		$pos  = 0;
		$skip = 0;

		for ( $round = 0; $round < 64; $round++ ) {
			foreach ( $lengths as $length ) {
				for ( $i = 0; $i < intdiv( $length, 2 ); $i++ ) {
					$a = ( $pos + $i ) % $size;
					$b = ( $pos + $length - 1 - $i ) % $size;

					[ $list[ $a ], $list[ $b ] ] = [ $list[ $b ], $list[ $a ] ];
				}

				$pos = ( $pos + $length + $skip ) % $size;
				$skip++;
			}
		}

		$dense = '';
		for ( $block = 0; $block < 16; $block++ ) {
			$xor = 0;
			for ( $i = 0; $i < 16; $i++ ) {
				$xor ^= $list[ $block * 16 + $i ];
			}
			$dense .= sprintf( '%02x', $xor );
		}

		return $dense;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? "/data/day-10-test{$this->part}.txt" : '/data/day-10.txt';

		$this->raw = trim( file_get_contents( __DIR__ . $file ) );

		return array_map( 'intval', explode( ',', $this->raw ) );
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
	$day10  = new Day10( $test, $part );
	$result = $day10->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 12,
			'real' => 4114,
		],
		2 => [
			'test' => '3efbe78a8d82f29979031a4aa0b16a9d',
			'real' => '2f8c3d2100fdd57cec130d928b0fd2dd',
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
