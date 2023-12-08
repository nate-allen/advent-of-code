<?php

/**
 * Day 08: Haunted Wasteland
 */
class Day08 {
	/**
	 * The raw puzzle data.
	 *
	 * @var array
	 */
	private array $data;
	public function __construct( $part, $test ) {
		$this->parse_data( $test, $part );
	}

	/**
	 * Part 1: Starting at AAA, follow the left/right instructions. How many steps are required to reach ZZZ?
	 *
	 * @return int The answer.
	 */
	public function part_1(): int {
		$instructions = str_split( array_shift( $this->data ) ); // Extract the instructions
		$node_map     = $this->build_node_map(); // Create a map from the data
		$current_node = 'AAA';
		$steps        = 0;
		$visited      = []; // Cache results

		while ( $current_node !== 'ZZZ' ) {
			// Check if the current node with the remaining instructions was visited before
			$instruction_pointer = $steps % count( $instructions );
			$cache_key           = $current_node . '-' . $instruction_pointer;

			if ( isset( $visited[ $cache_key ] ) ) {
				return $visited[ $cache_key ]; // Use cached result
			}

			$next_direction = $instructions[ $instruction_pointer ]; // Get next direction
			$current_node   = $node_map[ $current_node ][ $next_direction === 'R' ? 1 : 0 ]; // Get next node
			$steps ++;

			// Cache result
			$visited[ $cache_key ] = $steps;
		}

		return $steps;
	}

	/**
	 * Part 2: Determine the number of steps to align all 'A'-ending nodes to 'Z'-ending nodes.
	 *
	 * @return int The answer
	 */
	public function part_2(): int {
		$instructions   = str_split( array_shift( $this->data ) );
		$node_map       = $this->build_node_map();
		$starting_nodes = $this->find_starting_nodes( $node_map );

		$steps_per_path = [];
		foreach ( $starting_nodes as $start_node ) {
			$steps_per_path[] = $this->steps_to_reach_z( $start_node, $node_map, $instructions );
		}

		$lcm = 1;

		foreach ( $steps_per_path as $step ) {
			$lcm = gmp_lcm( $lcm, $step );
		}

		return gmp_intval( $lcm );
	}

	/**
	 * Builds a node map from the puzzle data.
	 *
	 * Each line of the data represents a node and its connections. This method
	 * parses each line and constructs an associative array where each key is a node,
	 * and the value is an array of its two connected nodes.
	 *
	 * @return array The constructed node map.
	 */
	private function build_node_map(): array {
		$node_map = [];
		foreach ( $this->data as $line ) {
			if ( trim( $line ) === '' ) {
				continue;
			}
			[ $node, $connections ] = explode( ' = ', $line );
			$node_map[ $node ] = explode( ', ', trim( $connections, '()' ) );
		}

		return $node_map;
	}

	/**
	 * Calculates the number of steps required to reach a 'Z'-ending node from a given starting node.
	 *
	 * @param string $start_node The starting node.
	 * @param array $node_map The map of all nodes and their connections.
	 * @param array $instructions The set of navigation instructions.
	 *
	 * @return int The number of steps to reach a 'Z'-ending node.
	 */
	private function steps_to_reach_z( string $start_node, array $node_map, array $instructions ): int {
		$current_node = $start_node;
		$steps        = 0;

		while ( ! str_ends_with( $current_node, 'Z' ) ) {
			$next_direction = $instructions[ $steps % count( $instructions ) ];
			$current_node   = $node_map[ $current_node ][ $next_direction === 'R' ? 1 : 0 ];
			$steps ++;
		}

		return $steps;
	}

	/**
	 * Finds all nodes ending with 'A' in the node map.
	 *
	 * @param array $node_map The node map.
	 *
	 * @return array The array of nodes ending with 'A'.
	 */
	private function find_starting_nodes( array $node_map ): array {
		return array_keys( array_filter( $node_map, function ( $key ) {
			return str_ends_with( $key, 'A' );
		}, ARRAY_FILTER_USE_KEY ) );
	}

	private function parse_data( string $test, int $part ): void {
		$file       = $test ? '/data/day-08-test.txt' : '/data/day-08.txt';
		$this->data = explode( PHP_EOL, file_get_contents( __DIR__ . $file ) );
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
	$day08  = new Day08( 1, $test );
	$result = $day08->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	if ( $test ) {
		printf( 'Expected: %s' . PHP_EOL, 6 );
	}
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start = microtime( true );
	$day08 = new Day08( 2, $test );

	$result = $day08->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	if ( $test ) {
		printf( 'Expected: %s' . PHP_EOL, 6 );
	}
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
