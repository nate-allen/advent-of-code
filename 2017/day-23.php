<?php

namespace AdventOfCode\Year2017;

/**
 * Day 23: Coprocessor Conflagration
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
	 * Part 1: Count how many times mul is invoked.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$registers = [];
		$pc        = 0;
		$mul_count = 0;
		$count     = count( $this->data );

		$val = function ( $x ) use ( &$registers ) {
			return ctype_alpha( $x ) ? ( $registers[ $x ] ?? 0 ) : (int) $x;
		};

		while ( $pc >= 0 && $pc < $count ) {
			$parts = $this->data[ $pc ];
			$op    = $parts[0];
			$x     = $parts[1];
			$y     = $parts[2] ?? null;

			switch ( $op ) {
				case 'set':
					$registers[ $x ] = $val( $y );
					break;
				case 'sub':
					$registers[ $x ] = ( $registers[ $x ] ?? 0 ) - $val( $y );
					break;
				case 'mul':
					$registers[ $x ] = ( $registers[ $x ] ?? 0 ) * $val( $y );
					$mul_count++;
					break;
				case 'jnz':
					if ( $val( $x ) !== 0 ) {
						$pc += $val( $y );
						continue 2;
					}
					break;
			}

			$pc++;
		}

		return $mul_count;
	}

	/**
	 * Part 2: Count composite numbers in the range the program checks.
	 *
	 * The program counts non-prime numbers from b to c, stepping by 17.
	 * With a=1: b = 93*100+100000 = 109300, c = b+17000 = 126300.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$initial_b = (int) $this->data[0][2];
		$b         = $initial_b * 100 + 100000;
		$c         = $b + 17000;
		$h         = 0;

		for ( $n = $b; $n <= $c; $n += 17 ) {
			if ( ! $this->is_prime( $n ) ) {
				$h++;
			}
		}

		return $h;
	}

	/**
	 * Check if a number is prime.
	 *
	 * @param integer $n The number to check.
	 *
	 * @return bool
	 */
	private function is_prime( int $n ): bool {
		if ( $n < 2 ) {
			return false;
		}
		for ( $i = 2; $i * $i <= $n; $i++ ) {
			if ( $n % $i === 0 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? "/data/day-23-test{$this->part}.txt" : '/data/day-23.txt';
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
			'test' => 2,
			'real' => 8281,
		],
		2 => [
			'test' => 910,
			'real' => 911,
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
