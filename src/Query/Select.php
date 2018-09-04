<?php
/**
 * Created by PhpStorm.
 * User: machavia
 * Date: 03/09/2018
 * Time: 15:11
 */

namespace SqlZero\Query;


use SqlZero\SqlZeroException;

class Select {

	public $field;
	public $request;
	public $name;
	public $value;

	public function __construct( string $request ) {
		$this->getField( $request );
	}

	public function exec( array $data ) : array {
		if( !isset( $data[$this->field] ) ) {
			throw new SqlZeroException( 'Unknwon field ' . $this->field . ' in ' . $this->request );
		}

		$this->value = $data[$this->field];

		return array( $this->name, $this->value );
	}

	private function getValue( $value ) {

	}

	private function getField( string $request ) : void {

		if (strpos( $request, ' as ') !== false) {
			list($this->field, $this->name  ) = explode( ' as ', $request );
		}
		else {
			$this->field = $this->name = $this->request = $request;
		}
	}
}