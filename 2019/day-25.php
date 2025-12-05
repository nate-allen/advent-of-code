<?php

namespace AdventOfCode\Year2019;

include_once 'lib/IntcodeComputer.php';

/**
 * Day 25:
 */
class Day25 {
	/**
	 * Parsed data from the input file.
	 *
	 * @var array
	 */
	private array $data;

	public function __construct() {
		$this->data = $this->parse_data();
	}

	/**
	 * Executes the puzzle.
	 *
	 * @return integer
	 */
	public function run(): int {
		return $this->solve_part_1();
	}

	/**
	 * Part 1: Navigate Santa's ship, collect items, and find the correct weight to pass the security checkpoint.
	 *
	 * @return int
	 */
	private function solve_part_1(): int {
		// Create computer instance
		$computer = new IntcodeComputer( $this->data );

		// Interactive mode: Play the text adventure game
		// (Type 'solve' during gameplay to switch to automated mode)
		return $this->play_interactive( $computer );
	}

	/**
	 * Plays the game interactively, allowing manual exploration.
	 *
	 * @param IntcodeComputer $computer The Intcode computer instance.
	 *
	 * @return int
	 */
	private function play_interactive( IntcodeComputer $computer ): int {
		// Create saves directory if it doesn't exist
		$save_dir = __DIR__ . '/.saves';
		if ( ! is_dir( $save_dir ) ) {
			mkdir( $save_dir, 0755, true );
		}

		// Track visited rooms
		$visited_rooms = [];

		// Display welcome screen and wait for start
		$this->show_intro();
		
		// Wait for player to start
		while ( true ) {
			$start_command = trim( readline( "\n\033[1;36m>\033[0m " ) );
			
			if ( $start_command === 'start' ) {
				break;
			} elseif ( $start_command === 'solve' ) {
				echo "\n\033[1;33mLaunching automated solver...\033[0m\n\n";
				$result = $this->automated_solve( $computer );
				return $result['password'];
			} elseif ( strpos( $start_command, 'load ' ) === 0 ) {
				$save_name = substr( $start_command, 5 );
				$save_file = $save_dir . '/' . preg_replace( '/[^a-zA-Z0-9_-]/', '', $save_name ) . '.save';
				
				if ( file_exists( $save_file ) ) {
					$computer = unserialize( file_get_contents( $save_file ) );
					echo "\033[32m✓ Game loaded from '$save_name'\033[0m\n";
					break;
				} else {
					echo "\033[31m✗ No save found. Type 'start' to begin a new game.\033[0m\n";
				}
			} elseif ( $start_command === 'saves' ) {
				$this->list_saved_games( $save_dir );
			} elseif ( $start_command === 'quit' || $start_command === 'exit' ) {
				echo "Goodbye!\n";
				return 0;
			} else {
				echo "\033[33mType 'start' to begin, 'solve' to auto-solve, or 'quit' to exit.\033[0m\n";
			}
		}

		// Clear screen and show help
		echo "\n" . str_repeat( '─', 70 ) . "\n\n";
		$this->show_help();

		// Display initial output
		$initial_output = $this->collect_output( $computer );

		// Track initial room
		if ( preg_match( '/== (.+?) ==/', $initial_output, $matches ) ) {
			$visited_rooms[ $matches[1] ] = true;
		}

		echo $initial_output;

		while ( ! $computer->has_halted() ) {
			$command = trim( readline( "\n\033[36mCommand>\033[0m " ) );

			if ( $command === '' ) {
				continue;
			}

			// Handle special commands (returns modified computer if loaded)
			$special_result = $this->handle_special_command( $command, $computer, $save_dir );
			if ( $special_result['action'] === 'quit' ) {
				break;
			} elseif ( $special_result['action'] === 'continue' ) {
				if ( isset( $special_result['computer'] ) ) {
					$computer = $special_result['computer'];
				}
				continue;
			}

			// Apply shortcuts
			$command = $this->apply_shortcuts( $command );

			// Send command to computer and display response
			$this->send_command( $computer, $command );
			$output = $this->collect_output( $computer );

			// Track new rooms
			if ( preg_match( '/== (.+?) ==/', $output, $matches ) ) {
				$room_name = $matches[1];
				if ( ! isset( $visited_rooms[ $room_name ] ) ) {
					$visited_rooms[ $room_name ] = true;
					echo "\033[33m[New location discovered!]\033[0m\n";
				}
			}

			echo $output;
		}

		return 0;
	}

