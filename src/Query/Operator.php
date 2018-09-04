<?php
/**
 * Created by PhpStorm.
 * User: machavia
 * Date: 31/08/2018
 * Time: 11:29
 */

namespace SqlZero\Query;


class Operator {

	private static $operators = [ '=', '!=', '>', '<', '>=', '<=' ];

	public static function validate( string $operator ) : void {
		if( !in_array( $operator, self::$operators ) ) {
			throw new SqlZeroException('Invalid operator ' . $operator );
		}
	}

	public static function evaluate( $value1, $operator, $value2 ) : bool {

		switch( $operator ) {
			case '=' :
				return $value1 == $value2;
			case '!=' :
				return $value1 != $value2;
			case '>=' :
				return $value1 >= $value2;
			case '<=' :
				return $value1 <= $value2;
			case '>' :
				return $value1 > $value2;
			case '<' :
				return $value1 != $value2;
		}
	}
}