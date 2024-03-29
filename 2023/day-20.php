<?php

/**
 * Day 20: Pulse Propagation
 */
class Day20 {
	/**
	 * Which part of the puzzle is being solved.
	 *
	 * @var int
	 */
	private int $part;

	/**
	 * Array of modules and what they're connected to.
	 *
	 * @var array
	 */
	private array $module_connections ;

	/**
	 * Array of conjunction modules.
	 *
	 * @var array
	 */
	private array $conjunction_modules;

	/**
	 * Array of flip-flop modules.
	 *
	 * @var array
	 */
	private array $flip_flop_modules;

	/**
	 * The total number of low pulses.
	 *
	 * @var int
	 */
	private int $low_pulses;

	/**
	 * The total number of high pulses.
	 *
	 * @var int
	 */
	private int $high_pulse;

	private array $rx_connections = [];
	private array $found_cycles = [];

	private int $button_presses = 0;

	private mixed $part_2_result = 0;

	/**
	 * Constructor for Day20 class.
	 *
	 * @param string $test Indicates whether to use test data.
	 */
	public function __construct( string $test, int $part ) {
		$this->parse_data( $test );
		$this->low_pulses = 0;
		$this->high_pulse = 0;
		$this->part = $part;
	}

	/**
	 * Part 1: Multiply the total number of low pulses by the total number of high pulses after 1000 button presses.
	 *
	 * @return int The result of multiplying the low and high pulses.
	 */
	public function part_1(): int {
		for ( $i = 0; $i < 1000; $i ++ ) {
			$this->press_button();
		}

		return $this->low_pulses * $this->high_pulse;
	}

	/**
	 * Part 2:
	 *
	 * @return int The answer.
	 */
	public function part_2(): int {
		// Get the module that is connected to rx
		$connected_to_rx = key( array_filter($this->module_connections, function($value) {
			return in_array('rx', $value);
		}) );

		// Get the modules that connect to the module connected to rx
		if ( isset( $this->conjunction_modules[ $connected_to_rx ] ) ) {
			$this->rx_connections = array_merge( $this->rx_connections, $this->conjunction_modules[ $connected_to_rx ] );
		}
		if ( isset( $this->flip_flop_modules[ $connected_to_rx ] ) ) {
			$this->rx_connections = array_merge( $this->rx_connections, $this->flip_flop_modules[ $connected_to_rx ] );
		}

		// Loop until press_button doesn't return 0
		$result = 0;
		for ( $i = 0; $i < 10000000; $i ++ ) {
			$this->press_button();

			if ( (int) $this->part_2_result > 0 ) {
				break;
			}
		}

		return (int) $this->part_2_result;
	}

	/**
	 * Handles the pulse logic.
	 */
	private function press_button(): int {
		// Add the low pulse from the button press
		$this->low_pulses += 1 + count( $this->module_connections ['broadcaster'] );
		$pulse_queue      = [];

		// Start with pulses from the broadcaster
		foreach ( $this->module_connections ['broadcaster'] as $destination ) {
			$pulse_queue[] = [ 0, 'broadcaster', $destination ];
		}

		while ( ! empty( $pulse_queue ) ) {
			// Get the next pulse for processing
			[ $pulse, $source, $label ] = array_pop( $pulse_queue );

			// Skip modules that don't have any connections
			if ( ! isset( $this->module_connections [ $label ] ) ) {
				continue;
			}

			$pulse_to_send = 0;

			// Check for cycles for part 2
			if ( ! $pulse && isset( $this->rx_connections[ $label ] ) ) {
				if ( $this->rx_connections[ $label ] > 0 && ! isset( $this->found_cycles[ $label ] ) ) {
					// Mark a cycle as found and calculate its length.

					$this->found_cycles[ $label ] = $this->button_presses - $this->rx_connections[ $label ];

					// If all cycles are found, calculate the least common multiple for part 2.
					if ( count( $this->found_cycles ) === count( $this->rx_connections ) ) {

						$this->part_2_result = array_shift( $this->found_cycles );

						foreach ( $this->found_cycles as $cycleLength ) {
							$this->part_2_result = gmp_lcm( $this->part_2_result, $cycleLength );
						}
					}
				} else {
					// Update the cycle tracker.
					$this->rx_connections[ $label ] = $this->button_presses;
				}
			}

			// Handle conjunction modules
			if ( array_key_exists( $label, $this->conjunction_modules ) ) {
				$this->conjunction_modules[ $label ][ $source ] = $pulse;
				$pulse_to_send = in_array( 0, $this->conjunction_modules[ $label ] ) ? 1 : 0;
			}

			// Handle flip-flop modules
			if ( array_key_exists( $label, $this->flip_flop_modules ) ) {
				if ( $pulse == 1 ) {
					continue;
				}
				$this->flip_flop_modules[ $label ] = ! $this->flip_flop_modules[ $label ];
				$pulse_to_send = $this->flip_flop_modules[ $label ] ? 1 : 0;
			}

			// Count the pulses sent
			if ( $pulse_to_send == 1 ) {
				$this->high_pulse += count( $this->module_connections [ $label ] );
			} else {
				$this->low_pulses += count( $this->module_connections [ $label ] );
			}

			// Add to the queue
			foreach ( $this->module_connections [ $label ] as $destination ) {
				$pulse_queue[] = [ $pulse_to_send, $label, $destination ];
			}
		}
	}

	/**
	 * Parses the puzzle input.
	 *
	 * @param bool $test Whether this is a test.
	 */
	private function parse_data( bool $test ): void {
		$file_path = $test ? '/data/day-20-test.txt' : '/data/day-20.txt';
		$lines     = explode( "\n", trim( file_get_contents( __DIR__ . $file_path ) ) );

		$this->module_connections  = [];
		$this->conjunction_modules = [];
		$this->flip_flop_modules   = [];

		foreach ( $lines as $line ) {
			[ $module, $destinations ] = explode( ' -> ', $line );
			$destinations = explode( ', ', $destinations );
			$module_type  = $module[0];

			if ( 'broadcaster' === $module ) {
				$this->module_connections ['broadcaster'] = $destinations;
			} else {
				$module_label = substr( $module, 1 );

				$this->module_connections [ $module_label ] = $destinations;

				if ( '&' === $module_type ) {
					$this->conjunction_modules[ $module_label ] = [];
				}

				if ( '%' === $module_type ) {
					$this->flip_flop_modules[ $module_label ] = false;
				}
			}
		}

		foreach ( $this->module_connections  as $label => $destinations ) {
			foreach ( $destinations as $destination ) {
				if ( array_key_exists( $destination, $this->conjunction_modules ) ) {
					$this->conjunction_modules[ $destination ][ $label ] = 0;
				}
			}
		}
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
	$day20    = new Day20( $test, 1 );
	$result   = $day20->part_1();
	$end      = microtime( true );
	$expected = $test ? 32000000 : 0;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day20    = new Day20( $test, 2 );
	$result   = $day20->part_2();
	$end      = microtime( true );
	$expected = $test ? 0 : 220366255099387;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
