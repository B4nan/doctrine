<?php

namespace B4nan\Tests\Models;

use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NativeQuery;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\PessimisticLockException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\UnitOfWork;
use Doctrine\ORM\Mapping as ORM;

class EntityManagerMock implements EntityManagerInterface
{

	/** @var array */
	private $data = [];

	/** @var array */
	private $persisted = [];

	/** @var array */
	private $dataToRemove = [];

	/** @var int */
	private $autoincrement = 1;

	/**
	 * @return \Doctrine\DBAL\Connection
	 */
	public function getConnection()
	{
		//
	}

	/**
	 * @return \Doctrine\ORM\Query\Expr
	 */
	public function getExpressionBuilder()
	{
		//
	}

	/**
	 * @return void
	 */
	public function beginTransaction()
	{
		//
	}

	/**
	 * @param callable $func The function to execute transactionally.
	 * @return mixed The non-empty value returned from the closure or true instead.
	 */
	public function transactional($func)
	{
		//
	}

	/**
	 * @return void
	 */
	public function commit()
	{
		//
	}

	/**
	 * @return void
	 */
	public function rollback()
	{
		//
	}

	/**
	 * @param string $dql The DQL string.
	 * @return Query
	 */
	public function createQuery($dql = '')
	{
		//
	}

	/**
	 * @param string $name
	 * @return Query
	 */
	public function createNamedQuery($name)
	{
		//
	}

	/**
	 * @param string $sql
	 * @param ResultSetMapping $rsm The ResultSetMapping to use.
	 * @return NativeQuery
	 */
	public function createNativeQuery($sql, ResultSetMapping $rsm)
	{
		//
	}

	/**
	 * @param string $name
	 * @return NativeQuery
	 */
	public function createNamedNativeQuery($name)
	{
		//
	}

	/**
	 * @return QueryBuilder
	 */
	public function createQueryBuilder()
	{
		//
	}

	/**
	 * @param string $entityName The name of the entity type.
	 * @param mixed $id The entity identifier.
	 * @return object The entity reference.
	 * @throws ORMException
	 */
	public function getReference($entityName, $id)
	{
		return new Log(['id' => $id]);
	}

	/**
	 * @param string $entityName The name of the entity type.
	 * @param mixed $identifier The entity identifier.
	 * @return object The (partial) entity reference.
	 */
	public function getPartialReference($entityName, $identifier)
	{
		//
	}

	/**
	 * @return void
	 */
	public function close()
	{
		//
	}

	/**
	 * @param object $entity The entity to copy.
	 * @param boolean $deep FALSE for a shallow copy, TRUE for a deep copy.
	 * @return object The new entity.
	 * @throws \BadMethodCallException
	 */
	public function copy($entity, $deep = FALSE)
	{
		//
	}

	/**
	 * @param object $entity
	 * @param int $lockMode
	 * @param int|null $lockVersion
	 * @return void
	 * @throws OptimisticLockException
	 * @throws PessimisticLockException
	 */
	public function lock($entity, $lockMode, $lockVersion = NULL)
	{
		//
	}

	/**
	 * @return \Doctrine\Common\EventManager
	 */
	public function getEventManager()
	{
		//
	}

	/**
	 * @return Configuration
	 */
	public function getConfiguration()
	{
		//
	}

	/**
	 * @return bool
	 */
	public function isOpen()
	{
		//
	}

	/**
	 * @return UnitOfWork
	 */
	public function getUnitOfWork()
	{
		//
	}

	/**
	 * @deprecated
	 * @param int $hydrationMode
	 * @return \Doctrine\ORM\Internal\Hydration\AbstractHydrator
	 */
	public function getHydrator($hydrationMode)
	{
		//
	}

	/**
	 * @param int $hydrationMode
	 * @return \Doctrine\ORM\Internal\Hydration\AbstractHydrator
	 * @throws ORMException
	 */
	public function newHydrator($hydrationMode)
	{
		//
	}

	/**
	 * @return \Doctrine\ORM\Proxy\ProxyFactory
	 */
	public function getProxyFactory()
	{
		//
	}

	/**
	 * @return \Doctrine\ORM\Query\FilterCollection The active filter collection.
	 */
	public function getFilters()
	{
		//
	}

	/**
	 * @return boolean True, if the filter collection is clean.
	 */
	public function isFiltersStateClean()
	{
		//
	}

	/**
	 * @return boolean True, if the EM has a filter collection.
	 */
	public function hasFilters()
	{
		//
	}

	/**
	 * @param string $className The class name of the object to find.
	 * @param mixed $id The identity of the object to find.
	 * @return object The found object.
	 */
	public function find($className, $id)
	{
		if (is_array($id) && isset($id['id'])) {
			$id = $id['id'];
		}
		if (isset($this->persisted[$id])) {
			return $this->persisted[$id];
		}
		if ($id === NULL) {
			return $this->persisted;
		}
		return NULL;
	}

	/**
	 * @param object $object The instance to make managed and persistent.
	 * @return void
	 */
	public function persist($object)
	{
		if (! isset($this->data[spl_object_hash($object)])) {
			$this->data[spl_object_hash($object)] = $object;
		}
	}

	/**
	 * @param object $object The object instance to remove.
	 * @return void
	 */
	public function remove($object)
	{
		$this->dataToRemove[$object->id] = $object;
	}

	/**
	 * @param object $object
	 * @return object
	 */
	public function merge($object)
	{
		//
	}

	/**
	 * @param string|null $objectName if given, only objects of this type will get detached.
	 * @return void
	 */
	public function clear($objectName = NULL)
	{
		//
	}

	/**
	 * @param object $object The object to detach.
	 * @return void
	 */
	public function detach($object)
	{
		//
	}

	/**
	 * @param object $object The object to refresh.
	 * @return void
	 */
	public function refresh($object)
	{
		//
	}

	/**
	 * @return void
	 */
	public function flush()
	{
		foreach ($this->dataToRemove as $object) {
			unset($this->persisted[$object->id]);
		}
		$this->dataToRemove = [];
		foreach ($this->data as $object) {
			if (! $object->id) {
				$object->id = $this->autoincrement++;
			}
			$this->persisted[$object->id] = $object;
		}
		$this->data = [];
	}

	/**
	 * @param string $className
	 * @return \Doctrine\Common\Persistence\ObjectRepository
	 */
	public function getRepository($className)
	{
		return new RepositoryMock($this);
	}

	/**
	 * @param string $className
	 * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
	 */
	public function getClassMetadata($className)
	{
		//
	}

	/**
	 * @return \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory
	 */
	public function getMetadataFactory()
	{
		//
	}

	/**
	 * @param object $obj
	 * @return void
	 */
	public function initializeObject($obj)
	{
		//
	}

	/**
	 * @param object $object
	 * @return bool
	 */
	public function contains($object)
	{
		//
	}

	/**
	 * Returns the cache API for managing the second level cache regions or NULL if the cache is not enabled.
	 *
	 * @return \Doctrine\ORM\Cache|null
	 */
	public function getCache()
	{
		// TODO: Implement getCache() method.
	}
}
