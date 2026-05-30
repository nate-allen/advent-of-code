<?php

namespace AdventOfCode\Year2017;

/**
 * Day 08: I Heard You Like Registers
 */
class Day08 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var integer
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
	 * Part 1: Find the largest register value after all instructions.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$registers = [];

		foreach ( $this->data as $instruction ) {
			$this->execute( $instruction, $registers );
		}

		return max( $registers );
	}

	/**
	 * Part 2: Find the highest register value at any point during execution.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$registers = [];
		$max_ever  = 0;

		foreach ( $this->data as $instruction ) {
			$this->execute( $instruction, $registers );
			if ( ! empty( $registers ) ) {
				$max_ever = max( $max_ever, max( $registers ) );
			}
		}

		return $max_ever;
	}

	/**
	 * Executes a single instruction, modifying registers if the condition is met.
	 *
	 * @param array $instruction The parsed instruction.
	 * @param array $registers   The register values (passed by reference).
	 */
	private function execute( array $instruction, array &$registers ): void {
		$cond_val = $registers[ $instruction['cond_reg'] ] ?? 0;
		$cond_met = match ( $instruction['cond_op'] ) {
			'>'  => $cond_val > $instruction['cond_amt'],
			'<'  => $cond_val < $instruction['cond_amt'],
			'>=' => $cond_val >= $instruction['cond_amt'],
			'<=' => $cond_val <= $instruction['cond_amt'],
			'==' => $cond_val === $instruction['cond_amt'],
			'!=' => $cond_val !== $instruction['cond_amt'],
		};

		if ( $cond_met ) {
			$current = $registers[ $instruction['reg'] ] ?? 0;
			$delta   = $instruction['op'] === 'inc' ? $instruction['amt'] : -$instruction['amt'];

			$registers[ $instruction['reg'] ] = $current + $delta;
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-08-test.txt' : '/data/day-08.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( function ( $line ) {
			$parts = explode( ' ', $line );

			return [
				'reg'      => $parts[0],
				'op'       => $parts[1],
				'amt'      => (int) $parts[2],
				'cond_reg' => $parts[4],
				'cond_op'  => $parts[5],
				'cond_amt' => (int) $parts[6],
			];
		}, $lines );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool    $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day08  = new Day08( $test, $part );
	$result = $day08->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 1,
			'real' => 4888,
		],
		2 => [
			'test' => 10,
			'real' => 7774,
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
