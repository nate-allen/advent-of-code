<?php

namespace AdventOfCode\Year2017;

/**
 * Day 21: Fractal Art
 */
class Day21 {
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
	 * Enhancement rules mapping pattern keys to output grids.
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
			1 => $this->solve( $this->is_test ? 2 : 5 ),
			2 => $this->solve( 18 ),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Run the fractal enhancement for a given number of iterations and count on pixels.
	 *
	 * @param integer $iterations Number of iterations.
	 *
	 * @return integer
	 */
	private function solve( int $iterations ): int {
		$grid = [ '.#.', '..#', '###' ];

		for ( $i = 0; $i < $iterations; $i++ ) {
			$size       = count( $grid );
			$block_size = $size % 2 === 0 ? 2 : 3;
			$blocks     = $size / $block_size;
			$new_size   = $blocks * ( $block_size + 1 );
			$new_grid   = array_fill( 0, $new_size, '' );

			for ( $br = 0; $br < $blocks; $br++ ) {
				for ( $bc = 0; $bc < $blocks; $bc++ ) {
					// Extract block.
					$key = '';
					for ( $r = 0; $r < $block_size; $r++ ) {
						if ( $r > 0 ) {
							$key .= '/';
						}
						$key .= substr( $grid[ $br * $block_size + $r ], $bc * $block_size, $block_size );
					}

					$output = $this->data[ $key ];

					// Place output into new grid.
					$out_size = $block_size + 1;
					for ( $r = 0; $r < $out_size; $r++ ) {
						$new_grid[ $br * $out_size + $r ] .= $output[ $r ];
					}
				}
			}

			$grid = $new_grid;
		}

		$count = 0;
		foreach ( $grid as $row ) {
			$count += substr_count( $row, '#' );
		}

		return $count;
	}

	/**
	 * Rotate a grid 90 degrees clockwise.
	 *
	 * @param array $grid Grid as array of strings.
	 *
	 * @return array
	 */
	private function rotate( array $grid ): array {
		$n      = count( $grid );
		$result = [];
		for ( $c = 0; $c < $n; $c++ ) {
			$row = '';
			for ( $r = $n - 1; $r >= 0; $r-- ) {
				$row .= $grid[ $r ][ $c ];
			}
			$result[] = $row;
		}

		return $result;
	}

	/**
	 * Flip a grid horizontally.
	 *
	 * @param array $grid Grid as array of strings.
	 *
	 * @return array
	 */
	private function flip( array $grid ): array {
		return array_map( 'strrev', $grid );
	}

	/**
	 * Parses the puzzle input data into a rules lookup with all rotations/flips.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-21-test.txt' : '/data/day-21.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$rules = [];

		foreach ( $lines as $line ) {
			[ $input, $output ] = explode( ' => ', $line );
			$output_rows = explode( '/', $output );
			$pattern     = explode( '/', $input );

			// Generate all 8 orientations (4 rotations x 2 flips).
			for ( $f = 0; $f < 2; $f++ ) {
				for ( $r = 0; $r < 4; $r++ ) {
					$rules[ implode( '/', $pattern ) ] = $output_rows;
					$pattern = $this->rotate( $pattern );
				}
				$pattern = $this->flip( $pattern );
			}
		}

		return $rules;
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
	$day21  = new Day21( $test, $part );
	$result = $day21->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 12,
			'real' => 150,
		],
		2 => [
			'test' => 0,
			'real' => 2606275,
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
