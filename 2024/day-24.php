<?php

namespace AdventOfCode\Year2024;

/**
 * Day 24: Crossed Wires
 */
class Day24 {
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

	/**
	 * Values.
	 *
	 * @var array
	 */
	private array $values = [];

	/**
	 * Expressions.
	 *
	 * @var array
	 */
	private array $expressions = [];

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->parse_data( $this->is_test );
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
	 * Part 1: Simulate the system of gates and wires. What decimal number does it output on the wires starting with z?
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		// Process all variables in expressions.
		foreach ( array_keys( $this->expressions ) as $var ) {
			$this->evaluate_wire( $var );
		}

		// Collect binary string for wires starting with 'z', ordered by suffix.
		$binary_parts = [];
		foreach ( $this->values as $key => $value ) {
			if ( str_starts_with( $key, 'z' ) ) {
				$binary_parts[ (int) substr( $key, 1 ) ] = (string) $value;
			}
		}

		ksort( $binary_parts ); // Ensure numeric order of 'z' variables.
		$binary = implode( '', $binary_parts );

		// Reverse the binary string and convert to an integer.
		return bindec( strrev( $binary ) );
	}

	/**
	 * Part 2: Sort the names of the eight wires involved in a swap and then join those names with commas.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		$wrong = [];

		// Find the highest 'z' gate.
		$highest_z = max( array_filter( array_keys( $this->expressions ), fn( string $key ): bool => str_starts_with( $key, 'z' ) ) );

		foreach ( $this->expressions as $output => [$input1, $operation, $input2] ) {
			if (
				( str_starts_with( $output, 'z' ) && $operation !== 'XOR' && $output !== $highest_z ) ||
				( $operation === 'XOR' && ! $this->valid_xor_inputs( $output, $input1, $input2 ) ) ||
				( $operation === 'AND' && $this->invalid_and_inputs( $this->expressions, $output, $input1, $input2 ) ) ||
				( $operation === 'XOR' && $this->xor_feeds_or( $this->expressions, $output ) )
			) {
				$wrong[] = $output;
			}
		}

		sort( $wrong );

		return implode( ',', array_unique( $wrong ) );
	}

	/**
	 * Recursively evaluates the value of a wire in the circuit.
	 *
	 * @param string $var The name of the wire to evaluate.
	 *
	 * @return void
	 */
	private function evaluate_wire( string $var ): void {
		if ( isset( $this->values[ $var ] ) ) {
			return; // Value already computed.
		}

		// Expression for this variable.
		$expression = $this->expressions[ $var ];

		$this->evaluate_wire( $expression[0] ); // Left side.
		$this->evaluate_wire( $expression[2] ); // Right side.

		// Apply the operator.
		$this->values[ $var ] = match ( $expression[1] ) {
			'AND' => $this->values[ $expression[0] ] & $this->values[ $expression[2] ],
			'OR' => $this->values[ $expression[0] ] | $this->values[ $expression[2] ],
			'XOR' => $this->values[ $expression[0] ] ^ $this->values[ $expression[2] ],
		};
	}

	/**
	 * Validates if XOR operation inputs are acceptable. XOR inputs are valid if the output or any of the inputs start
	 * with 'x', 'y', or 'z'.
	 *
	 * @param string $output The output wire.
	 * @param string $input1 The first input wire.
	 * @param string $input2 The second input wire.
	 *
	 * @return bool
	 */
	private function valid_xor_inputs( string $output, string $input1, string $input2 ): bool {
		$prefixes = [ 'x', 'y', 'z' ];

		// Check if any of the output or inputs start with one of the prefixes.
		foreach ( [ $output, $input1, $input2 ] as $string ) {
			foreach ( $prefixes as $prefix ) {
				if ( str_starts_with( $string, $prefix ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Determines if AND operation inputs are invalid. They are invalid if neither input is x00 and the output is used
	 * as input for another gate that is not an OR gate.
	 *
	 * @param array  $gates  The list of gate definitions.
	 * @param string $output The output wire.
	 * @param string $input1 The first input wire.
	 * @param string $input2 The second input wire.
	 *
	 * @return bool
	 */
	private function invalid_and_inputs( array $gates, string $output, string $input1, string $input2 ): bool {
		// If either input is x00, the inputs are valid.
		if ( $input1 === 'x00' || $input2 === 'x00' ) {
			return false;
		}

		// Check if the output is used as an input in another gate that is not an OR gate.
		foreach ( $gates as $gate ) {
			if ( ( $output === $gate[0] || $output === $gate[2] ) && $gate[1] !== 'OR' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if the output of an XOR operation feeds into an OR gate. If it does, the XOR operation is invalid.
	 *
	 * @param array  $gates  The list of gate definitions.
	 * @param string $output The output wire.
	 *
	 * @return bool
	 */
	private function xor_feeds_or( array $gates, string $output ): bool {
		// Loop over gates to find if the output is used as input to an OR gate.
		foreach ( $gates as $gate ) {
			if ( ( $output === $gate[0] || $output === $gate[2] ) && $gate[1] === 'OR' ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return void
	 */
	private function parse_data( bool $test ): void {
		$file = $test ? '/data/day-24-test.txt' : '/data/day-24.txt';

		[
			$block1,
			$block2
		] = preg_split( '/\n\s*\n/', trim( file_get_contents( __DIR__ . $file ) ), 2 ); // Split into two parts.

		// Parse the first block into $this->values.
		foreach ( explode( "\n", $block1 ) as $line ) {
			$this->values[ substr( $line, 0, 3 ) ] = (int) substr( $line, - 1 );
		}

		// Parse the second block into $this->expressions.
		foreach ( explode( "\n", $block2 ) as $line ) {
			[ $l, $r ] = explode( ' -> ', $line );
			$this->expressions[ $r ] = explode( ' ', $l );
		}
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
	$day24  = new Day24( $test, $part );
	$result = $day24->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 2024,
			'real' => 54715147844840,
		],
		2 => [
			'test' => 'ffh,hwm,kjc,mjb,ntg,rvg,tgd,wpb,z02,z03,z05,z06,z07,z08,z10,z11',
			'real' => 'ggn,grm,jcb,ndw,twr,z10,z32,z39',
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