	/**
	 * Handles special commands like help, save, load, etc.
	 *
	 * @param string          $command   The command to handle.
	 * @param IntcodeComputer $computer  The Intcode computer instance.
	 * @param string          $save_dir  Directory for save files.
	 *
	 * @return array Array with 'action' ('quit', 'continue', or null) and optionally 'computer' for loaded state.
	 */
	private function handle_special_command( string $command, IntcodeComputer $computer, string $save_dir ): array {
		if ( $command === 'quit' || $command === 'exit' ) {
			echo "Thanks for playing!\n";
			return [ 'action' => 'quit' ];
		}

		if ( $command === 'solve' ) {
			echo "\n\033[1;33m🤖 Switching to automated solver...\033[0m\n\n";
			echo "Starting fresh exploration from the beginning...\n";
			echo str_repeat( '─', 70 ) . "\n\n";
			
			// Create a fresh computer instance and run automated solver
			$new_computer = new IntcodeComputer( $this->data );
			$this->automated_solve( $new_computer );
			
			return [ 'action' => 'quit' ];
		}

		if ( $command === 'help' || $command === '?' ) {
			$this->show_help();
			return [ 'action' => 'continue' ];
		}

		if ( $command === 'commands' ) {
			$this->show_commands();
			return [ 'action' => 'continue' ];
		}

		if ( strpos( $command, 'save ' ) === 0 ) {
			$save_name = substr( $command, 5 );
			// Sanitize filename
			$safe_name = preg_replace( '/[^a-zA-Z0-9_-]/', '', $save_name );
			$save_file = $save_dir . '/' . $safe_name . '.save';
			
			file_put_contents( $save_file, serialize( $computer ) );
			echo "\033[32m✓ Game saved as '$save_name'\033[0m\n";
			echo "\033[90m  (Saved to: .saves/$safe_name.save)\033[0m\n";
			return [ 'action' => 'continue' ];
		}

		if ( strpos( $command, 'load ' ) === 0 ) {
			$save_name = substr( $command, 5 );
			// Sanitize filename
			$safe_name = preg_replace( '/[^a-zA-Z0-9_-]/', '', $save_name );
			$save_file = $save_dir . '/' . $safe_name . '.save';
			
			if ( file_exists( $save_file ) ) {
				$loaded_computer = unserialize( file_get_contents( $save_file ) );
				echo "\033[32m✓ Game loaded from '$save_name'\033[0m\n";
				// Display current inventory after loading
				$this->send_command( $loaded_computer, 'inv' );
				echo $this->collect_output( $loaded_computer );
				return [ 'action' => 'continue', 'computer' => $loaded_computer ];
			} else {
				echo "\033[31m✗ No save found with name '$save_name'\033[0m\n";
			}
			return [ 'action' => 'continue' ];
		}

		if ( $command === 'saves' ) {
			$this->list_saved_games( $save_dir );
			return [ 'action' => 'continue' ];
		}

		return [ 'action' => null ];
	}

