<?php

namespace AdventOfCode\Year2019;

include_once 'lib/IntcodeComputer.php';

/**
 * Day 21: Springdroid Adventure
 */
class Day21 {
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
	 * Part 1: Program the springdroid in WALK mode so that it safely jumps over holes.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$computer = new IntcodeComputer( $this->data );

		// Springscript program to determine when to jump.
		$springscript = <<<EOT
NOT A J
NOT B T
OR T J
NOT C T
OR T J
AND D J
WALK
EOT;
		// Ensure the program ends with a newline.
		$springscript .= "\n";

		// Feed each character (as its ASCII code) into the Intcode computer.
		foreach ( str_split( $springscript ) as $char ) {
			$computer->add_input( ord( $char ) );
		}

		$final_damage = 0;
		// Process outputs until the program halts.
		while ( ( $output = $computer->run_until_output() ) !== null ) {
			// If the output value is an ASCII code (<= 127), print the character.
			if ( $output <= 127 ) {
				echo chr( $output );
			} else {
				// Otherwise, this is the reported hull damage.
				$final_damage = $output;
			}
		}

		return $final_damage;
	}

	/**
	 * Part 2: Program the springdroid in RUN mode using extended sensor data.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$computer = new IntcodeComputer( $this->data );

		$springscript = <<<EOT
NOT B J 
NOT C T
OR T J
AND D J
AND H J
NOT A T
OR T J 
RUN
EOT;
		$springscript .= "\n";

		// Feed each character's ASCII code into the Intcode computer.
		foreach ( str_split( $springscript ) as $char ) {
			$computer->add_input( ord( $char ) );
		}

		$final_damage = 0;
		// Process outputs until the program halts.
		while ( ( $output = $computer->run_until_output() ) !== null ) {
			if ( $output <= 127 ) {
				echo chr( $output );
			} else {
				$final_damage = $output;
			}
		}

		return $final_damage;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @return array
	 */
	private function parse_data(): array {
		return array_map( 'intval', explode( ",", trim( file_get_contents( __DIR__ . '/data/day-21.txt' ) ) ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 */
function run_part( int $part ): void {
	$start  = microtime( true );
	$day21  = new Day21( $part );
	$result = $day21->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => 19353619,
		2 => 1142785329,
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
