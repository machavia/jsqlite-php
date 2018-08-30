<?php
/**
 * Created by PhpStorm.
 * User: machavia
 * Date: 29/08/2018
 * Time: 16:00
 */

namespace SqlZero;


class Table {

	/** @var string $name */
	public $name;

	private $storage;
	private $fields = [];

	private $uniques = [];
	private $primary = false;


	public function __construct( string $name, Storage $storage ) {
		if( empty( $name ) ) throw new SqlZeroException( 'Table name empty');
		$this->name = $name;
		$this->storage = $storage;
		$this->load();
	}

	public function addField( string $name, string $type, $lengthOrValue, string $indexType = '' ) {
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


}