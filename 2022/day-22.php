<?php
/**
 * Day 22: Monkey Map
 */
class Day22 {

	private array $map = array();

	private array $instructions = array();

	private array $directions = array(
		array( 0, 1 ), // North
		array( 1, 0 ), // East
		array( 0, -1 ), // South
		array( -1, 0 ), // West
	);

	private array $borders;

	private array $offset;

	private int $scale;

	public function __construct( $part, $test ) {
		$this->parse_data( $test );
		$this->set_borders( $part, $test );
		$this->set_offset( $test );
		$this->scale = ! $test ? 50 : 4;
	}

	/**
	 * Part 1: Determine the password, which is the sum of 1000 times the row, 4 times the column, and the facing direction.
	 *
	 * Facing is 0 for right (>), 1 for down (v), 2 for left (<), and 3 for up (^).
	 *
	 * @return int
	 */
	public function part_1(): int {
		return $this->navigate_map();
	}

	/**
	 * Part 2: The map is actually a cube. What is the password now?
	 *
	 * @return int
	 */
	public function part_2(): int {
		return $this->navigate_map();
	}

	private function navigate_map() {
		$faces = array();

		for ( $face_number = 0; $face_number < 6; $face_number ++ ) {
			$face = array();
			for ( $row = 0; $row < $this->scale; $row ++ ) {
				$face[ $row ] = array();
				for ( $col = 0; $col < $this->scale; $col ++ ) {
					$row2 = $this->offset[ $face_number ][0] * $this->scale + $row;
					$col2 = $this->offset[ $face_number ][1] * $this->scale + $col;

					$face[ $row ][ $col ] = $this->map[ $row2 ][ $col2 ];
				}
			}
			$faces[] = $face;
		}

		$current_face      = 0;
		$current_row       = 0;
		$current_column    = array_search( '.', $faces[ $current_face ][0], true );
		$current_direction = 0;

		foreach ( $this->instructions as $instruction ) {
			if ( 'L' === $instruction ) {
				$current_direction = ( $current_direction + 3 ) % 4;
				continue;
			}
			if ( 'R' === $instruction ) {
				$current_direction = ( $current_direction + 1 ) % 4;
				continue;
			}

			for ( $i = 0; $i < $instruction; $i ++ ) {
				$new_row    = $current_row + $this->directions[ $current_direction ][0];
				$new_column = $current_column + $this->directions[ $current_direction ][1];

				// Don't wrap.
				if ( $new_row >= 0 && $new_row < $this->scale && $new_column >= 0 && $new_column < $this->scale ) {
					// Don't move if we're going to hit a wall.
					if ( '#' === $faces[ $current_face ][ $new_row ][ $new_column ] ) {
						break;
					}

					$current_row    = $new_row;
					$current_column = $new_column;
					continue;
				}

				// Get new face and direction because we're wrapping.
				$new_face      = $this->borders[ $current_face ][ $current_direction ][0];
				$new_direction = $this->borders[ $current_face ][ $current_direction ][1];

				$row3 = array(
					$new_row,
					$this->scale - 1 - $new_column,
					$this->scale - 1 - $new_row,
					$new_column,
				)[ $current_direction ];

				$col3 = array(
					$this->scale - 1 - $new_row,
					$new_column,
					$new_row,
					$this->scale - 1 - $new_column,
				)[ $current_direction ];

				$new_row    = array( $row3, 0, $this->scale - 1 - $row3, $this->scale - 1 )[ $new_direction ];
				$new_column = array( 0, $col3, $this->scale - 1, $this->scale - 1 - $col3 )[ $new_direction ];

				// Wall.
				if ( '#' === $faces[ $new_face ][ $new_row ][ $new_column ] ) {
					break;
				}

				$current_face      = $new_face;
				$current_direction = $new_direction;
				$current_row       = $new_row;
				$current_column    = $new_column;
			}
		}

		$row = $this->offset[ $current_face ][0] * $this->scale + $current_row;
		$col = $this->offset[ $current_face ][1] * $this->scale + $current_column;

		return ( ( $row + 1 ) * 1000 ) + ( ( $col + 1 ) * 4 ) + $current_direction;
	}

	/**
	 * Parse the data.
	 *
	 * @param string $test The test data.
	 *
	 * @return void
	 */
	private function parse_data( string $test ) {
		$path = $test ? '/data/day-22-test.txt' : '/data/day-22.txt';

		$data = explode( PHP_EOL.PHP_EOL, file_get_contents( __DIR__ . $path ) );

		$this->instructions = preg_split( '/([\/L\/R])/', $data[1], -1, PREG_SPLIT_DELIM_CAPTURE );

		foreach ( explode( PHP_EOL, $data[0] ) as $key => $value ) {
			$this->map[ $key ] = array();
			foreach ( str_split( $value ) as $k => $v ) {
				if ( in_array( $v, array( '#', '.' ), true ) ) {
					$this->map[ $key ][ $k ] = $v;
				}
			}
		}
	}