	/**
	 * Lists all saved games.
	 *
	 * @param string $save_dir Directory containing save files.
	 */
	private function list_saved_games( string $save_dir ): void {
		$saves = glob( $save_dir . '/*.save' );
		
		if ( empty( $saves ) ) {
			echo "No saved games found.\n";
		} else {
			echo "\033[1mSaved games:\033[0m\n";
			foreach ( $saves as $save_file ) {
				$save_name = basename( $save_file, '.save' );
				$date      = date( 'Y-m-d H:i:s', filemtime( $save_file ) );
				echo "  • \033[36m$save_name\033[0m (saved: $date)\n";
			}
			echo "\n";
		}
	}

	/**
	 * Applies command shortcuts (n/s/e/w/i).
	 *
	 * @param string $command The command to process.
	 *
	 * @return string The expanded command.
	 */
	private function apply_shortcuts( string $command ): string {
		$shortcuts = [
			'n' => 'north',
			's' => 'south',
			'e' => 'east',
			'w' => 'west',
			'i' => 'inv',
		];

		return $shortcuts[ $command ] ?? $command;
	}

	/**
	 * Displays the story introduction.
	 */
	private function show_intro(): void {
		echo "\n\n";
		echo "  \033[1;36m╔══════════════════════════════════════════════════════════════════╗\033[0m\n";
		echo "  \033[1;36m║                                                                  ║\033[0m\n";
		echo "  \033[1;36m║                █▀▀ █▀█ █▄█ █▀█ █▀ ▀█▀ ▄▀█ █▀ █ █▀                ║\033[0m\n";
		echo "  \033[1;36m║                █▄▄ █▀▄  █  █▄█ ▄█  █  █▀█ ▄█ █ ▄█                ║\033[0m\n";
		echo "  \033[1;36m║                                                                  ║\033[0m\n";
		echo "  \033[1;36m║\033[0m                     \033[1;33m~ Day 25: Santa's Ship ~\033[0m                     \033[1;36m║\033[0m\n";
		echo "  \033[1;36m║                                                                  ║\033[0m\n";
		echo "  \033[1;36m╚══════════════════════════════════════════════════════════════════╝\033[0m\n\n";

		echo "  Santa's ship is frozen at \033[31m-40°\033[0m! One faint life signature detected.\n";
		echo "  You've sent a droid through a hull breach to investigate.\n\n";
		echo "  \033[1;33mMISSION:\033[0m Find the airlock password by exploring the ship,\n";
		echo "          collecting items, and solving the weight puzzle!\n\n";

		echo "  \033[1;36m─────────────────────────────────────────────────────────────────\033[0m\n\n";
		echo "  Type \033[1;32mstart\033[0m to begin your adventure\n";
		echo "  Type \033[1;32msolve\033[0m to let the computer solve it automatically\n";
		echo "  Type \033[1;32mload <name>\033[0m to continue a saved game\n";
		echo "  Type \033[1;32msaves\033[0m to list all saved games\n";
		echo "  Type \033[1;32mquit\033[0m to exit\n";
	}

	/**
	 * Displays help information for the interactive mode.
	 */
	private function show_help(): void {
		echo "\033[1;33m=== HELP ===\033[0m\n\n";
		echo "\033[1mBasic Commands:\033[0m\n";
		echo "  \033[36mnorth\033[0m, \033[36msouth\033[0m, \033[36meast\033[0m, \033[36mwest\033[0m - Move around\n";
		echo "  \033[36mn\033[0m, \033[36ms\033[0m, \033[36me\033[0m, \033[36mw\033[0m               - Shortcut way to move\n";
		echo "  \033[36mtake <item>\033[0m              - Pick up an item (e.g., 'take mug')\n";
		echo "  \033[36mdrop <item>\033[0m              - Drop an item\n";
		echo "  \033[36minv\033[0m (or \033[36mi\033[0m)               - Check your inventory\n\n";
		echo "\033[1mSpecial Commands:\033[0m\n";
		echo "  \033[36mcommands\033[0m     - Show full command list and dangerous items\n";
		echo "  \033[36mhelp\033[0m or \033[36m?\033[0m    - Show this help again\n";
		echo "  \033[36msolve\033[0m        - Let the computer solve the puzzle automatically\n";
		echo "  \033[36msave <name>\033[0m  - Save current game state (e.g., 'save game1')\n";
		echo "  \033[36mload <name>\033[0m  - Load a saved game state\n";
		echo "  \033[36msaves\033[0m        - List all saved games\n";
		echo "  \033[36mquit\033[0m or \033[36mexit\033[0m - Exit the game\n\n";
		echo "\033[1;31m⚠️  Warning:\033[0m Be careful what you pick up! Some items are dangerous!\n\n";
	}

