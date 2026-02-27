<?php

namespace AdventOfCode\Year2018;

require_once __DIR__ . '/opcodeexecutor.php';

/**
 * Day 16: Chronal Classification
 */
class Day16 {
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
	 * Part 1: Count samples that behave like 3 or more opcodes
	 *
	 * @return integer
	 */
	private function solve_part_1(): int {
		$samples = $this->data['samples'];
		$count   = 0;

		foreach ( $samples as $sample ) {
			$matching_opcodes = 0;

			// Test each opcode
			$opcodes = [
				'addr', 'addi',
				'mulr', 'muli',
				'banr', 'bani',
				'borr', 'bori',
				'setr', 'seti',
				'gtir', 'gtri', 'gtrr',
				'eqir', 'eqri', 'eqrr',
			];

			foreach ( $opcodes as $opcode ) {
				if ( $this->test_opcode( $sample, $opcode ) ) {
					$matching_opcodes++;
				}
			}

			if ( $matching_opcodes >= 3 ) {
				$count++;
			}
		}

		return $count;
	}

	/**
	 * Part 2: Determine opcode mapping and execute test program
	 *
	 * @return integer Value in register 0 after executing the test program
	 */
	private function solve_part_2(): int {
		$samples = $this->data['samples'];
		$program = $this->data['program'];

		// Build possibilities map from samples
		$possibilities = $this->build_opcode_possibilities( $samples );

		// Deduce the final mapping using constraint satisfaction
		try {
			$mapping = $this->deduce_opcode_mapping( $possibilities );
		} catch ( \RuntimeException $e ) {
			// Test data may not have enough samples to determine all mappings
			if ( $this->is_test ) {
				return 0;
			}
			throw $e;
		}

		// Execute the test program
		return $this->execute_program( $program, $mapping );
	}

	/**
	 * Tests if an opcode produces the expected result for a sample.
	 *
	 * @param array  $sample  Sample data with 'before', 'instruction', and 'after'.
	 * @param string $opcode  Name of the opcode to test.
	 *
	 * @return bool True if the opcode produces the expected result.
	 */
	private function test_opcode( array $sample, string $opcode ): bool {
		// Clone the before registers
		$registers   = $sample['before'];
		$instruction = $sample['instruction'];
		$opcode_num  = $instruction[0];
		$a           = $instruction[1];
		$b           = $instruction[2];
		$c           = $instruction[3];

		// Apply the opcode
		OpcodeExecutor::apply_opcode( $registers, $opcode, $a, $b, $c );

		// Compare with expected result
		return $registers === $sample['after'];
	}

	/**
	 * Builds the initial possibilities map from samples.
	 *
	 * @param array $samples Array of sample data.
	 *
	 * @return array Array mapping opcode numbers (0-15) to arrays of possible opcode names.
	 */
	private function build_opcode_possibilities( array $samples ): array {
		// Initialize: all 16 opcodes are possible for each opcode number
		$all_opcodes = [
			'addr', 'addi',
			'mulr', 'muli',
			'banr', 'bani',
			'borr', 'bori',
			'setr', 'seti',
			'gtir', 'gtri', 'gtrr',
			'eqir', 'eqri', 'eqrr',
		];

		$possibilities = [];
		for ( $i = 0; $i < 16; $i++ ) {
			$possibilities[ $i ] = $all_opcodes;
		}

		// For each sample, find matching opcodes and intersect with possibilities
		foreach ( $samples as $sample ) {
			$opcode_num       = $sample['instruction'][0];
			$matching_opcodes = [];

			// Find which opcodes match this sample
			foreach ( $all_opcodes as $opcode ) {
				if ( $this->test_opcode( $sample, $opcode ) ) {
					$matching_opcodes[] = $opcode;
				}
			}

			// Intersect with current possibilities for this opcode number
			$possibilities[ $opcode_num ] = array_intersect(
				$possibilities[ $opcode_num ],
				$matching_opcodes
			);
		}

		return $possibilities;
	}

