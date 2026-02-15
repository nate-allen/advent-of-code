<?php

namespace AdventOfCode\Year2018;

/**
 * Day 08: Memory Maneuver
 */
class Day08 {
	/**
	 * The puzzle part, 1 or 2.
	 *
	 * @var integer
	 */
	private int $part;

	/**
	 * Whether to use the test data.
	 *
	 * @var bool
	 */
	private bool $is_test;

	/**
	 * Parsed data from the input file.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->data    = $this->parse_data( $this->is_test );
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
	 * Part 1: Sum of all metadata entries.
	 *
	 * Recursively parses the tree structure and sums all metadata entries
	 * from all nodes in the tree.
	 *
	 * @return integer The sum of all metadata entries.
	 */
	private function solve_part_1(): int {
		$data   = $this->data;
		$result = $this->parse_node( $data );
		return $result['metadata_sum'];
	}

	/**
	 * Recursively parses a node from the data array.
	 *
	 * Each node consists of:
	 * - Header: 2 numbers (child count, metadata count)
	 * - Zero or more child nodes (recursively parsed)
	 * - One or more metadata entries
	 *
	 * @param array $data Reference to the data array (consumed as we parse).
	 *
	 * @return array Array containing:
	 *               - 'metadata_sum': Sum of all metadata in this node and its children
	 *               - 'value': Node value (for part 2, currently same as metadata_sum)
	 *               - 'children': Array of child node results
	 *               - 'metadata': Array of metadata entries for this node
	 */
	private function parse_node( array &$data ): array {
		// Read header: child count and metadata count
		$num_children = array_shift( $data );
		$num_metadata = array_shift( $data );

		// Recursively parse all child nodes
		$children              = [];
		$children_metadata_sum = 0;
		for ( $i = 0; $i < $num_children; $i++ ) {
			$child_result           = $this->parse_node( $data );
			$children[]             = $child_result;
			$children_metadata_sum += $child_result['metadata_sum'];
		}

		// Read metadata entries for this node
		$metadata          = [];
		$node_metadata_sum = 0;
		for ( $i = 0; $i < $num_metadata; $i++ ) {
			$metadata_value     = array_shift( $data );
			$metadata[]         = $metadata_value;
			$node_metadata_sum += $metadata_value;
		}

		// Total metadata sum = children's metadata + this node's metadata
		$total_metadata_sum = $children_metadata_sum + $node_metadata_sum;

		return [
			'metadata_sum' => $total_metadata_sum,
			'value'        => $total_metadata_sum, // For part 2, will be calculated differently
			'children'     => $children,
			'metadata'     => $metadata,
		];
	}

	/**
	 * Part 2: Calculate the value of the root node.
	 *
	 * The value of a node depends on whether it has children:
	 * - If no children: value = sum of metadata entries
	 * - If has children: value = sum of values of children referenced by metadata (1-indexed)
	 *
	 * @return integer The value of the root node.
	 */
	private function solve_part_2(): int {
		$data   = $this->data;
		$result = $this->parse_node_with_value( $data );
		return $result['value'];
	}

	/**
	 * Recursively parses a node and calculates its value for part 2.
	 *
	 * @param array $data Reference to the data array (consumed as we parse).
	 *
	 * @return array Array containing:
	 *               - 'metadata_sum': Sum of all metadata (for reference)
	 *               - 'value': Node value (sum of metadata if no children, or sum of referenced child values)
	 *               - 'children': Array of child node results
	 *               - 'metadata': Array of metadata entries for this node
	 */
	private function parse_node_with_value( array &$data ): array {
		// Read header: child count and metadata count
		$num_children = array_shift( $data );
		$num_metadata = array_shift( $data );

		// Recursively parse all child nodes
		$children              = [];
		$children_metadata_sum = 0;
		for ( $i = 0; $i < $num_children; $i++ ) {
			$child_result           = $this->parse_node_with_value( $data );
			$children[]             = $child_result;
			$children_metadata_sum += $child_result['metadata_sum'];
		}

		// Read metadata entries for this node
		$metadata          = [];
		$node_metadata_sum = 0;
		for ( $i = 0; $i < $num_metadata; $i++ ) {
			$metadata_value     = array_shift( $data );
			$metadata[]         = $metadata_value;
			$node_metadata_sum += $metadata_value;
		}

		// Total metadata sum = children's metadata + this node's metadata
		$total_metadata_sum = $children_metadata_sum + $node_metadata_sum;

		// Calculate node value based on part 2 rules
		$node_value = 0;
		if ( $num_children === 0 ) {
			// No children: value = sum of metadata
			$node_value = $node_metadata_sum;
		} else {
			// Has children: value = sum of values of children referenced by metadata (1-indexed)
			foreach ( $metadata as $metadata_entry ) {
				// Metadata entries are 1-indexed, so subtract 1 for array index
				$child_index = $metadata_entry - 1;
				if ( $child_index >= 0 && $child_index < count( $children ) ) {
					$node_value += $children[ $child_index ]['value'];
				}
			}
		}

		return [
			'metadata_sum' => $total_metadata_sum,
			'value'        => $node_value,
			'children'     => $children,
			'metadata'     => $metadata,
		];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array of integers from the input file.
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-08-test.txt' : '/data/day-08.txt';
		$line = trim( file_get_contents( __DIR__ . $file ) );

		// Convert space-separated numbers to array of integers
		return array_map( 'intval', explode( ' ', $line ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool    $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day08  = new Day08( $test, $part );
	$result = $day08->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 138,
			'real' => 36891,
		],
		2 => [
			'test' => 66,
			'real' => 20083,
		],
	];

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer:   ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Expected: ' . $reset . '%s' . PHP_EOL, $test ? $expected_values[ $part ]['test'] : $expected_values[ $part ]['real'] );
	printf( $yellow . 'Time:     ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Prompt for part and test mode
while ( true ) {
	$part = (int) trim( readline( 'Which part do you want to run? (1/2): ' ) );
	if ( ! in_array( $part, [ 1, 2 ], true ) ) {
		echo 'Invalid part. Please enter 1 or 2.' . PHP_EOL;
		continue;
	}

	while ( true ) {
		$test = strtolower( trim( readline( 'Do you want to run the test? (y/n): ' ) ) );
		if ( in_array( $test, [ 'y', 'n' ], true ) ) {
			$test_mode = $test === 'y';
			run_part( $part, $test_mode );
			break 2;
		}
		echo 'Invalid input. Please enter y or n.' . PHP_EOL;
	}
}
