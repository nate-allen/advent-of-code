<?php

namespace AdventOfCode\Year2019;

/**
 * Implements an Intcode computer to execute opcode-based programs.
 */
class IntcodeComputer {
	/**
	 * The memory of the Intcode computer.
	 *
	 * @var array<int, int>
	 */
	private array $memory;

	/**
	 * The instruction pointer.
	 *
	 * @var int
	 */
	private int $pointer = 0;

	/**
	 *The relative base for relative mode addressing.
	 *
	 * @var int
	 */
	private int $relative_base = 0;

	/**
	 * The input queue for the program.
	 *
	 * @var array<int>
	 */
	private array $inputs = [];

	/**
	 * Whether the program has halted.
	 *
	 * @var bool|null
	 */
	private bool $halted = false;

	/**
	 * Initializes the Intcode computer with a given program.
	 *
	 * @param array<int> $program The Intcode program to execute.
	 */
	public function __construct( array $program ) {
		$this->memory = array_pad( $program, 10000, 0 );
	}

	/**
	 * Adds an input to the input queue.
	 *
	 * @param int $value The input value.
	 */
	public function add_input( int $value ): void {
		$this->inputs[] = $value;
	}

	/**
	 * Runs the program until an output is produced or the program halts.
	 *
	 * @return int|null The output value, or null if the program halts.
	 */
	public function run_until_output(): ?int {
		while (!$this->halted) {
			$instruction = str_pad((string) $this->memory[$this->pointer], 5, '0', STR_PAD_LEFT);
			$opcode = (int) substr($instruction, -2);
			$modes = [intval($instruction[2]), intval($instruction[1]), intval($instruction[0])];

			switch ($opcode) {
				case 1: // Addition
					$this->write(3, $this->get_value(1, $modes) + $this->get_value(2, $modes), $modes[2]);
					$this->pointer += 4;
					break;
				case 2: // Multiplication
					$this->write(3, $this->get_value(1, $modes) * $this->get_value(2, $modes), $modes[2]);
					$this->pointer += 4;
					break;
				case 3: // Input
					if (empty($this->inputs)) return null;
					$this->write(1, array_shift($this->inputs), $modes[0]);
					$this->pointer += 2;
					break;
				case 4: // Output
					$last_output   = null;
					$last_output   = $this->get_value(1, $modes);
					$this->pointer += 2;
					return $last_output;
				case 5: // Jump-if-true
					$this->pointer = ($this->get_value(1, $modes) != 0) ? $this->get_value(2, $modes) : $this->pointer + 3;
					break;
				case 6: // Jump-if-false
					$this->pointer = ($this->get_value(1, $modes) == 0) ? $this->get_value(2, $modes) : $this->pointer + 3;
					break;
				case 7: // Less than
					$this->write(3, $this->get_value(1, $modes) < $this->get_value(2, $modes) ? 1 : 0, $modes[2]);
					$this->pointer += 4;
					break;
				case 8: // Equals
					$this->write(3, $this->get_value(1, $modes) == $this->get_value(2, $modes) ? 1 : 0, $modes[2]);
					$this->pointer += 4;
					break;
				case 9: // Adjust relative base
					$this->relative_base += $this->get_value(1, $modes);
					$this->pointer += 2;
					break;
				case 99:
					$this->halted = true;
					return null;
				default:
					echo "Invalid opcode: $opcode" . PHP_EOL;
			}
		}
		return null;
	}

	/**
	 * Checks if the program has halted.
	 *
	 * @return bool
	 */
	public function has_halted(): bool {
		return $this->halted;
	}

	/**
	 * Retrieves the value stored at a specific memory address.
	 *
	 * @param int $address The memory address to fetch.
	 *
	 * @return int
	 */
	public function get_memory( int $address ): int {
		return $this->memory[ $address ] ?? 0;
	}

	/**
	 * Retrieves a value based on the parameter mode.
	 *
	 * @param int        $offset The parameter offset.
	 * @param array<int> $modes  The parameter modes.
	 *
	 * @return int The resolved value.
	 */
	private function get_value( int $offset, array $modes ): int {
		$mode  = $modes[ $offset - 1 ];
		$param = $this->memory[ $this->pointer + $offset ] ?? 0;

		return match ( $mode ) {
			0 => $this->memory[ $param ] ?? 0,
			1 => $param,
			2 => $this->memory[ $this->relative_base + $param ] ?? 0,
		};
	}

	/**
	 * Writes a value to the appropriate memory address based on the parameter mode.
	 *
	 * @param int $offset The parameter offset.
	 * @param int $value  The value to write.
	 * @param int $mode   The parameter mode.
	 */
	private function write( int $offset, int $value, int $mode ): void {
		$address  = ( $mode == 2 ) ? $this->relative_base + ( $this->memory[ $this->pointer + $offset ] ?? 0 ) : ( $this->memory[ $this->pointer + $offset ] ?? 0 );

		$this->memory[ $address ] = $value;
	}
}
