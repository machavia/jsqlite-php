<?php
/**
 * Created by PhpStorm.
 * User: machavia
 * Date: 29/08/2018
 * Time: 16:00
 */

namespace SqlZero;


class Structure {

	static private $validFieldsTypes = [
		'integer' => [ 'int', 'int'],
		'decimal'  => [ 'int', 'int'],
		'char' => 'int',
		'text' => null,
		'enum' => [],
		'timestamp' => null,
		'date' => 'string',
	];

	public static function validateFieldType( string $type, $value ) {

		if( !isset( self::$validFieldsTypes[$type] ) ) {
			throw new SqlZeroException( 'Invalid field type ' . $type );
		}

		if( is_array( self::$validFieldsTypes[$type] ) ) {
			if( empty( self::$validFieldsTypes[$type] ) ) return;

			if( count( $value ) != 2
				|| !is_numeric( $value[0] )
				|| !is_numeric( $value[1] )
			) {
				throw new SqlZeroException( 'Field type ' . $type . ' must have a minimum and a maximum value');
			}

		}
	}
}