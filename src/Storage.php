<?php
/**
 * Created by PhpStorm.
 * User: machavia
 * Date: 29/08/2018
 * Time: 11:04
 */

namespace SqlZero;


use SqlZero\Query\Query;

class Storage {

	private $dbPath = false;
	private $compression = false;
	private $checkIntegrityAtStartup = false;

	private $data = [];
	public $structure = [];

	public function __construct( array $config ) {
		$this->loadConfig( $config );

	}

	private function read( ) {
		$content = json_decode( file_get_contents( $this->dbPath ), true );
		$this->data = $content['data'];
		$this->structure = $content['structure'];
	}

	private function write() {

		$structure = [
			'data' => $this->data,
			'structure' => $this->structure
		];

		file_put_contents( $this->dbPath, json_encode( $structure ) );
	}

	public function getTable( string $name ) : array {
		if( isset( $this->structure[$name] ) ) return $this->structure[$name];

		return [];
	}

	public function updateStructure( ) {
		#TODO : check the user do not modify a table which contains data

		$this->write();
	}

	private function loadConfig( array $config ) {

		if( !isset( $config['path'], $config ) ) throw new SqlZeroException( 'You must specify a db path location' );

		if( isset( $config['compression'] ) ) $this->compression = true;
		if( isset( $config['checkIntegrityAtStartup'] ) ) $this->checkIntegrityAtStartup = true;

		$fileName = $config['path'];
		$this->dbPath  = $fileName;

		if ( file_exists($fileName) ) {
			$this->read();
			return;
		}

		$fp = fopen($fileName, "w");
		if ( !$fp ) {
			throw new SqlZeroException('Cannot create db file at given path: ' . $fileName );
		}

		$structure = [
			'data' => [],
			'structure' => []
		];

		fwrite($fp, json_encode( $structure ));
		fclose( $fp );

	}

	public function insert( string $table, array $data ) : void {

		if( !isset( $this->structure[$table])) {
			throw new SqlZeroException('Unknown table ' .  $table );
		}

		$this->data[$table][] =  $data;

		$this->write();
	}

	public function increaseAutoIncrement( string $tableName ) : int {
		$table = $this->structure[$tableName];
		$fieldKey = array_search('primary', array_column($table['fields'], 'index'));

		if( $fieldKey === false ) {
			throw new SqlZeroException('Table ' . $tableName . ' has no primary field for auto-increment');
		}

		$this->structure[$tableName]['fields'][$fieldKey]['ai']++;

		return $this->structure[$tableName]['fields'][$fieldKey]['ai'];
	}

	public function checkValueExistence( string $table, string $field, $value ) : bool {
		$data = $this->data[$table];
		$result = array_search($value, array_column($data, $field));

		if( $result !== false ) return true;

		return false;
	}

	public function update( string $table, array $data, Query $query ) : int {
		$i = 0;
		$dataToUpdate = $query->exec( $this->data[$table]  );
		foreach( $dataToUpdate as $rowId => $useless ) {
			$i++;
			foreach( $data as $key => $value ) {
				$this->data[$table][$rowId][$key] = $value;
			}
		}

		$this->write();

		return $i;
	}

	public function get( string $table, Query $query ) : array {
		$data = $query->exec( $this->data[$table] );
		return array_values($data);
	}
}