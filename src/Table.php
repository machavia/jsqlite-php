<?php
/**
 * Created by PhpStorm.
 * User: machavia
 * Date: 29/08/2018
 * Time: 16:00
 */

namespace SqlZero;


use SqlZero\Query\Select;
use SqlZero\Query\Where;

class Table {

	/** @var string $name */
	public $name;

	private $storage;
	private $fields = [];

	private $uniques = [];
	private $primary = false;


	private $query;


	public function __construct( string $name, Storage $storage ) {
		if( empty( $name ) ) throw new SqlZeroException( 'Table name empty');
		$this->name = $name;
		$this->storage = $storage;
		$this->load();
		$this->query = new Query\Query();
	}

	public function addField( string $name, string $type, $lengthOrValue = '', string $indexType = '' ) {
		Structure::validateField( $type, $lengthOrValue, $indexType );
		if( $indexType == 'primary' && $this->primary != false) {
			throw new SqlZeroException( 'Table ' . $this->name . ' already has a primary field' );
		}


		if( in_array( $name, array_column($this->fields, 'name') ) ) {
			throw new SqlZeroException( 'Field ' . $name . ' already exist in table ' . $this->name );
		}

		$fields = [
			'name' => $name,
			'type' => $type,
			'value' => $lengthOrValue,
			'index' => $indexType
		];

		if( $indexType == 'primary' ) {
			$fields['ai'] = 0;
			$this->primary = $name;
		}
		else if( $indexType == 'unique' ) {
			$this->uniques[] = $name;
		}

		$this->fields[] = $fields;

		return $this;
	}

	private function load() : void {
		$table = $this->storage->getTable( $this->name );
		$this->fields = isset( $table['fields'] ) ? $table['fields'] : [];

		foreach( $this->fields as $field ) {
			if( $field['index'] == 'primary' ) {
				$this->primary = $field['name'];
			}

			if( $field['index'] == 'unique' ) $this->uniques[] = $field['name'];
		}
	}

	public function save() : void {
		$this->storage->structure[$this->name]['fields'] = $this->fields;
		$this->storage->updateStructure();
	}

	public function insert( array $data ) {

		$cleanData = [];
		$ai = false;

		//we want to set the primary first because we want it at the start of the array
		if( $this->primary != false ) {
			$cleanData[$this->primary] = $this->storage->increaseAutoIncrement( $this->name );
			$ai = $cleanData[$this->primary];
		}

		foreach( $data as $k => $v ) {

			$fieldKey = array_search($k, array_column($this->fields, 'name'));

			//if the field does not exist in the table
			if( $fieldKey === false ) {
				throw new SqlZeroException( 'Unknown field ' . $k );
			}

			//if the field is autoincrement we don't take care of the user value we'll override it later
			if($this->fields[$fieldKey]['index'] == 'primary') {
				continue;
			}
			else if($this->fields[$fieldKey]['index'] == 'unique') {
				$exist = $this->storage->checkValueExistence( $this->name, $k, $v );
				if( $exist ) throw new SqlZeroException( $v . ' already exist in this table' );
			}

			Structure::validateValue($this->fields[$fieldKey], $v );

			$cleanData[$k] = $v;
		}

		if( empty( $cleanData ) ) {
			throw new SqlZeroException( 'No data to insert' );
		}

		$this->storage->insert( $this->name, $cleanData );

		return $ai;
	}

	public function delete() {
		$deletedRows = $this->storage->delete( $this->name, $this->query );
		$this->query = new Query\Query();

		return $deletedRows;
	}

	public function update( array $data ) : int {
		$updatedRows = $this->storage->update( $this->name, $data, $this->query );
		$this->query = new Query\Query();

		return $updatedRows;
	}

	public function where( string $field, string $operatorOrValue, $value = false ) : Table {
		$ob = new Where( $field, $operatorOrValue, $value  );
		$this->query->add( $ob, 'and' );
		return $this;
	}

	public function orWhere( string $field, string $operatorOrValue, $value = false ) : Table {
		$ob = new Where( $field, $operatorOrValue, $value  );
		$this->query->add( $ob, 'or' );
		return $this;
	}

	public function select( $fields ) : Table {
		if( !is_array( $fields ) ) $fields = [ $fields ];

		foreach( $fields as $field ) {
			$ob = new Select($field);
			$this->query->add($ob);
		}

		return $this;
	}

	public function fetchAll() : array {
		$result = $this->storage->get( $this->name, $this->query );
		$this->query = new Query\Query();
		return $result;
	}

	public function fetchRow() : array {
		$result = $this->storage->get( $this->name, $this->query );
		$this->query = new Query\Query();
		return isset( $result[0] ) ?  $result[0] : [];
	}

	public function fetchPairs() : array {
		$result = $this->storage->get( $this->name, $this->query );
		$this->query = new Query\Query();
		if( empty( $result ) ) return [];

		if( count( $result[0] ) != 2 ) throw new SqlZeroException('You must select only 2 fields to use FetchPairs');
		$pairs = [];

		foreach( $result as $row ) {
			list( $key, $value ) = array_values($row);
			$pairs[$key] = $value;
		}

		return $pairs;
	}

	public function fetchCol() : array {
		$result = $this->storage->get( $this->name, $this->query );
		$this->query = new Query\Query();
		if( empty( $result ) ) return [];

		if( count( $result[0] ) != 1 ) throw new SqlZeroException('You must select only 1 fields to use FetchCol');
		$pairs = [];

		foreach( $result as $row ) {
			list( $value ) = array_values($row);
			$pairs[] = $value;
		}

		return $pairs;
	}

	public function fetchOne() : string {
		$result = $this->storage->get( $this->name, $this->query );
		$this->query = new Query\Query();
		if( empty( $result ) ) return [];

		if( count( $result[0] ) != 1 ) throw new SqlZeroException('You must select only 1 fields to use FetchOne');
		$v = array_values($result[0]);

		return $v[0];
	}

	public function get( $primary ) : array {

		if( !$this->primary ) throw new SqlZeroException('No primary field declared for this table');

		$ob = new Where( $this->primary, '=', $primary );
		$this->query->add( $ob, 'and' );
		$result = $this->storage->get( $this->name, $this->query );
		$this->query = new Query\Query();

		if( count( $result ) != 1 ) {
			throw new SqlZeroException('Data corruption: multiple result for the primary field: ' . $this->primary .  " ($primary)");
		}

		return $result[0];
	}


}