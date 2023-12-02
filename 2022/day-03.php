<?php
/**
 * Day 3: Rucksack Reorganization
 */

/**
 * Part 1: Priority based on the item that appears in both halves of the string.
 *
 * @return void
 */
function part_1() {
	$items = get_items();
	$total = 0;

	foreach ( $items as $item ) {
		// Determine which character in the array is duplicated.
		$character = get_duplicate_character( $item );

		$total += get_priority( $character );
	}

	echo $total . PHP_EOL;
}

/**
 * Part 2: Priority based on item appearing in groups of 3 arrays.
 *
 * @return void
 */
function part_2() {
	$items = get_items();
	$total = 0;

	// Create chunks of 3 items.
	$chunks = array_chunk( $items, 3 );

	// Loop through each chunk.
	foreach ( $chunks as $chunk ) {
		$total += get_priority( get_common_character( $chunk ) );
	}

	echo $total . PHP_EOL;
}

/**
 * Returns an array of random strings representing items in the rucksack.
 *
 * @return array
 */
function get_items() {
	$strategy = trim( file_get_contents( __DIR__ . '/data/day-03.txt' ) );

	return explode( "\n", $strategy );
}

/**
 * Returns the priority of an item based on the character that appears in the string.
 *
 * @param string $letter The letter to get the priority for.
 *
 * @return int
 */
function get_priority( $letter ) {
	$priority = array_merge( range('a', 'z'), range('A', 'Z') );

	return (int) array_search( $letter, $priority ) + 1;
}

/**
 * Returns the character that appears in both halves of the string.
 *
 * @param string $string The string to check.
 *
 * @return string
 */
function get_duplicate_character( $string ) {
	$characters = str_split( $string, strlen( $string ) / 2 );

	$duplicate = array_unique( array_intersect( str_split( $characters[0] ), str_split( $characters[1] ) ) );

	return reset( $duplicate );
}

/**
 * Returns the common character between the group of arrays.
 *
 * @param array $chunk The chunk of arrays.
 *
 * @return string The common character.
 */
function get_common_character( $chunk ) {
	$common = array_intersect( str_split( $chunk[0] ), str_split( $chunk[1] ), str_split( $chunk[2] ) );

	return reset( $common );
}