<?php
/**
 * Created by PhpStorm.
 * User: machavia
 * Date: 31/08/2018
 * Time: 11:24
 */

namespace SqlZero\Query;


class Query {

	private $where;
	private $select;
	private $groupBy;

	public function __construct() {

	}

	public function add( $ob, string $type = '' ): void {

		if ($ob instanceof Where ) {
			if( $type == 'or' ) {
				$this->where[] = [ 'type' => 'or', 'where' => $ob ];
			}
			else {
				$this->where[] = [ 'type' => 'and', 'where' => $ob ];
			}
		}
		else {
			throw new SqlZeroException( 'Unknown query type' );
		}
	}

	private function buid() {
		$evaluator = function( $row ) {

		};
	}

	public function exec( array $data ) : array {

		$rowIds = [];

		foreach( $data as $rowId => $row ) {
			$rowResult = true;

			foreach( $this->where as $where ) {
				$result = Operator::evaluate( $row[$where['where']->field], $where['where']->operator, $where['where']->value );
				if( !$result ) $rowResult = false;
			}

			if( $rowResult === true ) $rowIds[] = $rowId;
		}

		return $rowIds;
	}
}