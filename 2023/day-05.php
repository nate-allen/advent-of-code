<?php

/**
 * Day 05: If You Give A Seed A Fertilizer
 */
class Day05 {
	/**
	 * The raw puzzle data.
	 *
	 * @var array
	 */
	private array $data;

	/**
	 * Mappings for each category conversion.
	 *
	 * @var array
	 */
	private array $maps;

	public function __construct( $part, $test ) {
		$this->parse_data( $test );
		$this->maps = $this->extract_mappings();
	}

	/**
	 * Part 1: What is the lowest location number?
	 *
	 * @return int The answer.
	 */
	public function part_1(): int {
		// Parse the individual seed numbers.
		$seeds = $this->extract_seed_numbers();

		return $this->find_lowest_location_from_seeds( $seeds );
	}

	/**
	 * Part 2: What is the lowest location for a range of numbers?
	 *
	 * @return int The answer.
	 */
	public function part_2(): int {
		$index  = 0;
		$ranges = $this->extract_seed_ranges( $this->data, $index );
		$maps   = $this->extract_range_mappings( $this->data, $index );

		return $this->find_lowest_location_from_ranges( $ranges, $maps );
	}

	/**
	 * Parses the seed numbers from the data.
	 *
	 * @return array The seed numbers.
	 */
	private function extract_seed_numbers(): array {
		$seedsLine  = str_replace( 'seeds: ', '', $this->data[0] );
		$seedsParts = explode( ' ', $seedsLine );

		return array_map( 'intval', $seedsParts );
	}

	/**
	 * Finds the lowest location number from an array of seeds.
	 * This method applies each mapping to every seed and finds the minimum of the final results.
	 *
	 * @param array $seeds The array of seed numbers.
	 *
	 * @return int The lowest location number.
	 */
	private function find_lowest_location_from_seeds( array $seeds ): int {
		$location_numbers = [];

		foreach ( $seeds as $seed ) {
			$current_number = $seed;
			foreach ( $this->maps as $map ) {
				$current_number = $this->convert_number_using_map( $current_number, $map );
			}
			$location_numbers[] = $current_number;
		}

		return min( $location_numbers );
	}

	/**
	 * Applies a mapping to a number.
	 *
	 * @param int   $number The number to be mapped.
	 * @param array $map    The mapping to apply to the number.
	 *
	 * @return int The mapped number.
	 */
	private function convert_number_using_map( int $number, array $map ): int {
		foreach ( $map as $mapping ) {
			// Extract mapping details.
			list( $dest_start, $source_start, $range_length ) = $mapping;

			// Check if the number falls within the current mapping range and apply the mapping.
			if ( $number >= $source_start && $number < $source_start + $range_length ) {
				return $dest_start + ( $number - $source_start );
			}
		}

		return $number;
	}

	/**
	 * Parses the mappings from the data.
	 *
	 * @return array A multidimensional array of mappings.
	 */
	private function extract_mappings(): array {
		$maps        = [];
		$current_map = [];

		foreach ( $this->data as $line ) {
			// Start a new map when 'map:' is encountered.
			if ( str_contains( $line, 'map:' ) ) {
				if ( ! empty( $current_map ) ) {
					// Save the previous map before starting a new one.
					$maps[]      = $current_map;
					$current_map = [];
				}
			} else {
				$parts = explode( ' ', $line );
				if ( count( $parts ) === 3 ) {
					// Parse each mapping line into an array of integers.
					$current_map[] = array_map( 'intval', $parts );
				}
			}
		}

		// Save the last map.
		$maps[] = $current_map;

		return $maps;
	}

	/**
	 * Parses the puzzle data from a file.
	 *
	 * @param string $test Indicates the file path for test or real data.
	 */
	private function parse_data( string $test ): void {
		$data       = $test ? '/data/day-05-test.txt' : '/data/day-05.txt';
		$this->data = explode( PHP_EOL, file_get_contents( __DIR__ . $data ) );
	}

