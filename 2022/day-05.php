<?php
/**
 * Day 5: Supply Stacks
 */

/**
 * Part 1: After crates are moved one at a time, what crate ends up on top of each stack?
 *
 * @return void
 */
function part_1() {
	$data = get_data();

	foreach ( $data['instructions'] as $item ) {
		preg_match( '#^move (\d+) from (\d+) to (\d+)#', $item, $matches );

		$from = (int) $matches[2];
		$to   = (int) $matches[3];
		$move = (int) $matches[1];

		while ( $move > 0 ) {
			$data['crates'][ $to ][] = array_pop( $data['crates'][ $from ] );
			$move--;
		}
	}

	$result = array_reduce(
		$data['crates'],
		function ( $carry, $item ) {
			return $carry . str_replace( [ '[', ']' ], '', end( $item ) );
		}
	);

	echo $result . PHP_EOL;
}

/**
 * Part 2: After crates are moved multiples at a time, what crate ends up on top of each stack?
 *
 * @return void
 */
function part_2() {
	$data = get_data();

	foreach ( $data['instructions'] as $item ) {
		preg_match( '#^move (\d+) from (\d+) to (\d+)#', $item, $matches );

		$from = (int) $matches[2];
		$to   = (int) $matches[3];
		$move = (int) $matches[1];

		$stack = [];
		while ( $move > 0 ) {
			$stack[] = array_pop( $data['crates'][ $from ] );
			$move--;
		}

		$stack = array_reverse( $stack );

		foreach ( $stack as $item2 ) {
			$data['crates'][ $to ][] = $item2;
		}
	}

	$result = array_reduce(
		$data['crates'],
		function ( $carry, $item ) {
			return $carry . str_replace( [ '[', ']' ], '', end( $item ) );
		}
	);

	echo $result . PHP_EOL;
}

/**
 * Returns an array of crates and instructions.
 *
 * @return array
 */
function get_data() {
	$data  = file_get_contents( __DIR__ . '/data/day-05.txt' );
	$lines = explode( "\n", $data );

	$map = [
		'crates'       => [],
		'instructions' => [],
	];

	foreach ( $lines as $line ) {
		// Line contains a crate.
		if ( preg_match( '/\[([A-Z])\]/', $line ) ) {
			// Split by 4 characters.
			$positions = str_split( $line, 4 );
			$rows      = [];

			foreach ( $positions as $position ) {
				$rows[] = trim( $position );
			}

			foreach ( $rows as $index => $crate ) {
				$map['crates'][ $index + 1 ] ??= [];

				if ( empty( $crate ) ) {
					continue;
				}

				array_unshift( $map['crates'][ $index + 1 ], $crate );
			}
		}

		// Line starts with "move"
		if ( preg_match( '/^move/', $line ) ) {
			$map['instructions'][] = $line;
		}
	}

	return $map;
}
