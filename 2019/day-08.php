<?php

namespace AdventOfCode\Year2019;

/**
 * Day 08: Space Image Format
 */
class Day08 {
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
	 * Part 1: Find the layer with the fewest 0 digits, then multiply the number of 1 digits by the number of 2 digits
	 *         in that layer.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		// Split the data into layers of 25x6
		$layers = array_chunk( $this->data, 25 * 6 );

		// Find the layer with the fewest 0 digits
		$layer = array_reduce(
			$layers,
			fn( $carry, $item ) => ( count( array_keys( $item, 0 ) ) < count( array_keys( $carry, 0 ) ) ) ? $item : $carry,
			$layers[0]
		);

		return count( array_keys( $layer, 1 ) ) * count( array_keys( $layer, 2 ) );
	}

	/**
	 * Part 2: Decode the image by compositing the layers and returning the final message as a string.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		// Different image dimensions depending on if this is the test or real data.
		$width  = $this->is_test ? 2 : 25;
		$height = $this->is_test ? 2 : 6;

		// Break the data into layers.
		$layers = array_chunk( $this->data, $width * $height );

		// Initialize the final image as an array of transparent pixels ('2').
		$final_image = array_fill( 0, $width * $height, '2' );

		// For each pixel position, go through the layers and use the first non-transparent pixel.
		for ( $i = 0; $i < $width * $height; $i ++ ) {
			foreach ( $layers as $layer ) {
				if ( $layer[ $i ] !== '2' ) { // if not transparent
					$final_image[ $i ] = $layer[ $i ];
					break;
				}
			}
		}

		// Build the output string row by row.
		$output = '';
		for ( $row = 0; $row < $height; $row ++ ) {
			$line = PHP_EOL;

			for ( $col = 0; $col < $width; $col ++ ) {
				$pixel = $final_image[ $row * $width + $col ];
				$line  .= ( $pixel === '0' ? ' ' : ( $pixel === '1' ? '#' : ' ' ) );
			}

			$output .= $line;
		}

		return $output;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-08-test.txt' : '/data/day-08.txt';

		return str_split( trim( file_get_contents( __DIR__ . $file ) ) );
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
	$day08  = new Day08( $test, $part );
	$result = $day08->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 24,
			'real' => 1330,
		],
		2 => [
			'test' => "\n #\n#",
			'real' => "\n####  ##  #  # #### ####\n#    #  # #  # #    #\n###  #  # #### ###  ###\n#    #### #  # #    #\n#    #  # #  # #    #\n#    #  # #  # #### #",
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
