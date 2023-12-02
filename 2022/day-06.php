<?php
/**
 * Day 6: Tuning Trouble
 */

/**
 * Part 1: Find the start-of-packet marker, which is 4 unique characters.
 *
 * @return void
 */
function part_1() {
	printf( 'The packet starts at: %s', get_marker_position( 4 ) );
}

/**
 * Part 2: Find the start-of-message marker, which is 14 unique characters.
 *
 * @return void
 */
function part_2() {
	printf( 'The message starts at: %s', get_marker_position( 14 ) );
}

/**
 * Returns the position of the marker.
 *
 * @param int $marker_length The number of unique characters in the marker.
 *
 * @return int
 */
function get_marker_position( int $marker_length ) {
	$signal        = file_get_contents( __DIR__ . '/data/day-06.txt' );
	$signal_length = strlen( $signal );
	$buffer        = '';
	$marker        = - 1;

	for ( $i = 0; $i < $signal_length; $i ++ ) {
		$buffer .= $signal[ $i ];

		// Don't let the buffer get larger than the marker length.
		if ( strlen( $buffer ) > $marker_length ) {
			$buffer = substr( $buffer, 1 );
		}

		// If all the characters in the buffer are different, marker is found!
		if ( count( array_unique( str_split( $buffer ) ) ) === $marker_length ) {
			$marker = $i + 1;
			break;
		}
	}

	return $marker;
}
