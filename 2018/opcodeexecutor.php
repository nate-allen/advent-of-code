<?php

namespace AdventOfCode\Year2018;

/**
 * Shared opcode executor for Advent of Code 2018 puzzles.
 *
 * Provides implementations of all 16 opcodes used in Day 16 and Day 19.
 */
class OpcodeExecutor {
	/**
	 * Applies an opcode to the registers.
	 *
	 * @param array  $registers Register array (modified in place).
	 * @param string $opcode    Opcode name.
	 * @param int    $a         Parameter A.
	 * @param int    $b         Parameter B.
	 * @param int    $c         Parameter C (register to write to).
	 *
	 * @return void
	 */
	public static function apply_opcode( array &$registers, string $opcode, int $a, int $b, int $c ): void {
		match ( $opcode ) {
			'addr' => self::addr( $registers, $a, $b, $c ),
			'addi' => self::addi( $registers, $a, $b, $c ),
			'mulr' => self::mulr( $registers, $a, $b, $c ),
			'muli' => self::muli( $registers, $a, $b, $c ),
			'banr' => self::banr( $registers, $a, $b, $c ),
			'bani' => self::bani( $registers, $a, $b, $c ),
			'borr' => self::borr( $registers, $a, $b, $c ),
			'bori' => self::bori( $registers, $a, $b, $c ),
			'setr' => self::setr( $registers, $a, $b, $c ),
			'seti' => self::seti( $registers, $a, $b, $c ),
			'gtir' => self::gtir( $registers, $a, $b, $c ),
			'gtri' => self::gtri( $registers, $a, $b, $c ),
			'gtrr' => self::gtrr( $registers, $a, $b, $c ),
			'eqir' => self::eqir( $registers, $a, $b, $c ),
			'eqri' => self::eqri( $registers, $a, $b, $c ),
			'eqrr' => self::eqrr( $registers, $a, $b, $c ),
			default => throw new \InvalidArgumentException( "Unknown opcode: $opcode" ),
		};
	}

	// Addition opcodes
	private static function addr( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = $registers[ $a ] + $registers[ $b ];
	}

	private static function addi( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = $registers[ $a ] + $b;
	}

	// Multiplication opcodes
	private static function mulr( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = $registers[ $a ] * $registers[ $b ];
	}

	private static function muli( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = $registers[ $a ] * $b;
	}

	// Bitwise AND opcodes
	private static function banr( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = $registers[ $a ] & $registers[ $b ];
	}

	private static function bani( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = $registers[ $a ] & $b;
	}

	// Bitwise OR opcodes
	private static function borr( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = $registers[ $a ] | $registers[ $b ];
	}

	private static function bori( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = $registers[ $a ] | $b;
	}

	// Assignment opcodes
	private static function setr( array &$registers, int $a, int $b, int $c ): void { // phpcs:ignore
		$registers[ $c ] = $registers[ $a ];
	}

	private static function seti( array &$registers, int $a, int $b, int $c ): void { // phpcs:ignore
		$registers[ $c ] = $a;	
	}

	// Greater-than opcodes
	private static function gtir( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = ($a > $registers[ $b ]) ? 1 : 0;
	}

	private static function gtri( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = ($registers[ $a ] > $b) ? 1 : 0;
	}

	private static function gtrr( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = ($registers[ $a ] > $registers[ $b ]) ? 1 : 0;
	}

	// Equality opcodes
	private static function eqir( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = ($a === $registers[ $b ]) ? 1 : 0;
	}

	private static function eqri( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = ($registers[ $a ] === $b) ? 1 : 0;
	}

	private static function eqrr( array &$registers, int $a, int $b, int $c ): void {
		$registers[ $c ] = ($registers[ $a ] === $registers[ $b ]) ? 1 : 0;
	}
}
