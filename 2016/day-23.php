<?php

namespace AdventOfCode\Year2016;

/**
 * Day 23: Safe Cracking
 */
class Day23 {
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
	 * Parsed instructions.
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
	 * Part 1: Run with a=7.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->execute( [ 'a' => 7, 'b' => 0, 'c' => 0, 'd' => 0 ] );
	}

	/**
	 * Part 2: Run with a=12.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->execute( [ 'a' => 12, 'b' => 0, 'c' => 0, 'd' => 0 ] );
	}

	/**
	 * Executes the assembunny instructions with tgl support and multiply optimization.
	 *
	 * @param array $regs Initial register values.
	 *
	 * @return integer Value in register a after execution.
	 */
	private function execute( array $regs ): int {
		$inst = $this->data;
		$ip   = 0;
		$len  = count( $inst );

		while ( $ip < $len ) {
			// Multiplication optimization: detect pattern
			// cpy X c; inc a; dec c; jnz c -2; dec d; jnz d -5
			// which computes a += X * d, c = 0, d = 0
			if ( $ip + 5 < $len
				&& $inst[ $ip ][0] === 'cpy'
				&& $inst[ $ip + 1 ][0] === 'inc'
				&& $inst[ $ip + 2 ][0] === 'dec'
				&& $inst[ $ip + 3 ][0] === 'jnz'
				&& $inst[ $ip + 4 ][0] === 'dec'
				&& $inst[ $ip + 5 ][0] === 'jnz'
				&& $inst[ $ip + 2 ][1] === $inst[ $ip ][2]
				&& $inst[ $ip + 3 ][1] === $inst[ $ip ][2]
				&& (int) $inst[ $ip + 3 ][2] === -2
				&& $inst[ $ip + 5 ][1] === $inst[ $ip + 4 ][1]
				&& (int) $inst[ $ip + 5 ][2] === -5
			) {
				$x_val = is_numeric( $inst[ $ip ][1] ) ? (int) $inst[ $ip ][1] : $regs[ $inst[ $ip ][1] ];
				$d_reg = $inst[ $ip + 4 ][1];
				$a_reg = $inst[ $ip + 1 ][1];
				$c_reg = $inst[ $ip ][2];

				$regs[ $a_reg ] += $x_val * $regs[ $d_reg ];
				$regs[ $c_reg ]  = 0;
				$regs[ $d_reg ]  = 0;
				$ip += 6;
				continue;
			}

			$op = $inst[ $ip ][0];

			switch ( $op ) {
				case 'cpy':
					$val = is_numeric( $inst[ $ip ][1] ) ? (int) $inst[ $ip ][1] : $regs[ $inst[ $ip ][1] ];
					if ( ! is_numeric( $inst[ $ip ][2] ) ) {
						$regs[ $inst[ $ip ][2] ] = $val;
					}
					break;
				case 'inc':
					if ( ! is_numeric( $inst[ $ip ][1] ) ) {
						$regs[ $inst[ $ip ][1] ]++;
					}
					break;
				case 'dec':
					if ( ! is_numeric( $inst[ $ip ][1] ) ) {
						$regs[ $inst[ $ip ][1] ]--;
					}
					break;
				case 'jnz':
					$val    = is_numeric( $inst[ $ip ][1] ) ? (int) $inst[ $ip ][1] : $regs[ $inst[ $ip ][1] ];
					$offset = is_numeric( $inst[ $ip ][2] ) ? (int) $inst[ $ip ][2] : $regs[ $inst[ $ip ][2] ];
					if ( $val !== 0 ) {
						$ip += $offset;
						continue 2;
					}
					break;
				case 'tgl':
					$val    = is_numeric( $inst[ $ip ][1] ) ? (int) $inst[ $ip ][1] : $regs[ $inst[ $ip ][1] ];
					$target = $ip + $val;
					if ( $target >= 0 && $target < $len ) {
						$argc = count( $inst[ $target ] ) - 1;
						if ( $argc === 1 ) {
							$inst[ $target ][0] = $inst[ $target ][0] === 'inc' ? 'dec' : 'inc';
						} else {
							$inst[ $target ][0] = $inst[ $target ][0] === 'jnz' ? 'cpy' : 'jnz';
						}
					}
					break;
			}

			$ip++;
		}

		return $regs['a'];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of instruction arrays.
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-23-test.txt' : '/data/day-23.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( fn( $line ) => explode( ' ', $line ), $lines );
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
	$day23  = new Day23( $test, $part );
	$result = $day23->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 3,
			'real' => 12800,
		],
		2 => [
			'test' => 0,
			'real' => 479009360,
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
