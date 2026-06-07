<?php

namespace AdventOfCode\Year2017;

/**
 * Day 14: Disk Defragmentation
 */
class Day14 {
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
	 * Part 1: Count the number of used squares in the 128x128 grid.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$count = 0;

		for ( $row = 0; $row < 128; $row++ ) {
			$hash = $this->knot_hash( $this->data . '-' . $row );

			for ( $i = 0; $i < strlen( $hash ); $i++ ) {
				$nibble = hexdec( $hash[ $i ] );
				$count += substr_count( sprintf( '%04b', $nibble ), '1' );
			}
		}

		return $count;
	}

	/**
	 * Part 2: Count the number of connected regions in the grid.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$grid = [];

		for ( $row = 0; $row < 128; $row++ ) {
			$hash   = $this->knot_hash( $this->data . '-' . $row );
			$binary = '';

			for ( $i = 0; $i < strlen( $hash ); $i++ ) {
				$binary .= sprintf( '%04b', hexdec( $hash[ $i ] ) );
			}

			$grid[] = str_split( $binary );
		}

		$regions = 0;
		$seen    = [];

		for ( $r = 0; $r < 128; $r++ ) {
			for ( $c = 0; $c < 128; $c++ ) {
				if ( $grid[ $r ][ $c ] === '1' && ! isset( $seen[ "$r,$c" ] ) ) {
					$regions++;
					$stack = [ [ $r, $c ] ];

					while ( $stack ) {
						[ $y, $x ] = array_pop( $stack );
						$key = "$y,$x";

						if ( isset( $seen[ $key ] ) ) {
							continue;
						}

						$seen[ $key ] = true;

						foreach ( [ [ -1, 0 ], [ 1, 0 ], [ 0, -1 ], [ 0, 1 ] ] as [ $dy, $dx ] ) {
							$ny = $y + $dy;
							$nx = $x + $dx;

							if ( $ny >= 0 && $ny < 128 && $nx >= 0 && $nx < 128
								&& $grid[ $ny ][ $nx ] === '1' && ! isset( $seen[ "$ny,$nx" ] ) ) {
								$stack[] = [ $ny, $nx ];
							}
						}
					}
				}
			}
		}

		return $regions;
	}

	/**
	 * Computes the knot hash of a string, returning a 32-character hex string.
	 *
	 * @param string $input The input string.
	 *
	 * @return string
	 */
	private function knot_hash( string $input ): string {
		$lengths = array_merge(
			array_map( 'ord', str_split( $input ) ),
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
	 * @return string
	 */
	private function parse_data( bool $test ): string {
		$file = $test ? '/data/day-14-test.txt' : '/data/day-14.txt';

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
	$day14  = new Day14( $test, $part );
	$result = $day14->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 8108,
			'real' => 8190,
		],
		2 => [
			'test' => 1242,
			'real' => 1134,
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
