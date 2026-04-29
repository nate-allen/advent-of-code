<?php

namespace AdventOfCode\Year2016;

/**
 * Day 11: Radioisotope Thermoelectric Generators
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
	 * Parsed data: array of [generator_floor, chip_floor] pairs.
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
	 * Part 1: Minimum steps to move all items to floor 4.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->bfs( $this->data );
	}

	/**
	 * Part 2: SHORT_DESCRIPTION_HERE
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// Add elerium and dilithium pairs, both on floor 0.
		$pairs   = $this->data;
		$pairs[] = [ 0, 0 ];
		$pairs[] = [ 0, 0 ];

		return $this->bfs( $pairs );
	}

	/**
	 * BFS to find minimum steps to move all items to floor 4.
	 *
	 * State: [elevator_floor, [[gen_floor, chip_floor], ...]]
	 * Pairs are sorted for canonicalization (pairs are interchangeable).
	 *
	 * @param array $pairs Initial pairs of [gen_floor, chip_floor].
	 *
	 * @return integer
	 */
	private function bfs( array $pairs ): int {
		$n          = count( $pairs );
		$goal_pairs = array_fill( 0, $n, [ 3, 3 ] );
		$start      = $this->encode( 0, $pairs );
		$goal       = $this->encode( 3, $goal_pairs );

		if ( $start === $goal ) {
			return 0;
		}

		$visited = [ $start => true ];
		$queue   = [ [ 0, $pairs, 0 ] ]; // [elevator, pairs, steps]
		$head    = 0;

		while ( $head < count( $queue ) ) {
			[ $elev, $pairs, $steps ] = $queue[ $head++ ];

			// Collect all items on the current floor.
			$items = [];
			for ( $i = 0; $i < $n; $i++ ) {
				if ( $pairs[ $i ][0] === $elev ) {
					$items[] = [ $i, 'g' ];
				}
				if ( $pairs[ $i ][1] === $elev ) {
					$items[] = [ $i, 'c' ];
				}
			}

			// Generate all combinations of 1 or 2 items to move.
			$combos = [];
			$cnt    = count( $items );
			for ( $a = 0; $a < $cnt; $a++ ) {
				$combos[] = [ $items[ $a ] ];
				for ( $b = $a + 1; $b < $cnt; $b++ ) {
					$combos[] = [ $items[ $a ], $items[ $b ] ];
				}
			}

			// Try moving up and down.
			$dirs = [];
			if ( $elev < 3 ) {
				$dirs[] = 1;
			}
			if ( $elev > 0 ) {
				$dirs[] = -1;
			}

			foreach ( $dirs as $dir ) {
				$new_elev = $elev + $dir;

				foreach ( $combos as $combo ) {
					// Optimization: only move 2 items up, only move 1 item down.
					if ( $dir === 1 && count( $combo ) === 1 ) {
						// Still try single moves up (sometimes needed).
					}
					if ( $dir === -1 && count( $combo ) === 2 ) {
						continue;
					}

					$new_pairs = $pairs;
					foreach ( $combo as [ $idx, $type ] ) {
						if ( $type === 'g' ) {
							$new_pairs[ $idx ][0] = $new_elev;
						} else {
							$new_pairs[ $idx ][1] = $new_elev;
						}
					}

					if ( ! $this->is_valid( $new_pairs ) ) {
						continue;
					}

					$key = $this->encode( $new_elev, $new_pairs );

					if ( $key === $goal ) {
						return $steps + 1;
					}

					if ( ! isset( $visited[ $key ] ) ) {
						$visited[ $key ] = true;
						$queue[]         = [ $new_elev, $new_pairs, $steps + 1 ];
					}
				}
			}
		}

		return -1;
	}

	/**
	 * Checks if a state is valid (no chip fried).
	 *
	 * A chip is fried if it's on a floor with another generator but not its own.
	 *
	 * @param array $pairs The pairs to check.
	 *
	 * @return bool
	 */
	private function is_valid( array $pairs ): bool {
		$n = count( $pairs );

		for ( $i = 0; $i < $n; $i++ ) {
			$chip_floor = $pairs[ $i ][1];

			// Chip is safe if it's with its own generator.
			if ( $pairs[ $i ][0] === $chip_floor ) {
				continue;
			}

			// Check if any other generator is on this floor.
			for ( $j = 0; $j < $n; $j++ ) {
				if ( $j !== $i && $pairs[ $j ][0] === $chip_floor ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Encodes state as a string key, with pairs sorted for canonicalization.
	 *
	 * @param int   $elev  Elevator floor.
	 * @param array $pairs The pairs.
	 *
	 * @return string
	 */
	private function encode( int $elev, array $pairs ): string {
		$sorted = $pairs;
		sort( $sorted );

		return $elev . ':' . implode( ',', array_map( fn( $p ) => $p[0] . $p[1], $sorted ) );
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of [generator_floor, chip_floor] pairs.
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-11-test.txt' : '/data/day-11.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$generators = [];
		$chips      = [];

		foreach ( $lines as $floor => $line ) {
			preg_match_all( '/(\w+) generator/', $line, $gen_matches );
			foreach ( $gen_matches[1] as $name ) {
				$generators[ $name ] = $floor;
			}

			preg_match_all( '/(\w+)-compatible microchip/', $line, $chip_matches );
			foreach ( $chip_matches[1] as $name ) {
				$chips[ $name ] = $floor;
			}
		}

		$pairs = [];
		foreach ( $generators as $name => $gen_floor ) {
			$pairs[] = [ $gen_floor, $chips[ $name ] ];
		}

		return $pairs;
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
			'test' => 11,
			'real' => 31,
		],
		2 => [
			'test' => 0,
			'real' => 55,
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
