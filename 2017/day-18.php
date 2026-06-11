<?php

namespace AdventOfCode\Year2017;

/**
 * Day 18: Duet
 */
class Day18 {
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
	 * Part 1: Find the recovered frequency the first time rcv executes with a non-zero value.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$registers  = [];
		$last_sound = 0;
		$pc         = 0;

		$val = function ( $x ) use ( &$registers ) {
			return ctype_alpha( $x ) ? ( $registers[ $x ] ?? 0 ) : (int) $x;
		};

		while ( $pc >= 0 && $pc < count( $this->data ) ) {
			$parts = $this->data[ $pc ];
			$op    = $parts[0];
			$x     = $parts[1];
			$y     = $parts[2] ?? null;

			switch ( $op ) {
				case 'snd':
					$last_sound = $val( $x );
					break;
				case 'set':
					$registers[ $x ] = $val( $y );
					break;
				case 'add':
					$registers[ $x ] = ( $registers[ $x ] ?? 0 ) + $val( $y );
					break;
				case 'mul':
					$registers[ $x ] = ( $registers[ $x ] ?? 0 ) * $val( $y );
					break;
				case 'mod':
					$registers[ $x ] = ( $registers[ $x ] ?? 0 ) % $val( $y );
					break;
				case 'rcv':
					if ( $val( $x ) !== 0 ) {
						return $last_sound;
					}
					break;
				case 'jgz':
					if ( $val( $x ) > 0 ) {
						$pc += $val( $y );
						continue 2;
					}
					break;
			}

			$pc++;
		}

		return 0;
	}

	/**
	 * Part 2: Run two programs concurrently; count how many times program 1 sends a value.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$programs = [
			[ 'regs' => [ 'p' => 0 ], 'pc' => 0, 'queue' => [], 'send_count' => 0 ],
			[ 'regs' => [ 'p' => 1 ], 'pc' => 0, 'queue' => [], 'send_count' => 0 ],
		];

		$val = function ( $x, &$regs ) {
			return ctype_alpha( $x ) ? ( $regs[ $x ] ?? 0 ) : (int) $x;
		};

		$count = count( $this->data );

		while ( true ) {
			$progress = false;

			for ( $id = 0; $id <= 1; $id++ ) {
				$other = 1 - $id;
				$regs  = &$programs[ $id ]['regs'];
				$pc    = &$programs[ $id ]['pc'];

				while ( $pc >= 0 && $pc < $count ) {
					$parts = $this->data[ $pc ];
					$op    = $parts[0];
					$x     = $parts[1];
					$y     = $parts[2] ?? null;

					switch ( $op ) {
						case 'snd':
							$programs[ $other ]['queue'][] = $val( $x, $regs );
							$programs[ $id ]['send_count']++;
							$progress = true;
							break;
						case 'set':
							$regs[ $x ] = $val( $y, $regs );
							break;
						case 'add':
							$regs[ $x ] = ( $regs[ $x ] ?? 0 ) + $val( $y, $regs );
							break;
						case 'mul':
							$regs[ $x ] = ( $regs[ $x ] ?? 0 ) * $val( $y, $regs );
							break;
						case 'mod':
							$regs[ $x ] = ( $regs[ $x ] ?? 0 ) % $val( $y, $regs );
							break;
						case 'rcv':
							if ( empty( $programs[ $id ]['queue'] ) ) {
								break 2; // blocked, switch to other program
							}
							$regs[ $x ] = array_shift( $programs[ $id ]['queue'] );
							$progress   = true;
							break;
						case 'jgz':
							if ( $val( $x, $regs ) > 0 ) {
								$pc += $val( $y, $regs );
								continue 2; // skip $pc++ (switch=1, while=2)
							}
							break;
					}

					$pc++;
				}

				unset( $regs, $pc );
			}

			if ( ! $progress ) {
				break; // deadlock
			}
		}

		return $programs[1]['send_count'];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? "/data/day-18-test{$this->part}.txt" : '/data/day-18.txt';

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
	$day18  = new Day18( $test, $part );
	$result = $day18->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 4,
			'real' => 1187,
		],
		2 => [
			'test' => 3,
			'real' => 5969,
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
