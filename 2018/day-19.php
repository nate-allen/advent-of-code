<?php

namespace AdventOfCode\Year2018;

require_once __DIR__ . '/opcodeexecutor.php';

/**
 * Day 19: Go With The Flow
 */
class Day19 {
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
	 * Part 1: Execute the program and return register 0 value when it halts.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$ip_register = $this->data['ip_register'];
		$program     = $this->data['program'];
		$registers   = [ 0, 0, 0, 0, 0, 0 ];
		$ip          = 0;

		while ( $ip >= 0 && $ip < count( $program ) ) {
			// Write IP to bound register
			$registers[ $ip_register ] = $ip;

			// Execute instruction
			$instruction = $program[ $ip ];
			OpcodeExecutor::apply_opcode( $registers, $instruction['opcode'], $instruction['a'], $instruction['b'], $instruction['c'] );

			// Write bound register back to IP
			$ip = $registers[ $ip_register ];

			// Increment IP
			$ip++;
		}

		return $registers[0];
	}

	/**
	 * Part 2: Execute program with register 0 = 1, extract target number, and compute sum of divisors.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$ip_register = $this->data['ip_register'];
		$program     = $this->data['program'];
		$registers   = [ 1, 0, 0, 0, 0, 0 ]; // Register 0 starts at 1 for part 2
		$ip          = 0;

		// Run program until we reach instruction 1 (start of factorization loop)
		while ( $ip >= 0 && $ip < count( $program ) && $ip !== 1 ) {
			// Write IP to bound register
			$registers[ $ip_register ] = $ip;

			// Execute instruction
			$instruction = $program[ $ip ];
			OpcodeExecutor::apply_opcode( $registers, $instruction['opcode'], $instruction['a'], $instruction['b'], $instruction['c'] );

			// Write bound register back to IP
			$ip = $registers[ $ip_register ];

			// Increment IP
			$ip++;
		}

		// Extract target number from register 3 (the number to factorize)
		$target_number = $registers[3];

		// Compute sum of divisors efficiently
		return $this->sum_of_divisors( $target_number );
	}

	/**
	 * Computes the sum of all divisors of a number efficiently (O(√n)).
	 *
	 * @param int $n The number to find divisors for.
	 *
	 * @return int Sum of all divisors.
	 */
	private function sum_of_divisors( int $n ): int {
		$sum    = 0;
		$sqrt_n = (int) sqrt( $n );

		// Iterate from 1 to √n
		for ( $i = 1; $i <= $sqrt_n; $i++ ) {
			if ( $n % $i === 0 ) {
				// Add both i and n/i as divisors
				$sum    += $i;
				$divisor = $n / $i;
				// Don't double-count perfect squares
				if ( $divisor !== $i ) {
					$sum += $divisor;
				}
			}
		}

		return $sum;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array with 'ip_register' and 'program' keys.
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-19-test.txt' : '/data/day-19.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		// First line is the instruction pointer binding
		$ip_line = $lines[0];
		preg_match( '/#ip (\d+)/', $ip_line, $matches );
		$ip_register = (int) $matches[1];

		// Remaining lines are program instructions
		$program = [];
		for ( $i = 1; $i < count( $lines ); $i++ ) {
			$parts     = explode( ' ', trim( $lines[ $i ] ) );
			$program[] = [
				'opcode' => $parts[0],
				'a'      => (int) $parts[1],
				'b'      => (int) $parts[2],
				'c'      => (int) $parts[3],
			];
		}

		return [
			'ip_register' => $ip_register,
			'program'     => $program,
		];
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
	$day19  = new Day19( $test, $part );
	$result = $day19->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 6,
			'real' => 2352,
		],
		2 => [
			'test' => 0,
			'real' => 24619952,
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
