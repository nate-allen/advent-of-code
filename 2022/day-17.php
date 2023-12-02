<?php
ini_set( 'memory_limit', '1024M' );

/**
 * Day 17: Pyroclastic Flow
 */
class Day17 {

	private array $jets;
	private array $shapes;
	private array $screen;
	private array $row;
	private int $steps;

	// Index of the current shape.
	private int $current_shape = 0;

	// Index of the current jet stream direction.
	private int $current_jet = 0;

	// The top of the screen.
	private int $top = 1;

	// For part 2
	private int $multiplier = 0;

	public function __construct( $test ) {
		$this->jets = $this->get_jets( $test );

		$this->shapes = $this->get_shapes();
		$this->screen = array( array( '#', '#', '#', '#', '#', '#', '#', '#', '#' ) );
		$this->row    = array( '#', '.', '.', '.', '.', '.', '.', '.', '#' );
	}

	/**
	 * Part 1: How many units tall will the tower of rocks be after 2022 rocks have stopped falling?
	 *
	 * @return int
	 */
	public function part_1(): int {
		$this->steps = 2022;

		while( $this->steps-- ) {
			$rock   = $this->get_rock();
			$height = count( $rock );
			$rock_y = $this->top + 3;
			$rock_x = 3; // middle of the screen.

			// Add rows to the screen.
			while ( $rock_y + $height > count( $this->screen ) ) {
				$this->screen[] = $this->row;
			}

			while ( true ) {
				// Use modulus to keep it within the bounds of the array.
				$jet = $this->current_jet % count( $this->jets );

				$direction = $this->jets[ $jet ] === '>' ? 1 : -1;

				// Incremented the jet stream.
				$this->current_jet++;

				if ( ! $this->does_collide( $rock, $rock_x + $direction, $rock_y ) ) {
					$rock_x += $direction;
				}

				if ( ! $this->does_collide( $rock, $rock_x, $rock_y - 1 ) ) {
					$rock_y--;
				} else {
					foreach ( $rock as $key => $row ) {
						foreach ( $row as $x => $cell ) {
							if ( '#' === $cell ) {
								$this->screen[ $rock_y + $key ][ $rock_x + $x ] = '#';
							}
						}
					}
					$this->top = max( $rock_y + $height, $this->top );

					break;
				}
			}

			// Increment the shape and check if we've reached the end.
			if ( ++ $this->current_shape === count( $this->shapes ) ) {
				$this->current_shape = 0;
			}
		}

		return $this->top - 1;
	}

	/**
	 * Part 2:
	 *
	 * @return int
	 */
	public function part_2(): int {
		$this->steps    = 1000000000000;
		$pattern_found  = false;
		$pattern_length = 0;
		$pattern_next   = 0;
		$pattern_step   = 0;

		while( $this->steps-- ) {
			$rock   = $this->get_rock();
			$height = count( $rock );
			$rock_y = $this->top + 3;
			$rock_x = 3; // middle of the screen.

			// Add rows to the screen.
			while ( $rock_y + $height > count( $this->screen ) ) {
				$this->screen[] = $this->row;
			}

			while ( true ) {
				// Use modulus to keep it within the bounds of the array.
				$jet = $this->current_jet % count( $this->jets );

				$direction = $this->jets[ $jet ] === '>' ? 1 : -1;

				// Incremented the jet stream.
				$this->current_jet++;

				if ( ! $this->does_collide( $rock, $rock_x + $direction, $rock_y ) ) {
					$rock_x += $direction;
				}

				if ( ! $this->does_collide( $rock, $rock_x, $rock_y - 1 ) ) {
					$rock_y--;
				} else {
					foreach ( $rock as $key => $row ) {
						foreach ( $row as $x => $cell ) {
							if ( '#' === $cell ) {
								$this->screen[ $rock_y + $key ][ $rock_x + $x ] = '#';
							}
						}
					}
					$this->top = max( $rock_y + $height, $this->top );
					break;
				}
			}

			if ( 0 === $this->multiplier ) {
				if ( ! $pattern_found ) {
					$pattern = $this->check_for_pattern();
					if ( -1 !== $pattern ) {
						$pattern_found  = true;
						$pattern_next   = $this->top + $pattern;
						$pattern_length = $pattern;
						$pattern_step   = $this->steps;
					}
				} elseif ( $pattern_next === $this->top ) {
					$pattern_step    -= $this->steps;
					$this->multiplier = floor( $this->steps / $pattern_step );
					$this->steps     %= $pattern_step;
				}
			}

			// Increment the shape and check if we've reached the end.
			if ( ++ $this->current_shape === count( $this->shapes ) ) {
				$this->current_shape = 0;
			}
		}

		return $this->top - 1 + $this->multiplier * $pattern_length;
	}

