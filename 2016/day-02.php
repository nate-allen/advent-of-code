<?php

namespace AdventOfCode\Year2016;

/**
 * Day 02: Bathroom Security
 */
class Day02 {
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
	 * Part 1: Find bathroom code on a 3x3 keypad.
	 *
	 * @return string
	 */
	private function solve_part_1(): string {
		$keypad = [
			[ 1, 2, 3 ],
			[ 4, 5, 6 ],
			[ 7, 8, 9 ],
		];
		$r    = 1;
		$c    = 1;
		$code = '';
		$move = [ 'U' => [ -1, 0 ], 'D' => [ 1, 0 ], 'L' => [ 0, -1 ], 'R' => [ 0, 1 ] ];

		foreach ( $this->data as $line ) {
			foreach ( $line as $dir ) {
				$nr = $r + $move[ $dir ][0];
				$nc = $c + $move[ $dir ][1];

				if ( $nr >= 0 && $nr <= 2 && $nc >= 0 && $nc <= 2 ) {
					$r = $nr;
					$c = $nc;
				}
			}
			$code .= $keypad[ $r ][ $c ];
		}

		return $code;
	}

	/**
	 * Part 2: Find bathroom code on a diamond-shaped keypad.
	 *
	 * @return string
	 */
	private function solve_part_2(): string {
		$keypad = [
			[ null, null, '1', null, null ],
			[ null, '2',  '3', '4',  null ],
			[ '5',  '6',  '7', '8',  '9'  ],
			[ null, 'A',  'B', 'C',  null ],
			[ null, null, 'D', null, null ],
		];
		$r    = 2;
		$c    = 0;
		$code = '';
		$move = [ 'U' => [ -1, 0 ], 'D' => [ 1, 0 ], 'L' => [ 0, -1 ], 'R' => [ 0, 1 ] ];

		foreach ( $this->data as $line ) {
			foreach ( $line as $dir ) {
				$nr = $r + $move[ $dir ][0];
				$nc = $c + $move[ $dir ][1];

				if ( $nr >= 0 && $nr <= 4 && $nc >= 0 && $nc <= 4 && $keypad[ $nr ][ $nc ] !== null ) {
					$r = $nr;
					$c = $nc;
				}
			}
			$code .= $keypad[ $r ][ $c ];
		}

		return $code;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-02-test.txt' : '/data/day-02.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( 'str_split', $lines );
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
	$day02  = new Day02( $test, $part );
	$result = $day02->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 1985,
			'real' => 82958,
		],
		2 => [
			'test' => '5DB3',
			'real' => 'B3DB8',
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
