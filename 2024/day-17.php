<?php

namespace AdventOfCode\Year2024;

/**
 * Day 17: Chronospatial Computer
 */
class Day17 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
	 */
	private int $part;

	/**
	 * Whether to use the test data.
	 *
	 * @var bool
	 */
	private bool $is_test;

	/**
	 * Parsed data from the input file.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return int|string
	 */
	public function run(): int|string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Simulate a 3-bit computer and output results in comma-separated format.
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		[ $regA, $regB, $regC, $program ] = $this->data;

		$output = $this->compute( $program, $regA, $regB, $regC );

		return implode( ',', $output );
	}

	/**
	 * Part 2: Find the lowest initial value for register A that causes the program to output a copy of itself.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		return $this->build_register_a( 0, 1, $this->data[3] );
	}

	/**
	 * Recursively finds the lowest value for register A that makes the program output a copy of itself.
	 *
	 * This method builds the value of A incrementally, 3 bits at a time, starting from 0. At each step,
	 * it explores all 3-bit combinations (0 to 7), appends them to the current input using a bitwise shift,
	 * and runs the program to verify if the output matches the expected portion of the program.
	 *
	 * If a valid 3-bit combination produces the correct partial output, the method proceeds to the next
	 * position in the program, repeating the process until all outputs are validated.
	 *
	 * @param integer $input    The current value being built for register A.
	 * @param integer $position The current position being checked, starting from the end of the program.
	 * @param array   $program  The program instructions (output values to match).
	 *
	 * @return int|false
	 */
	function build_register_a( int $input, int $position, array &$program ): bool|int {
		// If we matched all outputs, return the current input as the solution.
		if ( $position > count( $program ) ) {
			return $input;
		}

		// Loop over all 3-bit combinations (0–7) to explore possible values for the next 3 bits.
		for ( $i = 0; $i < 8; $i++ ) {
			// Append the current 3-bit value ($i) to the existing input using bitwise shift.
			$candidate = $input << 3 | $i;

			// Compute the output produced by running the program with the current candidate input.
			$output = $this->compute( $program, $candidate, 0, 0 );

			// Check if the computed output matches the last $position elements of the program.
			if ( $output === array_slice( $program, - $position ) ) {
				// If the partial output matches, recursively try to extend the solution.
				$result = $this->build_register_a( $candidate, $position + 1, $program );

				// If a valid solution is found deeper in the recursion, return it.
				if ( $result !== false ) {
					return $result;
				}
			}
		}

		// If no valid combination works for this position, return false to backtrack.
		return false;
	}


	/**
	 * Executes the program instructions.
	 *
	 * @param array   $program The program instructions.
	 * @param integer $regA	   The value of register A.
	 * @param integer $regB    The value of register B.
	 * @param integer $regC    The value of register C.
	 *
	 * @return array
	 */
	private function compute(array $program, int $regA, int $regB, int $regC): array {
		$pointer = 0;
		$output  = [];

		while ( $pointer < count( $program ) ) {
			$code = $program[ $pointer ];
			$op   = $program[ $pointer + 1 ];

			$combo = match ( $op ) {
				0, 1, 2, 3 => $op,
				4 => $regA,
				5 => $regB,
				6 => $regC,
				default => - 1,
			};

			switch ( $code ) {
				case 0:
					$regA    = (int) ( $regA / ( 2 ** $combo ) );
					$pointer += 2;
					break;
				case 1:
					$regB    ^= $op;
					$pointer += 2;
					break;
				case 2:
					$regB    = $combo % 8;
					$pointer += 2;
					break;
				case 3:
					$pointer = ( $regA !== 0 ) ? $op : $pointer + 2;
					break;
				case 4:
					$regB    ^= $regC;
					$pointer += 2;
					break;
				case 5:
					$output[] = $combo % 8;
					$pointer  += 2;
					break;
				case 6:
					$regB    = (int) ( $regA / ( 2 ** $combo ) );
					$pointer += 2;
					break;
				case 7:
					$regC    = (int) ( $regA / ( 2 ** $combo ) );
					$pointer += 2;
					break;
			}
		}

		return $output;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-17-test.txt' : '/data/day-17.txt';
		[$registers, $program] = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );

		preg_match_all('/-?\d+/', $registers, $matches);

		[$regA, $regB, $regC] = array_map('intval', $matches[0]);
		$program = array_map('intval', explode(",", explode(":", $program)[1]));

		return [$regA, $regB, $regC, $program];
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day17  = new Day17( $test, $part );
	$result = $day17->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => '5,7,3,0',
			'real' => '2,0,1,3,4,0,2,1,7',
		],
		2 => [
			'test' => 117440,
			'real' => 236580836040301,
		],
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values[ $part ]['test'] : $expected_values[ $part ]['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$part = (int) trim( readline( 'Which part do you want to run? (1/2): ' ) );
	if ( ! in_array( $part, [ 1, 2 ], true ) ) {
		echo 'Invalid part. Please enter 1 or 2.' . PHP_EOL;
		continue;
	}

	while ( true ) {
		$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
		if ( in_array( $test, [ 'y', 'n' ], true ) ) {
			$test_mode = $test === 'y';
			run_part( $part, $test_mode );
			break 2;
		}
		echo 'Invalid input. Please enter y or n.' . PHP_EOL;
	}
}
