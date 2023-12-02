<?php
/**
 * Day 11: Monkey in the Middle
 */

/**
 * Part 1: Divide the result by 3. What is the level of monkey business after 20 rounds?
 *
 * @return void
 */
function part_1() {
	$worry = function ( $result ) {
		return floor( $result / 3 );
	};

	$inspections = monkey_business( 20, $worry );

	echo array_product( $inspections ) . PHP_EOL;
}

/**
 * Part 2: Don't divide the result by 3. What is the level of monkey business after 10,000 rounds?
 *
 * @return void
 */
function part_2() {
	$monkeys     = get_monkeys();
	$supermodulo = array_product( array_column( $monkeys, 'test' ) );

	$worry = function( $result ) use ( $supermodulo ) {
		return $result % $supermodulo;
	};

	$inspections = monkey_business( 10000, $worry );

	echo array_product( $inspections ) . PHP_EOL;
}

/**
 * Monkey Business
 *
 * @param int      $rounds Number of rounds to run.
 * @param callable $worry  A function to apply to the result of each iteration.
 *
 * @return array
 */
function monkey_business( $rounds = 0, callable $worry ) {
	$monkeys     = get_monkeys();
	$inspections = array_fill( 0, count( $monkeys ), 0 );

	// For however many rounds:
	for ( $i = 0; $i < $rounds; $i++ ) {

		// For each monkey:
		foreach ( $monkeys as $monkey => $monkey_data ) {
			// If there are no items, continue;
			if ( empty( $monkeys[ $monkey ]['items'] ) ) {
				continue;
			}

			// Loop over the items this monkey has:
			foreach ( $monkeys[ $monkey ]['items'] as $item ) {
				$inspections[ $monkey ] ++;

				// Remove the item.
				array_shift( $monkeys[ $monkey ]['items'] );

				// Do the operation:
				$result = operation( $monkeys[ $monkey ]['operation'], $item );

				// How you deal with stress:
				$result = $worry( $result );

				if ( 0 === $result % $monkeys[ $monkey ]['test'] ) {
					// Add to another monkey's items array:
					$monkeys[ intval( $monkeys[ $monkey ]['if_true'] ) ]['items'][] = $result;
				} else {
					// Add to another monkey's items array:
					$monkeys[ intval( $monkeys[ $monkey ]['if_false'] ) ]['items'][] = $result;
				}
			}
		}
	}

	// Sort and then get the top 2 inspections:
	arsort( $inspections );
	return array_slice( $inspections, 0, 2 );
}

/**
 * Parses the input file and returns an array of monkeys.
 *
 * Format:
 *   'items' => Array( [0] => 82 )
 *   'operation' => "old + 7"
 *   'test' => 13
 *   'if_true' => 4
 *   'if_false' => 3
 *
 * @return array
 */
function get_monkeys() {
	$data    = file_get_contents( __DIR__ . '/data/day-11.txt' );
	$lines   = explode( "\n", $data );
	$monkeys = array();

	foreach ( $lines as $line ) {
		if ( 0 === strpos( $line, 'Monkey' ) ) {
			$monkeys[] = array();
		} elseif ( false !== strpos( $line, 'Starting items:' ) ) {
			$monkey          = array_pop( $monkeys );
			$items           = explode( ': ', $line )[1];
			$monkey['items'] = explode( ', ', $items );
			$monkeys[]       = $monkey;
		} elseif ( false !== strpos( $line, 'Operation:' ) ) {
			$monkey              = array_pop( $monkeys );
			$monkey['operation'] = explode( 'Operation: new = ', $line )[1];
			$monkeys[]           = $monkey;
		} elseif ( false !== strpos( $line, 'Test:' ) ) {
			$monkey         = array_pop( $monkeys );
			$monkey['test'] = explode( 'Test: divisible by ', $line )[1];
			$monkeys[]      = $monkey;
		} elseif ( false !== strpos( $line, 'If true:' ) ) {
			$monkey            = array_pop( $monkeys );
			$monkey['if_true'] = explode( 'If true: throw to monkey ', $line )[1];
			$monkeys[]         = $monkey;
		} elseif ( false !== strpos( $line, 'If false:' ) ) {
			$monkey             = array_pop( $monkeys );
			$monkey['if_false'] = explode( 'If false: throw to monkey ', $line )[1];
			$monkeys[]          = $monkey;
		}
	}

	return $monkeys;
}

/**
 * Handles the math operation instead of using eval().
 *
 * @param $operation
 * @param $item
 *
 * @return float|int
 */
function operation( $operation, $item ) {
	$math   = explode( ' ', trim( str_replace( 'old', $item, $operation ) ) );
	$symbol = $math[1];
	unset( $math[1] );

	return '*' === $symbol ? array_product( $math ) : array_sum( $math );
}
