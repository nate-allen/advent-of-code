<?php

namespace AdventOfCode\Year2021;

/**
 * Day 20: Trench Map
 */
class Day20 {
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
	 * The algorithm and image data from the puzzle input.
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
	 * Part 1: Apply the image enhancement algorithm twice. How many pixels are lit in the resulting image?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		// Start with the initial image.
		$image     = $this->data['image'];
		$lit_count = 0;

		// Apply the enhancement algorithm twice
		for ( $i = 0; $i < 2; $i++ ) {
			$image = $this->enhance_image( $image );
		}

		// Count lit pixels (#) in the final image
		foreach ( $image as $row ) {
			foreach ( $row as $pixel ) {
				if ( $pixel === '#' ) {
					$lit_count++;
				}
			}
		}

		return $lit_count;
	}

	/**
	 * Part 2: Apply the image enhancement algorithm 50 times! How many pixels are lit in the resulting image?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$image     = $this->data['image'];
		$lit_count = 0;

		// Apply the enhancement algorithm fifty times!
		for ( $i = 0; $i < 50; $i++ ) {
			$image = $this->enhance_image( $image );
		}

		// ==== UNCOMMENT THE CODE BELOW TO SEE THE IMAGE ====
		// foreach ( $image as $row ) {
		// 	echo implode( '', $row ) . PHP_EOL;
		// }

		// Count lit pixels (#) in the final image
		foreach ( $image as $row ) {
			foreach ( $row as $pixel ) {
				if ( $pixel === '#' ) {
					$lit_count++;
				}
			}
		}

		return $lit_count;
	}

	/**
	 * Enhances the image by one iteration.
	 *
	 * @param array $image The current 2D image array.
	 *
	 * @return array
	 */
	private function enhance_image( array $image ): array {
		$old_height = count( $image );
		$old_width  = count( $image[0] );

		// Expand the dimensions by 2 in each direction.
		$new_height = $old_height + 2;
		$new_width  = $old_width + 2;

		$new_image = [];

		for ( $y = 0; $y < $new_height; $y ++ ) {
			$new_row = [];
			for ( $x = 0; $x < $new_width; $x ++ ) {

				// Build the 9-bit index from the 3x3 region around (x, y)
				$bits = '';
				for ( $dy = - 1; $dy <= 1; $dy ++ ) {
					for ( $dx = - 1; $dx <= 1; $dx ++ ) {
						// Coordinates in the old image
						$old_y = $y + $dy - 1;  // -1 because we shifted everything by +1
						$old_x = $x + $dx - 1;

						// If out of bounds, use '.' otherwise use $image[$old_y][$old_x].
						$pixel = ( $old_y < 0 || $old_y >= $old_height || $old_x < 0 || $old_x >= $old_width ) ? '.' : $image[ $old_y ][ $old_x ];

						$bits .= ( $pixel === '#' ) ? '1' : '0';
					}
				}

				$new_row[ $x ] = $this->data['algorithm'][ bindec( $bits ) ];
			}
			$new_image[] = $new_row;
		}

		return $new_image;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-20-test.txt' : '/data/day-20.txt';
		[ $algorithm, $image ] = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return [
			'algorithm' => str_split( $algorithm ),
			'image'     => array_map( 'str_split', explode( "\n", $image ) )
		];
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
	$day20  = new Day20( $test, $part );
	$result = $day20->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 35,
			'real' => 4917,
		],
		2 => [
			'test' => 3351,
			'real' => 16389,
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
