<?php
declare(strict_types=1);
/**
 * Created by PhpStorm.
 * User: machavia
 * Date: 04/09/2018
 * Time: 12:41
 */


use PHPUnit\Framework\TestCase;
use SqlZero\SqlZero;

final class SqlZeroTest extends TestCase {

	private $s;
	private $path;

	public function setUp()
	{
		$this->path =  __DIR__ . '/../testDb.json';

		$config = [
			'path' => $this->path
		];
		$sqlZero = new SqlZero( $config );
		$this->s = $sqlZero;
	}

	public function tearDown() {
		unlink( $this->path );
	}

	public function testInit() {

		$db = $this->readDb();

		$mustBe = [
			'data' => [],
			'structure' => []
		];

		$this->assertSame($mustBe, $db);
	}

	/**
	 * @depends testInit
	 */
	public function testCreateTable() {

		$t = $this->s->table( 'my_table' );
		$t->addField( 'id', 'integer', [0,10], 'primary' );
		$t->addField( 'mail', 'char', 16, 'unique' );
		$t->addField( 'date', 'timestamp' );
		$t->addField( 'first_name', 'text' );
		$t->addField( 'last_name', 'text' );
		$t->addField( 'gender', 'char', 1 );
		$t->save();

		$db = $this->readDb();
		$this->assertArrayHasKey('my_table', $db['structure']);

		$table = [
			'fields' => [
				[
					'name' => 'id',
					'type' => 'integer',
					'value' => [ 0, 10],
					'index' => 'primary',
					'ai' => 0,

				],
				[
					'name' => 'mail',
					'type' => 'char',
					'value' => 16,
					'index' => 'unique',

				],
				[
					'name' => 'date',
					'type' => 'timestamp',
					'value' => '',
					'index' => '',

				],
				[
					'name' => 'first_name',
					'type' => 'text',
					'value' => '',
					'index' => '',

				],
				[
					'name' => 'last_name',
					'type' => 'text',
					'value' => '',
					'index' => '',

				],
				[
					'name' => 'gender',
					'type' => 'char',
					'value' => 1,
					'index' => '',

				],
			]
		];

		$this->assertSame($table, $db['structure']['my_table']);

	}

	/**
	 * @expectedException \SqlZero\SqlZeroException
	 * @dataProvider unauthorizedFields
	 */
	public function testTextFieldAsUnique( $type, $value, $index ) {

		$t = $this->s->table( 'my_table' );
		$t->addField( 'foo', $type, $value, $index );
	}

	public function unauthorizedFields() {
		return [
			['text', '', 'unique'],
			['integer', '', ''],
			['integer', [1], ''],
			['decimal', [10,2], 'primary'],
		];
	}

	private function readDb() : array {
		$result = file_get_contents( $this->path );
		$result = json_decode( $result, true );

		return $result;
	}
}

