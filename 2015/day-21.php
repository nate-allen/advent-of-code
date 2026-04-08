<?php

namespace AdventOfCode\Year2015;

/**
 * Day 21: RPG Simulator 20XX
 */
class Day21 {
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
	 * Part 1: Least gold to spend and still win.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$min_cost = PHP_INT_MAX;

		foreach ( $this->loadouts() as [ $cost, $damage, $armor ] ) {
			if ( $this->player_wins( 100, $damage, $armor ) ) {
				$min_cost = min( $min_cost, $cost );
			}
		}

		return $min_cost;
	}

	/**
	 * Part 2: Most gold to spend and still lose.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$max_cost = 0;

		foreach ( $this->loadouts() as [ $cost, $damage, $armor ] ) {
			if ( ! $this->player_wins( 100, $damage, $armor ) ) {
				$max_cost = max( $max_cost, $cost );
			}
		}

		return $max_cost;
	}

	/**
	 * Determines if the player wins with the given stats.
	 *
	 * @param int $hp     Player hit points.
	 * @param int $damage Player damage.
	 * @param int $armor  Player armor.
	 *
	 * @return bool
	 */
	private function player_wins( int $hp, int $damage, int $armor ): bool {
		[ $boss_hp, $boss_damage, $boss_armor ] = $this->data;

		$player_dps = max( 1, $damage - $boss_armor );
		$boss_dps   = max( 1, $boss_damage - $armor );

		$turns_to_kill_boss   = (int) ceil( $boss_hp / $player_dps );
		$turns_to_kill_player = (int) ceil( $hp / $boss_dps );

		return $turns_to_kill_boss <= $turns_to_kill_player;
	}

	/**
	 * Generates all possible equipment loadouts.
	 *
	 * @return \Generator yields [cost, damage, armor]
	 */
	private function loadouts(): \Generator {
		// [cost, damage, armor]
		$weapons = [
			[ 8, 4, 0 ], [ 10, 5, 0 ], [ 25, 6, 0 ], [ 40, 7, 0 ], [ 74, 8, 0 ],
		];
		$armors = [
			[ 0, 0, 0 ],
			[ 13, 0, 1 ], [ 31, 0, 2 ], [ 53, 0, 3 ], [ 75, 0, 4 ], [ 102, 0, 5 ],
		];
		$rings = [
			[ 25, 1, 0 ], [ 50, 2, 0 ], [ 100, 3, 0 ],
			[ 20, 0, 1 ], [ 40, 0, 2 ], [ 80, 0, 3 ],
		];

		// Build ring combos: no rings, 1 ring, or 2 rings.
		$ring_combos = [ [ 0, 0, 0 ] ];
		for ( $i = 0; $i < count( $rings ); $i++ ) {
			$ring_combos[] = $rings[ $i ];
			for ( $j = $i + 1; $j < count( $rings ); $j++ ) {
				$ring_combos[] = [
					$rings[ $i ][0] + $rings[ $j ][0],
					$rings[ $i ][1] + $rings[ $j ][1],
					$rings[ $i ][2] + $rings[ $j ][2],
				];
			}
		}

		foreach ( $weapons as $w ) {
			foreach ( $armors as $a ) {
				foreach ( $ring_combos as $r ) {
					yield [
						$w[0] + $a[0] + $r[0],
						$w[1] + $a[1] + $r[1],
						$w[2] + $a[2] + $r[2],
					];
				}
			}
		}
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-21-test.txt' : '/data/day-21.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$boss  = [];

		foreach ( $lines as $line ) {
			preg_match( '/: (\d+)/', $line, $m );
			$boss[] = (int) $m[1];
		}

		return $boss;
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
	$day21  = new Day21( $test, $part );
	$result = $day21->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 0,
			'real' => 121,
		],
		2 => [
			'test' => 0,
			'real' => 201,
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
