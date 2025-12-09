<?php

namespace AdventOfCode\Year2025;

/**
 * Day 09: Movie Theater
 */
class Day09 {
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
	 * Part 1: Find the largest rectangle area using any two red tiles as opposite corners.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$max_area = 0;
		$count    = count( $this->data );

		// Iterate over all pairs of coordinates
		for ( $i = 0; $i < $count; $i++ ) {
			for ( $j = $i + 1; $j < $count; $j++ ) {
				[ $x1, $y1 ] = $this->data[ $i ];
				[ $x2, $y2 ] = $this->data[ $j ];

				// Calculate rectangle dimensions
				$width  = abs( $x2 - $x1 ) + 1;
				$height = abs( $y2 - $y1 ) + 1;
				$area   = $width * $height;

				$max_area = max( $max_area, $area );
			}
		}

		return $max_area;
	}

	/**
	 * Part 2: Find the largest rectangle area using only red and green tiles.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$edges    = $this->build_polygon_edges();
		$max_area = 0;
		$count    = count( $this->data );

		// Iterate over all pairs of coordinates
		for ( $i = 0; $i < $count; $i++ ) {
			for ( $j = $i + 1; $j < $count; $j++ ) {
				[ $x1, $y1 ] = $this->data[ $i ];
				[ $x2, $y2 ] = $this->data[ $j ];

				// Calculate rectangle dimensions
				$width  = abs( $x2 - $x1 ) + 1;
				$height = abs( $y2 - $y1 ) + 1;
				$area   = $width * $height;

				// Check if this rectangle could be larger than current max
				if ( $area > $max_area ) {
					if ( $this->is_rect_valid( $x1, $y1, $x2, $y2, $edges ) ) {
						$max_area = $area;
					}
				}
			}
		}

		return $max_area;
	}

	/**
	 * Builds polygon edges from consecutive red tiles.
	 *
	 * @return array Array of edges, each as [x1, y1, x2, y2].
	 */
	private function build_polygon_edges(): array {
		$edges = [];
		$count = count( $this->data );

		// Create edges from consecutive points
		for ( $i = 0; $i < $count; $i++ ) {
			$next        = ($i + 1) % $count; // Wrap around 
			[ $x1, $y1 ] = $this->data[ $i ];
			[ $x2, $y2 ] = $this->data[ $next ];
			$edges[]     = [ $x1, $y1, $x2, $y2 ];
		}

		return $edges;
	}

	/**
	 * Checks if an edge intersects the interior of a rectangle.
	 *
	 * @param array $edge Edge as [x1, y1, x2, y2].
	 * @param int   $min_x Minimum x coordinate of rectangle.
	 * @param int   $max_x Maximum x coordinate of rectangle.
	 * @param int   $min_y Minimum y coordinate of rectangle.
	 * @param int   $max_y Maximum y coordinate of rectangle.
	 *
	 * @return bool True if edge intersects rectangle interior.
	 */
	private function edge_intersects_rect_interior( array $edge, int $min_x, int $max_x, int $min_y, int $max_y ): bool {
		[ $x1, $y1, $x2, $y2 ] = $edge;

		$is_horizontal = ($y1 === $y2);
		$is_vertical   = ($x1 === $x2);

		if ( $is_horizontal ) {
			// Horizontal edge... check if it passes through rectangle interior
			$edge_min_x = min( $x1, $x2 );
			$edge_max_x = max( $x1, $x2 );

			// Edge must be between min_y and max_y
			// And x-range must overlap with rectangle x-range
			if ( $y1 > $min_y && $y1 < $max_y ) {
				// Check if x-ranges overlap
				return $edge_min_x < $max_x && $edge_max_x > $min_x;
			}
		} elseif ( $is_vertical ) {
			// Vertical edge... check if it passes through rectangle interior
			$edge_min_y = min( $y1, $y2 );
			$edge_max_y = max( $y1, $y2 );

			// Edge must be between min_x and max_x
			// And y-range must overlap with rectangle y-range
			if ( $x1 > $min_x && $x1 < $max_x ) {
				// Check if y-ranges overlap
				return $edge_min_y < $max_y && $edge_max_y > $min_y;
			}
		}

		return false;
	}

	/**
	 * Checks if a rectangle is valid (contains only red/green tiles).
	 *
	 * A rectangle is valid if no polygon edges intersect its interior.
	 *
	 * @param int   $x1 X coordinate of first corner.
	 * @param int   $y1 Y coordinate of first corner.
	 * @param int   $x2 X coordinate of second corner.
	 * @param int   $y2 Y coordinate of second corner.
	 * @param array $edges Array of polygon edges.
	 *
	 * @return bool True if rectangle is valid.
	 */
	private function is_rect_valid( int $x1, int $y1, int $x2, int $y2, array $edges ): bool {
		$min_x = min( $x1, $x2 );
		$max_x = max( $x1, $x2 );
		$min_y = min( $y1, $y2 );
		$max_y = max( $y1, $y2 );

		// Check if any edge intersects the rectangle interior
		foreach ( $edges as $edge ) {
			if ( $this->edge_intersects_rect_interior( $edge, $min_x, $max_x, $min_y, $max_y ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of [x, y] coordinate pairs.
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-09-test.txt' : '/data/day-09.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$coordinates = [];
		foreach ( $lines as $line ) {
			$parts = explode( ',', trim( $line ) );
			if ( count( $parts ) === 2 ) {
				$coordinates[] = [ (int) $parts[0], (int) $parts[1] ];
			}
		}

		return $coordinates;
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
	$day09  = new Day09( $test, $part );
	$result = $day09->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 50,
			'real' => 4781546175,
		],
		2 => [
			'test' => 24,
			'real' => 1573359081,
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
