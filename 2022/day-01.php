<?php
/**
 * Day 1: Calorie Counting
 */

/**
 * Part 1: Find the Elf with the most calories and the total that elf is carrying.
 *
 * @return void
 */
function part_1() {
	// Keep track of who has the most calories.
	$most_calories = 0;

	// Loop through each elf and calculate their calories.
	foreach ( get_elves() as $elf ) {
		// Get the items by new line.
		$items = explode( "\n", $elf );

		$calories = array_sum( $items );

		// If this elf has more calories than the most calories, update the most calories.
		if ( $calories > $most_calories ) {
			$most_calories = $calories;
		}
	}

	echo $most_calories . PHP_EOL;
}

/**
 * Part 2: Find the top 3 elves with the most calories and the total that they are carrying.
 *
 * @return void
 */
function part_2() {
	$elf_calories = array();

	// Loop through each elf and calculate their calories.
	foreach ( get_elves() as $elf ) {
		// Get the items by new line.
		$items = explode( "\n", $elf );

		// Use array_sum to get total calories.
		$elf_calories[] = array_sum( $items );
	}

	// Sort the elves by calories.
	sort( $elf_calories );

	// Get the total of the top 3 elves.
	echo array_sum( array_slice( $elf_calories, -3 ) ) . PHP_EOL;
}

/**
 * Returns an array of elves and their calories.
 *
 * It gets a list of calories from the data file, and then splits them by blank lines to get each elf.
 *
 * @return array An array of elves with their calories.
 */
function get_elves() {
	// Get calories from file.
	$calories = trim( file_get_contents( __DIR__ . '/data/day-01.txt' ) );

	// Split by blank lines.
	return preg_split( '/\R\R/', $calories );
}
