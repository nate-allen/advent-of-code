<?php

namespace AdventOfCode\Year2016;

/**
 * Day 21: Scrambled Letters and Hash
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
	 * Scrambling instructions.
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
	 * @return string
	 */
	public function run(): string {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Scramble the password.
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		$password = $this->is_test ? 'abcde' : 'abcdefgh';

		return $this->scramble( $password, $this->data );
	}

	/**
	 * Part 2: Un-scramble the password by trying all permutations.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		$target = 'fbgdceah';
		$chars  = str_split( 'abcdefgh' );

		foreach ( $this->permutations( $chars ) as $perm ) {
			$candidate = implode( '', $perm );

			if ( $this->scramble( $candidate, $this->data ) === $target ) {
				return $candidate;
			}
		}

		return '';
	}

	/**
	 * Generates all permutations of an array.
	 *
	 * @param array $items Items to permute.
	 *
	 * @return \Generator
	 */
	private function permutations( array $items ): \Generator {
		if ( count( $items ) <= 1 ) {
			yield $items;
			return;
		}

		foreach ( $items as $i => $item ) {
			$rest = array_merge( array_slice( $items, 0, $i ), array_slice( $items, $i + 1 ) );

			foreach ( $this->permutations( $rest ) as $perm ) {
				yield array_merge( [ $item ], $perm );
			}
		}
	}

	/**
	 * Applies scrambling operations to a password.
	 *
	 * @param string $str          The starting string.
	 * @param array  $instructions The operations to apply.
	 *
	 * @return string
	 */
	private function scramble( string $str, array $instructions ): string {
		$len = strlen( $str );

		foreach ( $instructions as $inst ) {
			if ( preg_match( '/swap position (\d+) with position (\d+)/', $inst, $m ) ) {
				$x          = (int) $m[1];
				$y          = (int) $m[2];
				$tmp        = $str[ $x ];
				$str[ $x ]  = $str[ $y ];
				$str[ $y ]  = $tmp;
			} elseif ( preg_match( '/swap letter (\w) with letter (\w)/', $inst, $m ) ) {
				$str = strtr( $str, [ $m[1] => $m[2], $m[2] => $m[1] ] );
			} elseif ( preg_match( '/rotate (left|right) (\d+) step/', $inst, $m ) ) {
				$steps = (int) $m[2] % $len;
				if ( $m[1] === 'left' ) {
					$str = substr( $str, $steps ) . substr( $str, 0, $steps );
				} else {
					$str = substr( $str, $len - $steps ) . substr( $str, 0, $len - $steps );
				}
			} elseif ( preg_match( '/rotate based on position of letter (\w)/', $inst, $m ) ) {
				$idx   = strpos( $str, $m[1] );
				$steps = ( 1 + $idx + ( $idx >= 4 ? 1 : 0 ) ) % $len;
				$str   = substr( $str, $len - $steps ) . substr( $str, 0, $len - $steps );
			} elseif ( preg_match( '/reverse positions (\d+) through (\d+)/', $inst, $m ) ) {
				$x   = (int) $m[1];
				$y   = (int) $m[2];
				$str = substr( $str, 0, $x ) . strrev( substr( $str, $x, $y - $x + 1 ) ) . substr( $str, $y + 1 );
			} elseif ( preg_match( '/move position (\d+) to position (\d+)/', $inst, $m ) ) {
				$x   = (int) $m[1];
				$y   = (int) $m[2];
				$ch  = $str[ $x ];
				$str = substr_replace( $str, '', $x, 1 );
				$str = substr( $str, 0, $y ) . $ch . substr( $str, $y );
			}
		}

		return $str;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Instructions as strings.
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-21-test.txt' : '/data/day-21.txt';

		return explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
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
			'test' => 'decab',
			'real' => 'gfdhebac',
		],
		2 => [
			'test' => 0,
			'real' => 'dhaegfbc',
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
