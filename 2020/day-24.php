<?php

namespace AdventOfCode\Year2020;

/**
 * Day 24: Lobby Layout
 */
class Day24 {
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

	/**
	 * The moves for each neighbor.
	 *
	 * @var array
	 */
	private array $moves = [
		'e'  => [ 1,  0 ],
		'se' => [ 0,  1 ],
		'sw' => [ -1, 1 ],
		'w'  => [ -1, 0 ],
		'nw' => [ 0, -1 ],
		'ne' => [ 1, -1 ],
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
	 * Part 1: Determine which tiles need to be flipped. Determine the number of black tiles.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return count( $this->get_initial_black_tiles() );
	}

	/**
	 * Part 2: Simulate 100 days of flipping tiles according to the rules.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		// Get the initial state from part 1.
		$black_tiles = $this->get_initial_black_tiles();

		$days = 100;
		for ( $day = 1; $day <= $days; $day++ ) {
			$neighbor_counts = [];

			// For each black tile, count all its neighbors.
			foreach ( $black_tiles as $key => $_ ) {
				[$q, $r] = explode( ',', $key );
				$q = (int) $q;
				$r = (int) $r;

				// Ensure the tile itself is recorded even if it might have 0 black neighbors.
				if ( ! isset( $neighbor_counts[ $key ] ) ) {
					$neighbor_counts[ $key ] = 0;
				}

				foreach ( $this->moves as $delta ) {
					$neighbor_q = $q + $delta[0];
					$neighbor_r = $r + $delta[1];
					$neighbor_key = "$neighbor_q,$neighbor_r";

					$neighbor_counts[ $neighbor_key ] = ($neighbor_counts[ $neighbor_key ] ?? 0) + 1;
				}
			}

			// Determine new state based on flipping rules.
			$new_black_tiles = [];
			foreach ( $neighbor_counts as $tile_key => $count ) {
				$is_black = isset( $black_tiles[ $tile_key ] );
				// Black tile stays if it has 1 or 2 black neighbors.
				if ( $is_black && ($count == 1 || $count == 2) ) {
					$new_black_tiles[ $tile_key ] = true;
				}
				// White tile becomes black if exactly 2 black neighbors.
				elseif ( ! $is_black && $count === 2 ) {
					$new_black_tiles[ $tile_key ] = true;
				}
			}

			$black_tiles = $new_black_tiles;
		}

		return count( $black_tiles );
	}

	/**
	 * Utility function to determine the initial set of black tiles.
	 *
	 * @return array
	 */
	private function get_initial_black_tiles(): array {
		$black_tiles = [];

		foreach ( $this->data as $line ) {
			// Extract tokens using regex.
			preg_match_all( '/(e|se|sw|w|nw|ne)/', $line, $matches );
			$tokens = $matches[0];

			$q = 0;
			$r = 0;

			foreach ( $tokens as $token ) {
				$delta = $this->moves[ $token ];

				$q += $delta[0];
				$r += $delta[1];
			}

			// Flip the tile: remove it if already black; otherwise add it.
			if ( isset( $black_tiles[ "$q,$r" ] ) ) {
				unset( $black_tiles[ "$q,$r" ] );
			} else {
				$black_tiles[ "$q,$r" ] = true;
			}
		}

		return $black_tiles;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-24-test.txt' : '/data/day-24.txt';
		// Each line is returned as a string
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
	$day24  = new Day24( $test, $part );
	$result = $day24->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 10,
			'real' => 282,
		],
		2 => [
			'test' => 2208,
			'real' => 3445,
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