	/**
	 * Sets the borders. The borders are different depending on the part and if it's the test data.
	 *
	 * @param $part int  Is this part 1 or 2 of the puzzle?
	 * @param $test bool Is it the test data?
	 *
	 * @return void
	 */
	private function set_borders( int $part, bool $test ) {
		if ( 1 === $part ) {
			if ( ! $test ) {
				$this->borders = array(
					array( array( 1, 0 ), array( 2, 1 ), array( 1, 2 ), array( 4, 3 ) ),
					array( array( 0, 0 ), array( 1, 1 ), array( 0, 2 ), array( 1, 3 ) ),
					array( array( 2, 0 ), array( 4, 1 ), array( 2, 2 ), array( 0, 3 ) ),
					array( array( 4, 0 ), array( 5, 1 ), array( 4, 2 ), array( 5, 3 ) ),
					array( array( 3, 0 ), array( 0, 1 ), array( 3, 2 ), array( 2, 3 ) ),
					array( array( 5, 0 ), array( 3, 1 ), array( 5, 2 ), array( 3, 3 ) ),
				);
			} else {
				$this->borders = array(
					array( array( 0, 0 ), array( 3, 1 ), array( 0, 2 ), array( 4, 3 ) ),
					array( array( 2, 0 ), array( 1, 1 ), array( 3, 2 ), array( 1, 3 ) ),
					array( array( 3, 0 ), array( 2, 1 ), array( 1, 2 ), array( 2, 3 ) ),
					array( array( 1, 0 ), array( 4, 1 ), array( 2, 2 ), array( 0, 3 ) ),
					array( array( 5, 0 ), array( 0, 1 ), array( 5, 2 ), array( 3, 3 ) ),
					array( array( 4, 0 ), array( 5, 1 ), array( 4, 2 ), array( 5, 3 ) ),
				);
			}
		} elseif ( 2 === $part ) {
			if ( ! $test ) {
				$this->borders = array(
					array( array( 1, 0 ), array( 2, 1 ), array( 3, 0 ), array( 5, 0 ) ),
					array( array( 4, 2 ), array( 2, 2 ), array( 0, 2 ), array( 5, 3 ) ),
					array( array( 1, 3 ), array( 4, 1 ), array( 3, 1 ), array( 0, 3 ) ),
					array( array( 4, 0 ), array( 5, 1 ), array( 0, 0 ), array( 2, 0 ) ),
					array( array( 1, 2 ), array( 5, 2 ), array( 3, 2 ), array( 2, 3 ) ),
					array( array( 4, 3 ), array( 1, 1 ), array( 0, 1 ), array( 3, 3 ) ),
				);
			} else {
				$this->borders = array(
					array( array( 5, 2 ), array( 3, 1 ), array( 2, 1 ), array( 1, 1 ) ),
					array( array( 2, 0 ), array( 4, 3 ), array( 5, 3 ), array( 0, 1 ) ),
					array( array( 3, 0 ), array( 4, 0 ), array( 1, 2 ), array( 0, 0 ) ),
					array( array( 5, 1 ), array( 4, 1 ), array( 2, 2 ), array( 0, 3 ) ),
					array( array( 5, 0 ), array( 1, 3 ), array( 2, 3 ), array( 3, 3 ) ),
					array( array( 0, 2 ), array( 1, 0 ), array( 4, 2 ), array( 3, 2 ) ),
				);
			}
		}
	}

	/**
	 * Sets the offset. The offset is different depending on if it's the test data or not.
	 *
	 * @param $test bool Is it the test data?
	 *
	 * @return void
	 */
	private function set_offset( bool $test ) {
		if ( ! $test ) {
			$this->offset = array(
				array( 0, 1 ),
				array( 0, 2 ),
				array( 1, 1 ),
				array( 2, 0 ),
				array( 2, 1 ),
				array( 3, 0 ),
			);
		} else {
			$this->offset = array(
				array( 0, 2 ),
				array( 1, 0 ),
				array( 1, 1 ),
				array( 1, 2 ),
				array( 2, 2 ),
				array( 2, 3 ),
			);
		}
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
	$day22  = new Day22( 1, $test );
	$result = $day22->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start  = microtime( true );
	$day22  = new Day22( 2, $test );

	$result = $day22->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}
