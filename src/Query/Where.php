<?php
/**
 * Created by PhpStorm.
 * User: machavia
 * Date: 31/08/2018
 * Time: 11:22
 */

namespace SqlZero\Query;


class Where {

	public $field;
	public $operator;
	public $value;

	public function __construct( string $field, string $operatorOrValue, $value = false ) {
		$this->field = $field;
		$this->operator = $operatorOrValue;
		$this->value = $value;

		if( !$value ) {
			$this->operator = '=';
			$this->value = $operatorOrValue;
		}

		Operator::validate( $this->operator );
	}
}