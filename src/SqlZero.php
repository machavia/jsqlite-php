<?php
/**
 * Created by PhpStorm.
 * User: machavia
 * Date: 29/08/2018
 * Time: 15:20
 */
declare(strict_types=1);
namespace SqlZero;

final class SqlZero {


	protected $dbHandle;
	protected $storage;

	/**
	 * SqlZero constructor.
	 * @param array $config
	 */
	public function __construct( array $config ) {
		$this->storage = new Storage( $config );
	}

	public function exist() : bool {
		if( empty( $this->storage->structure ) ) return true;

		return true;
	}


	public function table( string $name ) : Table {
		return new Table( $name, $this->storage );
	}

	public function insert( string $table, array $data ) {
		$t = new Table( $table, $this->storage );
		return $t->insert( $data );
	}

	public function delete( string $table ) {
		$t = new Table( $table );
		return $t->delete();
	}

	public function find( string $table, $primary ) {
		$t = new Table( $table );
		return $t->get( $primary );
	}




}