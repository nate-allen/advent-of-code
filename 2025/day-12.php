<?php

namespace AdventOfCode\Year2025;

/**
 * Day 12: Christmas Tree Farm
 */
class Day12 {
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
	 * Part 1: Count how many regions can fit all their required presents.
	 *
	 * Uses a simple area check: if the total area of all required presents
	 * is less than or equal to the region area, they can fit.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$shape_areas = $this->data['shape_areas'];
		$regions     = $this->data['regions'];
		
		$count = 0;
		
		foreach ( $regions as $region ) {
			$width  = $region['width'];
			$height = $region['height'];
			$counts = $region['counts'];
			
			// Calculate total area needed
			$total_area_needed = 0;
			foreach ( $counts as $shape_index => $quantity ) {
				$total_area_needed += $shape_areas[ $shape_index ] * $quantity;
			}
			
			// Calculate available area
			$available_area = $width * $height;
			
			// If total area needed <= available area, it can fit
			if ( $total_area_needed <= $available_area ) {
				$count++;
			}
		}
		
		return $count;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @return array
	 */
	private function parse_data(): array {
		$file    = '/data/day-12.txt';
		$content = file_get_contents( __DIR__ . $file );
		
		$sections = explode( "\n\n", trim( $content ) );
		
		$regions_text = array_pop( $sections );
		$shapes_text  = $sections;
		
		$shape_areas = [];
		foreach ( $shapes_text as $shape_text ) {
			$lines = explode( "\n", trim( $shape_text ) );
			// First line is "N:", skip it
			$pattern       = implode( "\n", array_slice( $lines, 1 ) );
			$area          = substr_count( $pattern, '#' );
			$shape_areas[] = $area;
		}
		
		$regions      = [];
		$region_lines = explode( "\n", trim( $regions_text ) );
		foreach ( $region_lines as $line ) {
			// Format: "WxH: count0 count1 count2 ..."
			if ( preg_match( '/^(\d+)x(\d+):\s*(.+)$/', $line, $matches ) ) {
				$width     = (int) $matches[1];
				$height    = (int) $matches[2];
				$counts    = array_map( 'intval', explode( ' ', trim( $matches[3] ) ) );
				$regions[] = [
					'width' => $width,
					'height' => $height,
					'counts' => $counts,
				];
			}
		}
		
		return [
			'shape_areas' => $shape_areas,
			'regions' => $regions,
		];
	}
}

/**
 * Runs the puzzle and outputs results.
 */
function run_puzzle(): void {
	$start  = microtime( true );
	$day12  = new Day12();
	$result = $day12->run();
	$end    = microtime( true );

	// ANSI color codes
	$yellow = "\033[33m"; // Yellow text
	$reset  = "\033[0m";  // Reset text formatting

	printf( PHP_EOL );
	printf( $yellow . 'Answer: ' . $reset . '%s' . PHP_EOL, $result );
	printf( $yellow . 'Time:   ' . $reset . '%s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

// Run the puzzle
run_puzzle();
