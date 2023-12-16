<?php

/**
 * Day 10:Pipe Maze
 */
class Day10 {
	/**
	 * The raw puzzle data.
	 *
	 * @var string
	 */
	private string $data;

	/**
	 * Lines of the puzzle data.
	 *
	 * @var array
	 */
	private array $lines;

	private int $columns;

	/**
	 * The grid representation of the puzzle data.
	 *
	 * @var array
	 */
	private array $grid;

	/**
	 * Mapping of pipe characters to their respective movement offsets.
	 *
	 * @var array
	 */
	private array $pipes;

	/**
	 * Visited positions in the grid.
	 *
	 * @var array
	 */
	private array $visited = [];

	/**
	 * Day10 constructor.
	 * Initializes the puzzle data and pipes configuration.
	 *
	 * @param string $part Indicates the puzzle part.
	 * @param bool $test Indicates whether to use test data.
	 */
	public function __construct( $part, $test ) {
		$this->parse_data( $test, $part );

		$this->columns = $test ? 20 : 140;

		$this->pipes = [
			"|" => [ - $this->columns, $this->columns ],
			"-" => [ - 1, 1 ],
			"L" => [ - $this->columns, 1 ],
			"F" => [ $this->columns, 1 ],
			"7" => [ $this->columns, - 1 ],
			"J" => [ - $this->columns, - 1 ],
			"S" => [ $this->columns, - $this->columns, - 1, 1 ]
		];

		$this->grid = str_split( str_replace( "\n", '', $this->data ) );
	}

	/**
	 * Part 1: Calculates the number of steps along the loop from the start position to the farthest point.
	 *
	 * @return int The number of steps.
	 */
	public function part_1(): int {
		$position = strpos( $this->data, 'S' ) - 1;
		$distance = 0;
		$this->visited = [ $position => $distance ];

		// Loop to find the farthest point from the starting position.
		while ( ( $position = $this->get_next( $position ) ) !== null ) {
			// Increment the distance and add the new position as visited.
			$this->visited[ $position ] = ++ $distance;
		}

		// Return the farthest point.
		return count( $this->visited ) / 2;
	}

	/**
	 * Part 2: How many tiles are enclosed by the loop?
	 *
	 * @return int The answer.
	 */
	public function part_2(): int {
		$start = [ 0, 0 ];

		foreach ( $this->lines as $index => $line ) {
			$pos = strpos( $line, 'S' );

			if ( false !== $pos ) {
				$start = [ $pos, $index ];
				break;
			}
		}

		$directions_to_check = [
			[ 1, 0 ],
			[ 0, 1 ],
			[ -1, 0 ],
			[ 0, -1 ]
		];

		foreach ( $directions_to_check as $direction ) {
			$x_direction      = $direction[0];
			$y_direction      = $direction[1];
			$current_position = $start;
			$loop_positions   = [ $start ];
			$loop_found       = false;

			while ( true ) {
				$current_position = [ $current_position[0] + $x_direction, $current_position[1] + $y_direction ];

				if ( $current_position === $start ) {
					$loop_found = true;
					break;
				}

				$loop_positions[] = $current_position;

				$x = $current_position[0];
				$y = $current_position[1];

				if ( $this->is_out_of_bounds( $x, $y ) || $this->is_ground( $x, $y ) ) {
					break;
				}

				$new_directions = $this->get_new_direction( $x, $y, $x_direction, $y_direction );

				if ( false === $new_directions ) {
					break;
				}

				$x_direction = $new_directions[0];
				$y_direction = $new_directions[1];
			}

			if ( $loop_found ) {
				break;
			}
		}

		$sum = 0;
		foreach ( $loop_positions as $index => $pos ) {
			$next_position = $loop_positions[ ( $index + 1 ) % count( $loop_positions ) ];

			$sum += $pos[0] * $next_position[1] - $pos[1] * $next_position[0];
		}

		return abs( $sum / 2 ) - count( $loop_positions ) / 2 + 1;
	}

	/**
	 * Determines the next position in the grid based on current position and seen positions.
	 *
	 * @param int $position Current position in the grid.
	 *
	 * @return int|null The next position or null if none found.
	 */
	private function get_next( int $position ): ?int {
		$next_positions = [];

		foreach ( $this->pipes[ $this->grid[ $position ] ] as $move ) {
			$next_positions[] = $position + $move;
		}

		if ( 'S' === $this->grid[ $position ] ) {
			foreach ( $next_positions as $next_position ) {
				foreach ( $this->pipes[ $this->grid[ $next_position ] ] as $move ) {
					if ( $next_position + $move === $position ) {
						return $next_position;
					}
				}
			}
		}

		// Return the first unvisited position as the next move.
		foreach ( $next_positions as $next_position ) {
			if ( ! isset( $this->visited[ $next_position ] ) ) {
				return $next_position;
			}
		}

		// No more unvisited positions.
		return null;
	}

	function is_out_of_bounds( $x, $y ): bool {
		return $x < 0 || $y < 0 || $x >= strlen( $this->lines[0] ) || $y >= count( $this->lines );
	}

	function is_ground( $x, $y ): bool {
		return $this->lines[ $y ][ $x ] === '.';
	}

	function get_new_direction($x, $y, $direction_x, $direction_y): array|bool {
		$current = $this->lines[$y][$x];

		if ( '-' === $current && 0 !== $direction_x && 0 === $direction_y ) {
			return [ $direction_x, $direction_y ];
		} elseif ( '|' === $current && 0 !== $direction_y && 0 === $direction_x ) {
			return [ $direction_x, $direction_y ];
		} elseif ( $current === 'L' ) {
			if ( 0 === $direction_x && 1 === $direction_y ) {
				return [ 1, 0 ];
			} elseif ( -1 === $direction_x && 0 === $direction_y ) {
				return [ 0, - 1 ];
			}
		} elseif ( $current === 'J' ) {
			if ( 0 === $direction_x && 1 === $direction_y ) {
				return [ - 1, 0 ];
			} elseif ( 1 === $direction_x && 0 === $direction_y ) {
				return [ 0, - 1 ];
			}
		} elseif ( '7' === $current ) {
			if ( 0 === $direction_x && -1 === $direction_y ) {
				return [ - 1, 0 ];
			} elseif ( 1 === $direction_x && 0 === $direction_y ) {
				return [ 0, 1 ];
			}
		} elseif ( 'F' === $current ) {
			if ( 0 === $direction_x && -1 === $direction_y ) {
				return [ 1, 0 ];
			} elseif ( -1 === $direction_x && 0 === $direction_y ) {
				return [ 0, 1 ];
			}
		}

		return false;
	}

	/**
	 * Parses puzzle data from the file.
	 *
	 * @param string $test Flag to use test data.
	 * @param int    $part Puzzle part, 1 or 2.
	 */
	private function parse_data( string $test, int $part ): void {
		$file        = $test ? '/data/day-10-test.txt' : '/data/day-10.txt';
		$this->lines = file( __DIR__ . $file, FILE_IGNORE_NEW_LINES );
		$this->data  = file_get_contents( __DIR__ . $file );
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
	$day10    = new Day10( 1, $test );
	$result   = $day10->part_1();
	$end      = microtime( true );
	$expected = $test ? 80 : 6897;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day10    = new Day10( 2, $test );
	$result   = $day10->part_2();
	$end      = microtime( true );
	$expected = $test ? 10 : 367;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
