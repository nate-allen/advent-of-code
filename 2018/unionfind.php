<?php

namespace AdventOfCode\Year2018;

/**
 * Union-Find data structure for efficient set operations.
 */
class UnionFind {
	/**
	 * Parent array for union-find structure.
	 *
	 * @var array
	 */
	private array $parent;

	/**
	 * Rank array for union by rank optimization.
	 *
	 * @var array
	 */
	private array $rank;

	/**
	 * Number of distinct sets.
	 *
	 * @var int
	 */
	private int $num_sets;

	/**
	 * Initialize Union-Find with n sets.
	 *
	 * @param int $n Number of elements.
	 */
	public function __construct( int $n ) {
		$this->parent   = range( 0, $n - 1 );
		$this->rank     = array_fill( 0, $n, 0 );
		$this->num_sets = $n;
	}

	/**
	 * Find the root of element x with path compression.
	 *
	 * @param int $x Element to find.
	 * 
	 * @return int Root of the element.
	 */
	public function find( int $x ): int {
		if ( $this->parent[ $x ] !== $x ) {
			$this->parent[ $x ] = $this->find( $this->parent[ $x ] );
		}
		return $this->parent[ $x ];
	}

	/**
	 * Union two sets by rank.
	 *
	 * @param int $x First element.
	 * @param int $y Second element.
	 */
	public function union( int $x, int $y ): void {
		$x_root = $this->find( $x );
		$y_root = $this->find( $y );

		if ( $x_root === $y_root ) {
			return;
		}

		if ( $this->rank[ $x_root ] < $this->rank[ $y_root ] ) {
			$this->parent[ $x_root ] = $y_root;
		} elseif ( $this->rank[ $x_root ] > $this->rank[ $y_root ] ) {
			$this->parent[ $y_root ] = $x_root;
		} else {
			$this->parent[ $y_root ] = $x_root;
			$this->rank[ $x_root ]++;
		}

		$this->num_sets--;
	}

	/**
	 * Count the number of distinct sets.
	 *
	 * @return int Number of distinct sets.
	 */
	public function count_sets(): int {
		return $this->num_sets;
	}
}
