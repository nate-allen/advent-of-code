import {init} from 'z3-solver';
import {readFile} from 'fs/promises';

export async function part2(input) {
	let hail_stones = [];
	const lines     = input.split( '\n' );
	for (let line of lines) {
		let [point, velocity] = line.split( ' @ ' );

		point    = point.split( ', ' ).map( n => +n );
		velocity = velocity.split( ', ' ).map( n => +n );

		hail_stones.push( { point, velocity } );
	}

	const {Context}     = await init();
	const {Solver, Int} = Context( 'main' );
	const solver        = new Solver();

	const r   = [Int.const( 'rx' ), Int.const( 'ry' ), Int.const( 'rz' )];
	const rv  = [Int.const( 'rvx' ), Int.const( 'rvy' ), Int.const( 'rvz' )];
	const num = hail_stones.length;

	for ( let i = 0; i < num; i++ ) {
		let {point, velocity} = hail_stones[i];

		const t = Int.const( `t${i}` );

		solver.add( t.ge( 0 ) );

		for (let j = 0; j < 3; j++) {
			solver.add( t.mul( velocity[j] ).add( point[j] ).eq( t.mul( rv[j] ).add( r[j] ) ) );
		}
	}

	if ( ( await solver.check() ) === 'sat' ) {
		const model  = solver.model();
		const result = model.eval( r[0].add( r[1] ).add( r[2] ) ).toString();

		return +result;
	}
}

async function process_input() {
	try {
		const data   = await readFile( 'data/day-24.txt', 'utf8' );
		const result = await part2( data );
		console.log( 'Total:', result );
		process.exit( 0 );
	} catch (err) {
		console.error( 'Error:', err );
		process.exit( 1 );
	}
}

process_input();