	/**
	 * Displays available game commands.
	 */
	private function show_commands(): void {
		echo "\033[1;33m=== AVAILABLE GAME COMMANDS ===\033[0m\n";
		echo "\033[1mMovement:\033[0m\n";
		echo "  north, south, east, west (or n, s, e, w)\n\n";
		echo "\033[1mItems:\033[0m\n";
		echo "  take <item>  - Pick up an item\n";
		echo "  drop <item>  - Drop an item from inventory\n";
		echo "  inv          - Show inventory (shortcut: i)\n\n";
		echo "\033[1mTip:\033[0m You need to collect items and reach the security\n";
		echo "     checkpoint with the right combination to proceed!\n\n";
	}

	/**
	 * Automated solution with verbose output for the player.
	 *
	 * @param IntcodeComputer $computer The Intcode computer instance.
	 *
	 * @return array Array with 'password', 'items', 'checkpoint', and 'winning_items'.
	 */
	private function automated_solve( IntcodeComputer $computer ): array {
		echo "Exploring Santa's ship...\n\n";
		
		// Items that should never be picked up
		$dangerous_items = [
			'infinite loop',
			'giant electromagnet',
			'molten lava',
			'photons',
			'escape pod',
		];

		// Explore the ship and collect safe items
		$exploration = $this->explore_ship( $computer, $dangerous_items );
		$items       = $exploration['items'];
		$checkpoint  = $exploration['checkpoint'];

		if ( empty( $checkpoint ) ) {
			echo "Error: Could not find Security Checkpoint\n";
			return [ 'password' => 0, 'items' => [], 'checkpoint' => [], 'winning_items' => [] ];
		}

		// Display collected items
		echo "\033[1;32m✓ Exploration complete!\033[0m\n\n";
		echo "\033[1mItems collected:\033[0m\n";
		foreach ( $items as $item ) {
			echo "  • $item\n";
		}
		echo "\n\033[1mPath to Security Checkpoint:\033[0m\n";
		echo "  " . implode( ' → ', $checkpoint ) . "\n\n";

		// Navigate to the checkpoint
		echo "Navigating to Security Checkpoint...\n";
		foreach ( $checkpoint as $direction ) {
			$this->send_command( $computer, $direction );
			$this->collect_output( $computer );
		}

		// Try all combinations of items
		echo "Testing item combinations to pass security...\n\n";
		$item_count    = count( $items );
		$total_combos  = pow( 2, $item_count );
		$winning_items = [];

		for ( $combination = 0; $combination < $total_combos; $combination++ ) {
			// Drop all items first
			foreach ( $items as $item ) {
				$this->send_command( $computer, "drop $item" );
				$this->collect_output( $computer );
			}

			// Pick up items based on the current combination
			$current_items = [];
			for ( $i = 0; $i < $item_count; $i++ ) {
				if ( $combination & (1 << $i) ) {
					$current_items[] = $items[ $i ];
					$this->send_command( $computer, "take {$items[$i]}" );
					$this->collect_output( $computer );
				}
			}

			// Try to go north through the security checkpoint
			$this->send_command( $computer, 'north' );
			$output = $this->collect_output( $computer );

			// Check if we passed
			if ( strpos( $output, 'lighter' ) === false && strpos( $output, 'heavier' ) === false ) {
				// Extract password
				if ( preg_match( '/typing (\d+) on the keypad/', $output, $matches ) ) {
					$password      = (int) $matches[1];
					$winning_items = $current_items;

					// Display solution
					echo "\033[1;32m✓ Security bypassed!\033[0m\n\n";
					echo str_repeat( '═', 70 ) . "\n";
					echo "\033[1;36m                         SOLUTION FOUND\033[0m\n";
					echo str_repeat( '═', 70 ) . "\n\n";

					echo "\033[1mWinning item combination:\033[0m\n";
					foreach ( $winning_items as $item ) {
						echo "  \033[32m✓\033[0m $item\n";
					}
					echo "\n\033[1mAirlock password:\033[0m \033[1;33m$password\033[0m\n\n";

					return [
						'password'      => $password,
						'items'         => $items,
						'checkpoint'    => $checkpoint,
						'winning_items' => $winning_items,
					];
				}
			}
		}

		return [ 'password' => 0, 'items' => $items, 'checkpoint' => $checkpoint, 'winning_items' => [] ];
	}

