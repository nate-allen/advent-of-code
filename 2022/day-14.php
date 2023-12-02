<?php
/**
 * Day 14:
 */

$map    = array();
$max_x  = 0;
$max_y  = 0;
$height = 0;
$floor  = false; // part 1 has no floor, part 2 does.

/**
 * Part 1:
 *
 * @return void
 */
function part_1() {
	global $map;

	// keep track of time when this function is called
	$start = microtime(true);

	$total = 0;
	$map    = get_map();

	$status = 0;
	while ( $status >= 0 ) {
		$total++;
		$status = drop_sand( 500, 0 );
	}

	// echo the time it took to run this function
	echo 'Part 1: ' . ( $total - 1 ) . ' (' . ( microtime( true ) - $start ) . 's)' . PHP_EOL;
}

/**
 * Part 2:
 *
 * @return void
 */
function part_2() {
	global $map, $floor;

	// keep track of time when this function is called
	$start = microtime(true);

	$floor = false;
	$map   = get_map();

	// $status = 0;
	// while ( $status >= 0 ) {
	// 	$total++;
	// 	$status = drop_sand( 500, 0 );
	// }

	$total = countSand( 500, 0 );

	// Loop through the map and print out the map
	foreach( $map as $y => $row ) {
		foreach( $row as $x => $col ) {
			echo $col;
		}
		echo PHP_EOL;
	}

	echo 'Part 2: ' . ( $total - 1 ) . ' (' . ( microtime( true ) - $start ) . 's)' . PHP_EOL;
}

function get_map() {
	global $height, $floor, $max_y, $max_x;

	$lines = get_lines();

	$map   = array();
	$rocks = array();
	$min_x = 1000000;
	$min_y = 0;
	$max_y = 0;

	foreach( $lines as $line ) {
		$rock        = array();
		$coordinates = explode( ' ', str_replace( ' -> ', ' ', $line ) );

		foreach ( $coordinates as $coordinate ) {
			$rock[] = explode( ',', $coordinate );
		}

		$rocks[] = $rock;

		// Figure out the map size. There's probably a better way to do this.
		foreach ( $rocks as $rock ) {
			foreach ( $rock as $piece ) {
				$min_x = min( $min_x, $piece[0] );
				$max_y = max( $max_y, $piece[1] );
				$max_x = max( $max_x, $piece[0] );
			}
		}

		$height = $max_y;

		// Set up the map. There's probably a better way to do this too...
		for ( $y = $min_y; $y <= $max_y; $y ++ ) {
			$map[ $y ] = array();

			for ( $x = $min_x; $x <= $max_x; $x ++ ) {
				$map[ $y ][ $x ] = '.';
			}
		}
		
		// Add the rocks to the map.
		foreach ( $rocks as $rock ) {
			for ( $r = 0; $r < ( count( $rock ) - 1 ); $r ++ ) {
				$start_x = $rock[ $r ][0];
				$start_y = $rock[ $r ][1];

				$end_x = $rock[ ( $r + 1 ) ][0];
				$end_y = $rock[ ( $r + 1 ) ][1];

				if ( $start_x === $end_x ) {
					$current = min( $start_y, $end_y );
					$max     = max( $start_y, $end_y );

					while ( $current <= $max ) {
						$map[ $current ][ $start_x ] = '#';
						$current++;
					}
				} else {
					$current = min( $start_x, $end_x );
					$max     = max( $start_x, $end_x );

					while ( $current <= $max ) {
						$map[ $start_y ][ $current ] = '#';
						$current++;
					}
				}
			}
		}
	}

	if ( $floor ) {
		$map[] = array_fill( 0, count( $map[0] ), '.' );
		$map[] = array_fill( 0, count( $map[0] ), '#' );
	}

	// Sand origin
	$map[0][500] = '+';

	return $map;
}

function drop_sand( $x, $y, $sub = 0 ) {
	global $map, $height, $max_y;

	// Out of bound
	if ( ! isset( $map[ $y ] ) ) {
		return 0;
	}

	// Location not free
	if ( isset( $map[ $y ][ $x ] ) && ! in_array( $map[ $y ][ $x ], [ '.', '+' ] ) ) {
		if ( $sub ) {
			return 0;
		}

		return - 1;
	}

	// straight until rock or map border
	for ( ; $y < ( $height - 1 ); $y ++ ) {
		if ( isset( $map[ ( $y + 1 ) ][ $x ]) && in_array( $map[ ( $y + 1 ) ][ $x ], [ '.', '+' ] ) ) {
			continue;
		}
		break;
	}

	// trying left
	$left = drop_sand( $x - 1, $y + 1, 1 );

	if ( ( $y + 1 ) === $max_y ) {
		$left = '#';
	}

	if ( -1 === $left ) {
		return -1;
	}
	if ( 1 === $left ) {
		return 1;
	}

	// trying right
	$right = drop_sand( $x + 1, $y + 1, 1 );

	if ( ( $y + 1 ) === $max_y ) {
		$right = '#';
	}

	if ( -1 === $right ) {
		return - 1;
	}
	if ( 1 === $right ) {
		return 1;
	}

	$map[ $y ][ $x ] = 'o';

	return 1;
}

function countSand( $currentX, $currentY, &$sand = 0 ) {
	// Declare global variables $map, $max_y, and $floor
	global $map, $max_y, $floor;

	// If the current y-coordinate is greater than the maximum y-coordinate
	// and `$floor` has not been set yet, set `$floor` to the current number of sand grains
	if ( $currentY > $max_y && ! $floor ) {
		$floor = $sand;
	}

	// If the element in `$map` at the coordinates `($currentX, $currentY + 1)` is a dot ('.'),
	// call `countSand` again with the coordinates shifted down by 1
	if ( $map[ $currentY + 1 ][ $currentX ] == '.' ) {
		countSand( $currentX, $currentY + 1, $sand );
	}

	// If the element in `$map` at the coordinates `($currentX - 1, $currentY + 1)` is a dot ('.'),
	// call `countSand` again with the coordinates shifted down and left by 1
	if ( $map[ $currentY + 1 ][ $currentX - 1 ] == '.' ) {
		countSand( $currentX - 1, $currentY + 1, $sand );
	}

	// If the element in `$map` at the coordinates `($currentX + 1, $currentY + 1)` is a dot ('.'),
	// call `countSand` again with the coordinates shifted down and right by 1
	if ( $map[ $currentY + 1 ][ $currentX + 1 ] == '.' ) {
		countSand( $currentX + 1, $currentY + 1, $sand );
	}

	// Set the element in `$map` at the coordinates `($currentX, $currentY)` to 'o'
	$map[ $currentY ][ $currentX ] = 'o';

	// Increment the number of sand grains and return it
	return ++$sand;
}

/**
 * Returns an array of coordinates from the input file.
 *
 * @return array
 */
function get_lines(): array {
	$data  = explode( PHP_EOL, file_get_contents( __DIR__ . '/data/day-14.txt' ) );

	return array_map(
		function( $line ) {
			return trim( $line );
		},
		$data
	);
}