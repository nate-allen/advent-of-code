<?php

/**
 * Day 25: Snowverload
 */
class Day25 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var int
	 */
	private int $part;

	/**
	 * Whether or not to use the test data.
	 *
	 * @var bool
	 */
	private bool $is_test;

	/**
	 * Array of components and their IDs.
	 *
	 * @var array
	 */
	private array $components = [];

	/**
	 * Array of wire connections between components.
	 *
	 * @var array
	 */
	private array $wires = [];

	public function __construct(string $test, int $part) {
		$this->part = $part;
		$this->is_test = $test;
		$this->parse_data($this->is_test);
	}

	/**
	 * Part 1: Find the three wires you need to disconnect in order to divide the components into two separate groups.
	 *         What do you get if you multiply the sizes of these two groups together?
	 *
	 * @return int
	 */
	public function part_1(): int {
		while ( true ) {
			$groups = $this->find_groups( count( $this->components ), $this->wires );

			if ( ! is_null( $groups ) ) {
				$group1_count = count( array_filter( $groups, function ( $x ) use ( $groups ) {
					return $x === $groups[0];
				} ) );

				return $group1_count * ( count( $this->components ) - $group1_count );
			}
		}
	}

	/**
	 * Attempts to find two groups by disconnecting wires.
	 *
	 * @param int   $component_count     Total number of components.
	 * @param array $wires               The wires connecting the components.
	 *
	 * @return array|null The groups after disconnection or null if unsuccessful.
	 */
	private function find_groups( int $component_count, array $wires ): ?array {// Shuffle the wires to randomize the order.
		shuffle( $wires );

		$group_parents    = [ - 1 ];
		$component_groups = array_fill( 0, $component_count, 0 );
		$group_promotions = [ - 1 ];

		// Function to find the parent group of a component.
		$find_parent_group = function ( $component ) use ( &$component_groups, &$group_parents ) {
			if ( empty( $component_groups[ $component ] ) ) {
				return - 1;
			}

			$group = $component_groups[ $component ];

			if ( empty( $group_parents[ $group ] ) ) {
				return - 1;
			}

			while ( $group !== $group_parents[ $group ] ) {
				$group = $group_parents[ $group ];
			}

			return $group;
		};

		// Function to union two components into the same group.
		$union_components = function ( $component1, $component2 ) use ( &$component_groups, &$group_parents, &$group_promotions, &$find_parent_group ) {
			// If neither component has a group, create a new group and assign both components to it.
			if ( ! $component_groups[ $component1 ] && ! $component_groups[ $component2 ] ) {
				$new_group                       = count( $group_parents );
				$group_parents[]                 = $new_group;
				$group_promotions[]              = 1;
				$component_groups[ $component1 ] = $new_group;
				$component_groups[ $component2 ] = $new_group;
			} elseif ( ! $component_groups[ $component1 ] ) {
				// If only component1 is ungrouped, add it to the group of component2.
				$group_of_component2 = $find_parent_group( $component2 );
				$group_promotions[ $group_of_component2 ] ++;
				$component_groups[ $component1 ] = $group_of_component2;
			} elseif ( ! $component_groups[ $component2 ] ) {
				// If only component2 is ungrouped, add it to the group of component1.
				$group_of_component1 = $find_parent_group( $component1 );
				$group_promotions[ $group_of_component1 ] ++;
				$component_groups[ $component2 ] = $group_of_component1;
			} else {
				// If both components have groups, attempt to merge the groups if they're different.
				$group1 = $find_parent_group( $component1 );
				$group2 = $find_parent_group( $component2 );

				if ( $group1 !== $group2 ) {
					if ( $group_promotions[ $group1 ] > $group_promotions[ $group2 ] ) {
						list( $group2, $group1 ) = [ $group1, $group2 ];
					}

					$group_promotions[ $group2 ]     += $group_promotions[ $group1 ] + 1;
					$group_parents[ $group1 ]        = $group2;
					$component_groups[ $component1 ] = $group2;
					$component_groups[ $component2 ] = $group2;
				} else {
					return false; // Components are already in the same group.
				}
			}

			return true; // Successful union of components.
		};

		$current_wire_index = 0;
		while ( $component_count > 2 ) {
			list( $component1, $component2 ) = $wires[ $current_wire_index ++ ];

			if ( $union_components( $component1, $component2 ) ) {
				$component_count --;
			}
		}

		$removed_wires = 0;
		foreach ( $wires as $wire ) {
			list( $component1, $component2 ) = $wire;

			if ( ( $component_groups[ $component1 ] = $find_parent_group( $component1 ) ) !== ( $component_groups[ $component2 ] = $find_parent_group( $component2 ) ) ) {
				$removed_wires ++;
			}

			if ( $removed_wires > 3 ) {
				return null;
			}
		}

		if ( $removed_wires < 3 ) {
			return null;
		}

		return $component_groups;
	}

	/**
	 * Adds a component to the network if it doesn't already exist.
	 *
	 * @param string $component The component to add.
	 */
	private function add_component( string $component ): void {
		if ( ! array_key_exists( $component, $this->components ) ) {
			$this->components[ $component ] = count( $this->components );
		}
	}

	/**
	 * Adds a wire (connection) between two components.
	 *
	 * @param string $component1 The first component.
	 * @param string $component2 The second component.
	 */
	private function add_wire( string $component1, string $component2 ): void {
		$this->wires[] = [ $this->components[ $component1 ], $this->components[ $component2 ] ];
	}

	/**
	 * Parses the puzzle input and builds the network of components and wires.
	 *
	 * @param bool $test Whether test data should be used.
	 */
	private function parse_data( bool $test ): void {
		$file  = $test ? '/data/day-25-test.txt' : '/data/day-25.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		foreach ( $lines as $line ) {
			list( $component, $connected_components ) = explode( ': ', $line );
			$this->add_component( $component );

			foreach ( explode( ' ', $connected_components ) as $connected_component ) {
				$this->add_component( $connected_component );
				$this->add_wire( $component, $connected_component );
			}
		}
	}
}

// Prompt which part to run and if it should use the test data.
while ( true ) {
	$test = trim( strtolower( readline( 'Do you want to run the test? (y/n)' ) ) );
	if ( in_array( $test, array( 'y', 'n' ), true ) ) {
		$test = 'y' === $test;
		call_user_func( "part_1", $test );
		break;
	}
	echo 'Please enter y or n' . PHP_EOL;
}

function part_1( $test = false ) {
	$start    = microtime( true );
	$day25    = new Day25( $test, 1 );
	$result   = $day25->part_1();
	$end      = microtime( true );
	$expected = $test ? 54 : 543036;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
