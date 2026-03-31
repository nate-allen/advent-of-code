<?php

namespace AdventOfCode\Year2015;

/**
 * Day 16: Aunt Sue
 */
class Day16 {
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

	private static array $target = [
		'children'    => 3,
		'cats'        => 7,
		'samoyeds'    => 2,
		'pomeranians' => 3,
		'akitas'      => 0,
		'vizslas'     => 0,
		'goldfish'    => 5,
		'trees'       => 3,
		'cars'        => 2,
		'perfumes'    => 1,
	];

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
	 * Part 1: Find the Sue that matches exactly.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		foreach ( $this->data as $num => $props ) {
			$match = true;
			foreach ( $props as $key => $val ) {
				if ( self::$target[ $key ] !== $val ) {
					$match = false;
					break;
				}
			}
			if ( $match ) {
				return $num;
			}
		}

		return 0;
	}

	/**
	 * Part 2: Find Sue with range-based matching for cats, trees, pomeranians, goldfish.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$greater = [ 'cats', 'trees' ];
		$fewer   = [ 'pomeranians', 'goldfish' ];

		foreach ( $this->data as $num => $props ) {
			$match = true;
			foreach ( $props as $key => $val ) {
				if ( in_array( $key, $greater, true ) ) {
					if ( $val <= self::$target[ $key ] ) {
						$match = false;
						break;
					}
				} elseif ( in_array( $key, $fewer, true ) ) {
					if ( $val >= self::$target[ $key ] ) {
						$match = false;
						break;
					}
				} elseif ( self::$target[ $key ] !== $val ) {
					$match = false;
					break;
				}
			}
			if ( $match ) {
				return $num;
			}
		}

		return 0;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-16-test.txt' : '/data/day-16.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$sues  = [];

		foreach ( $lines as $line ) {
			preg_match( '/Sue (\d+): (.+)/', $line, $m );
			$props = [];
			foreach ( explode( ', ', $m[2] ) as $pair ) {
				[ $key, $val ] = explode( ': ', $pair );
				$props[ $key ] = (int) $val;
			}
			$sues[ (int) $m[1] ] = $props;
		}

		return $sues;
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
	$day16  = new Day16( $test, $part );
	$result = $day16->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 0,
			'real' => 373,
		],
		2 => [
			'test' => 0,
			'real' => 260,
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
