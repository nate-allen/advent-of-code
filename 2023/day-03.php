<?php

/**
 * Day 03: Gear Ratios
 */
class Day03 {

	/**
	 * The puzzle data.
	 *
	 * @var array
	 */
	private array $data;

	private array $adjacent_offsets = [
		[-1, -1], [-1, 0], [-1, 1],
		[ 0, -1],          [ 0, 1],
		[ 1, -1], [ 1, 0], [ 1, 1]
	];

	public function __construct( $part, $test ) {
		$this->parse_data( $test );
	}

	/**
	 * Part 1: Calculates the sum of all part numbers.
	 *
	 * @return int The sum of all part numbers.
	 */
	public function part_1(): int {
		$sum = 0;

		for ( $i = 0; $i < count( $this->data ); $i ++ ) {
			for ( $j = 0; $j < strlen( $this->data[ $i ] ); $j ++ ) {
				if ( is_numeric( $this->data[ $i ][ $j ] ) ) {
					$number = $this->data[ $i ][ $j ];
					$k      = $j + 1;

					while ( $k < strlen( $this->data[ $i ] ) && is_numeric( $this->data[ $i ][ $k ] ) ) {
						$number .= $this->data[ $i ][ $k ];
						$k ++;
					}

					if ( $this->is_number_adjacent_to_symbol( $i, $j, $k - 1 ) ) {
						$sum += (int) $number;
					}

					$j = $k - 1;
				}
			}
		}

		return $sum;
	}

	/**
	 * Part 2: Calculates the sum of gear number products.
	 *
	 * @return int The sum of gear number products.
	 */
	public function part_2(): int {
		$total = 0;
		$gears = [];

		// Clean the schematic so it's just the gear symbol.
		$cleaned_schematic = array_map( function ( $line ) {
			return preg_replace( '/[^0-9*]/', '.', $line );
		}, $this->data );

		// Extract numbers that are adjacent to gears.
		foreach ( $cleaned_schematic as $row_index => $line ) {
			for ( $col_index = 0; $col_index < strlen( $line ); $col_index ++ ) {
				if ( is_numeric( $line[ $col_index ] ) ) {
					$number     = $line[ $col_index ];
					$next_index = $col_index + 1;

					while ( $next_index < strlen( $line ) && is_numeric( $line[ $next_index ] ) ) {
						$number .= $line[ $next_index ];
						$next_index ++;
					}

					$gear_position = $this->find_adjacent_gear( $cleaned_schematic, $row_index, $col_index, $next_index - 1 );
					if ( $gear_position ) {
						$gears[ $gear_position ][] = (int) $number;
					}

					$col_index = $next_index - 1;
				}
			}
		}

		// Calculate the sum of gear number products.
		foreach ( $gears as $numbers ) {
			if ( count( $numbers ) === 2 ) {
				$total += $numbers[0] * $numbers[1];
			}
		}

		return $total;
	}

	/**
	 * Checks if a number is adjacent to a symbol.
	 *
	 * @param int $row       The row index.
	 * @param int $start_col The column index where the number starts.
	 * @param int $end_col   The column index where the number ends.
	 *
	 * @return bool True if adjacent to a symbol, false otherwise.
	 */
	private function is_number_adjacent_to_symbol( int $row, int $start_col, int $end_col ): bool {
		for ( $col = $start_col; $col <= $end_col; $col ++ ) {
			if ( $this->has_adjacent_symbol( $row, $col ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Checks if a cell has an adjacent symbol.
	 *
	 * @param int $i The row index.
	 * @param int $j The column index.
	 *
	 * @return bool True if an adjacent symbol is found, false otherwise.
	 */
	private function has_adjacent_symbol(int $i, int $j): bool {
		foreach ( $this->adjacent_offsets as $offset ) {
			$di = $i + $offset[0];
			$dj = $j + $offset[1];

			if ( $di >= 0 && $di < count( $this->data ) && $dj >= 0 && $dj < strlen( $this->data[ $di ] ) ) {
				$adjacent_char = $this->data[ $di ][ $dj ];
				if ( ! is_numeric( $adjacent_char ) && $adjacent_char !== '.' && $adjacent_char !== ' ' ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Finds if a number is adjacent to a gear symbol and returns its position.
	 *
	 * @param array $schematic The schematic as an array of strings.
	 * @param int   $row       Row index of the current number.
	 * @param int   $start_col Start column index of the current number.
	 * @param int   $end_col   End column index of the current number.
	 *
	 * @return string|null Position of the adjacent gear or null if not found.
	 */
	private function find_adjacent_gear(array $schematic, int $row, int $start_col, int $end_col): ?string {
		for ( $col = $start_col; $col <= $end_col; $col ++ ) {
			$gear_position = $this->check_for_adjacent_gear( $schematic, $row, $col );
			if ( $gear_position ) {
				return $gear_position;
			}
		}

		return null;
	}

	/**
	 * Checks for a gear symbol adjacent to a specific position in the schematic.
	 *
	 * @param array $schematic The schematic as an array of strings.
	 * @param int   $row_index Row index of the current position.
	 * @param int   $col_index Column index of the current position.
	 *
	 * @return string|null Position of the adjacent gear or null if not found.
	 */
	private function check_for_adjacent_gear(array $schematic, int $row_index, int $col_index): ?string {
		foreach ( $this->adjacent_offsets as $offset ) {
			$adjacent_row = $row_index + $offset[0];
			$adjacent_col = $col_index + $offset[1];

			if ( isset( $schematic[ $adjacent_row ][ $adjacent_col ] ) ) {
				$adjacent_char = $schematic[ $adjacent_row ][ $adjacent_col ];
				if ( $adjacent_char === '*' ) {
					return $adjacent_row . "-" . $adjacent_col;
				}
			}
		}

		return null;
	}

	private function parse_data(string $test) {
		$data = $test ? '/data/day-03-test.txt' : '/data/day-03.txt';
		$this->data = explode(PHP_EOL, file_get_contents(__DIR__ . $data));
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
	$day03  = new Day03( 1, $test );
	$result = $day03->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start = microtime( true );
	$day03 = new Day03( 2, $test );

	$result = $day03->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 2 ) );
}
