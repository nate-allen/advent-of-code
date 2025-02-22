<?php

namespace AdventOfCode\Year2019;

include_once 'lib/IntcodeComputer.php';

/**
 * Day 13: Care Package
 */
class Day13 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var integer
	 */
	private int $part;

	/**
	 * Parsed data from the input file.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( int $part ) {
		$this->part = $part;
		$this->data = $this->parse_data();
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return integer
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Run the Intcode arcade game and count block tiles.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$computer = new IntcodeComputer( $this->data );
		$tiles    = [];

		while ( ! $computer->has_halted() ) {
			$x       = $computer->run_until_output();
			$y       = $computer->run_until_output();
			$tile_id = $computer->run_until_output();

			// Ensure all outputs were received
			if ( $x === null || $y === null || $tile_id === null ) {
				break;
			}

			// Store the tile ID at (x, y)
			$tiles["$x,$y"] = $tile_id;
		}

		// Count the number of block tiles (tile ID = 2)
		return count( array_filter( $tiles, fn( $tile ) => $tile === 2 ) );
	}

	/**
	 * Part 2: Play the game and return the final score.
	 *
	 * The game is free to play (memory[0] = 2). The paddle moves based on the ball position,
	 * and the score updates when outputs specify X=-1, Y=0.
	 *
	 * @return int The final score after breaking all blocks.
	 */
	private function solve_part_2(): int {
		// Set the game to free play mode.
		$this->data[0] = 2;
		$computer      = new IntcodeComputer( $this->data );

		$score    = 0;
		$ball_x   = 0;
		$paddle_x = 0;

		while ( ! $computer->has_halted() ) {
			$x = $computer->run_until_output();

			// If run_until_output returns null, the computer is waiting for input.
			if ( $x === null ) {
				// Determine joystick input based on ball and paddle positions.
				$joystick = 0;
				if ( $ball_x < $paddle_x ) {
					$joystick = - 1;
				} elseif ( $ball_x > $paddle_x ) {
					$joystick = 1;
				}
				$computer->add_input( $joystick );
				continue;
			}

			// Read y and tile_id
			$y       = $computer->run_until_output();
			$tile_id = $computer->run_until_output();

			// When x == -1 and y == 0, tile_id is actually the score.
			if ( $x === - 1 && $y === 0 ) {
				$score = $tile_id;
			} else {
				// Update paddle and ball positions as needed.
				if ( $tile_id === 3 ) {
					$paddle_x = $x;
				} elseif ( $tile_id === 4 ) {
					$ball_x = $x;
				}
			}
		}

		return $score;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @return array
	 */
	private function parse_data(): array {
		return array_map( 'intval', explode( ",", trim( file_get_contents( __DIR__ . '/data/day-13.txt' ) ) ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 */
function run_part( int $part): void {
	$start  = microtime( true );
	$day13  = new Day13( $part );
	$result = $day13->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => 306,
		2 => 15328,
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $expected_values[ $part ] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$part = (int) trim( readline( 'Which part do you want to run? (1/2): ' ) );
	if ( ! in_array( $part, [ 1, 2 ], true ) ) {
		echo 'Invalid part. Please enter 1 or 2.' . PHP_EOL;
		continue;
	}

	run_part( $part );
	break;
}
