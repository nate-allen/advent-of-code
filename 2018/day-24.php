<?php

namespace AdventOfCode\Year2018;

/**
 * Day 24: Immune System Simulator 20XX
 */
class Day24 {
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

	/**
	 * Immune System groups.
	 *
	 * @var array
	 */
	private array $immune_system = [];

	/**
	 * Infection groups.
	 *
	 * @var array
	 */
	private array $infection = [];

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
	 * Part 1: Calculate winning army units after combat
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$result = $this->simulate_combat( 0 );
		return $result['units'];
	}

	/**
	 * Part 2: Find minimum boost for immune system to win
	 *
	 * @return integer Sum of remaining immune system units with minimum boost
	 */
	private function solve_part_2(): int {
		$boost = 1;
		while ( true ) {
			$result = $this->simulate_combat( $boost );
			if ( $result['winner'] === 'immune' ) {
				return $result['units'];
			}
			// If infection wins or stalemate, try next boost
			$boost++;
		}
	}

	/**
	 * Gets the effective power of a group.
	 *
	 * @param array $group The group.
	 *
	 * @return int
	 */
	private function get_effective_power( array $group ): int {
		return $group['units'] * $group['damage'];
	}

	/**
	 * Calculates damage that an attacking group would deal to a defending group.
	 *
	 * @param array $attacker The attacking group.
	 * @param array $defender The defending group.
	 *
	 * @return int
	 */
	private function calculate_damage( array $attacker, array $defender ): int {
		// If immune, no damage
		if ( in_array( $attacker['attack_type'], $defender['immunities'], true ) ) {
			return 0;
		}

		$base_damage = $this->get_effective_power( $attacker );

		// If weak, double damage
		if ( in_array( $attacker['attack_type'], $defender['weaknesses'], true ) ) {
			return $base_damage * 2;
		}

		return $base_damage;
	}

	/**
	 * Simulates combat between immune system and infection.
	 *
	 * @param int $boost Boost to add to immune system attack damage.
	 *
	 * @return array Array with 'winner' ('immune'|'infection'|'stalemate') and 'units' (int).
	 */
	private function simulate_combat( int $boost = 0 ): array {
		// Create copies of groups with boost applied
		$groups = [];
		foreach ( $this->immune_system as $group ) {
			$group_copy           = $group;
			$group_copy['damage'] = $group['damage'] + $boost;
			$groups[]             = $group_copy;
		}
		foreach ( $this->infection as $group ) {
			$groups[] = $group; // No boost for infection
		}

		// Combat loop
		while ( true ) {
			// Remove dead groups and re-index
			$groups = array_values( array_filter( $groups, fn( $g ) => $g['units'] > 0 ) );

			// Check if one army is eliminated
			$immune_count    = count( array_filter( $groups, fn( $g ) => $g['side'] === 'immune' ) );
			$infection_count = count( array_filter( $groups, fn( $g ) => $g['side'] === 'infection' ) );

			if ( $immune_count === 0 || $infection_count === 0 ) {
				break;
			}

			// Target Selection Phase
			// Sort by effective power (desc), then initiative (desc)
			usort( $groups, function( $a, $b ) {
				$power_a = $this->get_effective_power( $a );
				$power_b = $this->get_effective_power( $b );
				if ( $power_a !== $power_b ) {
					return $power_b <=> $power_a; // Descending
				}
				return $b['initiative'] <=> $a['initiative']; // Descending
			} );

			$targets  = []; // Map of attacker index => defender index
			$targeted = []; // Set of defender indices already targeted

			foreach ( $groups as $attacker_idx => $attacker ) {
				if ( $attacker['units'] <= 0 ) {
					continue;
				}

				$best_target_idx = null;
				$best_damage     = 0;
				$best_power      = 0;
				$best_initiative = 0;

				// Find best enemy target
				foreach ( $groups as $defender_idx => $defender ) {
					// Skip if same side, already targeted, or dead
					if ( $defender['side'] === $attacker['side'] ) {
						continue;
					}
					if ( in_array( $defender_idx, $targeted, true ) ) {
						continue;
					}
					if ( $defender['units'] <= 0 ) {
						continue;
					}

					$damage = $this->calculate_damage( $attacker, $defender );
					if ( $damage === 0 ) {
						continue;
					}

					$defender_power = $this->get_effective_power( $defender );
					$defender_init  = $defender['initiative'];

					// Choose best: max damage → max effective power → max initiative
					if ( $best_target_idx === null ) {
						$best_target_idx = $defender_idx;
						$best_damage     = $damage;
						$best_power      = $defender_power;
						$best_initiative = $defender_init;
					} else {
						if ( $damage > $best_damage ) {
							$best_target_idx = $defender_idx;
							$best_damage     = $damage;
							$best_power      = $defender_power;
							$best_initiative = $defender_init;
						} elseif ( $damage === $best_damage ) {
							if ( $defender_power > $best_power ) {
								$best_target_idx = $defender_idx;
								$best_power      = $defender_power;
								$best_initiative = $defender_init;
							} elseif ( $defender_power === $best_power ) {
								if ( $defender_init > $best_initiative ) {
									$best_target_idx = $defender_idx;
									$best_initiative = $defender_init;
								}
							}
						}
					}
				}

				if ( $best_target_idx !== null ) {
					$targets[ $attacker_idx ] = $best_target_idx;
					$targeted[]               = $best_target_idx;
				}
			}

			// Attacking Phase
			// Sort by initiative (desc)
			$attack_order = array_keys( $groups );
			usort( $attack_order, function( $a, $b ) use ( $groups ) {
				return $groups[ $b ]['initiative'] <=> $groups[ $a ]['initiative'];
			} );

			$damage_dealt = false;

			foreach ( $attack_order as $attacker_idx ) {
				if ( ! isset( $targets[ $attacker_idx ] ) ) {
					continue;
				}

				$defender_idx = $targets[ $attacker_idx ];

				if ( $groups[ $attacker_idx ]['units'] <= 0 || $groups[ $defender_idx ]['units'] <= 0 ) {
					continue;
				}

				$damage = $this->calculate_damage( $groups[ $attacker_idx ], $groups[ $defender_idx ] );
				if ( $damage === 0 ) {
					continue;
				}

				// Calculate units killed: damage / hp (integer division)
				$units_killed = (int)($damage / $groups[ $defender_idx ]['hp']);
				$units_killed = min( $groups[ $defender_idx ]['units'], $units_killed );

				if ( $units_killed > 0 ) {
					$damage_dealt                      = true;
					$groups[ $defender_idx ]['units'] -= $units_killed;
				}
			}

			// Stalemate detection: if no damage was dealt, combat is stuck
			if ( ! $damage_dealt ) {
				// Stalemate - both armies still have units but no damage can be dealt
				$immune_units    = 0;
				$infection_units = 0;
				foreach ( $groups as $group ) {
					if ( $group['units'] > 0 ) {
						if ( $group['side'] === 'immune' ) {
							$immune_units += $group['units'];
						} else {
							$infection_units += $group['units'];
						}
					}
				}
				return [
					'winner' => 'stalemate',
					'units'  => $immune_units + $infection_units,
				];
			}
		}

		// Determine winner and sum remaining units
		$immune_units    = 0;
		$infection_units = 0;
		foreach ( $groups as $group ) {
			if ( $group['units'] > 0 ) {
				if ( $group['side'] === 'immune' ) {
					$immune_units += $group['units'];
				} else {
					$infection_units += $group['units'];
				}
			}
		}

		if ( $immune_units > 0 && $infection_units === 0 ) {
			return [
				'winner' => 'immune',
				'units'  => $immune_units,
			];
		} elseif ( $infection_units > 0 && $immune_units === 0 ) {
			return [
				'winner' => 'infection',
				'units'  => $infection_units,
			];
		} else {
			// Should not happen, but handle edge case
			return [
				'winner' => 'stalemate',
				'units'  => $immune_units + $infection_units,
			];
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
		$file  = $test ? '/data/day-24-test.txt' : '/data/day-24.txt';
		$input = file_get_contents( __DIR__ . $file );

		// Split by army sections
		$parts = preg_split( '/\n\n/', trim( $input ) );
		
		$immune_system = [];
		$infection     = [];

		foreach ( $parts as $part ) {
			$lines = explode( "\n", $part );
			$army  = trim( $lines[0] );

			// Combine multi-line group definitions
			$group_lines  = [];
			$current_line = '';
			for ( $i = 1; $i < count( $lines ); $i++ ) {
				$line = trim( $lines[ $i ] );
				if ( preg_match( '/^\d+ units/', $line ) ) {
					if ( $current_line ) {
						$group_lines[] = preg_replace( '/\s+/', ' ', $current_line );
					}
					$current_line = $line;
				} else {
					$current_line .= ' ' . $line;
				}
			}
			if ( $current_line ) {
				$group_lines[] = preg_replace( '/\s+/', ' ', $current_line );
			}

			// Parse each group
			foreach ( $group_lines as $line ) {
				// Pattern matches: units, hp, optional specials, damage, attack type, initiative
				$pattern = '/(\d+) units each with (\d+) hit points(?: \(([^)]+)\))? with an attack that does (\d+) (\w+) damage at initiative (\d+)/';
				if ( preg_match( $pattern, $line, $matches ) ) {
					$units       = (int) $matches[1];
					$hp          = (int) $matches[2];
					$specials    = $matches[3] ?? '';
					$damage      = (int) $matches[4];
					$attack_type = $matches[5];
					$initiative  = (int) $matches[6];

					// Parse weaknesses and immunities
					$weaknesses = [];
					$immunities = [];

					if ( $specials ) {
						$spec_parts = explode( '; ', $specials );
						foreach ( $spec_parts as $spec_part ) {
							if ( preg_match( '/^(weak|immune) to (.+)$/', trim( $spec_part ), $m ) ) {
								$type         = $m[1];
								$damage_types = array_map( 'trim', explode( ',', $m[2] ) );
								if ( $type === 'weak' ) {
									$weaknesses = $damage_types;
								} else {
									$immunities = $damage_types;
								}
							}
						}
					}

					$group = [
						'units'       => $units,
						'hp'          => $hp,
						'damage'      => $damage,
						'attack_type' => $attack_type,
						'initiative'  => $initiative,
						'weaknesses'  => $weaknesses,
						'immunities'  => $immunities,
						'side'        => strpos( $army, 'Immune' ) !== false ? 'immune' : 'infection',
					];

					if ( $group['side'] === 'immune' ) {
						$immune_system[] = $group;
					} else {
						$infection[] = $group;
					}
				}
			}
		}

		$this->immune_system = $immune_system;
		$this->infection     = $infection;

		return [ 'immune_system' => $immune_system, 'infection' => $infection ];
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
	$day24  = new Day24( $test, $part );
	$result = $day24->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 5216,
			'real' => 35947,
		],
		2 => [
			'test' => 51,
			'real' => 1105,
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
