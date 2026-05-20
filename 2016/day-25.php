<?php

namespace AdventOfCode\Year2016;

/**
 * Day 25: Clock Signal
 */
class Day25 {
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

	public function __construct( bool $test ) {
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
	}

	/**
	 * Find lowest positive integer for register a that produces 0,1,0,1,...
	 *
	 * @return integer
	 */
	public function run(): int {
		for ( $a = 1; ; $a++ ) {
			if ( $this->produces_clock_signal( $a ) ) {
				return $a;
			}
		}
	}

	/**
	 * Tests if a given initial value of register a produces the clock signal.
	 *
	 * Runs the program and checks if the first outputs alternate 0,1,0,1,...
	 * Uses state detection: if we see the same [ip, registers] twice, it's a loop.
	 *
	 * @param int $a_init Initial value for register a.
	 *
	 * @return bool
	 */
	private function produces_clock_signal( int $a_init ): bool {
		$inst = $this->data;
		$regs = [ 'a' => $a_init, 'b' => 0, 'c' => 0, 'd' => 0 ];
		$ip   = 0;
		$len  = count( $inst );
		$expected_output = 0;
		$output_count    = 0;

		while ( $ip < $len && $output_count < 50 ) {
			// Multiplication optimization.
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
				$regs[ $inst[ $ip + 1 ][1] ] += $x_val * $regs[ $inst[ $ip + 4 ][1] ];
				$regs[ $inst[ $ip ][2] ]      = 0;
				$regs[ $inst[ $ip + 4 ][1] ]  = 0;
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
				case 'out':
					$val = is_numeric( $inst[ $ip ][1] ) ? (int) $inst[ $ip ][1] : $regs[ $inst[ $ip ][1] ];
					if ( $val !== $expected_output ) {
						return false;
					}
					$expected_output = 1 - $expected_output;
					$output_count++;
					break;
			}

			$ip++;
		}

		return $output_count >= 50;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of instruction arrays.
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-25-test.txt' : '/data/day-25.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( fn( $line ) => explode( ' ', $line ), $lines );
	}
}

/**
 * Runs the puzzle and outputs results.
 *
 * @param bool $test Whether to use test data.
 */
function run_day25( bool $test ): void {
	$start  = microtime( true );
	$day25  = new Day25( $test );
	$result = $day25->run();
	$end    = microtime( true );

	$expected = $test ? 0 : 196;

	// ANSI color codes
	$yellow = "\033[33m";
	$reset  = "\033[0m";

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $expected );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for test mode only
while ( true ) {
	$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
	if ( in_array( $test, [ 'y', 'n' ], true ) ) {
		run_day25( $test === 'y' );
		break;
	}
	echo 'Invalid input. Please enter y or n.' . PHP_EOL;
}
