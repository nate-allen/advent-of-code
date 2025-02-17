<?php

namespace AdventOfCode\Year2019;

include_once 'lib/HullPaintingRobot.php';
include_once 'lib/IntcodeComputer.php';

/**
 * Day 11: Space Police
 */
class Day11 {
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
	 * @return integer|string
	 */
	public function run(): int|string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Solves Part 1: Count the number of unique panels that the robot paints at least once.
	 *
	 * The robot starts on a black panel, follows the Intcode instructions to move, paint,
	 * and turn accordingly, and the number of distinct panels it paints is returned.
	 *
	 * @return int The number of panels painted at least once.
	 */
	private function solve_part_1(): int {
		$robot = new HullPaintingRobot($this->data);
		$robot->run();
		return $robot->count_painted_panels();
	}

	/**
	 * Solves Part 2: Display the registration identifier painted by the robot.
	 *
	 * The robot starts on a white panel, follows the Intcode instructions, and paints
	 * a pattern on the grid. The final painting is displayed as ASCII output.
	 *
	 * @return string The final painted registration identifier.
	 */
	private function solve_part_2(): string {
		$robot = new HullPaintingRobot($this->data, true);
		$robot->run();
		return $robot->get_painting();
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @return array
	 */
	private function parse_data(): array {
		return array_map( 'intval', explode( ",", trim( file_get_contents( __DIR__ . '/data/day-11.txt' ) ) ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 */
function run_part( int $part ): void {
	$start  = microtime( true );
	$day11  = new Day11( $part );
	$result = $day11->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => 2172,
		2 => "\n   ## #### #    #### ####  ##  #  # ###\n    # #    #    #    #    #  # #  # #  #\n    # ###  #    ###  ###  #    #### #  #\n    # #    #    #    #    # ## #  # ###\n #  # #    #    #    #    #  # #  # #\n  ##  #### #### #### #     ### #  # #",
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