	/**
	 * Explores the ship using DFS and collects safe items.
	 *
	 * @param IntcodeComputer $computer         The Intcode computer instance.
	 * @param array           $dangerous_items  Items to avoid.
	 *
	 * @return array Array with 'items' (collected) and 'checkpoint' (path to checkpoint).
	 */
	private function explore_ship( IntcodeComputer $computer, array $dangerous_items ): array {
		$visited         = [];
		$items_collected = [];
		$checkpoint_path = [];
		$path            = [];

		// Get initial room description
		$output = $this->collect_output( $computer );

		// Extract starting location name
		preg_match( '/== (.+?) ==/', $output, $name_match );
		$current_location = $name_match[1] ?? 'start';

		// Parse initial room
		$room_info = $this->parse_room( $output );

		// Collect items in starting room
		foreach ( $room_info['items'] as $item ) {
			if ( ! in_array( $item, $dangerous_items, true ) ) {
				$this->send_command( $computer, "take $item" );
				$take_output = $this->collect_output( $computer );
				if ( strpos( $take_output, 'taken' ) !== false || strpos( $take_output, 'You take' ) !== false ) {
					$items_collected[] = $item;
				}
			}
		}

		// Start DFS exploration
		$this->dfs_explore(
			$computer,
			$current_location,
			$visited,
			$items_collected,
			$checkpoint_path,
			$path,
			$dangerous_items
		);

		return [
			'items'      => $items_collected,
			'checkpoint' => $checkpoint_path,
		];
	}

