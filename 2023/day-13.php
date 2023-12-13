<?php

/**
 * Day 13: Point of Incidence
 */
class Day13 {
	private array $data;

	private int $part;

	/**
	 * Constructor to parse the data.
	 *
	 * @param string $test Flag to use test data.
	 * @param int $part Puzzle part, 1 or 2.
	 */
	public function __construct( string $test, int $part ) {
		$this->parse_data( $test, $part );
		$this->part = $part;
	}

	/**
	 * Part 1: Find the reflection line in each pattern and calculate a score based on its position.
	 *
	 * @return int The answer.
	 */
	public function part_1(): int {
		$total_score = 0;

		foreach ( $this->data as $pattern ) {
			$total_score += $this->solve_pattern( $pattern );
		}

		return $total_score;
	}

	/**
	 * Part 2: Fix a single error in each pattern to find a new reflection line, then compute the score based on it.
	 *
	 * @return int The answer.
	 */
	public function part_2(): int {
		$total_score = 0;

		foreach ( $this->data as $pattern ) {
			$total_score += $this->solve_pattern( $pattern );
		}

		return $total_score;
	}

	/**
	 * Solves a single pattern.
	 *
	 * @param string $pattern The pattern to solve.
	 *
	 * @return int The score for the pattern.
	 */
	private function solve_pattern( string $pattern ): int {
		$grid           = array_map( 'str_split', explode( "\n", $pattern ) );
		$reflection_row = $this->find_reflection( $grid );

		if ( null !== $reflection_row ) {
			return 100 * ( $reflection_row + 1 );
		}

		$transposed_grid   = $this->transpose( $grid );
		$reflection_column = $this->find_reflection( $transposed_grid );

		if ( null !== $reflection_column ) {
			return $reflection_column + 1;
		}

		return 0;
	}

	/**
	 * Swaps rows and columns.
	 *
	 * @param array $grid The grid to transpose.
	 *
	 * @return array The transposed grid.
	 */
	private function transpose( array $grid ): array {
		return array_map( null, ...$grid );
	}

	/**
	 * Finds the line of reflection in the grid.
	 *
	 * @param array $grid The grid to check.
	 *
	 * @return int|null The index of the reflection line or null if not found.
	 */
	private function find_reflection( array $grid ): ?int {
		$length = count( $grid );

		// In part 1, we're looking for reflections with no mismatches.
		// In part 2, we're looking for a mismatch (and it's mirrored counterpart).
		$non_matches = $this->part === 1 ? 0 : 2;

		for ( $i = 0; $i < $length - 1; $i ++ ) {
			$mismatches = 0;
			for ( $j = 0; $j < $length; $j ++ ) {
				$mirrored_index = $i + 1 + ( $i - $j );
				if ( $mirrored_index >= 0 && $mirrored_index < $length ) {
					$mismatches += count( array_diff_assoc( $grid[ $j ], $grid[ $mirrored_index ] ) );
				}
			}

			// If we found a reflection with the correct number of mismatches, return it.
			if ( $mismatches === $non_matches ) {
				return $i;
			}
		}

		return null;
	}

	/**
	 * Parses the puzzle data from the file.
	 *
	 * @param string $test Flag to use test data.
	 * @param int $part Puzzle part, 1 or 2.
	 */
	private function parse_data( string $test, int $part ): void {
		$file       = $test ? '/data/day-13-test.txt' : '/data/day-13.txt';
		$this->data = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) ); // Patterns are separated by double newlines
	}
}

// Prompt which part to run and if it should use the test data.
while ( true ) {
	$part = trim( readline( 'Which part do you want to run? (1/2)' ) );
	if ( function_exists( "part_$part" ) ) {
		while ( true ) {
			$test = trim( strtolower( readline( 'Do you want to run the test? (y/n)' ) ) );
			if ( in_array( $test, array( 'y', 'n' ), true ) ) {
				$test = 'y' === $test;
				call_user_func( "part_$part", $test );
				break;
			}
			echo 'Please enter y or n' . PHP_EOL;
		}
		break;
	}
	echo 'Please enter 1 or 2' . PHP_EOL;
}

function part_1( $test = false ) {
	$start    = microtime( true );
	$day13    = new Day13( $test, 1 );
	$result   = $day13->part_1();
	$end      = microtime( true );
	$expected = $test ? 405 : 36015;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day13    = new Day13( $test, 2 );
	$result   = $day13->part_2();
	$end      = microtime( true );
	$expected = $test ? 400 : 35335;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
