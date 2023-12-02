<?php
/**
 * Day 9: Rope Bridge
 */

/**
 * Part 1: How many positions does the tail of the short rope visit at least once?
 *
 * @return void
 */
function part_1() {
	$movements = get_movements();

	$offset = array(
		'L' => array( -1, 0 ),
		'R' => array( 1, 0 ),
		'U' => array( 0, 1 ),
		'D' => array( 0, -1 ),
	);

	// Positions of the head and tail.
	$head_x = 0;
	$head_y = 0;
	$tail_x = 0;
	$tail_y = 0;

	// Positions the tail has visited.
	$visited = array(
		array( 0, 0 ),
	);

	foreach ( $movements as $movement ) {
		$direction   = $movement[0];
		$steps       = $movement[1];
		$direction_x = $offset[ $direction ][0];
		$direction_y = $offset[ $direction ][1];

		// Loop through the steps.
		for ( $i = 0; $i < $steps; $i++ ) {
			// Update the position of the head.
			$head_x += $direction_x;
			$head_y += $direction_y;

			// While the head and tail are more than one step from each other, move the tail one closer to the head.
			while ( max( abs( $tail_x - $head_x ), abs( $tail_y - $head_y ) ) > 1 ) {
				// Head and tail are not in the same column.
				if ( abs( $tail_x - $head_x ) > 0 ) {
					// Move the tail one step closer to the head in the X direction.
					$tail_x += $head_x > $tail_x ? 1 : -1;
				}

				// Head and tail are not in the same row.
				if ( abs( $tail_y - $head_y ) > 0 ) {
					// Move the tail one step closer to the head in the Y direction.
					$tail_y += $head_y > $tail_y ? 1 : -1;
				}

				// Add position of the tail to the array of visited positions.
				$visited[] = array( $tail_x, $tail_y );
			}
		}
	}

	printf( 'Visited positions: %s', count( array_unique( $visited, SORT_REGULAR ) ) . PHP_EOL );
}

/**
 * Part 2: How many positions does the tail of the long rope visit at least once?
 *
 * @return void
 */
function part_2() {
	$movements = get_movements();

	$offset = array(
		'L' => array( -1, 0 ),
		'R' => array( 1, 0 ),
		'U' => array( 0, 1 ),
		'D' => array( 0, -1 ),
	);

	// Positions for the tail.
	$tail = array(
		array( 0, 0 ),
		array( 0, 0 ),
		array( 0, 0 ),
		array( 0, 0 ),
		array( 0, 0 ),
		array( 0, 0 ),
		array( 0, 0 ),
		array( 0, 0 ),
		array( 0, 0 ),
		array( 0, 0 ),
	);

	// Positions the end of the tail has visited.
	$visited = array();

	foreach ( $movements as $movement ) {
		$direction   = $movement[0];
		$steps       = $movement[1];
		$direction_x = $offset[ $direction ][0];
		$direction_y = $offset[ $direction ][1];

		// Loop through the steps.
		for ( $i = 0; $i < $steps; $i ++ ) {

			// Update the position of the first knot in the rope.
			$tail[0][0] += $direction_x;
			$tail[0][1] += $direction_y;

			// Now loop through the rest of the knots in the rope.
			for ( $j = 1; $j < 10; $j++ ) {
				// Previous positions of the current knot.
				$previous_x = $tail[ $j - 1 ][0];
				$previous_y = $tail[ $j - 1 ][1];

				// Current position of the current knot.
				$current_x = $tail[ $j ][0];
				$current_y = $tail[ $j ][1];

				// While the previous and current knots are more than one away from each other, move the current knot
				// one closer to the head.
				while ( max( abs( $current_x - $previous_x ), abs( $current_y - $previous_y ) ) > 1 ) {
					// Previous and current knots are not in the same column.
					if ( abs( $current_x - $previous_x ) > 0 ) {
						// Move the current knot one step closer to the previous knot in the X direction.
						$current_x += $previous_x > $current_x ? 1 : - 1;
					}

					// Previous and current knots are not in the same row.
					if ( abs( $current_y - $previous_y ) > 0 ) {
						// Move the current knot one step closer to the previous knot in the Y direction.
						$current_y += $previous_y > $current_y ? 1 : - 1;
					}

					// Update the position of the current knot in the rope
					$tail[ $j ] = array( $current_x, $current_y );
				}
			}

			// Add position of the tail to the array of visited positions.
			$visited[] = $tail[ count( $tail ) - 1 ];
		}
	}

	printf( 'Visited positions: %s', count( array_unique( $visited, SORT_REGULAR ) ) . PHP_EOL );
}

/**
 * Returns an array of movements from the input file.
 *
 * @return array
 */
function get_movements() {
	$data = file_get_contents( __DIR__ . '/data/day-09.txt' );

	$movements = explode( "\n", $data );

	return array_map(
		function ( $line ) {
			return explode( ' ', $line );
		},
		$movements
	);
}
