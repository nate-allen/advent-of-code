<?php
/**
 * Day 12: Hill Climbing Algorithm
 */

/**
 * Part 1: Get the fewest steps from the current position to the final position
 *
 * @return void
 */
function part_1() {
	$map = get_heatmap();

	$starting = array( 0, 0 );
	$ending   = array( 0, 0 );

	// Get the starting position, and also change "S" and "E" to numbers.
	foreach ( $map as $y => $row ) {
		foreach ( $row as $x => $value ) {
			if ( ord( 'S' ) === $value ) {
				$starting        = array( $x, $y );
				$map[ $y ][ $x ] = ord( 'a' );
			}
			if ( ord( 'E' ) === $value ) {
				$ending          = array( $x, $y );
				$map[ $y ][ $x ] = ord( 'z' );
			}
		}
	}

	$queue   = array(
		array( $starting ),
	);
	$visited = array(
		array( $starting ),
	);

	while ( count( $queue ) > 0 ) {
		$path     = array_shift( $queue );
		$position = $path[ count( $path ) - 1 ];

		// Get the neighbors.
		$neighbors = array(
			array( $position[0] + 1, $position[1] ),
			array( $position[0], $position[1] + 1 ),
			array( $position[0] - 1, $position[1] ),
			array( $position[0], $position[1] - 1 ),
		);

		// Loop over the neighbors and add them to the queue if they are valid.
		foreach ( $neighbors as $neighbor ) {

			// Neighbor is not in bounds?
			if ( $neighbor[0] < 0 || $neighbor[0] >= count( $map[0] ) || $neighbor[1] < 0 || $neighbor[1] >= count( $map ) ) {
				continue;
			}

			// Neighbor has already been visited?
			if ( in_array( $neighbor, $visited, true ) ) {
				continue;
			}

			// Check neighbor's elevation.
			if ( $map[ $neighbor[1] ][ $neighbor[0] ] - $map[ $position[1] ][ $position[0] ] > 1 ) {
				continue;
			}

			// We found the end!
			if ( $ending === $neighbor ) {
				printf( 'It takes %s steps to get to the top.' . PHP_EOL, count( $path ) );
				exit;
			}

			// Add the neighbor to the queue and list of visited positions.
			$visited[] = $neighbor;
			$queue[]   = array_merge( $path, array( $neighbor ) );
		}
	}
}

/**
 * Part 2: Get the fewest steps from the lowest position to the highest position.
 *
 * @return void
 */
function part_2() {
	$map = get_heatmap();

	$starting = array( 0, 0 );

	// Get the starting position, and also change "S" and "z" to numbers.
	foreach ( $map as $y => $row ) {
		foreach ( $row as $x => $value ) {
			if ( ord( 'S' ) === $value ) {
				$map[ $y ][ $x ] = ord( 'a' );
			}
			if ( ord( 'E' ) === $value ) {
				$starting        = array( $x, $y );
				$map[ $y ][ $x ] = ord( 'z' );
			}
		}
	}

	$queue   = array(
		array( $starting ),
	);
	$visited = array(
		array( $starting ),
	);

	while ( count( $queue ) > 0 ) {
		$path     = array_shift( $queue );
		$position = $path[ count( $path ) - 1 ];

		// Get the neighbors.
		$neighbors = array(
			array( $position[0] + 1, $position[1] ),
			array( $position[0], $position[1] + 1 ),
			array( $position[0] - 1, $position[1] ),
			array( $position[0], $position[1] - 1 ),
		);

		// Loop over the neighbors and add them to the queue if they are valid.
		foreach ( $neighbors as $neighbor ) {

			// Neighbor is not in bounds?
			if ( $neighbor[0] < 0 || $neighbor[0] >= count( $map[0] ) || $neighbor[1] < 0 || $neighbor[1] >= count( $map ) ) {
				continue;
			}

			// Neighbor has already been visited?
			if ( in_array( $neighbor, $visited, true ) ) {
				continue;
			}

			// Check neighbor's elevation.
			if ( $map[ $position[1] ][ $position[0] ] - $map[ $neighbor[1] ][ $neighbor[0] ] > 1 ) {
				continue;
			}

			// We found the end!
			if ( ord( 'a' ) === $map[ $neighbor[1] ][ $neighbor[0] ] ) {
				printf( 'The shortest path takes %s steps.' . PHP_EOL, count( $path ) );
				exit;
			}

			// Add the neighbor to the queue and list of visited positions.
			$visited[] = $neighbor;
			$queue[]   = array_merge( $path, array( $neighbor ) );
		}
	}
}

/**
 * Returns a heatmap from the input file.
 *
 * @return array
 */
function get_heatmap() {
	$line = file_get_contents( __DIR__ . '/data/day-12.txt' );
	$rows = explode( "\n", $line );

	// Convert rows of letters into numbers.
	return array_map(
		function ( $row ) {
			return array_map( 'ord', str_split( $row ) );
		},
		$rows
	);
}
