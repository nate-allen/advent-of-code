<?php

/**
 * Day 14: Parabolic Reflector Dish
 */
class Day14 {

	/**
	 * The puzzle data represented as a grid.
	 *
	 * @var array
	 */
	private array $grid;

	/**
	 * Constructor to parse the data.
	 *
	 * @param string $test Flag to use test data.
	 * @param int    $part Puzzle part, 1 or 2.
	 */
	public function __construct(string $test, int $part) {
		$this->parse_data($test, $part);
	}

	/**
	 * Part 1: Calculate the total load on the north support beams.
	 *
	 * @return int The total load.
	 */
	public function part_1(): int {
		$this->tilt_north();

		return $this->calculate_load();
	}


	/**
	 * Part 2: Run the spin cycle for 1000000000 cycles, then calculate the total load on the north support beams.
	 *
	 * @return int The total load.
	 */
	public function part_2(): int {
		return 0;
	}

	/**
	 * Tilts the platform north, causing rounded rocks to move up.
	 */
	private function tilt_north(): void {
		// Loop over each row in the grid, starting from the top
		for ( $y = 0; $y < count( $this->grid ); $y ++ ) {
			// Loop over each column in the current row
			for ( $x = 0; $x < count( $this->grid[ $y ] ); $x ++ ) {
				// Check if the current cell contains a rounded rock
				if ( 'O' === $this->grid[ $y ][ $x ] ) {
					// If a rock is found, scan upwards in the same column
					for ( $i = $y - 1; $i >= 0; $i -- ) {
						// Check for an obstacle, like another rock 'O' or a '#'
						if ( '#' === $this->grid[ $i ][ $x ] || 'O' === $this->grid[ $i ][ $x ] ) {
							// Move the rock to the position just below the obstacle
							$this->grid[ $y ][ $x ]     = '.'; // Clear the original position of the rock
							$this->grid[ $i + 1 ][ $x ] = 'O'; // Place the rock below the obstacle
							break; // Stop scanning
						}

						// If the top row is reached and no obstacle is found
						if ( 0 === $i ) {
							// Move the rock to the top row
							$this->grid[ $y ][ $x ] = '.'; // Clear the original position of the rock
							$this->grid[0][ $x ]    = 'O'; // Place the rock at the top row
							break; // Stop scanning
						}
					}
				}
			}
		}
	}

	/**
	 * Calculates the total load on the north support beams.
	 *
	 * @return int The total load.
	 */
	private function calculate_load(): int {
		$sum = 0;
		for ($y = count($this->grid) - 1; $y >= 0; $y--) {
			for ($x = 0; $x < count($this->grid[$y]); $x++) {
				if ($this->grid[$y][$x] === 'O') {
					$sum += (count($this->grid) - $y);
				}
			}
		}
		return $sum;
	}

	/**
	 * Parses the puzzle data from the file.
	 *
	 * @param string $test Flag to use test data.
	 * @param int    $part Puzzle part, 1 or 2.
	 */
	private function parse_data(string $test, int $part): void {
		$file = $test ? '/data/day-14-test.txt' : '/data/day-14.txt';
		$lines = explode("\n", trim(file_get_contents(__DIR__ . $file)));
		$this->grid = array_map(fn($line) => str_split($line), $lines);
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
	$day14    = new Day14( $test, 1 );
	$result   = $day14->part_1();
	$end      = microtime( true );
	$expected = $test ? 136 : 109385;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day14    = new Day14( $test, 2 );
	$result   = $day14->part_2();
	$end      = microtime( true );
	$expected = $test ? 64 : 0;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
