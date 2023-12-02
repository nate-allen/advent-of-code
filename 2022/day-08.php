<?php
/**
 * Day 8: Treetop Tree House
 */

/**
 * Part 1: How many trees are visible from outside the grid?
 *
 * @return void
 */
function part_1() {
	$trees = get_trees();

	$row_width     = count( $trees[0] );
	$column_height = count( $trees );
	$total         = 0;

	// Iterate through the rows and columns of the grid
	for ( $i = 0; $i < $column_height; $i ++ ) {
		for ( $j = 0; $j < $row_width; $j ++ ) {
			// If the current tree is at the edge of the grid, it is visible.
			if ( 0 === $i || 0 === $j || $row_width - 1 === $i || $column_height - 1 === $j ) {
				$total++;
			} else {
				$current_tree = $trees[ $i ][ $j ];

				$column = array_column( $trees, $j );
				$up     = max( array_slice( $column, 0, $i ) );
				$down   = max( array_slice( $column, $i + 1 ) );

				$row   = $trees[ $i ];
				$left  = max( array_slice( $row, 0, $j ) );
				$right = max( array_slice( $row, $j + 1 ) );

				// If the current tree is the tallest any each direction, it is visible.
				if ( $current_tree > $right || $current_tree > $left || $current_tree > $up || $current_tree > $down ) {
					$total ++;
				}
			}
		}
	}

	printf( 'Visible Trees: %s', $total . PHP_EOL );
}

/**
 * Part 2: What is the highest scenic score possible for any tree?
 *
 * @return void
 */
function part_2() {
	$trees = get_trees();

	$row_width     = count( $trees[0] );
	$column_height = count( $trees );
	$top_score     = 0;

	// Iterate through the rows and columns of the grid
	for ( $i = 0; $i < $column_height; $i ++ ) {
		for ( $j = 0; $j < $row_width; $j ++ ) {
			$current_tree = $trees[ $i ][ $j ];
			$column       = array_column( $trees, $j );
			$row          = $trees[ $i ];
			$score        = array( 0, 0, 0, 0 );
			$directions   = array(
				array_reverse( array_slice( $column, 0, $i ) ),
				array_slice( $column, $i + 1 ),
				array_reverse( array_slice( $row, 0, $j ) ),
				array_slice( $row, $j + 1 ),
			);

			// Go in each direction and get a score for each one.
			foreach ( $directions as $key => $direction ) {
				foreach ( $direction as $tree ) {
					$score[ $key ] ++;
					if ( $current_tree <= $tree ) {
						break;
					}
				}
			}

			// Keep the top score updated.
			$top_score = max( $top_score, array_product( $score ) );
		}
	}

	printf( 'Top Scenic Score: %s', $top_score . PHP_EOL );
}

/**
 * Parses the data and returns an array of trees.
 *
 * @return array
 */
function get_trees() {
	$data  = file_get_contents( __DIR__ . '/data/day-08.txt' );
	$trees = explode( "\n", $data );

	// Map over the array and convert each string to an array of integers.
	$trees = array_map( function( $tree ) {
		return array_map( 'intval', str_split( $tree ) );
	}, $trees );

	return $trees;
}