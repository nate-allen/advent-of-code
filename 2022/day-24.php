<?php
/**
 * Day 24:
 */
class Day24 {

	private array $blizzards;

	private array $map;

	public function __construct( $test ) {
		$this->parse_data( $test );
	}

	/**
	 * Part 1:
	 *
	 * @return int
	 */
	public function part_1(): int {
		return $this->run( array( 1, 0 ) );
	}

	/**
	 * Part 2: The map is actually a cube. What is the password now?
	 *
	 * @return int
	 */
	public function part_2(): int {
		return 0;
	}

	private function run( $start_position ) {
		$directions = array(
			array( 0, 1 ), // Down
			array( 0, - 1 ), // Up
			array( 1, 0 ), // left
			array( - 1, 0 ), // Right
			array( 0, 0 ), // Stop
		);

		$minimum   = array( 1, 1 );
		$maximum   = array( count( $this->map[0] ) - 2, count( $this->map ) - 2 );
		$targetY   = $start_position[1] == 0 ? $maximum[1] + 1 : 0;
		$positions = [ $start_position ];
		$step      = 0;

		while ( ++$step ) {
			// Move all blizzards.
			$map = $this->map;
			$newPositions = [];
			foreach ( $this->blizzards as $blizzard ) {
				if ( $blizzard['direction'][0] > 0 ) {
					$blizzard['position'][0] ++;
					if ( $blizzard['position'][0] > $maximum[0] ) {
						$blizzard['position'][0] = $minimum[0];
					}
				} elseif ( $blizzard['direction'][0] < 0 ) {
					$blizzard['position'][0] --;
					if ( $blizzard['position'][0] < $minimum[0] ) {
						$blizzard['position'][0] = $maximum[0];
					}
				} elseif ( $blizzard['direction'][1] > 0 ) {
					$blizzard['position'][1] ++;
					if ( $blizzard['position'][1] > $maximum[1] ) {
						$blizzard['position'][1] = $minimum[1];
					}
				} else {
					$blizzard['position'][1] --;
					if ( $blizzard['position'][1] < $minimum[1] ) {
						$blizzard['position'][1] = $maximum[1];
					}
				}
				$map[ $blizzard['position'][1] ][ $blizzard['position'][0] ] = false;
			}

			foreach ( $positions as $position ) {
				foreach ( $directions as $direction ) {
					if ( ! empty( $map[ $position[1] + $direction[1] ][ $position[0] + $direction[0] ] ) ) {
						$newPos = array( $position[0] + $direction[0], $position[1] + $direction[1] );
						// Check if we're at the target
						if ( $newPos[1] == $targetY ) {
							return $step;
						}
						$newPositions[ $newPos[0] . ',' . $newPos[1] ] = $newPos;
					}
				}
			}

			$positions = $newPositions;
		}

		return -1;
	}

	/**
	 * Parse the data.
	 *
	 * @param string $test The test data.
	 *
	 * @return void
	 */
	private function parse_data( string $test ) {
		$path = $test ? '/data/day-24-test.txt' : '/data/day-24.txt';

		$lines = array_map(
			function ( $line ) {
				return str_split( trim( $line ) );
			},
			explode( PHP_EOL, file_get_contents( __DIR__ . $path ) )
		);

		foreach ( $lines as $y => $row ) {
			foreach ( $row as $x => $value ) {
				$direction             = null;
				$this->map[ $y ][ $x ] = true;

				// $answer = readline('Continue?');
				// if ( 'n' === $answer ) die();

				switch ($value) {
					case '#':
						$this->map[ $y ][ $x ] = false;
						break;
					case '>':
						$direction = array( 1, 0 );
						break;
					case '<':
						$direction = array( - 1, 0 );
						break;
					case '^':
						$direction = array( 0, - 1 );
						break;
					case 'v':
						$direction = array( 0, 1 );
						break;
				}

				if ( $direction ) {
					$this->blizzards[] = array(
						'position' => array( $x, $y ),
						'direction' => $direction
					);
				}
			}
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
	$day24  = new Day24( 1, $test );
	$result = $day24->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start  = microtime( true );
	$day24  = new Day24( 2, $test );

	$result = $day24->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}