	/**
	 * Does the shape collide with anything?
	 *
	 * @param $shape   array The shape to check.
	 * @param $shape_x int   The x position of the shape.
	 * @param $shape_y int   The y position of the shape.
	 *
	 * @return bool
	 */
	private function does_collide( array $shape, int $shape_x, int $shape_y ): bool {
		foreach ( $shape as $y => $r ) {
			foreach ( $r as $x => $state ) {
				if ( '#' === $state && '#' === $this->screen[ $shape_y + $y ][ $shape_x + $x ] ) {
					return true;
				}
			}
		}
		return false;
	}

	private function check_for_pattern() {
		$l   = $this->top - 1;
		$max = (int) ( count( $this->screen ) / 2 ) - 5;
		$len = $max;

		for ( $len; $len > count( $this->jets ) / 5; $len -- ) {
			$same = true;
			for ( $i = 0; $i < $len; $i ++ ) {
				$match = true;
				foreach ( $this->screen[ $l - $i ] as $ix => $el ) {
					if ( $el !== $this->screen[ $l - ( $i + $len ) ][ $ix ] ) {
						$match = false;
						break;
					}
				}
				if ( ! $match ) {
					$same = false;
					break;
				}
			}
			if ( $same ) {
				return $len;
			}
		}

		return - 1;
	}

	/**
	 * Parses the data from the input file.
	 *
	 * @param $test bool Whether to use the test data.
	 *
	 * @return array
	 */
	private function get_jets( $test = false ) {
		$path = $test ? '/data/day-17-test.txt' : '/data/day-17.txt';

		return str_split( trim( file_get_contents( __DIR__ . $path ) ) );
	}

	/**
	 * Returns an oddly-shaped rock.
	 *
	 * @return array
	 */
	private function get_rock() {
		return $this->shapes[ $this->current_shape ];
	}

	/**
	 * Returns an array of shapes representing the oddly-shaped rocks.
	 *
	 * @return array;
	 */
	private function get_shapes() {
		return array(
			array(
				array( '#', '#', '#', '#' ),
			),
			array(
				array( '.', '#', '.' ),
				array( '#', '#', '#' ),
				array( '.', '#', '.' ),
			),
			// upside down L
			array(
				array( '#', '#', '#' ),
				array( '.', '.', '#' ),
				array( '.', '.', '#' ),
			),
			array(
				array( '#' ),
				array( '#' ),
				array( '#' ),
				array( '#' ),
			),
			array(
				array( '#', '#' ),
				array( '#', '#' ),
			),
		);
	}
}

// Prompt which part to run and if it should use the test data.
while ( true ) {
	$part = trim( readline( 'Which part do you want to run? (1/2)' ) );
	if ( function_exists( "part_{$part}" ) ) {
		while ( true ) {
			$test = trim( strtolower( readline( 'Do you want to run the test? (y/n)' ) ) );
			if ( in_array( $test, array( 'y', 'n' ), true ) ) {
				$test = 'y' === $test;
				call_user_func( "part_{$part}", $test );
				break;
			}
			echo 'Please enter y or n' . PHP_EOL;
		}
		break;
	}
	echo 'Please enter 1 or 2' . PHP_EOL;
}

function part_1( $test = false ) {
	$start  = microtime( true );
	$day17  = new Day17( $test );
	$result = $day17->part_1();
	$end    = microtime( true );

	printf( 'The tower is this tall: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start  = microtime( true );
	$day17  = new Day17( $test );
	$result = $day17->part_2();
	$end    = microtime( true );

	printf( 'Result: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}
