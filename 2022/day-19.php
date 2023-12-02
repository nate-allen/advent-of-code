<?php
/**
 * Day 19: Not Enough Minerals
 */
class Day19 {

	// Blueprints.
	private array $blueprints;

	// Types of Robots.
	private array $robots;

	// Number of minutes.
	private int $minutes;

	// The current blueprint being used.
	private array $current_blueprint;

	public function __construct( $test ) {
		$this->blueprints = $this->parse_data( $test );
		$this->robots     = array( 'geode', 'obsidian', 'clay', 'ore' );
	}

	/**
	 * Part 1: What do you get if you add up the quality level of all the blueprints in your list?
	 *
	 * @return int
	 */
	public function part_1(): int {
		$this->minutes = 24;
		$total         = 0;

		foreach ( $this->blueprints as $key => $blueprint ) {
			$this->current_blueprint = $blueprint;
			$geodes                  = $this->mine();

			echo "Blueprint: $key, Geodes: $geodes" . PHP_EOL;

			$total += $geodes * $key;
		}

		return $total; // 1009
	}

	/**
	 * Part 2: You only have the first 3 blueprints. What is the maximum number of geodes you can get in 32 minutes?
	 *
	 * @return int
	 */
	public function part_2(): int {
		$this->minutes = 32;
		$total         = 1;

		foreach ( array_slice( $this->blueprints, 0, 3 ) as $key => $blueprint ) {
			$this->current_blueprint = $blueprint;
			$geodes                  = $this->mine();

			echo "Blueprint: $key, Geodes: $geodes" . PHP_EOL;

			$total *= $geodes;
		}

		return $total; // 18816
	}

	/**
	 * Logic for mining the geodes.
	 *
	 * @return int
	 */
	private function mine() {
		$geodes = 0;
		$seen   = array();
		$queue  = $this->get_initial_state();

		while ( ! empty( $queue ) ) {
			$current = array_shift( $queue );
			$key     = $this->get_cache_key( $current );
			$geodes  = max( $geodes, $current['inventory']['geode'] );

			if ( $current['time'] === $this->minutes || $current['inventory']['geode'] + 1 < $geodes ) {
				continue;
			}

			if ( ! $this->is_worth_considering( $current, $seen ) ) {
				continue;
			}

			$seen[ $key ][] = $current['inventory'];

			foreach ( $this->get_next_states( $current ) as $next ) {
				$queue[] = $next;
			}
		}

		return $geodes;
	}

	/**
	 * Gets the next state.
	 *
	 * @param array $current
	 *
	 * @return array
	 */
	private function get_next_states( array $current ) {
		$next_states = array();
		$current['time']++;

		// If we can build a geode bot, we always should.
		if ( $this->can_build( 'geode', $current['inventory'] ) ) {
			$next = $current;

			$next['inventory'] = $this->build_robot( 'geode', $next['inventory'] );
			$next['inventory'] = $this->gather_resources( $next['robots'], $next['inventory'] );

			$next['robots']['geode']++;

			$next['skip'] = array();

			$next_states[] = $next;

			return $next_states;
		}

		// A state where we don't build any bots.
		$do_not_build_state              = $current;
		$do_not_build_state['inventory'] = $this->gather_resources( $do_not_build_state['robots'], $do_not_build_state['inventory'] );
		$do_not_build_state['skip']      = array();

		if ( $this->can_build( 'obsidian', $current['inventory'] ) ) {
			$next = $current;

			$next['inventory'] = $this->build_robot( 'obsidian', $next['inventory'] );
			$next['inventory'] = $this->gather_resources( $next['robots'], $next['inventory'] );

			$next['robots']['obsidian']++;

			$next['skip'] = array();

			$next_states[] = $next;
		}

		// Don't build a clay bot if current production exceeds what's needed to build every bot.
		if ( $this->can_build( 'clay', $current['inventory'] ) && $current['robots']['clay'] < $this->current_blueprint['obsidian']['clay'] ) {
			if ( ! in_array( 'clay', $current['skip'], true ) ) {
				$next = $current;

				$next['inventory'] = $this->build_robot( 'clay', $next['inventory'] );
				$next['inventory'] = $this->gather_resources( $next['robots'], $next['inventory'] );

				$next['robots']['clay']++;
				$next['skip'] = array();

				$next_states[] = $next;
			}

			$do_not_build_state['skip'][] = array( 'clay' );
		}

		// Don't build an ore bot if current production exceeds what's needed to build every bot.
		if ( $this->can_build( 'ore', $current['inventory'] ) && $current['robots']['ore'] < $this->get_max_ores() ) {
			if ( ! in_array( 'ore', $current['skip'], true ) ) {
				$next = $current;

				$next['inventory'] = $this->build_robot( 'ore', $next['inventory'] );
				$next['inventory'] = $this->gather_resources( $next['robots'], $next['inventory'] );

				$next['robots']['ore']++;
				$next['skip'] = array();

				$next_states[] = $next;
			}

			$do_not_build_state['skip'][] = array( 'ore' );
		}

		$next_states[] = $do_not_build_state;

		return $next_states;
	}