	/**
	 * Deduces the final opcode mapping using constraint satisfaction.
	 *
	 * @param array $possibilities Initial possibilities map.
	 *
	 * @return array Mapping from opcode numbers to opcode names.
	 */
	private function deduce_opcode_mapping( array $possibilities ): array {
		$mapping = [];

		// Continue until all 16 opcodes are determined
		while ( count( $mapping ) < 16 ) {
			$found_new = false;

			// Find opcode numbers with exactly one possibility
			foreach ( $possibilities as $opcode_num => $possible_opcodes ) {
				if ( isset( $mapping[ $opcode_num ] ) ) {
					continue; // Already determined
				}

				if ( count( $possible_opcodes ) === 1 ) {
					$opcode_name            = reset( $possible_opcodes );
					$mapping[ $opcode_num ] = $opcode_name;
					$found_new              = true;

					// Remove this opcode from all other possibilities
					foreach ( $possibilities as $other_num => &$other_possibilities ) {
						if ( $other_num !== $opcode_num ) {
							$other_possibilities = array_values(
								array_filter(
									$other_possibilities,
									function( $op ) use ( $opcode_name ) {
										return $op !== $opcode_name;
									}
								)
							);
						}
					}
					unset( $other_possibilities );
				}
			}

			// If no progress was made, check if we can continue
			if ( ! $found_new ) {
				// Check if we have any undetermined opcodes
				$undetermined = array_filter(
					$possibilities,
					function( $poss, $num ) use ( $mapping ) {
						return ! isset( $mapping[ $num ] ) && count( $poss ) > 0;
					},
					ARRAY_FILTER_USE_BOTH
				);

				if ( ! empty( $undetermined ) ) {
					throw new \RuntimeException( 'Unable to determine all opcode mappings' );
				}
			}
		}

		return $mapping;
	}

	/**
	 * Executes the test program using the determined opcode mapping.
	 *
	 * @param array $program Array of instructions.
	 * @param array $mapping Mapping from opcode numbers to opcode names.
	 *
	 * @return int Value in register 0 after execution.
	 */
	private function execute_program( array $program, array $mapping ): int {
		$registers = [ 0, 0, 0, 0 ];

		foreach ( $program as $instruction ) {
			$opcode_num = $instruction[0];
			$a          = $instruction[1];
			$b          = $instruction[2];
			$c          = $instruction[3];

			// Get the opcode name from the mapping
			if ( ! isset( $mapping[ $opcode_num ] ) ) {
				throw new \RuntimeException( "Unknown opcode number: $opcode_num" );
			}

			$opcode_name = $mapping[ $opcode_num ];

			// Apply the opcode
			OpcodeExecutor::apply_opcode( $registers, $opcode_name, $a, $b, $c );
		}

		return $registers[0];
	}

	/**
	 * Parses the puzzle input data.
	 *
	 * @param bool $test Whether test data should be used.
	 *
	 * @return array Array with 'samples' and 'program' keys.
	 */
	private function parse_data( bool $test ): array {
		$file  = $test ? '/data/day-16-test.txt' : '/data/day-16.txt';
		$lines = explode( "\n", trim( file_get_contents( __DIR__ . $file ) ) );

		$samples    = [];
		$program    = [];
		$i          = 0;
		$in_program = false;

		// Parse samples until we hit two consecutive blank lines
		while ( $i < count( $lines ) ) {
			$line = $lines[ $i ];

			// Check for two consecutive blank lines (end of samples, start of program)
			if ( empty( $line ) && $i + 1 < count( $lines ) && empty( $lines[ $i + 1 ] ) ) {
				$i         += 2; // Skip both blank lines
				$in_program = true;
				continue;
			}

			if ( $in_program ) {
				// Parse test program instructions
				if ( ! empty( $line ) ) {
					$program[] = array_map( 'intval', explode( ' ', trim( $line ) ) );
				}
			} else {
				// Parse sample: Before, instruction, After
				if ( preg_match( '/^Before: \[(\d+), (\d+), (\d+), (\d+)\]$/', $line, $matches ) ) {
					$before = [
						(int) $matches[1],
						(int) $matches[2],
						(int) $matches[3],
						(int) $matches[4],
					];

					// Next line is the instruction
					$i++;
					if ( $i < count( $lines ) ) {
						$instruction = array_map( 'intval', explode( ' ', trim( $lines[ $i ] ) ) );

						// Next line should be After
						$i++;
						if ( $i < count( $lines ) && preg_match( '/^After:  \[(\d+), (\d+), (\d+), (\d+)\]$/', $lines[ $i ], $after_matches ) ) {
							$after = [
								(int) $after_matches[1],
								(int) $after_matches[2],
								(int) $after_matches[3],
								(int) $after_matches[4],
							];

							$samples[] = [
								'before'      => $before,
								'instruction' => $instruction,
								'after'       => $after,
							];
						}
					}
				}
			}

			$i++;
		}

		return [
			'samples' => $samples,
			'program' => $program,
		];
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
	$day16  = new Day16( $test, $part );
	$result = $day16->run();
	$end    = microtime( true );

	// Define expected results for validation
	$expected_values = [
		1 => [
			'test' => 3,
			'real' => 580,
		],
		2 => [
			'test' => 0,
			'real' => 537,
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
