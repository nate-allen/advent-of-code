<?php

namespace AdventOfCode\Year2019;

include_once 'lib/IntcodeComputer.php';

/**
 * Day 17: Set and Forget
 */
class Day17 {
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
	 * Part 1: Calibrate the cameras by finding scaffold intersections and summing their alignment parameters.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$computer = new IntcodeComputer( $this->data );
		$map      = [];
		$line     = '';

		// Run the Intcode computer until it halts
		while ( true ) {
			$output = $computer->run_until_output();
			if ( $output === null ) {
				break;
			}
			$char = chr( $output );
			if ( $char === "\n" ) {
				if ( $line !== '' ) {
					// Split the line into individual characters and add to our grid.
					$map[] = str_split( $line );
					$line  = '';
				}
			} else {
				$line .= $char;
			}
		}

		$alignment_sum = 0;
		$height        = count( $map );

		// Loop starting at 1 and ending one before the last row because we need to check neighbors.
		for ( $y = 1; $y < $height - 1; $y ++ ) {
			$width = count( $map[ $y ] );
			for ( $x = 1; $x < $width - 1; $x ++ ) {
				// Check if current cell and its four adjacent cells are scaffold.
				if (
					$map[ $y ][ $x ] === '#' &&
					$map[ $y - 1 ][ $x ] === '#' &&
					$map[ $y + 1 ][ $x ] === '#' &&
					$map[ $y ][ $x - 1 ] === '#' &&
					$map[ $y ][ $x + 1 ] === '#'
				) {
					$alignment_sum += $x * $y;
				}
			}
		}

