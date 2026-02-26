<?php

namespace AdventOfCode\Year2018;

/**
 * Day 15: Beverage Bandits
 */
class Day15 {
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
	 * The map grid (walls and open spaces).
	 *
	 * @var array
	 */
	private array $map;

	/**
	 * List of units in the combat.
	 *
	 * @var array
	 */
	private array $units;

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
	 * Part 1: Simulate combat and calculate outcome
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		return $this->simulate_combat( 3 );
	}

	/**
	 * Part 2: Find minimum Elf attack power where no Elves die
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		// Try increasing elf attack powers starting from 4
		for ( $elf_power = 4; ; $elf_power++ ) {
			$result = $this->simulate_combat( $elf_power, true );

			if ( $result['success'] ) {
				return $result['outcome'];
			}
			// Continue to next attack power if elves died or didn't win
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
		$file  = $test ? '/data/day-15-test.txt' : '/data/day-15.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$this->map   = [];
		$this->units = [];

		foreach ( $lines as $row => $line ) {
			$this->map[ $row ] = [];
			for ( $col = 0; $col < strlen( $line ); $col++ ) {
				$char = $line[ $col ];
				if ( $char === '#' ) {
					$this->map[ $row ][ $col ] = '#';
				} elseif ( $char === '.' ) {
					$this->map[ $row ][ $col ] = '.';
				} elseif ( $char === 'G' || $char === 'E' ) {
					$this->map[ $row ][ $col ] = '.';
					$this->units[]             = [
						'row'   => $row,
						'col'   => $col,
						'type'  => $char,
						'hp'    => 200,
						'power' => 3,
					];
				}
			}
		}

		return $lines;
	}

	/**
	 * Simulates combat with the given elf attack power.
	 *
	 * @param int $elf_attack_power The attack power for elves.
	 * 
	 * @return int|array The outcome: (full rounds) × (sum of remaining HP), or array with outcome and success status.
	 */
	private function simulate_combat( int $elf_attack_power, bool $track_elf_survival = false ) {
		// Initialize units with proper attack power
		$units             = [];
		$initial_elf_count = 0;
		foreach ( $this->units as $unit ) {
			$unit_copy = $unit;
			if ( $unit_copy['type'] === 'E' ) {
				$unit_copy['power'] = $elf_attack_power;
				if ( $track_elf_survival ) {
					$initial_elf_count++;
				}
			}
			$units[] = $unit_copy;
		}

		$rounds = 0;

		while ( true ) {
			// Sort units by reading order (row, then column)
			usort( $units, function( $a, $b ) {
				if ( $a['row'] !== $b['row'] ) {
					return $a['row'] <=> $b['row'];
				}
				return $a['col'] <=> $b['col'];
			} );

			$combat_ended = false;

			foreach ( $units as $unit_idx => &$unit ) {
				if ( $unit['hp'] <= 0 ) {
					continue;
				}

				// Check if combat should end (no targets)
				$has_targets = false;
				foreach ( $units as $other ) {
					if ( $other['hp'] > 0 && $other['type'] !== $unit['type'] ) {
						$has_targets = true;
						break;
					}
				}

				if ( ! $has_targets ) {
					$combat_ended = true;
					break;
				}

				// Get occupied positions
				$occupied = [];
				foreach ( $units as $other ) {
					if ( $other['hp'] > 0 && $other !== $unit ) {
						$occupied[ $other['row'] . ',' . $other['col'] ] = true;
					}
				}

				// Try to move
				$moved = $this->try_move( $unit, $units, $occupied );

				// Try to attack
				$this->try_attack( $unit, $units );
			}
			unset( $unit );

			// Remove dead units
			$units = array_filter( $units, function( $u ) {
				return $u['hp'] > 0;
			} );
			$units = array_values( $units );

			if ( $combat_ended ) {
				break;
			}

			$rounds++;
		}

		// Calculate sum of remaining HP
		$sum_hp          = 0;
		$final_elf_count = 0;
		foreach ( $units as $unit ) {
			if ( $unit['hp'] > 0 ) {
				$sum_hp += $unit['hp'];
				if ( $track_elf_survival && $unit['type'] === 'E' ) {
					$final_elf_count++;
				}
			}
		}

		$outcome = $rounds * $sum_hp;

		// If tracking elf survival, return array with outcome and success status
		if ( $track_elf_survival ) {
			$elves_survived = ($final_elf_count === $initial_elf_count);
			$elves_won      = true;
			foreach ( $units as $unit ) {
				if ( $unit['hp'] > 0 && $unit['type'] === 'G' ) {
					$elves_won = false;
					break;
				}
			}
			return [
				'outcome'        => $outcome,
				'success'        => $elves_survived && $elves_won,
				'elves_survived' => $elves_survived,
			];
		}

		return $outcome;
	}

	/**
	 * Attempts to move a unit toward the nearest target.
	 *
	 * @param array $unit The unit to move (passed by reference).
	 * @param array $units All units.
	 * @param array $occupied Map of occupied positions.
	 * 
	 * @return bool True if unit moved.
	 */
	private function try_move( array &$unit, array $units, array $occupied ): bool {
		// Find all enemy targets
		$targets = [];
		foreach ( $units as $other ) {
			if ( $other['hp'] > 0 && $other['type'] !== $unit['type'] ) {
				$targets[] = $other;
			}
		}

		if ( empty( $targets ) ) {
			return false;
		}

		// Check if already adjacent to an enemy (in range)
		$is_adjacent = false;
		foreach ( $targets as $target ) {
			$dr = abs( $unit['row'] - $target['row'] );
			$dc = abs( $unit['col'] - $target['col'] );
			if ( $dr + $dc === 1 ) {
				$is_adjacent = true;
				break;
			}
		}

		if ( $is_adjacent ) {
			return false; // Already in range, no need to move
		}

		// Find squares in range (adjacent to targets)
		$in_range = [];
		foreach ( $targets as $target ) {
			$directions = [ [ -1, 0 ], [ 0, 1 ], [ 1, 0 ], [ 0, -1 ] ];
			foreach ( $directions as $dir ) {
				$r   = $target['row'] + $dir[0];
				$c   = $target['col'] + $dir[1];
				$key = $r . ',' . $c;

				// Check if it's open and not occupied
				if ( isset( $this->map[ $r ][ $c ] ) && 
					 $this->map[ $r ][ $c ] === '.' && 
					 ! isset( $occupied[ $key ] ) ) {
					$in_range[ $key ] = [ 'row' => $r, 'col' => $c ];
				}
			}
		}

		if ( empty( $in_range ) ) {
			return false; // No reachable squares in range
		}

		// BFS to find distances to all in-range squares
		$distances = $this->bfs_distances( $unit['row'], $unit['col'], $occupied );

		// Find nearest reachable in-range square
		$nearest  = null;
		$min_dist = PHP_INT_MAX;
		foreach ( $in_range as $pos ) {
			$key  = $pos['row'] . ',' . $pos['col'];
			$dist = $distances[ $key ] ?? PHP_INT_MAX;
			if ( $dist < $min_dist ) {
				$min_dist = $dist;
				$nearest  = $pos;
			} elseif ( $dist === $min_dist && $nearest !== null ) {
				// Tie: choose by reading order
				if ( $pos['row'] < $nearest['row'] || 
					 ($pos['row'] === $nearest['row'] && $pos['col'] < $nearest['col']) ) {
					$nearest = $pos;
				}
			}
		}

		if ( $nearest === null || $min_dist === PHP_INT_MAX ) {
			return false; // Cannot reach any in-range square
		}

		// BFS from destination to find first step
		$distances_from_dest = $this->bfs_distances( $nearest['row'], $nearest['col'], $occupied );

		// Find best first step (reading order)
		$directions = [ [ -1, 0 ], [ 0, 1 ], [ 1, 0 ], [ 0, -1 ] ];
		$best_step  = null;
		$best_dist  = PHP_INT_MAX;
		foreach ( $directions as $dir ) {
			$r   = $unit['row'] + $dir[0];
			$c   = $unit['col'] + $dir[1];
			$key = $r . ',' . $c;

			if ( isset( $this->map[ $r ][ $c ] ) && 
				 $this->map[ $r ][ $c ] === '.' && 
				 ! isset( $occupied[ $key ] ) ) {
				$dist = $distances_from_dest[ $key ] ?? PHP_INT_MAX;
				if ( $dist < $best_dist ) {
					$best_dist = $dist;
					$best_step = [ 'row' => $r, 'col' => $c ];
				} elseif ( $dist === $best_dist && $best_step !== null ) {
					// Tie: choose by reading order
					if ( $r < $best_step['row'] || 
						 ($r === $best_step['row'] && $c < $best_step['col']) ) {
						$best_step = [ 'row' => $r, 'col' => $c ];
					}
				}
			}
		}

		if ( $best_step !== null ) {
			$unit['row'] = $best_step['row'];
			$unit['col'] = $best_step['col'];
			return true;
		}

		return false;
	}

	/**
	 * Performs BFS to find distances from a starting position.
	 *
	 * @param int   $start_row Starting row.
	 * @param int   $start_col Starting column.
	 * @param array $occupied  Map of occupied positions.
	 * 
	 * @return array Map of position keys to distances.
	 */
	private function bfs_distances( int $start_row, int $start_col, array $occupied ): array {
		$distances = [];
		$queue     = [ [ $start_row, $start_col, 0 ] ];
		$visited   = [];

		while ( ! empty( $queue ) ) {
			list( $r, $c, $dist ) = array_shift( $queue );
			$key                  = $r . ',' . $c;

			if ( isset( $visited[ $key ] ) ) {
				continue;
			}

			// Check bounds and if it's a wall
			if ( ! isset( $this->map[ $r ][ $c ] ) || $this->map[ $r ][ $c ] === '#' ) {
				continue;
			}

			// Check if occupied (except starting position)
			if ( isset( $occupied[ $key ] ) && ($r !== $start_row || $c !== $start_col) ) {
				continue;
			}

			$visited[ $key ]   = true;
			$distances[ $key ] = $dist;

			// Add neighbors
			$directions = [ [ -1, 0 ], [ 0, 1 ], [ 1, 0 ], [ 0, -1 ] ];
			foreach ( $directions as $dir ) {
				$nr   = $r + $dir[0];
				$nc   = $c + $dir[1];
				$nkey = $nr . ',' . $nc;
				if ( ! isset( $visited[ $nkey ] ) ) {
					$queue[] = [ $nr, $nc, $dist + 1 ];
				}
			}
		}

		return $distances;
	}

	/**
	 * Attempts to attack an adjacent enemy.
	 *
	 * @param array $unit The attacking unit (passed by reference).
	 * @param array $units All units (passed by reference).
	 * 
	 * @return bool True if an attack was made.
	 */
	private function try_attack( array &$unit, array &$units ): bool {
		// Find adjacent enemies
		$adjacent_enemies = [];
		$directions       = [ [ -1, 0 ], [ 0, 1 ], [ 1, 0 ], [ 0, -1 ] ];

		foreach ( $units as $idx => $other ) {
			if ( $other['hp'] <= 0 || $other['type'] === $unit['type'] ) {
				continue;
			}

			// Check if adjacent
			$dr = abs( $unit['row'] - $other['row'] );
			$dc = abs( $unit['col'] - $other['col'] );
			if ( $dr + $dc === 1 ) {
				$adjacent_enemies[] = $idx;
			}
		}

		if ( empty( $adjacent_enemies ) ) {
			return false;
		}

		// Select target: lowest HP, then reading order
		$target_idx = null;
		$min_hp     = PHP_INT_MAX;
		foreach ( $adjacent_enemies as $idx ) {
			$enemy = $units[ $idx ];
			if ( $enemy['hp'] < $min_hp ) {
				$min_hp     = $enemy['hp'];
				$target_idx = $idx;
			} elseif ( $enemy['hp'] === $min_hp && $target_idx !== null ) {
				// Tie: choose by reading order
				$target = $units[ $target_idx ];
				if ( $enemy['row'] < $target['row'] || 
					 ($enemy['row'] === $target['row'] && $enemy['col'] < $target['col']) ) {
					$target_idx = $idx;
				}
			}
		}

		if ( $target_idx !== null ) {
			$units[ $target_idx ]['hp'] -= $unit['power'];
			return true;
		}

		return false;
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
	$day15  = new Day15( $test, $part );
	$result = $day15->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 18740,
			'real' => 206236,
		],
		2 => [
			'test' => 1140,
			'real' => 88537,
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
