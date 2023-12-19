<?php

/**
 * Day 19:
 */
class Day19 {
	/**
	 * Workflows definition.
	 *
	 * @var array
	 */
	private array $workflows = [];

	/**
	 * Parts to be processed.
	 *
	 * @var array
	 */
	private array $parts = [];

	private array $ranges = [
		'x' => [ 'min' => 1, 'max' => 4000 ],
		'm' => [ 'min' => 1, 'max' => 4000 ],
		'a' => [ 'min' => 1, 'max' => 4000 ],
		's' => [ 'min' => 1, 'max' => 4000 ],
	];

	public function __construct(string $test, int $part ) {
		$this->parse_data($test);
		$this->part = $part;
	}

	/**
	 * Part 1: Process each part through the workflows. What do you get if you add together all of the rating numbers
	 *         for all of the parts that ultimately get accepted?
	 *
	 * @return int The sum of ratings for accepted parts.
	 */
	public function part_1(): int {
		$total = 0;

		foreach ( $this->parts as $part ) {
			$workflow = 'in';
			while ( $workflow !== 'A' && $workflow !== 'R' ) {
				foreach ( $this->workflows[ $workflow ] as $rule ) {
					$pass = true;

					// If the rule contains < or >
					if ( array_intersect( [ '<', '>' ], $rule ) ) {
						[ $rating, $op, $number, $destination ] = $rule;
						$pass = $op === '>' ? $part[ $rating ] > $number : $part[ $rating ] < $number;
					} else {
						$destination = $rule[0];
					}

					if ( $pass ) {
						if ( $destination === 'A' ) {
							$total += array_sum( $part );
						}
						$workflow = $destination;
						break;
					}
				}
			}
		}

		return $total;
	}

	/**
	 * Part 2: How many distinct combinations of ratings will be accepted?
	 *
	 * @return int The total count of accepted configurations.
	 */
	public function part_2(): int {
		return $this->calculate_total( $this->ranges );
	}

	/**
	 * Recursive function to calculate the total number of accepted configurations.
	 *
	 * @param array  $ranges Current ranges for each rating
	 * @param string $label  Current workflow label
	 *
	 * @return int Total count of accepted configurations
	 */
	private function calculate_total( array $ranges, string $label = 'in' ): int {
		$total = 0;

		if ( 'R' === $label ) {
			return 0;
		} elseif ( 'A' === $label ) {
			$total = 1;
			foreach ( $ranges as $rating => $range ) {
				$total *= $range['max'] - $range['min'] + 1;
			}

			return $total;
		}

		// Iterate through each rule in the current workflow
		foreach ( $this->workflows[ $label ] as $rule ) {
			$new_ranges = $ranges;

			// Check if the rule contains comparison operators (< or >)
			if ( array_intersect( [ '<', '>' ], $rule ) ) {
				[ $rating, $symbol, $number, $destination ] = $rule;

				// Update ranges based on the symbol
				if ( '>' === $symbol ) {
					$new_ranges[ $rating ]['min'] = max( $new_ranges[ $rating ]['min'], $number + 1 );
					$ranges[ $rating ]['max']     = $new_ranges[ $rating ]['min'] - 1;
				} else {
					$new_ranges[ $rating ]['max'] = min( $new_ranges[ $rating ]['max'], $number - 1 );
					$ranges[ $rating ]['min']     = $new_ranges[ $rating ]['max'] + 1;
				}
			} else {
				$destination = $rule[0];
			}

			// Calculate the total for the new ranges and add to the total
			$total += $this->calculate_total( $new_ranges, $destination );
		}

		return $total;
	}

	/**
	 * Parses the input data into workflows and parts.
	 *
	 * @param string $test File path for the data.
	 */
	private function parse_data(string $test): void {
		$file = $test ? '/data/day-19-test.txt' : '/data/day-19.txt';
		[$workflowData, $partsData] = explode("\n\n", trim(file_get_contents(__DIR__ . $file)));

		// Parse workflows
		foreach (explode("\n", $workflowData) as $line) {
			[$id, $rules] = preg_split("/[{}]/", $line, -1, PREG_SPLIT_NO_EMPTY);
			$rules = explode(",", $rules);
			$rules = array_map(fn($a) => preg_split("/(?:([<>])|[:])/", $a, 3, PREG_SPLIT_DELIM_CAPTURE), $rules);
			$this->workflows[$id] = $rules;
		}

		// Parse parts
		foreach (explode("\n", $partsData) as $line) {
			$part = explode(",", trim($line, "{}"));
			$part = array_map(fn($a) => explode("=", $a)[1], $part);
			$part = array_combine(['x', 'm', 'a', 's'], $part);
			$this->parts[] = $part;
		}
	}
}

// Prompt which part to run and if it should use the test data.
while ( true ) {
	$part = trim( readline( 'Which part do you want to run? (1/2)' ) );
	if ( function_exists( "part_$part" ) ) {
		while ( true ) {
			$test = trim( strtolower( readline( 'Do you want to run the test? (y/n)' ) ) );
			if ( in_array( $test, array( 'y', 'n' ), true ) ) {
				$test = 'y' === $test;
				call_user_func( "part_$part", $test );
				break;
			}
			echo 'Please enter y or n' . PHP_EOL;
		}
		break;
	}
	echo 'Please enter 1 or 2' . PHP_EOL;
}

function part_1( $test = false ) {
	$start    = microtime( true );
	$day19    = new Day19( $test, 1 );
	$result   = $day19->part_1();
	$end      = microtime( true );
	$expected = $test ? 19114 : 377025;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}

function part_2( $test = false ) {
	$start    = microtime( true );
	$day19    = new Day19( $test, 2 );
	$result   = $day19->part_2();
	$end      = microtime( true );
	$expected = $test ? 167409079868000 : 135506683246673;

	printf( 'Total:    %s' . PHP_EOL, $result );
	printf( 'Expected: %s' . PHP_EOL, $expected );
	printf( 'Time:     %s seconds' . PHP_EOL, round( $end - $start, 4 ) );
}
