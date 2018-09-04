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
		'text' => '',
		'enum' => [],
		'timestamp' => '',
		'date' => 'string',
	];

	static private $indexes = [ 'primary', 'unique'];

	public static function validateField( string $type, $value, string $index ) : void {

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

		if( !empty( $index ) ) {
			if( $index == 'primary' ) {
				if( $type != 'integer' ) {
					throw new SqlZeroException( 'Primary fields like auto-increments in Mysql, they must be in interger type');
				}
			} else if( $index == 'unique' ) {
				if( $type == 'text' ) throw new SqlZeroException( 'Text fields can not be unique');
			} else {
				throw new SqlZeroException( 'Unsupported index type ' . $index );
			}
		}
	}

	public static function validateValue( array $field, $value ) {
		#TODO : $field need to be an object field and not a simple array

		if( $field['type'] == 'integer' ) {

			if( !is_numeric( $field['type'] ) ) {
				throw new SqlZeroException($field['name'] . ' field value must be an integer (' . $value . ')');
			}

			if( $value < $field['value'][0] || $value > $field['value'][1] ) {
				throw new SqlZeroException($field['name'] . ' field value must be between ' . $field['value'][0] . ' and ' . $field['value'][1]);
			}
		}
		else {
			throw new SqlZeroException( 'TO DO !');
		}
	}

}