	/**
	 * Depth-first search exploration of the ship.
	 *
	 * @param IntcodeComputer $computer         The Intcode computer.
	 * @param string          $location         Current location.
	 * @param array           $visited          Visited locations.
	 * @param array           $items_collected  Items collected so far.
	 * @param array           $checkpoint_path  Path to security checkpoint.
	 * @param array           $path             Current path from start.
	 * @param array           $dangerous_items  Items to avoid.
	 */
	private function dfs_explore(
		IntcodeComputer $computer,
		string $location,
		array &$visited,
		array &$items_collected,
		array &$checkpoint_path,
		array $path,
		array $dangerous_items
	): void {
		// Mark as visited
		$visited[ $location ] = true;

		// Check if current location is the Security Checkpoint
		if ( strpos( $location, 'Security Checkpoint' ) !== false ) {
			$checkpoint_path = $path;
			return; // Don't explore further from here
		}

		// Explore each direction
		$directions   = [ 'north', 'south', 'east', 'west' ];
		$opposite_dir = [
			'north' => 'south',
			'south' => 'north',
			'east'  => 'west',
			'west'  => 'east',
		];

		foreach ( $directions as $direction ) {
			// Try to move in this direction
			$this->send_command( $computer, $direction );
			$move_output = $this->collect_output( $computer );

			// Check if we successfully moved (has room header "== Name ==")
			if ( strpos( $move_output, '==' ) !== false ) {
				// We moved successfully, get new location name
				preg_match( '/== (.+?) ==/', $move_output, $name_match );
				$new_location = $name_match[1] ?? md5( $move_output );

				// Parse room for items
				$room_info = $this->parse_room( $move_output );

				// Collect safe items in this room
				foreach ( $room_info['items'] as $item ) {
					if ( ! in_array( $item, $dangerous_items, true ) ) {
						$this->send_command( $computer, "take $item" );
						$take_output = $this->collect_output( $computer );

						// Check if taking the item didn't cause a problem
						if ( strpos( $take_output, 'You take' ) !== false ) {
							$items_collected[] = $item;
						}
					}
				}

				// Check if this is Security Checkpoint
				if ( strpos( $new_location, 'Security Checkpoint' ) !== false ) {
					// Found it! Save the path
					$new_path        = $path;
					$new_path[]      = $direction;
					$checkpoint_path = $new_path;
					// Move back and don't explore it
					$this->send_command( $computer, $opposite_dir[ $direction ] );
					$this->collect_output( $computer );
				} elseif ( ! isset( $visited[ $new_location ] ) ) {
					// Recursively explore
					$new_path   = $path;
					$new_path[] = $direction;
					$this->dfs_explore(
						$computer,
						$new_location,
						$visited,
						$items_collected,
						$checkpoint_path,
						$new_path,
						$dangerous_items
					);

					// Move back
					$this->send_command( $computer, $opposite_dir[ $direction ] );
					$this->collect_output( $computer );
				} else {
					// Already visited, just move back
					$this->send_command( $computer, $opposite_dir[ $direction ] );
					$this->collect_output( $computer );
				}
			}
		}
	}

	/**
	 * Parses room output to extract items and available directions.
	 *
	 * @param string $output Room description output.
	 *
	 * @return array Array with 'items' and 'doors'.
	 */
	private function parse_room( string $output ): array {
		$items = [];
		$doors = [];

		// Extract items
		if ( preg_match( '/Items here:\n((?:- .+\n)+)/', $output, $matches ) ) {
			preg_match_all( '/- (.+)/', $matches[1], $item_matches );
			$items = $item_matches[1];
		}

		// Extract doors
		if ( preg_match( '/Doors here lead:\n((?:- .+\n)+)/', $output, $matches ) ) {
			preg_match_all( '/- (.+)/', $matches[1], $door_matches );
			$doors = $door_matches[1];
		}

		return [
			'items' => $items,
			'doors' => $doors,
		];
	}

	/**
	 * Converts a string to ASCII codes and sends to the computer.
	 *
	 * @param IntcodeComputer $computer The Intcode computer instance.
	 * @param string          $command  The command string.
	 */
	private function send_command( IntcodeComputer $computer, string $command ): void {
		// Convert string to ASCII codes
		$ascii_codes = array_merge(
			array_map( 'ord', str_split( $command ) ),
			[ 10 ] // Add newline
		);

		// Send each ASCII code as input
		foreach ( $ascii_codes as $code ) {
			$computer->add_input( $code );
		}
	}

	/**
	 * Collects output from the computer and returns as string.
	 *
	 * @param IntcodeComputer $computer The Intcode computer instance.
	 *
	 * @return string The output text.
	 */
	private function collect_output( IntcodeComputer $computer ): string {
		$output = '';

		while ( ! $computer->has_halted() ) {
			$value = $computer->run_until_output();
			if ( $value === null ) {
				break;
			}
			$output .= chr( $value );
		}

		return $output;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @return array
	 */
	private function parse_data(): array {
		return array_map( 'intval', explode( ",", trim( file_get_contents( __DIR__ . '/data/day-25.txt' ) ) ) );
	}
}

// Run the puzzle
$day25 = new Day25();
$day25->run();
