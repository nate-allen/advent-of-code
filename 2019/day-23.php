<?php

namespace AdventOfCode\Year2019;

include_once 'lib/IntcodeComputer.php';

/**
 * Day 23: Category Six
 */
class Day23 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var integer
	 */
	private int $part;

	/**
	 * Parsed data from the input file.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( int $part ) {
		$this->part = $part;
		$this->data = $this->parse_data();
	}

	/**
	 * Executes the specified part of the puzzle.
	 *
	 * @return integer
	 */
	public function run(): int {
		return match ( $this->part ) {
			1 => $this->solve_part_1(),
			2 => $this->solve_part_2(),
			default => throw new \InvalidArgumentException( 'Invalid part specified.' ),
		};
	}

	/**
	 * Part 1: Boot up 50 computers using the Intcode program. Determine the Y value of the first packet sent to address 255.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		$num_computers  = 50;
		$computers      = [];
		$network_queues = [];
		$packet_buffers = [];

		// Initialize each computer with its unique address and an empty input queue.
		for ( $i = 0; $i < $num_computers; $i ++ ) {
			$computers[ $i ] = new IntcodeComputer( $this->data );
			// Provide the network address as the first input.
			$computers[ $i ]->add_input( $i );
			$network_queues[ $i ] = [];
			$packet_buffers[ $i ] = [];
		}

		// Loop forever until a packet is sent to address 255.
		while ( true ) {
			// For each computer...
			for ( $i = 0; $i < $num_computers; $i ++ ) {
				// If no packet is queued, feed a -1.
				if ( empty( $network_queues[ $i ] ) ) {
					$computers[ $i ]->add_input( - 1 );
				} else {
					// Otherwise, get the next packet (which contains [X, Y]) and add them as inputs.
					$packet = array_shift( $network_queues[ $i ] );
					$computers[ $i ]->add_input( $packet[0] );
					$computers[ $i ]->add_input( $packet[1] );
				}

				// Run the computer until it produces an output.
				while ( ( $output = $computers[ $i ]->run_until_output() ) !== null ) {
					$packet_buffers[ $i ][] = $output;
					// Once three outputs are available, we have a complete packet.
					if ( count( $packet_buffers[ $i ] ) === 3 ) {
						list( $dest, $x, $y ) = $packet_buffers[ $i ];
						$packet_buffers[ $i ] = [];

						// If the destination is 255, return the Y value.
						if ( $dest === 255 ) {
							return $y;
						}

						// Otherwise, enqueue the packet to the destination computer.
						$network_queues[ $dest ][] = [ $x, $y ];
					}
				}
			}
		}
	}

	/**
	 * Part 2: Monitor packets released to the computer at address 0 by the NAT. The NAT receives packets sent to
	 * address 255, and when the network is idle it sends its stored packet to computer 0. Return the first Y value
	 * delivered twice in a row.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		$num_computers  = 50;
		$computers      = [];
		$network_queues = [];
		$packet_buffers = [];

		// Initialize each computer with its unique address and an empty input queue.
		for ( $i = 0; $i < $num_computers; $i++ ) {
			$computers[ $i ] = new IntcodeComputer( $this->data );
			// Provide the network address as the first input.
			$computers[ $i ]->add_input( $i );
			$network_queues[ $i ] = [];
			$packet_buffers[ $i ] = [];
		}

		$nat_packet = null;
		$prev_nat_y = null;

		while ( true ) {
			$network_activity = false;

			// Process each computer.
			for ( $i = 0; $i < $num_computers; $i++ ) {
				if ( ! empty( $network_queues[ $i ] ) ) {
					$network_activity = true;
					$packet = array_shift( $network_queues[ $i ] );
					$computers[ $i ]->add_input( $packet[0] );
					$computers[ $i ]->add_input( $packet[1] );
				} else {
					$computers[ $i ]->add_input( -1 );
				}

				// Run the computer until it no longer produces output.
				while ( ( $output = $computers[ $i ]->run_until_output() ) !== null ) {
					$network_activity = true;
					$packet_buffers[ $i ][] = $output;

					if ( count( $packet_buffers[ $i ] ) === 3 ) {
						list( $dest, $x, $y ) = $packet_buffers[ $i ];
						$packet_buffers[ $i ] = [];

						if ( $dest === 255 ) {
							// NAT receives the packet.
							$nat_packet = [ $x, $y ];
						} else {
							$network_queues[ $dest ][] = [ $x, $y ];
						}
					}
				}
			}

			// If no computer produced output and all queues are empty, the network is idle.
			if ( ! $network_activity ) {
				if ( $nat_packet !== null ) {
					// The NAT sends its stored packet to computer 0.
					$network_queues[0][] = $nat_packet;
					// Check if the Y value is sent twice in a row.
					if ( $prev_nat_y !== null && $prev_nat_y === $nat_packet[1] ) {
						return $nat_packet[1];
					}
					$prev_nat_y = $nat_packet[1];
				}
			}
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @return array
	 */
	private function parse_data(): array {
		return array_map( 'intval', explode( ",", trim( file_get_contents( __DIR__ . '/data/day-23.txt' ) ) ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 */
function run_part( int $part ): void {
	$start  = microtime( true );
	$day23  = new Day23( $part );
	$result = $day23->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => 21089,
		2 => 16658,
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $expected_values[ $part ] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$part = (int) trim( readline( 'Which part do you want to run? (1/2): ' ) );
	if ( ! in_array( $part, [ 1, 2 ], true ) ) {
		echo 'Invalid part. Please enter 1 or 2.' . PHP_EOL;
		continue;
	}

	run_part( $part );
	break;
}
