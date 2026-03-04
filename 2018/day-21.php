<?php

namespace AdventOfCode\Year2018;

/**
 * Day 21: Chronal Conversion
 */
class Day21 {
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
	 * Part 1: Find the lowest non-negative integer value for register 0 that causes
	 * the program to halt after executing the fewest instructions.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$c1 = $this->data['constant_1'];
		$c2 = $this->data['constant_2'];

		// Reverse-engineered logic: simulate the core computation
		$prev = 0;
		$r5   = $c1;
		$r4   = $prev | 0x10000;

		// Do 3 iterations of the core computation
		$r5   = ((($r5 + ($r4 & 0xFF)) & 0xFFFFFF) * $c2) & 0xFFFFFF;
		$r4 >>= 8;
		$r5   = ((($r5 + ($r4 & 0xFF)) & 0xFFFFFF) * $c2) & 0xFFFFFF;
		$r4 >>= 8;
		$r5   = ((($r5 + ($r4 & 0xFF)) & 0xFFFFFF) * $c2) & 0xFFFFFF;

		// Return first value (this is the answer for part 1)
		return $r5;
	}

	/**
	 * Part 2: Find the value for register 0 that causes the program to halt
	 * after executing the most instructions (last unique value before cycle).
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$c1   = $this->data['constant_1'];
		$c2   = $this->data['constant_2'];
		$memo = [];
		$prev = 0;

		while ( true ) {
			// Reverse-engineered logic: simulate the core computation
			$r5 = $c1;
			$r4 = $prev | 0x10000;

			// Do 3 iterations of the core computation
			$r5   = ((($r5 + ($r4 & 0xFF)) & 0xFFFFFF) * $c2) & 0xFFFFFF;
			$r4 >>= 8;
			$r5   = ((($r5 + ($r4 & 0xFF)) & 0xFFFFFF) * $c2) & 0xFFFFFF;
			$r4 >>= 8;
			$r5   = ((($r5 + ($r4 & 0xFF)) & 0xFFFFFF) * $c2) & 0xFFFFFF;

			// Check if we've seen this value before (cycle detected)
			if ( isset( $memo[ $r5 ] ) ) {
				// Return the last unique value before the cycle
				return $prev;
			}

			// Track this value and remember it as the last unique one
			$memo[ $r5 ] = true;
			$prev        = $r5;
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array with 'constant_1' and 'constant_2' keys.
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-21-test.txt' : '/data/day-21.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$constant_1 = null;
		$constant_2 = null;

		for ( $i = 1; $i < count( $lines ); $i++ ) {
			$parts  = explode( ' ', trim( $lines[ $i ] ) );
			$opcode = $parts[0];
			$a      = (int) $parts[1];
			$b      = (int) $parts[2];

			$instruction_index = $i - 1; // 0-based index

			// Extract constants for optimization
			// Instruction 7 (index 7): seti instruction that sets initial value
			if ( $instruction_index === 7 && $opcode === 'seti' ) {
				$constant_1 = $a;
			}

			// Instruction 11 (index 11): muli instruction with multiplier 65899
			if ( $instruction_index === 11 && $opcode === 'muli' && $b === 65899 ) {
				$constant_2 = $b;
			}
		}

		if ( $constant_1 === null || $constant_2 === null ) {
			throw new \RuntimeException( 'Could not extract optimization constants from program' );
		}

		return [
			'constant_1' => $constant_1,
			'constant_2' => $constant_2,
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
	$day21  = new Day21( $test, $part );
	$result = $day21->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 5,
			'real' => 13522479,
		],
		2 => [
			'test' => 0,
			'real' => 14626276,
		],
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values[$part]['test'] : $expected_values[$part]['real'] );
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
