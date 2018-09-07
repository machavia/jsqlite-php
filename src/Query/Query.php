<?php
/**
 * Created by PhpStorm.
 * User: machavia
 * Date: 31/08/2018
 * Time: 11:24
 */

namespace SqlZero\Query;


use SqlZero\SqlZeroException;

final class Query {

	private $where = [];
	private $select = [];
	private $groupBy = [];

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
		else if ( $ob instanceof Select ) {
			$this->select[] = $ob;
		}
		else {
			throw new SqlZeroException( 'Unknown query type' );
		}
	}

	public function exec( array $data ) : array {

		$rows = [];


		foreach( $data as $rowId => $row ) {
			$rowResult = true;
			$lastType = '';

			foreach( $this->where as $where ) {

				$result = Operator::evaluate( $row[$where['where']->field], $where['where']->operator, $where['where']->value );


				//in the case of an AND statement if the current result or the past result are not valid the row isn't valid
				if( $where['type'] == 'and' ) {
					if( $lastType == 'and' ) {
						$rowResult = ( $result && $rowResult );
					}
					else $rowResult = $result;
				}

				//in case of an OR statement we need to check if the past record AND the current one are invalid to ignore the row
				if( $where['type'] == 'or' ) {
					$rowResult = ( $rowResult || $result );
				}

				$lastType = $where['type'];
			}

			//if the row is valid we add its index to the return array
			if( $rowResult === true ) {
				$a = [];
				foreach( $this->select as $select ) {
					list($keyName, $value) = $select->exec( $row );
					$a[$keyName] = $value;
				}

				$rows[$rowId] = empty($this->select) ? $row : $a;
			}
		}

		return $rows;
	}
}