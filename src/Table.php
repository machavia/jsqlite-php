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

	public function __construct( string $name, Storage $storage ) {
		if( empty( $name ) ) throw new SqlZeroException( 'Table name empty');
		$this->name = $name;
		$this->storage = $storage;
	}

	public function addField( string $name, string $type, $lengthOrValue, string $indexType ) {
		Structure::validateFieldType( $type, $lengthOrValue );
		if( empty( $this->fields ) ) $this->load();

		if( in_array( $name, array_column($this->fields, 'name') ) ) {
			throw new SqlZeroException( 'Field ' . $name . ' already exist in table ' . $this->name );
		}

		$field = [
			'name' => $name,
			'type' => $type,
			'value' => $lengthOrValue,
			'index' => $indexType
		];

		$this->fields[] = $field;

		return $this;
	}

	private function load() {
		$table = $this->storage->getTable( $this->name );
		$this->fields = isset( $table['fields'] ) ? $table['fields'] : [];
	}

	public function save() {
		$this->storage->structure[$this->name]['fields'] = $this->fields;
		$this->storage->updateStructure();
	}


}