	/**
	 * Returns the max number of ores for a given blueprint.
	 *
	 * @param array $blueprint The blueprint.
	 *
	 * @return int
	 */
	private function get_max_ores() {
		return max(
			$this->current_blueprint['ore']['ore'],
			$this->current_blueprint['clay']['ore'],
			$this->current_blueprint['obsidian']['ore'],
			$this->current_blueprint['geode']['ore']
		);
	}

	/**
	 * Determines if a given state is worth considering as a potential solution.
	 *
	 * Return true if the state has not been seen before or has more resources than a previously seen state with the
	 * same key, false otherwise.
	 *
	 * @param array $current The current state of the bots and inventory.
	 * @param array $seen    An array of previously seen states.
	 *
	 * @return bool
	 */
	private function is_worth_considering( array $current, array $seen ) {
		$key = $this->get_cache_key( $current );

		if ( ! isset( $seen[ $key ] ) ) {
			return true;
		}

		$inventory = $current['inventory'];

		// If the current inventory is equal to or less than the previously seen inventory, return false.
		foreach ( $seen[ $key ] as $seen_inventory ) {
			if ( $inventory['ore'] <= $seen_inventory['ore']
				&& $inventory['clay'] <= $seen_inventory['clay']
				&& $inventory['obsidian'] <= $seen_inventory['obsidian']
				&& $inventory['geode'] <= $seen_inventory['geode']
			) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Returns the initial state.
	 *
	 * @return array
	 */
	private function get_initial_state() {
		$state = array(
			'time'      => 0,
			'inventory' => array(
				'ore'      => 0,
				'clay'     => 0,
				'obsidian' => 0,
				'geode'    => 0,
			),
			'robots'    => array(
				'ore'      => 1,
				'clay'     => 0,
				'obsidian' => 0,
				'geode'    => 0,
			),
			'skip'      => array(),
		);

		return array( $state );
	}

	/**
	 * Returns a cache key based on the time and robots.
	 *
	 * @param $state
	 *
	 * @return string
	 */
	private function get_cache_key( $state ) {
		if ( ! is_array( $state ) || ! is_array( $state['robots'] ) ) {
			echo 'Invalid state: ' . print_r( $state, true );exit;
		}

		return $state['time'] . ',' . implode( ',', $state['robots'] );
	}

	/**
	 * Determines if we can build a given robot.
	 *
	 * @param string $robot     The robot to build.
	 * @param array  $inventory The current inventory.
	 *
	 * @return bool
	 */
	private function can_build( string $robot, array $inventory ) {
		foreach ( $this->current_blueprint[ $robot ] as $resource => $amount ) {
			if ( $inventory[ $resource ] < $amount ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Builds a robot.
	 *
	 * @param string $robot     The robot to build.
	 * @param array  $inventory The current inventory.
	 *
	 * @return array
	 */
	private function build_robot( string $robot, array $inventory ) {
		foreach ( $this->current_blueprint[ $robot ] as $material => $quantity ) {
			$inventory[ $material ] -= $quantity;
		}

		return $inventory;
	}

	/**
	 * Gathers resources for the given robots.
	 *
	 * @param array $robots    The robots.
	 * @param array $inventory The current inventory.
	 *
	 * @return array
	 */
	private function gather_resources( array $robots, array $inventory ) {
		foreach ( $robots as $material => $quantity ) {
			$inventory[ $material ] += $quantity;
		}

		return $inventory;
	}

	/**
	 * Parse the data.
	 *
	 * @param string $test The test data.
	 *
	 * @return array
	 */
	private function parse_data( string $test ): array {
		$path  = $test ? '/data/day-19-test.txt' : '/data/day-19.txt';
		$lines = explode( PHP_EOL, file_get_contents( __DIR__ . $path ) );

		// Blueprint 1: Each ore robot costs 2 ore. Each clay robot costs 4 ore. Each obsidian robot costs 4 ore and 17 clay. Each geode robot costs 3 ore and 11 obsidian.
		$regex = '/Blueprint (\d+).*ore robot costs (\d+).*clay robot costs (\d+).*obsidian robot costs (\d+) ore and (\d+).*geode robot costs (\d+) ore and (\d+)/';

		$blueprints = array();

		foreach ( $lines as $line ) {
			preg_match( $regex, $line, $matches );
			$blueprints[ $matches[1] ] = array(
				'ore'      => array(
					'ore' => (int) $matches[2],
				),
				'clay'     => array(
					'ore' => (int) $matches[3],
				),
				'obsidian' => array(
					'ore'  => (int) $matches[4],
					'clay' => (int) $matches[5],
				),
				'geode'    => array(
					'ore'      => (int) $matches[6],
					'obsidian' => (int) $matches[7],
				),
			);
		}

		return $blueprints;
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
	$day19  = new Day19( $test );
	$result = $day19->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start  = microtime( true );
	$day19  = new Day19( $test );
	$result = $day19->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}
