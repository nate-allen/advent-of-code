<?php

namespace AdventOfCode\Year2020;

/**
 * Day 11: Seating System
 */
class Day11 {
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
	 * Part 1: Apply the seating rules repeatedly until no seats change state. How many seats end up occupied?
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$seats = $this->data;

		$changed = true;

		while ( $changed ) {
			$changed = false;
			$new_seats = $seats;

			foreach ( $seats as $row => $line ) {
				$new_line = '';

				for ( $i = 0; $i < strlen( $line ); $i++ ) {
					$seat = $line[ $i ];
					$adjacent = 0;

					for ( $r = $row - 1; $r <= $row + 1; $r++ ) {
						if ( $r < 0 || $r >= count( $seats ) ) {
							continue;
						}

						for ( $c = $i - 1; $c <= $i + 1; $c++ ) {
							if ( $c < 0 || $c >= strlen( $line ) || ( $r === $row && $c === $i ) ) {
								continue;
							}

							if ( $seats[ $r ][ $c ] === '#' ) {
								$adjacent++;
							}
						}
					}

					if ( $seat === 'L' && $adjacent === 0 ) {
						$new_line .= '#';
						$changed = true;
					} elseif ( $seat === '#' && $adjacent >= 4 ) {
						$new_line .= 'L';
						$changed = true;
					} else {
						$new_line .= $seat;
					}
				}

				$new_seats[ $row ] = $new_line;
			}

			$seats = $new_seats;
		}

		$occupied = 0;

		foreach ( $seats as $line ) {
			$occupied += substr_count( $line, '#' );
		}

		return $occupied;
	}

	/**
	 * Part 2: Apply the seating rules repeatedly until no seats change state. How many seats end up occupied?
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$seats   = $this->data;
		$changed = true;

		while ( $changed ) {
			$changed = false;
			$new_seats = $seats;

			foreach ( $seats as $row => $line ) {
				$new_line = '';

				for ( $i = 0; $i < strlen( $line ); $i++ ) {
					$seat = $line[ $i ];
					$adjacent = 0;

					for ( $r = $row - 1; $r <= $row + 1; $r++ ) {
						if ( $r < 0 || $r >= count( $seats ) ) {
							continue;
						}

						for ( $c = $i - 1; $c <= $i + 1; $c++ ) {
							if ( $c < 0 || $c >= strlen( $line ) || ( $r === $row && $c === $i ) ) {
								continue;
							}

							$dr = $r - $row;
							$dc = $c - $i;

							$rr = $r;
							$cc = $c;

							while ( $rr >= 0 && $rr < count( $seats ) && $cc >= 0 && $cc < strlen( $line ) ) {
								if ( $seats[ $rr ][ $cc ] === '#' ) {
									$adjacent++;
									break;
								} elseif ( $seats[ $rr ][ $cc ] === 'L' ) {
									break;
								}

								$rr += $dr;
								$cc += $dc;
							}
						}
					}

					if ( $seat === 'L' && $adjacent === 0 ) {
						$new_line .= '#';
						$changed = true;
					} elseif ( $seat === '#' && $adjacent >= 5 ) {
						$new_line .= 'L';
						$changed = true;
					} else {
						$new_line .= $seat;
					}
				}

				$new_seats[ $row ] = $new_line;
			}

			$seats = $new_seats;
		}

		$occupied = 0;

		// Count occupied seats
		foreach ( $seats as $line ) {
			$occupied += substr_count( $line, '#' );
		}

		return $occupied;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-11-test.txt' : '/data/day-11.txt';

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
	$day11  = new Day11( $test, $part );
	$result = $day11->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 37,
			'real' => 2427,
		],
		2 => [
			'test' => 26,
			'real' => 2199,
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
