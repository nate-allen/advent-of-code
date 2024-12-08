<?php

namespace AdventOfCode\Year2024;

/**
 * Day 08: Resonant Collinearity
 */
class Day08 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
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
	 * @return int
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Calculate unique locations of antinodes.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$antinodes = [];

		// For each frequency, find antinodes
		foreach ( $this->get_antennas_by_frequency() as $positions ) {
			$count = count( $positions );
			for ( $i = 0; $i < $count; $i ++ ) {
				for ( $j = $i + 1; $j < $count; $j ++ ) {
					$pos1 = $positions[ $i ]; // First antenna position
					$pos2 = $positions[ $j ]; // Second antenna position

					// Difference in x and y between the two antennas.
					$dx = $pos2[0] - $pos1[0];
					$dy = $pos2[1] - $pos1[1];

					// Calculate the first antinode position by extending the line backward
					$x1 = $pos1[0] - $dx;
					$y1 = $pos1[1] - $dy;

					// Make sure it's within the bounds of the map
					if ( $x1 >= 0 && $x1 < strlen( $this->data[0] ) && $y1 >= 0 && $y1 < count( $this->data ) ) {
						$antinodes["$x1,$y1"] = true;
					}

					// Calculate the second antinode position by extending the line forward
					$x2 = $pos2[0] + $dx;
					$y2 = $pos2[1] + $dy;

					// Also make sure it's within the bounds of the map
					if ( $x2 >= 0 && $x2 < strlen( $this->data[0] ) && $y2 >= 0 && $y2 < count( $this->data ) ) {
						$antinodes["$x2,$y2"] = true;
					}
				}
			}
		}

		// Return the total number of unique antinodes
		return count( $antinodes );
	}

	/**
	 * Part 2: How many unique locations within the bounds of the map contain an antinode?
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$antinodes = [];

		// Process each frequency group
		foreach ( $this->get_antennas_by_frequency() as $positions ) {
			$count = count( $positions );

			// Since all antennas are antinodes, add them all.
			foreach ( $positions as $antenna ) {
				$antinodes[ implode( ',', $antenna ) ] = true;
			}

			// Find all the points that line up with at least two antennas in a straight line
			for ( $i = 0; $i < $count; $i ++ ) {
				for ( $j = $i + 1; $j < $count; $j ++ ) {
					$pos1 = $positions[ $i ];
					$pos2 = $positions[ $j ];

					// Figure out how far apart the two antennas are
					$dx  = $pos2[0] - $pos1[0];
					$dy  = $pos2[1] - $pos1[1];

					// Extend the line in the forward direction (from pos1 to beyond pos2).
					$x = $pos1[0];
					$y = $pos1[1];
					while ( true ) {
						$x += $dx;
						$y += $dy;

						// Stop if we go out of bounds.
						if ( $x < 0 || $y < 0 || $y >= count( $this->data ) || $x >= strlen( $this->data[0] ) ) {
							break;
						}

						$antinodes[ "$x,$y" ] = true;
					}

					// Reset to the starting position
					$x = $pos1[0];
					$y = $pos1[1];

					// Now extend the line in the backward direction (from pos1 to before pos2).
					while ( true ) {
						$x -= $dx;
						$y -= $dy;

						// Stop if we go out of bounds.
						if ( $x < 0 || $y < 0 || $y >= count( $this->data ) || $x >= strlen( $this->data[0] ) ) {
							break;
						}

						$antinodes[ "$x,$y" ] = true;
					}
				}
			}
		}

		// Count unique antinodes
		return count($antinodes);
	}

	/**
	 * Parse the map and group antennas by frequency. ('A', '1', etc) and their positions.
	 *
	 * @return array
	 */
	private function get_antennas_by_frequency(): array {
		$antennas = [];

		foreach ( $this->data as $y => $line ) {
			for ( $x = 0; $x < strlen( $line ); $x ++ ) {
				$char = $line[ $x ];

				// Check if it's alphanumeric.
				if ( preg_match( '/^[a-zA-Z0-9]$/', $char ) ) {
					$antennas[ $char ][] = [ $x, $y ];
				}
			}
		}

		return $antennas;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-08-test.txt' : '/data/day-08.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		return $lines;
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param int $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day08  = new Day08( $test, $part );
	$result = $day08->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 14,
			'real' => 369,
		],
		2 => [
			'test' => 34,
			'real' => 1169,
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
