<?php

namespace AdventOfCode\Year2021;

/**
 * Day 11: Dumbo Octopus
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
	 * Part 1: Simulate 100 steps and count total flashes.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$total_flashes = 0;

		for ( $step = 0; $step < 100; $step ++ ) {
			// Step 1: Increase the energy level of each octopus by 1.
			$flash_queue = [];
			$flashed     = [];
			foreach ( $this->data as $row => $cols ) {
				foreach ( $cols as $col => $energy ) {
					$this->data[ $row ][ $col ] ++;
					if ( $this->data[ $row ][ $col ] > 9 ) {
						$flash_queue[] = [ $row, $col ];
					}
				}
			}

			// Step 2: Handle flashes.
			while ( $flash_queue ) {
				[ $row, $col ] = array_pop( $flash_queue );

				// Skip if this octopus already flashed.
				if ( isset( $flashed["$row,$col"] ) ) {
					continue;
				}

				$flashed["$row,$col"] = true;
				$total_flashes ++;

				// Increase the energy of all adjacent octopuses.
				for ( $dr = - 1; $dr <= 1; $dr ++ ) {
					for ( $dc = - 1; $dc <= 1; $dc ++ ) {
						if ( $dr === 0 && $dc === 0 ) {
							continue;
						}

						$nr = $row + $dr;
						$nc = $col + $dc;

						if ( isset( $this->data[ $nr ][ $nc ] ) ) {
							$this->data[ $nr ][ $nc ] ++;
							if ( $this->data[ $nr ][ $nc ] > 9 && ! isset( $flashed["$nr,$nc"] ) ) {
								$flash_queue[] = [ $nr, $nc ];
							}
						}
					}
				}
			}

			// Step 3: Reset energy levels of flashed octopuses to 0.
			foreach ( $flashed as $key => $_ ) {
				[ $row, $col ] = explode( ',', $key );
				$this->data[ $row ][ $col ] = 0;
			}
		}

		return $total_flashes;
	}

	/**
	 * Part 2: Find the first step during which all octopuses flash at the same time.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$step = 0;

		while ( true ) {
			$step ++;
			$flash_queue = [];
			$flashed     = [];

			// Step 1: Increase the energy level of each octopus by 1.
			foreach ( $this->data as $row => $cols ) {
				foreach ( $cols as $col => $energy ) {
					$this->data[ $row ][ $col ] ++;
					if ( $this->data[ $row ][ $col ] > 9 ) {
						$flash_queue[] = [ $row, $col ];
					}
				}
			}

			// Step 2: Handle flashes.
			while ( $flash_queue ) {
				[ $row, $col ] = array_pop( $flash_queue );

				if ( isset( $flashed["$row,$col"] ) ) {
					continue;
				}

				$flashed["$row,$col"] = true;

				for ( $dr = - 1; $dr <= 1; $dr ++ ) {
					for ( $dc = - 1; $dc <= 1; $dc ++ ) {
						if ( $dr === 0 && $dc === 0 ) {
							continue;
						}

						$nr = $row + $dr;
						$nc = $col + $dc;

						if ( isset( $this->data[ $nr ][ $nc ] ) ) {
							$this->data[ $nr ][ $nc ] ++;
							if ( $this->data[ $nr ][ $nc ] > 9 && ! isset( $flashed["$nr,$nc"] ) ) {
								$flash_queue[] = [ $nr, $nc ];
							}
						}
					}
				}
			}

			// Step 3: Reset energy levels of flashed octopuses to 0.
			foreach ( $flashed as $key => $_ ) {
				[ $row, $col ] = explode( ',', $key );
				$this->data[ $row ][ $col ] = 0;
			}

			// Check if all octopuses flashed.
			if ( count( $flashed ) === 100 ) {
				return $step;
			}
		}
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
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return array_map( 'str_split', $lines );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day11  = new Day11( $test, $part );
	$result = $day11->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 1656,
			'real' => 1697,
		],
		2 => [
			'test' => 195,
			'real' => 344,
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
