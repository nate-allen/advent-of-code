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
	 * The states of the grid after each cycle.
	 *
	 * @var array
	 */
	private array $states = [];

	/**
	 * The directions to tilt the platform.
	 *
	 * @var array
	 */
	private array $directions = ['north', 'west', 'south', 'east'];

	/**
	 * The number of cycles to run for part 2.
	 */
	private int $cycles = 1_000_000_000;

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
		$this->tilt( $this->directions[0] );

		return $this->calculate_load();
	}

	/**
	 * Part 2: Run the spin cycle for 1000000000 cycles, then calculate the total load on the north support beams.
	 *
	 * @return int The total load.
	 */
	public function part_2(): int {
		$remaining_cycles = $this->cycles;
		$cycle_found      = false;

		for ( $i = 0; $i < $this->cycles; $i ++ ) {
			$this->perform_spin_cycle();
			$state = $this->serialize_grid();

			if ( isset( $this->states[ $state ] ) ) {
				// Calculate the length of the cycle
				$cycle_length = $i - $this->states[ $state ];
				// Calculate remaining cycles after detecting the cycle
				$remaining_cycles = ( $this->cycles - $i - 1 ) % $cycle_length;
				$cycle_found      = true;
				break;
			}

			$this->states[ $state ] = $i;
		}

		if ( $cycle_found ) {
			// Only perform the remaining cycles after the cycle is detected
			for ( $i = 0; $i < $remaining_cycles; $i ++ ) {
				$this->perform_spin_cycle();
			}
		}

		return $this->calculate_load();
	}

	/**
	 * Tilts the platform in the specified direction, causing rounded rocks to move.
	 *
	 * @param string $direction The direction to tilt ('north', 'south', 'east', 'west')
	 */
	private function tilt( string $direction ): void {
		$height = count($this->grid);
		$width = count($this->grid[0]);

		switch ($direction) {
			case 'west':
			case 'north':
				for ($y = 0; $y < $height; $y++) {
					for ($x = 0; $x < $width; $x++) {
						$this->move_rock($x, $y, $direction);
					}
				}
				break;

			case 'south':
				for ($y = $height - 1; $y >= 0; $y--) {
					for ($x = 0; $x < $width; $x++) {
						$this->move_rock($x, $y, $direction);
					}
				}
				break;

			case 'east':
				for ($y = 0; $y < $height; $y++) {
					for ($x = $width - 1; $x >= 0; $x--) {
						$this->move_rock($x, $y, $direction);
					}
				}
				break;

			default:
				die( 'How did you even get here lol' );
		}
	}

	/**
	 * Moves a rock based in the direction.
	 *
	 * @param int    $x         The x-coordinate of the rock
	 * @param int    $y         The y-coordinate of the rock
	 * @param string $direction The direction to move
	 */
	private function move_rock( int $x, int $y, string $direction ): void {
		if ( 'O' !== $this->grid[ $y ][ $x ] ) {
			return;
		}

		$height = count( $this->grid );
		$width  = count( $this->grid[0] );

		switch ( $direction ) {
			case 'north':
				for ( $i = $y - 1; $i >= 0; $i -- ) {
					if ( '#' === $this->grid[ $i ][ $x ] || 'O' === $this->grid[ $i ][ $x ] ) {
						$this->grid[ $y ][ $x ]     = '.';
						$this->grid[ $i + 1 ][ $x ] = 'O';
						break;
					}

					if ( 0 === $i ) {
						$this->grid[ $y ][ $x ] = '.';
						$this->grid[0][ $x ]    = 'O';
					}
				}
				break;

			case 'south':
				for ( $i = $y + 1; $i < $height; $i ++ ) {
					if ( '#' === $this->grid[ $i ][ $x ] || 'O' === $this->grid[ $i ][ $x ] ) {
						$this->grid[ $y ][ $x ]     = '.';
						$this->grid[ $i - 1 ][ $x ] = 'O';
						break;
					}

					if ( $i === $height - 1 ) {
						$this->grid[ $y ][ $x ]          = '.';
						$this->grid[ $height - 1 ][ $x ] = 'O';
					}
				}
				break;

			case 'east':
				for ( $i = $x + 1; $i < $width; $i ++ ) {
					if ( '#' === $this->grid[ $y ][ $i ] || 'O' === $this->grid[ $y ][ $i ] ) {
						$this->grid[ $y ][ $x ]     = '.';
						$this->grid[ $y ][ $i - 1 ] = 'O';
						break;
					}

					if ( $i === $width - 1 ) {
						$this->grid[ $y ][ $x ]         = '.';
						$this->grid[ $y ][ $width - 1 ] = 'O';
					}
				}
				break;

			case 'west':
				for ( $i = $x - 1; $i >= 0; $i -- ) {
					if ( '#' === $this->grid[ $y ][ $i ] || 'O' === $this->grid[ $y ][ $i ] ) {
						$this->grid[ $y ][ $x ]     = '.';
						$this->grid[ $y ][ $i + 1 ] = 'O';
						break;
					}

					if ( 0 === $i ) {
						$this->grid[ $y ][ $x ] = '.';
						$this->grid[ $y ][0]    = 'O';
					}
				}
				break;
		}
	}

	/**
	 * Calculates the total load on the north support beams.
	 *
	 * @return int The total load.
	 */
	private function calculate_load(): int {
		$sum = 0;
		for ( $y = count( $this->grid ) - 1; $y >= 0; $y -- ) {
			for ( $x = 0; $x < count( $this->grid[ $y ] ); $x ++ ) {
				if ( $this->grid[ $y ][ $x ] === 'O' ) {
					$sum += ( count( $this->grid ) - $y );
				}
			}
		}

		return $sum;
	}

	/**
	 * Performs one cycle by tilting north, west, south, and east.
	 */
	public function perform_spin_cycle(): void {
		foreach ( $this->directions as $direction ) {
			$this->tilt( $direction );
		}
	}

	private function serialize_grid(): string {
		return implode("\n", array_map(fn($row) => implode('', $row), $this->grid));
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
	$expected = $test ? 64 : 93102;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
