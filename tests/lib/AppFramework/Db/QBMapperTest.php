<?php
/**
 * SPDX-FileCopyrightText: 2019 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace Test\AppFramework\Db;

use OCP\AppFramework\Db\Entity;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\QueryBuilder\IExpressionBuilder;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

/**
 * @method bool getBoolProp()
 * @method void setBoolProp(bool $boolProp)
 * @method integer getIntProp()
 * @method void setIntProp(integer $intProp)
 * @method string getStringProp()
 * @method void setStringProp(string $stringProp)
 * @method bool getBooleanProp()
 * @method void setBooleanProp(bool $booleanProp)
 * @method integer getIntegerProp()
 * @method void setIntegerProp(integer $integerProp)
 */
class QBTestEntity extends Entity {
	protected $intProp;
	protected $boolProp;
	protected $stringProp;
	protected $integerProp;
	protected $booleanProp;
	protected $jsonProp;

	public function __construct() {
		$this->addType('intProp', 'int');
		$this->addType('boolProp', 'bool');
		$this->addType('stringProp', 'string');
		$this->addType('integerProp', 'integer');
		$this->addType('booleanProp', 'boolean');
		$this->addType('jsonProp', 'json');
	}
}

/**
 * Class QBTestMapper
 *
 * @package Test\AppFramework\Db
 */
class QBTestMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'table');
	}

	public function getParameterTypeForPropertyForTest(Entity $entity, string $property) {
		return parent::getParameterTypeForProperty($entity, $property);
	}
}

/**
 * Class QBMapperTest
 *
 * @package Test\AppFramework\Db
 */
class QBMapperTest extends \Test\TestCase {
	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject|IDBConnection
	 */
	protected $db;

	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject|IQueryBuilder
	 */
	protected $qb;

	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject|IExpressionBuilder
	 */
	protected $expr;

	/**
	 * @var \Test\AppFramework\Db\QBTestMapper
	 */
	protected $mapper;

