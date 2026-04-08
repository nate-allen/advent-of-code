<?php

namespace AdventOfCode\Year2015;

/**
 * Day 22: Wizard Simulator 20XX
 */
class Day22 {
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
	 * Boss stats [hp, damage].
	 *
	 * @var array
	 */
	private array $boss;

	public function __construct( bool $test, int $part ) {
		$this->part    = $part;
		$this->is_test = $test;
		$this->boss    = $this->parse_data( $this->is_test );
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
	 * Part 1: Least mana to win the fight.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->find_min_mana( false );
	}

	/**
	 * Part 2: Hard mode - lose 1 HP at start of each player turn.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		return $this->find_min_mana( true );
	}

	/**
	 * Finds the minimum mana to spend and still win using DFS with pruning.
	 *
	 * @param bool $hard_mode Whether hard mode is active (lose 1 HP each player turn).
	 *
	 * @return integer
	 */
	private function find_min_mana( bool $hard_mode ): int {
		$this->best = PHP_INT_MAX;

		// State: [player_hp, mana, boss_hp, shield_timer, poison_timer, recharge_timer, mana_spent]
		$this->dfs( 50, 500, $this->boss[0], 0, 0, 0, 0, true, $hard_mode );

		return $this->best;
	}

	private int $best;

	// Spell costs.
	private const MISSILE_COST  = 53;
	private const DRAIN_COST    = 73;
	private const SHIELD_COST   = 113;
	private const POISON_COST   = 173;
	private const RECHARGE_COST = 229;

	/**
	 * DFS through game states.
	 */
	private function dfs(
		int $hp, int $mana, int $boss_hp,
		int $shield_t, int $poison_t, int $recharge_t,
		int $spent, bool $player_turn, bool $hard_mode
	): void {
		// Pruning.
		if ( $spent >= $this->best ) {
			return;
		}

		// Hard mode: player loses 1 HP at start of their turn.
		if ( $player_turn && $hard_mode ) {
			$hp--;
			if ( $hp <= 0 ) {
				return;
			}
		}

		// Apply effects.
		$armor = 0;
		if ( $shield_t > 0 ) {
			$armor = 7;
			$shield_t--;
		}
		if ( $poison_t > 0 ) {
			$boss_hp -= 3;
			$poison_t--;
		}
		if ( $recharge_t > 0 ) {
			$mana += 101;
			$recharge_t--;
		}

		// Check if boss is dead after effects.
		if ( $boss_hp <= 0 ) {
			$this->best = min( $this->best, $spent );
			return;
		}

		if ( $player_turn ) {
			// Try each spell.
			// Magic Missile.
			if ( $mana >= 53 ) {
				$this->dfs(
					$hp, $mana - 53, $boss_hp - 4,
					$shield_t, $poison_t, $recharge_t,
					$spent + 53, false, $hard_mode
				);
			}

			// Drain.
			if ( $mana >= 73 ) {
				$this->dfs(
					$hp + 2, $mana - 73, $boss_hp - 2,
					$shield_t, $poison_t, $recharge_t,
					$spent + 73, false, $hard_mode
				);
			}

			// Shield.
			if ( $mana >= 113 && $shield_t === 0 ) {
				$this->dfs(
					$hp, $mana - 113, $boss_hp,
					6, $poison_t, $recharge_t,
					$spent + 113, false, $hard_mode
				);
			}

			// Poison.
			if ( $mana >= 173 && $poison_t === 0 ) {
				$this->dfs(
					$hp, $mana - 173, $boss_hp,
					$shield_t, 6, $recharge_t,
					$spent + 173, false, $hard_mode
				);
			}

			// Recharge.
			if ( $mana >= 229 && $recharge_t === 0 ) {
				$this->dfs(
					$hp, $mana - 229, $boss_hp,
					$shield_t, $poison_t, 5,
					$spent + 229, false, $hard_mode
				);
			}
		} else {
			// Boss turn.
			$damage = max( 1, $this->boss[1] - $armor );
			$hp    -= $damage;

			if ( $hp <= 0 ) {
				return; // Player dies.
			}

			$this->dfs(
				$hp, $mana, $boss_hp,
				$shield_t, $poison_t, $recharge_t,
				$spent, true, $hard_mode
			);
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
		$file  = $test ? '/data/day-22-test.txt' : '/data/day-22.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );
		$stats = [];

		foreach ( $lines as $line ) {
			preg_match( '/: (\d+)/', $line, $m );
			$stats[] = (int) $m[1];
		}

		return $stats;
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
	$day22  = new Day22( $test, $part );
	$result = $day22->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 0,
			'real' => 953,
		],
		2 => [
			'test' => 0,
			'real' => 1289,
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
