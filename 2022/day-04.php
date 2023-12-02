<?php
/**
 * Day 4: Camp Cleanup
 */

/**
 * Part 1: In how many assignment pairs does one range fully contain the other?
 *
 * @return void
 */
function part_1() {
	$assignments = get_assignments();
	$total = 0;

	foreach ( $assignments as $assignment ) {
		$elf1 = explode( '-', explode( ',', $assignment )[0] );
		$elf2 = explode( '-', explode( ',', $assignment )[1] );

		if (
			$elf1[0] >= $elf2[0] && $elf1[1] <= $elf2[1] ||
			$elf2[0] >= $elf1[0] && $elf2[1] <= $elf1[1]
		) {
			$total++;
		}
	}

	echo $total . PHP_EOL;
}

/**
 * Part 2: In how many assignment pairs do the ranges overlap?
 *
 * @return void
 */
function part_2() {
	$assignments = get_assignments();
	$total = 0;

	foreach ( $assignments as $assignment ) {
		$elf1 = explode( '-', explode( ',', $assignment )[0] );
		$elf2 = explode( '-', explode( ',', $assignment )[1] );

		if ( $elf1[1] >= $elf2[0] && $elf1[0] <= $elf2[1] ) {
			$total++;
		}
	}

	echo $total . PHP_EOL;
}

/**
 * Returns an array of random strings representing assignment areas.
 *
 * @return array
 */
function get_assignments() {
	$data = trim( file_get_contents( __DIR__ . '/data/day-04.txt' ) );

	return explode( "\n", $data );
}