	/**
	 * @throws \ReflectionException
	 */
	protected function setUp(): void {
		$this->db = $this->getMockBuilder(IDBConnection::class)
			->disableOriginalConstructor()
			->getMock();

		$this->qb = $this->getMockBuilder(IQueryBuilder::class)
			->disableOriginalConstructor()
			->getMock();

		$this->expr = $this->getMockBuilder(IExpressionBuilder::class)
			->disableOriginalConstructor()
			->getMock();


		$this->qb->method('expr')->willReturn($this->expr);
		$this->db->method('getQueryBuilder')->willReturn($this->qb);


		$this->mapper = new QBTestMapper($this->db);
	}

	
	public function testInsertEntityParameterTypeMapping(): void {
		$entity = new QBTestEntity();
		$entity->setIntProp(123);
		$entity->setBoolProp(true);
		$entity->setStringProp('string');
		$entity->setIntegerProp(456);
		$entity->setBooleanProp(false);

		$intParam = $this->qb->createNamedParameter('int_prop', IQueryBuilder::PARAM_INT);
		$boolParam = $this->qb->createNamedParameter('bool_prop', IQueryBuilder::PARAM_BOOL);
		$stringParam = $this->qb->createNamedParameter('string_prop', IQueryBuilder::PARAM_STR);
		$integerParam = $this->qb->createNamedParameter('integer_prop', IQueryBuilder::PARAM_INT);
		$booleanParam = $this->qb->createNamedParameter('boolean_prop', IQueryBuilder::PARAM_BOOL);

		$this->qb->expects($this->exactly(5))
			->method('createNamedParameter')
			->withConsecutive(
				[$this->equalTo(123), $this->equalTo(IQueryBuilder::PARAM_INT)],
				[$this->equalTo(true), $this->equalTo(IQueryBuilder::PARAM_BOOL)],
				[$this->equalTo('string'), $this->equalTo(IQueryBuilder::PARAM_STR)],
				[$this->equalTo(456), $this->equalTo(IQueryBuilder::PARAM_INT)],
				[$this->equalTo(false), $this->equalTo(IQueryBuilder::PARAM_BOOL)]
			);
		$this->qb->expects($this->exactly(5))
			->method('setValue')
			->withConsecutive(
				[$this->equalTo('int_prop'), $this->equalTo($intParam)],
				[$this->equalTo('bool_prop'), $this->equalTo($boolParam)],
				[$this->equalTo('string_prop'), $this->equalTo($stringParam)],
				[$this->equalTo('integer_prop'), $this->equalTo($integerParam)],
				[$this->equalTo('boolean_prop'), $this->equalTo($booleanParam)]
			);

		$this->mapper->insert($entity);
	}

	
	public function testUpdateEntityParameterTypeMapping(): void {
		$entity = new QBTestEntity();
		$entity->setId(789);
		$entity->setIntProp(123);
		$entity->setBoolProp('true');
		$entity->setStringProp('string');
		$entity->setIntegerProp(456);
		$entity->setBooleanProp(false);
		$entity->setJsonProp(['hello' => 'world']);

		$idParam = $this->qb->createNamedParameter('id', IQueryBuilder::PARAM_INT);
		$intParam = $this->qb->createNamedParameter('int_prop', IQueryBuilder::PARAM_INT);
		$boolParam = $this->qb->createNamedParameter('bool_prop', IQueryBuilder::PARAM_BOOL);
		$stringParam = $this->qb->createNamedParameter('string_prop', IQueryBuilder::PARAM_STR);
		$integerParam = $this->qb->createNamedParameter('integer_prop', IQueryBuilder::PARAM_INT);
		$booleanParam = $this->qb->createNamedParameter('boolean_prop', IQueryBuilder::PARAM_BOOL);
		$jsonParam = $this->qb->createNamedParameter('json_prop', IQueryBuilder::PARAM_JSON);

		$this->qb->expects($this->exactly(7))
			->method('createNamedParameter')
			->withConsecutive(
				[$this->equalTo(123), $this->equalTo(IQueryBuilder::PARAM_INT)],
				[$this->equalTo(true), $this->equalTo(IQueryBuilder::PARAM_BOOL)],
				[$this->equalTo('string'), $this->equalTo(IQueryBuilder::PARAM_STR)],
				[$this->equalTo(456), $this->equalTo(IQueryBuilder::PARAM_INT)],
				[$this->equalTo(false), $this->equalTo(IQueryBuilder::PARAM_BOOL)],
				[$this->equalTo(['hello' => 'world']), $this->equalTo(IQueryBuilder::PARAM_JSON)],
				[$this->equalTo(789), $this->equalTo(IQueryBuilder::PARAM_INT)],
			);

		$this->qb->expects($this->exactly(6))
			->method('set')
			->withConsecutive(
				[$this->equalTo('int_prop'), $this->equalTo($intParam)],
				[$this->equalTo('bool_prop'), $this->equalTo($boolParam)],
				[$this->equalTo('string_prop'), $this->equalTo($stringParam)],
				[$this->equalTo('integer_prop'), $this->equalTo($integerParam)],
				[$this->equalTo('boolean_prop'), $this->equalTo($booleanParam)],
				[$this->equalTo('json_prop'), $this->equalTo($jsonParam)]
			);

		$this->expr->expects($this->once())
			->method('eq')
			->with($this->equalTo('id'), $this->equalTo($idParam));


		$this->mapper->update($entity);
	}

	
	public function testGetParameterTypeForProperty(): void {
		$entity = new QBTestEntity();

		$intType = $this->mapper->getParameterTypeForPropertyForTest($entity, 'intProp');
		$this->assertEquals(IQueryBuilder::PARAM_INT, $intType, 'Int type property mapping incorrect');

		$integerType = $this->mapper->getParameterTypeForPropertyForTest($entity, 'integerProp');
		$this->assertEquals(IQueryBuilder::PARAM_INT, $integerType, 'Integer type property mapping incorrect');

		$boolType = $this->mapper->getParameterTypeForPropertyForTest($entity, 'boolProp');
		$this->assertEquals(IQueryBuilder::PARAM_BOOL, $boolType, 'Bool type property mapping incorrect');

		$booleanType = $this->mapper->getParameterTypeForPropertyForTest($entity, 'booleanProp');
		$this->assertEquals(IQueryBuilder::PARAM_BOOL, $booleanType, 'Boolean type property mapping incorrect');

		$stringType = $this->mapper->getParameterTypeForPropertyForTest($entity, 'stringProp');
		$this->assertEquals(IQueryBuilder::PARAM_STR, $stringType, 'String type property mapping incorrect');

		$jsonType = $this->mapper->getParameterTypeForPropertyForTest($entity, 'jsonProp');
		$this->assertEquals(IQueryBuilder::PARAM_JSON, $jsonType, 'JSON type property mapping incorrect');

		$unknownType = $this->mapper->getParameterTypeForPropertyForTest($entity, 'someProp');
		$this->assertEquals(IQueryBuilder::PARAM_STR, $unknownType, 'Unknown type property mapping incorrect');
	}
}
