<?php

/**
 * Day 17:
 */
class Day17 {
	private array $data;
	private int $rows;
	private int $columns;
	private array $directions = [
		'up'    => [ 0, - 1 ],  // Move up: No change in x, decrease y by 1
		'down'  => [ 0, 1 ],   // Move down: No change in x, increase y by 1
		'left'  => [ - 1, 0 ],  // Move left: Decrease x by 1, no change in y
		'right' => [ 1, 0 ]    // Move right: Increase x by 1, no change in y
	];

	public function __construct(string $test, int $part) {
		$this->parse_data($test, $part);
		$this->rows    = count($this->data);
		$this->columns = strlen($this->data[0]);
	}

	/**
	 * Part 1: The crucible can move a maximum of 3 blocks in a straight line. What is the least heat loss the crucible can incur?
	 *
	 * @return int The minimum heat loss.
	 */
	public function part_1(): int {
		return $this->find_min_heat_loss( [ $this->columns - 1, $this->rows - 1 ], 1, 3 );
	}

	/**
	 * Part 2: The ultra crucible moves a minimum of 4 spaces but a maximum of 10, what is the least heat loss it can incur?
	 *
	 * @return int The minimum heat loss.
	 */
	public function part_2(): int {
		return $this->find_min_heat_loss( [ $this->columns - 1, $this->rows - 1 ], 4, 10 );
	}

	/**
	 * Find the minimum heat loss within specified distance constraints.
	 *
	 * @param array $target_position The target position (end point).
	 * @param int   $min_distance    The minimum distance before a direction change counts.
	 * @param int   $max_distance    The maximum distance allowed for a single direction.
	 *
	 * @return int The minimum heat loss encountered on the path to the target.
	 */
	private function find_min_heat_loss( array $target_position, int $min_distance, int $max_distance ): int {
		// Starting with 0 heat loss, at position 0,0, coming from outside the grid, with 0 moves
		$queue         = [ [ 0, [ 0, 0, 'down', 0 ] ] ];
		$visited       = []; // Track visited
		$min_heat_loss = PHP_INT_MAX; // Start with a large number

		while ( ! empty( $queue ) ) {
			// Sort the queue based on the heat loss
			usort( $queue, function ( $a, $b ) {
				return $a[0] - $b[0];
			} );

			// Get next result with the lowest heat loss
			[ $current_heat_loss, [ $x, $y, $current_direction, $number_of_moves ] ] = array_shift( $queue );

			// If we reached the end, update the minimum heat loss
			if ( [ $x, $y ] === $target_position ) {
				$min_heat_loss = min( $min_heat_loss, $current_heat_loss );
			}

			// Try neighboring blocks
			foreach ( $this->directions as $new_direction => $move ) {
				$new_x = $x + $move[0];
				$new_y = $y + $move[1];

				// Check if the new position is within the grid and not going back
				if ( $new_x < 0 || $new_x >= $this->columns || $new_y < 0 || $new_y >= $this->rows || $this->is_opposite_direction( $current_direction, $new_direction ) ) {
					continue;
				}

				// Calculate the number of moves in the new direction
				$new_direction_change_count = $new_direction === $current_direction ? $number_of_moves + 1 : 1;

				// Check if the new position is within the distance allowed
				if ( $new_direction_change_count > $max_distance || ( $current_heat_loss && $new_direction !== $current_direction && $number_of_moves < $min_distance ) ) {
					continue;
				}

				// Get heat loss for the new position
				$new_heat_loss = $current_heat_loss + (int) ( $this->data[ $new_y ][ $new_x ] );
				$position_key  = "$new_x,$new_y,$new_direction,$new_direction_change_count";

				// Update the priority queue and visited blocks
				if ( ! isset( $visited[ $position_key ] ) || $visited[ $position_key ] > $new_heat_loss ) {
					$visited[ $position_key ] = $new_heat_loss;
					$queue[]                  = [
						$new_heat_loss,
						[ $new_x, $new_y, $new_direction, $new_direction_change_count ]
					];
				}
			}
		}

		return $min_heat_loss;
	}

	/**
	 * Determines if two directions are opposite.
	 *
	 * @param string $current_direction The current direction.
	 * @param string $new_direction     The new direction.
	 *
	 * @return bool True if the directions are opposite, otherwise false.
	 */
	private function is_opposite_direction( string $current_direction, string $new_direction): bool {
		$opposites = [
			'up'    => 'down',
			'down'  => 'up',
			'left'  => 'right',
			'right' => 'left'
		];

		return $opposites[ $current_direction ] === $new_direction;
	}

	private function parse_data(string $test, int $part): void {
		$file = $test ? '/data/day-17-test.txt' : '/data/day-17.txt';
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
	$day17    = new Day17( $test, 1 );
	$result   = $day17->part_1();
	$end      = microtime( true );
	$expected = $test ? 102 : 855;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day17    = new Day17( $test, 2 );
	$result   = $day17->part_2();
	$end      = microtime( true );
	$expected = $test ? 94 : 980;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
