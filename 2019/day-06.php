<?php

namespace AdventOfCode\Year2019;

/**
 * Day 06: Universal Orbit Map
 */
class Day06 {
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
	 * Part 1: Determine the total number of direct and indirect orbits.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		// Build a mapping: child => parent.
		$orbits = [];
		foreach ( $this->data as $orbit ) {
			// Each orbit is represented as "AAA)BBB" meaning BBB orbits AAA.
			$orbits[ $orbit[1] ] = $orbit[0];
		}

		$total_orbits = 0;
		// For every object (child) that orbits something,
		// get the orbit chain length (number of steps up to COM).
		foreach ( array_keys( $orbits ) as $object ) {
			$chain        = $this->get_orbit_chain( $object, $orbits );
			$total_orbits += count( $chain );
		}

		return $total_orbits;
	}

	/**
	 * Part 2: Determine the minimum number of orbital transfers required to move from the object YOU are orbiting to
	 *         the object SAN is orbiting.
	 *
	 * @return int
	 */
	private function solve_part_2(): int {
		// Build a mapping: child => parent.
		$orbits = [];
		foreach ( $this->data as $orbit ) {
			$orbits[ $orbit[1] ] = $orbit[0];
		}

		// Get the orbit chains for YOU and SAN.
		// The chain for an object is all the objects it orbits up to COM.
		$you_chain = $this->get_orbit_chain( 'YOU', $orbits );
		$san_chain = $this->get_orbit_chain( 'SAN', $orbits );

		// Map each object in YOU's chain to the number of steps from YOU.
		$you_steps = [];
		foreach ( $you_chain as $steps => $object ) {
			$you_steps[ $object ] = $steps;
		}

		// Traverse SAN's chain and look for the first common object.
		// The total transfers is the sum of the steps from YOU and SAN to that common object.
		foreach ( $san_chain as $steps => $object ) {
			if ( isset( $you_steps[ $object ] ) ) {
				return $steps + $you_steps[ $object ];
			}
		}

		// This should never happen if the input data is correct.
		return - 1;
	}

	/**
	 * Gets the orbit chain for an object. Starting from the given object, it returns an array of objects
	 * that it indirectly orbits (all the way up to COM).
	 *
	 * @param string $object The starting object.
	 * @param array $orbits The mapping of child => parent.
	 *
	 * @return array
	 */
	private function get_orbit_chain( string $object, array $orbits ): array {
		$chain = [];
		// Follow the orbit chain upward until COM is reached.
		while ( isset( $orbits[ $object ] ) ) {
			$object  = $orbits[ $object ];
			$chain[] = $object;
		}

		return $chain;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file = $test ? '/data/day-06-test.txt' : '/data/day-06.txt';

		return array_map( static fn( $orbit ) => explode( ')', $orbit ), explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) ) );
	}
}

/**
 * Runs the specified part with the given settings and outputs results.
 *
 * @param integer $part The part to run (1 or 2).
 * @param bool $test Whether to use test data.
 */
function run_part( int $part, bool $test ): void {
	$start  = microtime( true );
	$day06  = new Day06( $test, $part );
	$result = $day06->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 54,
			'real' => 621125,
		],
		2 => [
			'test' => 4,
			'real' => 550,
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
