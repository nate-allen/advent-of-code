<?php

/**
 * Day 11: Cosmic Expansion
 */
class Day11 {
	/**
	 * The grid representing the universe.
	 *
	 * @var array
	 */
	private array $grid;

	/**
	 * Coordinates of all the galaxies.
	 *
	 * @var array
	 */
	private array $galaxies = [];

	/**
	 * Parses the data, expands the universe, and finds the galaxies.
	 *
	 * @param string $test Flag to use test data.
	 * @param int    $part Puzzle part, 1 or 2.
	 */
	public function __construct( string $test, int $part ) {
		$this->parse_data( $test, $part );
		$this->expand_universe();
		$this->find_galaxies();
	}

	/**
	 * Solve part 1 of the puzzle.
	 * Calculates the sum of the Manhattan distances between each pair of galaxies.
	 *
	 * @return int The sum of all Manhattan distances.
	 */
	public function part_1(): int {
		$sum = 0;

		for ( $i = 0; $i < count( $this->galaxies ); $i ++ ) {
			for ( $j = $i + 1; $j < count( $this->galaxies ); $j ++ ) {
				// Calculate "Manhattan distance" for each pair of galaxies.
				$x1 = $this->galaxies[ $i ][0];
				$y1 = $this->galaxies[ $i ][1];
				$x2 = $this->galaxies[ $j ][0];
				$y2 = $this->galaxies[ $j ][1];

				$sum += abs( $x1 - $x2 ) + abs( $y1 - $y2 );
			}
		}

		return $sum;
	}

	/**
	 * Expands the universe by doubling rows and columns with only empty space.
	 */
	private function expand_universe(): void {
		$new_rows = [];

		// Check for rows with only empty space.
		foreach ( $this->grid as $row ) {
			// If the row has no galaxies, we will add an extra empty row.
			if ( count( array_unique( $row ) ) === 1 && $row[0] === '.' ) {
				$new_rows[] = $row;
			}

			$new_rows[] = $row;
		}

		// New grid to include expanded columns
		$new_grid = array_fill( 0, count( $new_rows ), [] );

		for ( $i = 0; $i < count( $new_rows[0] ); $i ++ ) {
			$just_stars = true;

			for ( $j = 0; $j < count( $new_rows ); $j ++ ) {
				if ( '.' !== $new_rows[ $j ][ $i ] ) {
					$just_stars = false;
					break;
				}
			}

			// Double the column if it's all empty space
			for ( $j = 0; $j < count( $new_rows ); $j ++ ) {
				if ( $just_stars ) {
					$new_grid[ $j ][] = '.';
					$new_grid[ $j ][] = '.';
				} else {
					$new_grid[ $j ][] = $new_rows[ $j ][ $i ];
				}
			}
		}

		$this->grid = $new_grid;
	}

	/**
	 * Locates the galaxies in the expanded universe grid.
	 */
	private function find_galaxies(): void {
		// Iterate over the grid to find the galaxies
		for ( $i = 0; $i < count( $this->grid[0] ); $i ++ ) {
			for ( $j = 0; $j < count( $this->grid ); $j ++ ) {
				if ( $this->grid[ $j ][ $i ] === '#' ) {
					$this->galaxies[] = [ $i, $j ];
				}
			}
		}
	}

	/**
	 * Parses the puzzle data from the file.
	 *
	 * @param string $test Flag to use test data.
	 * @param int    $part Puzzle part, 1 or 2.
	 */
	private function parse_data( string $test, int $part ): void {
		$file       = $test ? '/data/day-11-test.txt' : '/data/day-11.txt';
		$this->grid = array_map( 'str_split', explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) ) );
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
	$day11    = new Day11( $test, 1 );
	$result   = $day11->part_1();
	$end      = microtime( true );
	$expected = $test ? 374 : 10289334;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day11    = new Day11( $test, 2 );
	$result   = $day11->part_2();
	$end      = microtime( true );
	$expected = $test ? 0 : 0;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
