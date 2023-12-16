<?php

/**
 * Day 16:
 */
class Day16 {
	/**
	 * The puzzle data.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * Movement directions for the beam.
	 *
	 * @var array|array[]
	 */
	private array $movement = [
		'right' => [ 0, 1 ],
		'down'  => [ 1, 0 ],
		'left'  => [ 0, -1 ],
		'up'    => [ -1, 0 ]
	];

	public function __construct(string $test, int $part) {
		$this->parse_data($test, $part);
	}

	/**
	 * Part 1: Find The number of tiles the beam will pass through.
	 *
	 * @return int The number of energized tiles.
	 */
	public function part_1(): int {
		return $this->count_energized( $this->data, [ 0, - 1, $this->movement['right'] ] );
	}

	/**
	 * Part 2: Find The maximum number of tiles energized from any starting position on the edge of the grid.
	 *
	 * @return int The max number of energized tiles.
	 */
	public function part_2(): int {
		$grid = $this->data;
		$rows = count( $grid );
		$cols = strlen( $grid[0] );
		$max  = 0;

		for ($i = 0; $i < $rows; $i++) {
			$max = max( $max, $this->count_energized( $grid, [ $i, -1, $this->movement[ 'right' ] ] ) );
			$max = max( $max, $this->count_energized( $grid, [ $i, $cols, $this->movement[ 'left' ] ] ) );
		}

		for ($j = 0; $j < $cols; $j++) {
			$max = max( $max, $this->count_energized( $grid, [ -1, $j, $this->movement['down'] ] ) );
			$max = max( $max, $this->count_energized( $grid, [ $rows, $j, $this->movement['up'] ] ) );
		}

		return $max;
	}

	/**
	 * Tracks the path of the beam.
	 *
	 * @param array $grid    The grid representing the puzzle.
	 * @param array $value   The initial position and direction.
	 * @param array &$matrix The matrix to track energized tiles.
	 */
	private function trace_beam_path( array $grid, array $value, array &$matrix ): void {
		$seen  = [ $value ];
		$queue = [ $value ];

		while ( ! empty( $queue ) ) {
			[ $x, $y, $direction ] = array_shift( $queue );

			if ( $x >= 0 && $y >= 0 && $x < count( $grid ) && $y < strlen( $grid[ $x ] ) ) {
				$matrix[ $x ][ $y ] ++;
			}

			$new_x = $x + $direction[0];
			$new_y = $y + $direction[1];

			if ( $new_x < 0 || $new_y < 0 || $new_x >= count( $grid ) || $new_y >= strlen( $grid[ $new_x ] ) ) {
				continue;
			}

			$char    = $grid[ $new_x ][ $new_y ];
			$updates = $this->get_updates( $char, $direction );

			foreach ( $updates as $new_direction ) {
				$newPos = [ $new_x, $new_y, $new_direction ];

				if ( ! in_array( $newPos, $seen, true ) ) {
					$seen[]  = $newPos;
					$queue[] = $newPos;
				}
			}
		}
	}

	/**
	 * Gets the new directions based on the current tile and beam direction.
	 *
	 * @param string $char      The character on the current tile.
	 * @param array  $direction The current direction of the beam.
	 *
	 * @return array The new directions for the beam.
	 */
	private function get_updates( string $char, array $direction ): array {
		$updates = [];

		switch ( $char ) {
			case '.':
				$updates[] = $direction;
				break;
			case '|':
				if ( $direction === $this->movement['down'] || $direction === $this->movement['up'] ) {
					$updates[] = $direction;
				} else {
					$updates[] = $this->movement['down'];
					$updates[] = $this->movement['up'];
				}
				break;
			case '-':
				if ( $direction === $this->movement['left'] || $direction === $this->movement['right'] ) {
					$updates[] = $direction;
				} else {
					$updates[] = $this->movement['left'];
					$updates[] = $this->movement['right'];
				}
				break;
			case '\\':
				$updates[] = $this->get_reflection( $direction, '\\' );
				break;
			case '/':
				$updates[] = $this->get_reflection( $direction, '/' );
				break;
		}

		return $updates;
	}

	/**
	 * Calculates the reflection direction based on the mirror type.
	 *
	 * @param array  $direction The current direction of the beam.
	 * @param string $mirror    The type of mirror (/ or \\).
	 *
	 * @return array The new direction after reflection.
	 */
	private function get_reflection( array $direction, string $mirror ): array {
		if ( $mirror === '\\' ) {
			if ( $direction === $this->movement['down'] ) {
				return $this->movement['right'];
			} elseif ( $direction === $this->movement['left'] ) {
				return $this->movement['up'];
			} elseif ( $direction === $this->movement['up'] ) {
				return $this->movement['left'];
			} else {
				return $this->movement['down'];
			}
		} elseif ( $mirror === '/' ) {
			if ( $direction === $this->movement['down'] ) {
				return $this->movement['left'];
			} elseif ( $direction === $this->movement['left'] ) {
				return $this->movement['down'];
			} elseif ( $direction === $this->movement['up'] ) {
				return $this->movement['right'];
			} else {
				return $this->movement['up'];
			}
		} else {
			die( 'Not a valid mirror type dude' );
		}
	}

	/**
	 * Counts the number of energized tiles
	 *
	 * @param array $grid The grid representing the puzzle.
	 * @param array $start The starting position and direction.
	 *
	 * @return int The number of energized tiles.
	 */
	private function count_energized( array $grid, array $start ): int {
		$rows   = count( $grid );
		$cols   = strlen( $grid[0] );
		$matrix = array_fill( 0, $rows, array_fill( 0, $cols, 0 ) );

		$this->trace_beam_path( $grid, $start, $matrix );

		$total = 0;

		foreach ( $matrix as $row ) {
			$total += array_sum( array_map( fn( $x ) => min( $x, 1 ), $row ) );
		}

		return $total;
	}

	/**
	 * Parses the puzzle data from the file.
	 *
	 * @param string $test Flag to use test data.
	 * @param int    $part Puzzle part, 1 or 2.
	 */
	private function parse_data(string $test, int $part): void {
		$file = $test ? '/data/day-16-test.txt' : '/data/day-16.txt';
		$this->data = explode("\n", trim(file_get_contents(__DIR__ . $file)));
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
	$day16    = new Day16( $test, 1 );
	$result   = $day16->part_1();
	$end      = microtime( true );
	$expected = $test ? 46 : 7543;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day16    = new Day16( $test, 2 );
	$result   = $day16->part_2();
	$end      = microtime( true );
	$expected = $test ? 51 : 0;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