	/**
	 * Parses the seed ranges from the data.
	 *
	 * @param array $lines The puzzle data lines.
	 * @param int   &$index The current line index, passed by reference to update it for subsequent parsing.
	 *
	 * @return array An array of seed ranges.
	 */
	private function extract_seed_ranges( array $lines, int &$index ): array {
		// Extracts the seed range line, removes the 'seeds: ' prefix, and then splits it into individual parts.
		$seed_ranges_line = explode( ' ', substr( trim( $lines[ $index ++ ] ), 7 ) );
		$ranges         = [];

		// Parse each range from the line.
		for ( $i = 0; $i < count( $seed_ranges_line ); $i += 2 ) {
			$start    = (int) $seed_ranges_line[ $i ]; // Range start.
			$length   = (int) $seed_ranges_line[ $i + 1 ]; // Range length.

			// Calculate the end of the range and add it to the list.
			$ranges[] = [ $start, $start + $length - 1 ];
		}

		return $ranges;
	}

	/**
	 * Parses the mappings from the data.
	 *
	 * @param array $lines  The puzzle data lines.
	 * @param int   &$index The current line index for parsing continuity.
	 *
	 * @return array An array of mappings.
	 */
	private function extract_range_mappings( array $lines, int &$index ): array {
		$maps = [];
		while ( $index < count( $lines ) ) {
			// Skip empty lines.
			if ( trim( $lines[ $index ] ) === "" ) {
				$index ++;
				continue;
			}

			// Start a new mapping array when a 'map:' line is found.
			if ( str_contains( $lines[ $index ], 'map:' ) ) {
				$maps[] = [];
			} else {
				// Add the mapping to the current map array.
				$maps[ count( $maps ) - 1 ][] = explode( ' ', trim( $lines[ $index ] ) );
			}
			$index ++;
		}

		return $maps;
	}

	/**
	 * Processes seed ranges through mappings to find the lowest location number.
	 *
	 * @param array $ranges Seed ranges to be processed.
	 * @param array $maps   Mappings to apply to the ranges.
	 *
	 * @return int|null The lowest location number, or null if no valid location found.
	 */
	private function find_lowest_location_from_ranges( array $ranges, array $maps ): ?int {
		foreach ( $maps as $map ) {
			$new_ranges = [];

			// Process each range through the current map by converting it to the new ranges and merging them.
			foreach ( $ranges as $range ) {
				$converted_ranges = $this->convert_range_using_mappings( $range, $map );
				$new_ranges       = array_merge( $new_ranges, $converted_ranges );
			}
			$ranges = $new_ranges;
		}

		$lowest_location = PHP_INT_MAX;
		foreach ( $ranges as $range ) {
			// Find the lowest number within all ranges.
			$lowest_location = min( $lowest_location, $range[0], $range[1] );
		}

		return $lowest_location === PHP_INT_MAX ? null : $lowest_location;
	}

	/**
	 * Converts a range of numbers based on the mappings.
	 *
	 * @param array $range    The range to be converted.
	 * @param array $mappings The mappings to apply to the range.
	 *
	 * @return array An array of resulting ranges after conversion.
	 */
	private function convert_range_using_mappings( array $range, array $mappings ): array {
		$result_ranges = [];
		list( $min, $max ) = $range;

		foreach ( $mappings as $mapping ) {
			list( $destination_start, $source_start, $length ) = $mapping;
			$source_end = $source_start + $length;

			// Check if the current range overlaps with the mapping range.
			if ( $min < $source_end && $max >= $source_start ) {
				// Find the overlap range and calculate the destination range.
				$mapped_min = max( $min, $source_start );
				$mapped_max = min( $max, $source_end - 1 );

				$result_ranges[] = [
					$destination_start + ( $mapped_min - $source_start ),
					$destination_start + ( $mapped_max - $source_start )
				];
			}
		}

		// If the range does not intersect with any mapping, add it unchanged.
		if ( empty( $result_ranges ) ) {
			$result_ranges[] = $range;
		}

		return $result_ranges;
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
	$day05  = new Day05( 1, $test );
	$result = $day05->part_1();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}

function part_2( $test = false ) {
	$start = microtime( true );
	$day05 = new Day05( 2, $test );

	$result = $day05->part_2();
	$end    = microtime( true );

	printf( 'Total: %s' . PHP_EOL, $result );
	printf( 'Time: %s seconds' . PHP_EOL, round( $end - $start, 3 ) );
}
