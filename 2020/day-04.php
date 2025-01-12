<?php

namespace AdventOfCode\Year2020;

/**
 * Day 04: Passport Processing
 */
class Day04 {
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
	private array $passports;

	public function __construct( bool $test, int $part ) {
		$this->part      = $part;
		$this->is_test   = $test;
		$this->passports = $this->parse_data( $this->is_test );
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
	 * Part 1: Count the number of valid passports - those that have all required fields.
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$required_fields  = [ 'byr', 'iyr', 'eyr', 'hgt', 'hcl', 'ecl', 'pid' ];
		$valid_passports = 0;

		foreach ( $this->passports as $passport ) {
			$keys = array_keys( $passport );

			if ( count( array_intersect( $required_fields, $keys ) ) === count( $required_fields ) ) {
				$valid_passports++;
			}
		}

		return $valid_passports;
	}

	/**
	 * Part 2: Count the number of valid passports - those that have all required fields and valid values.
	 *
	 * @return integer
	 */
	private function solve_part_2(): int {
		$valid_passports = 0;

		foreach ( $this->passports as $passport ) {
			if ( $this->is_valid_passport( $passport ) ) {
				$valid_passports++;
			}
		}

		return $valid_passports;
	}

	/**
	 * Validates a passport entry.
	 *
	 * @param array $passport The passport data.
	 *
	 * @return bool
	 */
	private function is_valid_passport( array $passport ): bool {
		$required_fields = [ 'byr', 'iyr', 'eyr', 'hgt', 'hcl', 'ecl', 'pid' ];

		// Check if all required fields are present
		if ( count( array_intersect( $required_fields, array_keys( $passport ) ) ) !== count( $required_fields ) ) {
			return false;
		}

		// Validate the birth year is between 1920 and 2002
		if ( ! $this->is_valid_year( $passport['byr'], 1920, 2002 ) ) {
			return false;
		}

		// Validate that the issue year is between 2010 and 2020
		if ( ! $this->is_valid_year( $passport['iyr'], 2010, 2020 ) ) {
			return false;
		}

		// Validate that the expiration year is between 2020 and 2030
		if ( ! $this->is_valid_year( $passport['eyr'], 2020, 2030 ) ) {
			return false;
		}

		// Validate the hair color is a valid hex color
		if ( ! preg_match( '/^#[0-9a-f]{6}$/', $passport['hcl'] ) ) {
			return false;
		}

		// Validate the eye color is one of the allowed values
		if ( ! in_array( $passport['ecl'], [ 'amb', 'blu', 'brn', 'gry', 'grn', 'hzl', 'oth' ], true ) ) {
			return false;
		}

		// Validate the passport ID is a 9-digit number
		if ( ! preg_match( '/^\d{9}$/', $passport['pid'] ) ) {
			return false;
		}

		// Validate the height is between 150-193cm or 59-76in
		if ( ! preg_match( '/^(\d+)(cm|in)$/', $passport['hgt'], $matches ) ) {
			return false;
		}

		$height = (int) $matches[1];
		$unit   = $matches[2];

		// If the unit is cm, the height must be between 150-193cm
		if ( $unit === 'cm' && ( $height < 150 || $height > 193 ) ) {
			return false;
		}

		// If the unit is in, the height must be between 59-76in
		if ( $unit === 'in' && ( $height < 59 || $height > 76 ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Validates a year value.
	 *
	 * @param string $year The year value.
	 * @param int    $min  The minimum allowed value.
	 * @param int    $max  The maximum allowed value.
	 *
	 * @return bool
	 */
	private function is_valid_year( string $year, int $min, int $max ): bool {
		if ( ! preg_match( '/^\d{4}$/', $year ) ) {
			return false;
		}

		$year = (int) $year;

		return $year >= $min && $year <= $max;
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-04-test.txt' : '/data/day-04.txt';
		$passports = explode( "\n\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$data = [];

		foreach ( $passports as $passport ) {
			$lines = explode( "\n", $passport );
			$entry = [];

			foreach ( $lines as $line ) {
				$parts = explode( ' ', $line );

				foreach ( $parts as $part ) {
					$pair = explode( ':', $part );
					$entry[ $pair[0] ] = $pair[1];
				}
			}

			$data[] = $entry;
		}

		return $data;
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
	$day04  = new Day04( $test, $part );
	$result = $day04->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 6,
			'real' => 239,
		],
		2 => [
			'test' => 2,
			'real' => 188,
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
