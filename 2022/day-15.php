<?php
// Increase the memory limit lol
ini_set( 'memory_limit', '512M' );

/**
 * Day 15: Beacon Exclusion Zone
 */
class Day15 {

	/**
	 * Part 1: In the row where y=2000000, how many positions cannot contain a beacon?
	 *
	 * @return int
	 */
	public function part1() {
		$line_to_check = 2000000;
		$sensors       = $this->get_sensors();
		$grid          = array(
			$line_to_check => array(),
		);

		// Loop through each sensor and beacon.
		foreach ( $sensors as $sensor_data ) {
			$sensor   = $sensor_data[0];
			$beacon   = $sensor_data[1];
			$distance = $sensor_data[2];

			// If the distance between the line to check and the sensor is less than or equal
			// to the set distance, then we know that the beacon will be detected.
			if ( abs( $line_to_check - $sensor[1] ) <= $distance ) {

				// Calculate the y distance between the beacon and the line to check.
				$y = abs( $line_to_check - $sensor[1] );

				// Loop through each x coordinate from -dist to dist.
				for ( $i = -$distance; $i <= $distance; ++$i ) {
					// Get the x coordinate.
					$x = $sensor[0] + $i;

					// If the sum of the x and y distances is less than or equal to the sensor distance,
					// then we know that the beacon will be detected.
					if ( ( abs( $x - $sensor[0] ) + $y ) <= $distance ) {
						$grid[ $line_to_check ][ $x ] = '#';

						// Add the sensor and beacon to the grid.
						$grid[ $sensor[1] ][ $sensor[0] ] = 'S';
						$grid[ $beacon[1] ][ $beacon[0] ] = 'B';
					}
				}
			}
		}

		return substr_count( implode( '', $grid[ $line_to_check ] ), '#' );
	}

	/**
	 * Part 2: Find the only possible position for the distress beacon. What is its tuning frequency?
	 *
	 * @return int
	 */
	public function part2() {
		$area    = 4000000;
		$sensors = $this->get_sensors();

		// Loop through each sensor and beacon.
		foreach ($sensors as $sensor_data) {
			$sensor   = $sensor_data[0];
			$distance = $sensor_data[2];

			// Loop through a range of values for the x coordinate.
			for ( $i = -( $distance + 1 ); $i <= ( $distance + 1 ); ++ $i ) {
				// Calculate the difference between the current x coordinate and the maximum distance
				$difference = abs( $i - ( $distance + 1 ) );

				// Calculate the x and y coordinates of the current position
				$x = $sensor[0] + $i;
				$y = $sensor[1] - $difference;

				// Check if the x and y are outside the area
				if ( $x > $area ||
					$x < 0 ||
					$y > $area ||
					$y < 0
				) {
					continue;
				}

				// Make sure it's not occupied by other sensors
				if ( $this->check_sensors( $x, $y, $sensors ) ) {
					return $x * 4000000 + $y;
				}
			}
		}

		// I guess we didn't find it.
		return 0;
	}

	/**
	 * Checks if the x and y are occupied by other sensors.
	 *
	 * @param int   $x       The x coordinate.
	 * @param int   $y       The y coordinate.
	 * @param array $sensors The sensors.
	 *
	 * @return bool
	 */
	private function check_sensors( int $x, int $y, array $sensors ) {
		foreach ( $sensors as $sensor_data ) {
			$sensor   = $sensor_data[0];
			$distance = $sensor_data[2];

			if ( ( abs( $x - $sensor[0] ) + abs( $y - $sensor[1] ) ) <= $distance ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns an array of arrays containing the sensor, beacon, and distance.
	 *
	 * Format:
	 * [
	 *  [ [sensor_x, sensor_y], [beacon_x, beacon_y], distance ],
	 *  [ [sensor_x, sensor_y], [beacon_x, beacon_y], distance ],
	 *  ...
	 * ]
	 *
	 * @return array
	 */
	private function get_sensors(): array {
		$lines = explode( PHP_EOL, file_get_contents( __DIR__ . '/data/day-15.txt' ) );

		$sensors = array();

		foreach ( $lines as $line ) {
			// Match positive and negative numbers.
			preg_match_all( '/-?\d+/', $line, $matches );

			$sensor   = array(
				intval( $matches[0][0] ),
				intval( $matches[0][1] ),
			);
			$beacon   = array(
				intval( $matches[0][2] ),
				intval( $matches[0][3] ),
			);
			$distance = abs( $sensor[0] - $beacon[0] ) + abs( $sensor[1] - $beacon[1] );

			$sensors[] = array( $sensor, $beacon, $distance );
		}

		return $sensors;
	}
}

function part_1() {
	$start  = microtime( true );
	$day15  = new Day15();
	$result = $day15->part1();
	$end    = microtime( true );

	printf( 'Positions containing a becond in row 2M: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}

function part_2() {
	$start  = microtime( true );
	$day15  = new Day15();
	$result = $day15->part2();
	$end    = microtime( true );

	printf( 'The tuning frequency is: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}