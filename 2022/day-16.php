<?php
/**
 * Day 16: Proboscidea Volcanium
 */
class Day16 {

	private array $valves;
	private array $working_valves;
	private array $cache = array();

	public function __construct( $test ) {
		$this->parse_data( $test );
	}

	/**
	 * Part 1: Work out the steps to release the most pressure in 30 minutes. What is the most pressure you can release?
	 *
	 * @return int
	 */
	public function part_1(): int {
		return $this->tick( 'AA', $this->working_valves, 30, $this->get_cache_key() );
	}

	/**
	 * Part 2:
	 *
	 * @return int
	 */
	public function part_2(): int {
		// This is a work in progress ;)
		return $this->tick( 'AA', $this->working_valves, 26, $this->get_cache_key() );
	}

	/**
	 * Ticks down the time as we travel to valves and maybe open them.
	 *
	 * @param string $valve          The valve we are currently at.
	 * @param array  $working_valves The valves that are still open.
	 * @param int    $time_remaining The time remaining.
	 * @param string $cache_key      The current cache key.
	 *
	 * @return int|mixed
	 */
	private function tick( string $valve, array $working_valves, int $time_remaining, string $cache_key ) {
		// When time is up, or we've opened all the valves, return 0.
		if ( 0 === $time_remaining || empty( $working_valves ) ) {
			return 0;
		}

		$pressures = array();
		$time_remaining--;

		// If this valve has a PPM greater than 0, and it hasn't been opened yet, open it.
		if ( $this->valves[ $valve ]['ppm'] > 0 && isset( $working_valves[ $valve ] ) ) {
			// Remove the valve from the working valves now that it has been opened.
			unset( $working_valves[ $valve ] );

			// Update the pressure. Calculated by multiplying the PPM by the remaining time.
			$pressure = $this->valves[ $valve ]['ppm'] * $time_remaining;

			// Generate a cache key based on the valve name, the remaining valves, and the remaining time.
			$cache_key = $this->get_cache_key( $time_remaining, $valve, $working_valves );

			// If this combination isn't cached yet, cache it and keep going.
			if ( ! isset( $this->cache[ $cache_key ] ) ) {
				$this->cache[ $cache_key ] = $this->tick( $valve, $working_valves, $time_remaining, $cache_key );
			}

			$pressures[] = $pressure + $this->cache[ $cache_key ];
		}

		// Continue to the next valves.
		foreach ( $this->valves[ $valve ]['connections'] as $next_valve ) {

			// Generate a cache key.
			$cache_key = $this->get_cache_key( $time_remaining, $next_valve, $working_valves );

			// If this combination isn't cached yet, cache it and keep going.
			if ( ! isset( $this->cache[ $cache_key ] ) ) {
				$this->cache[ $cache_key ] = $this->tick( $next_valve, $working_valves, $time_remaining, $cache_key );
			}

			$pressures[] = $this->cache[ $cache_key ];
		}

		// Return the highest pressure.
		return max( $pressures );
	}

	/**
	 * Returns a cache key based on the remaining time, the valve name, and the remaining valves.
	 *
	 * @param int    $remaining_time The remaining time.
	 * @param string $valve          The valve name.
	 * @param array  $visited_valves The remaining valves.
	 *
	 * @return string
	 */
	private function get_cache_key( int $remaining_time = 30, string $valve = 'AA', array $visited_valves = array() ) {
		$visited_valves = empty( $visited_valves ) ? $this->working_valves : $visited_valves;

		return $remaining_time . $valve . implode( '', $visited_valves );
	}

	/**
	 * Parses the data from the input file.
	 *
	 * @param $test bool Whether to use the test data.
	 *
	 * @return void
	 */
	private function parse_data( $test = false ) {
		$path  = $test ? '/data/day-16-test.txt' : '/data/day-16.txt';
		$lines = explode( PHP_EOL, file_get_contents( __DIR__ . $path ) );

		foreach ( $lines as $line ) {
			preg_match( "/Valve (\w+) has flow rate=(\d+); (tunnel|tunnels) lead(s)? to valve(s)? (.*)/", $line, $matches );

			$name = $matches[1];
			$pmm  = (int) $matches[2]; // Pressure per minute.

			// Keep track of the working valves, where the Pressure per Minute is greater than 0.
			if ( $pmm > 0 ) {
				$this->working_valves[] = $name;
			}

			$this->valves[ $name ] = array(
				'ppm'        => $pmm,
				'connections' => explode( ', ', $matches[6] ),
			);
		}

		$this->working_valves = array_flip( $this->working_valves );
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
	$day16  = new Day16( $test );
	$result = $day16->part_1();
	$end    = microtime( true );

	printf( 'The highest pressure released in 30 minutes is: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start  = microtime( true );
	$day16  = new Day16( $test );
	$result = $day16->part_2();
	$end    = microtime( true );

	printf( 'Result: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}