		return $alignment_sum;
	}

	/**
	 * Part 2: Wake up the vacuum robot with new movement routines so that it covers all scaffold parts.
	 *         Return the amount of dust collected by the robot.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		// Build the scaffold grid from the Intcode output
		$computer = new IntcodeComputer( $this->data );
		$grid     = [];
		$line     = '';

		while ( true ) {
			$output = $computer->run_until_output();
			if ( $output === null ) {
				break;
			}
			$char = chr( $output );
			if ( $char === "\n" ) {
				if ( $line !== '' ) {
					$grid[] = str_split( $line );
					$line   = '';
				}
			} else {
				$line .= $char;
			}
		}

		// Locate the robot's starting position and orientation
		$start_x   = 0;
		$start_y   = 0;
		$start_dir = '';
		for ( $y = 0, $y_max = count( $grid ); $y < $y_max; $y ++ ) {
			for ( $x = 0, $x_max = count( $grid[ $y ] ); $x < $x_max; $x ++ ) {
				if ( in_array( $grid[ $y ][ $x ], [ '^', 'v', '<', '>' ], true ) ) {
					$start_x   = $x;
					$start_y   = $y;
					$start_dir = $grid[ $y ][ $x ];
					break 2;
				}
			}
		}

		// Compute the full movement path over the scaffold
		// The path will be an array of tokens, e.g. ["L", "8", "R", "4", ...].
		$path = $this->compute_robot_path( $grid, $start_x, $start_y, $start_dir );

		// Decompose the path into routines
		$result = $this->search_routines( $path, [], [] );
		if ( $result === null ) {
			throw new \Exception( "Failed to decompose path into routines" );
		}

		// $result['main'] is an array of letters; $result['functions'] is an associative array:
		// e.g., [ 'A' => ["R","8","R","8"], 'B' => ["R","4","R","4","R","8"], ... ]
		$main_routine = implode( ',', $result['main'] ) . "\n";
		$function_a   = implode( ',', $result['functions']['A'] ) . "\n";
		$function_b   = implode( ',', $result['functions']['B'] ) . "\n";
		$function_c   = implode( ',', $result['functions']['C'] ) . "\n";

		// Feed the routines into the computer and run
		// Wake up the robot.
		$this->data[0] = 2;
		$computer      = new IntcodeComputer( $this->data );
		$inputs        = array_merge(
			$this->string_to_ascii( $main_routine ),
			$this->string_to_ascii( $function_a ),
			$this->string_to_ascii( $function_b ),
			$this->string_to_ascii( $function_c ),
			$this->string_to_ascii( "n\n" )  // Disable continuous video feed.
		);
		foreach ( $inputs as $input ) {
			$computer->add_input( $input );
		}

		// Run to completion and capture the dust output
		$last_output = 0;
		while ( ! $computer->has_halted() ) {
			$output = $computer->run_until_output();
			if ( $output !== null ) {
				$last_output = $output;
			}
		}

		return $last_output;
	}

	/**
	 * Simulate the robot's movement over the scaffold.
	 *
	 * Starting from ($start_x, $start_y) with initial direction $start_dir,
	 * follow the continuous scaffold path. At each step, try turning left;
	 * if that isn't possible, try right. For each turn, count the number of
	 * steps the robot can move forward. Returns the full movement path as an
	 * array of tokens (alternating turn commands and step counts as strings).
	 *
	 * @param array $grid The 2D scaffold grid.
	 * @param int $start_x Starting x-coordinate.
	 * @param int $start_y Starting y-coordinate.
	 * @param string $start_dir Starting direction (one of '^', 'v', '<', '>').
	 *
	 * @return array The movement path, e.g. ["L", "8", "R", "4", ...].
	 */
	private function compute_robot_path( array $grid, int $start_x, int $start_y, string $start_dir ): array {
		$path = [];
		// Define movement vectors.
		$dir_map = [
			'^' => [ 0, - 1 ],
			'v' => [ 0, 1 ],
			'<' => [ - 1, 0 ],
			'>' => [ 1, 0 ],
		];
		// Define turning mappings.
		$left_turn_map  = [
			'^' => '<',
			'<' => 'v',
			'v' => '>',
			'>' => '^',
		];
		$right_turn_map = [
			'^' => '>',
			'>' => 'v',
			'v' => '<',
			'<' => '^',
		];

		$x           = $start_x;
		$y           = $start_y;
		$current_dir = $start_dir;

		while ( true ) {
			// Try turning left.
			$ldir = $left_turn_map[ $current_dir ];
			$ldx  = $dir_map[ $ldir ][0];
			$ldy  = $dir_map[ $ldir ][1];
			if ( isset( $grid[ $y + $ldy ][ $x + $ldx ] ) && $grid[ $y + $ldy ][ $x + $ldx ] === '#' ) {
				$path[]      = 'L';
				$current_dir = $ldir;
			} else {
				// If left turn not possible, try right turn.
				$rdir = $right_turn_map[ $current_dir ];
				$rdx  = $dir_map[ $rdir ][0];
				$rdy  = $dir_map[ $rdir ][1];
				if ( isset( $grid[ $y + $rdy ][ $x + $rdx ] ) && $grid[ $y + $rdy ][ $x + $rdx ] === '#' ) {
					$path[]      = 'R';
					$current_dir = $rdir;
				} else {
					// No further move possible.
					break;
				}
			}

			// Move forward as far as possible.
			$steps = 0;
			$dx    = $dir_map[ $current_dir ][0];
			$dy    = $dir_map[ $current_dir ][1];
			while ( isset( $grid[ $y + $dy ][ $x + $dx ] ) && $grid[ $y + $dy ][ $x + $dx ] === '#' ) {
				$x += $dx;
				$y += $dy;
				$steps ++;
			}
			if ( $steps === 0 ) {
				break;
			}
			$path[] = (string) $steps;
		}

		return $path;
	}

	/**
	 * Recursively decomposes the movement path into a main routine and up to three functions.
	 *
	 * Each function (A, B, or C) is defined as a sequence of tokens (e.g. ["R", "8", "R", "8"])
	 * whose comma-separated string representation must not exceed 20 characters.
	 * The main routine is represented as an array of letters (each one "A", "B", or "C").
	 *
	 * @param array $seq The remaining movement path tokens.
	 * @param array $functions Associative array of functions defined so far.
	 * @param array $main The main routine built so far.
	 *
	 * @return array|null An array with keys 'main' and 'functions' if a valid decomposition is found; null otherwise.
	 */
	private function search_routines( array $seq, array $functions, array $main ): ?array {
		if ( empty( $seq ) ) {
			return [ 'main' => $main, 'functions' => $functions ];
		}

		// Try matching an existing function.
		foreach ( $functions as $letter => $func ) {
			$len = count( $func );
			if ( array_slice( $seq, 0, $len ) === $func ) {
				$result = $this->search_routines( array_slice( $seq, $len ), $functions, array_merge( $main, [ $letter ] ) );
				if ( $result !== null ) {
					return $result;
				}
			}
		}

		// If fewer than 3 functions are defined, try to define a new function.
		if ( count( $functions ) < 3 ) {
			$new_letter = [ 'A', 'B', 'C' ][ count( $functions ) ];
			// Try all candidate lengths for the new function.
			for ( $i = 1; $i <= count( $seq ); $i ++ ) {
				$candidate     = array_slice( $seq, 0, $i );
				$candidate_str = implode( ',', $candidate );
				if ( strlen( $candidate_str ) > 20 ) {
					break;
				}
				$new_functions                = $functions;
				$new_functions[ $new_letter ] = $candidate;
				$result                       = $this->search_routines( array_slice( $seq, $i ), $new_functions, array_merge( $main, [ $new_letter ] ) );
				if ( $result !== null ) {
					return $result;
				}
			}
		}

		return null;
	}

	/**
	 * Converts a string into an array of its ASCII codes.
	 *
	 * @param string $input The input string.
	 *
	 * @return array The array of ASCII code values.
	 */
	private function string_to_ascii( string $input ): array {
		return array_map( 'ord', str_split( $input ) );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @return array
	 */
	private function parse_data(): array {
		return array_map( 'intval', explode( ",", trim( file_get_contents( __DIR__ . '/data/day-17.txt' ) ) ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 */
function run_part( int $part ): void {
	$start  = microtime( true );
	$day17  = new Day17( $part );
	$result = $day17->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => 4112,
		2 => 0,
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
