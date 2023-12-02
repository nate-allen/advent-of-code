<?php
/**
 * Day 10: Cathode-Ray Tube
 */

/**
 * Part 1: Find the signal strength during the 20th, 60th, 100th, 140th, 180th, and 220th cycles.
 *         What is the sum of these six signal strengths?
 *
 * @return void
 */
function part_1() {
	// Get the instructions.
	$lines = get_instructions();

	// X register, number of cycles, and signal strengths.
	$register = 1;
	$cycles   = 1;
	$signals  = array();

	foreach ( $lines as $line ) {
		$line = explode( ' ', $line );

		// All commands take at least one cycle to complete.
		process_cycle( $cycles, $signals, $register );

		// addx takes 2 cycles to complete, and the register is increased by the value.
		if ( 'addx' === $line[0] ) {
			process_cycle( $cycles, $signals, $register );
			$register += intval( $line[1] );
		}
	}

	printf( 'Sum of the signal strengths: %s', array_sum( $signals ) . PHP_EOL );
}

/**
 * Processes the cycle.
 *
 * @param int   $cycles   The number of cycles.
 * @param array $signals  Array of signals strengths at certain points.
 * @param int   $register The register.
 *
 * @return void
 */
function process_cycle( int &$cycles, array &$signals, int $register ) {
	// Find the signal strength during the 20th, 60th, 100th, 140th, 180th, and 220th cycles.

	// 40 goes into 20 once and remainder is 20.
	// 40 goes into 60 once and remainder is 20.
	// 40 goes into 100 twice and remainder is 20, etc
	if ( 20 === $cycles % 40 ) {
		$signals[] = $cycles * $register;
	}

	$cycles++;
}

/**
 * Part 2: Render the image given by your program. What eight capital letters appear on your CRT?
 *
 * @return void
 */
function part_2() {
	// Get the instructions.
	$instructions = get_instructions();

	// Sprite position, number of cycles, and the screen.
	$sprite = 1;
	$cycles = 0;
	$screen = '';

	foreach ( $instructions as $instruction ) {
		$instruction = explode( ' ', $instruction );

		// Draw the sprite on the screen.
		draw_sprite( $cycles, $screen, $sprite );

		// If the instruction is "addx", draw the sprite again and update the sprite position.
		if ( 'addx' === $instruction[0] ) {
			draw_sprite( $cycles, $screen, $sprite );
			$sprite += intval( $instruction[1] );
		}
	}

	echo $screen;
}

/**
 * Logic for drawing the sprite on the screen.
 *
 * @param $screen string The screen.
 * @param $cycles int    The number of cycles.
 * @param $sprite int    The position of the sprite.
 *
 * @return void
 */
function draw_sprite( int &$cycles, string &$screen, int $sprite ) {
	// Get the position. The lines are 40 pixels long.
	$position = $cycles % 40;

	// If the difference between the sprite and position is 0, add a ██ character.
	// Otherwise, add a ██ character.
	if ( abs( $sprite - $position ) <= 1 ) {
		$screen .= '██';
	} else {
		$screen .= '░░';
	}

	// Increment the cycle.
	$cycles++;

	// If we're at the end of the line, add a newline.
	if ( 0 === $cycles % 40 ) {
		$screen .= PHP_EOL;
	}
}

/**
 * Returns an array of instructions from the input file.
 *
 * @return array
 */
function get_instructions() {
	$line = file_get_contents( __DIR__ . '/data/day-10.txt' );

	return explode( "\n", $line );
}