<?php
/**
 * Day 13: Distress Signal
 */

/**
 * Part 1: Determine which pairs are already in the right order. What is the sum of the indices of those pairs?
 *
 * @return void
 */
function part_1() {
	$lines = get_lines();
	$index = 0;
	$sum   = 0;

	while ( $lines ) {
		$left  = array_shift( $lines );
		$right = array_shift( $lines );
		array_shift( $lines ); // blank line

		$index++;

		if ( compare( $left, $right ) < 1 ) {
			$sum += $index;
		}
	}

	printf( 'Sum of the indices: %d' . PHP_EOL, $sum );
}

/**
 * Part 2: What is the decoder key for the distress signal?
 *
 * @return void
 */
function part_2() {
	$lines = get_lines();

	// Remove empty lines.
	$lines = array_filter(
		$lines,
		function( $line ) {
			return ! empty( trim( $line ) );
		}
	);

	// Add the dividers.
	$lines[] = '[[2]]';
	$lines[] = '[[6]]';

	// Sort them using our compare function.
	usort( $lines, 'compare' );

	// Get the location of the divider packets.
	$divider_1 = array_search( '[[2]]', $lines ) + 1;
	$divider_2 = array_search( '[[6]]', $lines ) + 1;

	// Get the decoder by multiplying them together.
	$decoder = $divider_1 * $divider_2;

	// Print the answer.
	printf( 'The decoder key is: %s' . PHP_EOL, $decoder );
}

/**
 * Compares two values to see if left is less than the right.
 *
 * @param $left  string The left side.
 * @param $right string The right side.
 *
 * @return bool Is left is less than right.
 */
function compare( $left, $right ) {
	$left  = json_decode( $left );
	$right = json_decode( $right );

	// If they are both ints, compare them.
	if ( is_int( $left ) && is_int( $right ) ) {

		// If they are equal, return 0.
		if ( $left === $right ) {
			return 0;
		}

		// If left is less than right, return -1. Otherwise, return 1.
		return $left < $right ? -1 : 1;
	}

	// At this point, we know that at least one of them is an int.

	// If left is an int, convert it to an array.
	if ( is_int( $left ) ) {
		$left = array( $left );
	}

	// If right is an int, convert it to an array.
	if ( is_int( $right ) ) {
		$right = array( $right );
	}

	// Loop until one or both arrays are empty.
	while ( ! empty( $left ) && ! empty( $right ) ) {
		$l = array_shift( $left );
		$r = array_shift( $right );

		// Convert the values to json.
		$l = json_encode( $l );
		$r = json_encode( $r );

		$result = compare( $l, $r );

		// If the result is 1 or -1, return it. Otherwise, continue.
		if ( $result ) {
			return $result;
		}
	}

	// If both arrays are empty, return 0.
	if ( empty( $left ) && empty( $right ) ) {
		return 0;
	}

	// Left is less than right, return -1. Otherwise, return 1.
	return count( $left ) < count( $right ) ? -1 : 1;
}

/**
 * Returns an array of data from the input file.
 *
 * @return array
 */
function get_lines() {
	$lines  = file_get_contents( __DIR__ . '/data/day-13.txt' );

	return explode( PHP_EOL, $lines );
}