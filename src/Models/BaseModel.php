<?php

namespace B4nan\Models;

use B4nan\Application\Parameters;
use B4nan\Entities\IEntity;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\QueryBuilder;
use InvalidArgumentException;
use Kdyby\Doctrine\EntityDao;
use Nette\InvalidStateException;
use Nette\Object;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;

/**
 * @author Martin Adámek <martinadamek59@gmail.com>
 */
abstract class BaseModel extends Object
{

	/** @var string full entity class name */
	const ENTITY = NULL;

	/** @var EntityManagerInterface */
	private $em;

	/** @var EntityDao */
	private $repository;

	/** @var Parameters */
	protected $parameters;

	/**
	 * @param EntityManagerInterface $em
	 * @param Parameters $parameters
	 */
	public function __construct(EntityManagerInterface $em, Parameters $parameters = NULL)
	{
		$this->em = $em;
		$this->parameters = $parameters;
		$this->startup();
	}

	/**
	 * called after initialization
	 */
	protected function startup()
	{
		// placeholder
	}

	/**
	 * @param string $entity
	 * @return EntityDao
	 */
	public function getRepository($entity = NULL)
	{
		if ($this->repository === NULL && $entity === NULL) {
			$this->repository = $this->getEm()->getRepository(static::ENTITY);
		}

		if ($entity === NULL) {
			return $this->repository;
		} else {
			return $this->getEm()->getRepository($entity);
		}
	}

	/**
	 * @param string $alias
	 * @param string $entity
	 * @return \Kdyby\Doctrine\QueryBuilder
	 */
	protected function createQueryBuilder($alias = NULL, $entity = NULL)
	{
		return $this->getRepository($entity)->createQueryBuilder($alias);
	}

	/**
	 * @return EntityManagerInterface
	 */
	public function getEm()
	{
		return $this->em;
	}

	/**
	 * @param array|int $where
	 * @return IEntity
	 * @throws EntityNotFoundException
	 */
	public function get($where)
	{
		$e = $this->find($where);
		if ($e === NULL) {
			throw new EntityNotFoundException;
		}
		return $e;
	}

	/**
	 * @param array|int $where
	 * @return IEntity|NULL
	 */
	public function find($where)
	{
		if (is_numeric($where)) {
			return $this->getRepository()->find($where);
		}
		return $this->getRepository()->findOneBy($where);
	}

	/**
	 * @param int $id
	 * @param null $class
	 * @return IEntity reference proxy
	 */
	public function getReference($id, $class = NULL)
	{
		return $this->getEm()->getReference($class ?: static::ENTITY, $id);
	}

	/**
	 * @param array $where
	 * @param null $order
	 * @param null $limit
	 * @param null $offset
	 * @return array
	 */
	public function getAll($where = [], $order = NULL, $limit = NULL, $offset = NULL)
	{
		return $this->getRepository()->findBy($where, $order, $limit, $offset);
	}

	/**
	 * @param array $where
	 * @param array $order
	 * @param null $limit
	 * @param null $offset
	 * @return array
	 */
	public function getIdentifiers($where = [], $order = [], $limit = NULL, $offset = NULL)
	{
		$qb = $this->getRepository()->createQueryBuilder('e');
		$qb->select('e.id');
		$qb->setMaxResults($limit);
		$qb->setFirstResult($offset);
		$qb->whereCriteria($where);
		foreach ($order as $field => $sort) {
			$qb->orderBy('e.' . $field, $sort);
		}

		$result = $qb->getQuery()->getResult();
		$ret = [];
		foreach ($result as $res) {
			$ret[] = $res['id'];
		}

		return $ret;
	}

	/**
	 * @param string $value
	 * @param array $where
	 * @param array $order
	 * @param null $key
	 * @return array
	 */
	public function getPairs($value, $where = [], $order = [], $key = NULL)
	{
		return $this->getRepository()->findPairs($where, $value, $order, $key);
	}

	/**
	 * @param array $where
	 * @param null $order
	 * @param string $assoc
	 * @return array
	 */
	public function getAssoc($where = [], $order = NULL, $assoc = 'id')
	{
		$results = $this->getRepository()->findBy($where, $order);
		return $this->fetchAssoc($results, $assoc);
	}

	/**
	 * @param array $where
	 * @return int
	 */
	public function getCount($where = [])
	{
		return (int) $this->getRepository()->countBy($where);
	}

	/**
	 * Fetches all records from table and returns associative tree.
	 * Examples:
	 * - associative descriptor: col1[]col2->col3
	 *   builds a tree:          $tree[$val1][$index][$val2]->col3[$val3] = {record}
	 * - associative descriptor: col1|col2->col3=col4
	 *   builds a tree:          $tree[$val1][$val2]->col3[$val3] = val4
	 * @param array $results
	 * @param string $assoc associative $string descriptor
	 * @return array
	 * @author dg
	 */
	final public function fetchAssoc(array $results, $assoc)
	{
		$row = reset($results);
		if (! $row) {
			return [];  // empty result set
		}
		$row = (object) $row; // array hydration compatibility

		$data = NULL;
		$assoc = strtr($assoc, [ ',' => '|' ]);
		$assoc = preg_split('#(\[\]|->|=|\|)#', $assoc, NULL, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

		// check columns
		foreach ($assoc as $as) {
			if ($as !== '[]' && $as !== '=' && $as !== '->' && $as !== '|' && ! property_exists($row, $as)) {
				throw new InvalidArgumentException("Unknown column '$as' in associative descriptor.");
			}
		}

		if ($as === '->') { // must not be last
			array_pop($assoc);
		}

		if (empty($assoc)) {
			$assoc[] = '[]';
		}

		// make associative tree
		do {
			$row = (object) $row; // array hydration compatibility
			$x = & $data;

			// iterative deepening
			foreach ($assoc as $i => $as) {
				if ($as === '[]') { // indexed-array node
					$x = & $x[];

				} elseif ($as === '=') { // "value" node
					$x = $row->{$assoc[$i + 1]};
					continue 2;

				} elseif ($as === '->') { // "object" node
					if ($x === NULL) {
						$x = clone $row;
						$x = & $x->{$assoc[$i + 1]};
						$x = NULL; // prepare child node
					} else {
						$x = & $x->{$assoc[$i + 1]};
					}

				} elseif ($as !== '|') { // associative-array node
					$key = $row->$as;
					if ($key instanceof IEntity) {
						$key = $key->getId();
					}
					$x = & $x[$key];
				}
			}

			if ($x === NULL) { // build leaf
				$x = $row;
			}

		} while ($row = next($results));

		unset($x);
		return $data;
	}

	/**
	 * flush shortcut - sends queries
	 *
	 * @param IEntity $entity
	 */
	public function flush(IEntity $entity = NULL)
	{
		$this->getEm()->flush($entity);
	}

	/**
	 * @param IEntity $entity
	 * @param bool|IEntity $flush
	 * @return int entity id after flush
	 */
	public function persist($entity, $flush = TRUE)
	{
		$this->getEm()->persist($entity);

		if ($flush && !is_bool($flush)) {
			$this->getEm()->flush($flush);
		} elseif ($flush) {
			$this->getEm()->flush();
		}

		return $entity->id;
	}

	/**
	 * @param int|IEntity $entity
	 * @param bool $flush
	 * @return bool
	 */
	public function delete($entity, $flush = TRUE)
	{
		if (is_numeric($entity)) {
			$entity = $this->getRepository()->getReference($entity);
		}
		$this->getEm()->remove($entity);
		if ($flush) {
			$this->getEm()->flush();
		}
		return TRUE;
	}

	/**
	 * @param array|ArrayHash $values
	 * @param bool $returnEntity
	 * @return int|IEntity
	 */
	public function insert($values, $returnEntity = FALSE)
	{
		$class = static::ENTITY;
		$entity = new $class($values);
		unset($values['id']); // do not update PK
		$this->fillEntityReferences($entity, $values);
		$this->persist($entity);
		if ($returnEntity) {
			return $entity;
		}
		return $entity->getId();
	}

	/**
	 * @param array|ArrayHash $values
	 * @param bool $flush
	 * @return IEntity
	 * @throws EntityNotFoundException
	 */
	public function update($values, $flush = TRUE)
	{
		if (!isset($values['id'])) {
			throw new InvalidStateException('No ID given, cannot update entity');
		}

		$entity = $this->get(['id' => $values['id']]);
		unset($values['id']); // do not update PK
		$this->fillEntityReferences($entity, $values);

		$this->persist($entity, $flush);
		return $entity;
	}

	/**
	 * @param QueryBuilder $qb
	 * @param callable $cb
	 * @param string $key
	 * @return array
	 */
	public function toPairs(QueryBuilder $qb, $cb, $key = 'id')
	{
		$q = $qb->getQuery();

		if (is_string($cb)) {
			$result = $q->getArrayResult();
			$cb = function($e) use($cb) {
				return $e[$cb];
			};
		} else {
			$result = $q->getResult();
		}

		$ret = [];
		foreach ($result as $res) {
			$k = is_array($res) ? $res[$key] : $res->$key;
			$ret[$k] = $cb($res);
		}

		return $ret;
	}

	/**
	 * @param IEntity   $entity
	 * @param \Traversable|array $values
	 * @throws \Doctrine\ORM\ORMException
	 */
	public function fillEntityReferences(IEntity & $entity, $values)
	{
		$class = get_class($entity);
		foreach ($values as $key => $value) {
			if ($value instanceof IEntity) {
				continue;
			}
			$key = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
			$annotations = $entity->reflection->getProperty($key)->annotations;
			if (isset($annotations['ORM\ManyToOne'])) {
				$e = $annotations['ORM\ManyToOne'][0]['targetEntity'];
				if (substr($e, 0, 1) !== '\\' && ! Strings::startsWith($e, substr($class, 0, strrpos($class, '\\') + 1))) {
					$e = substr($class, 0, strrpos($class, '\\') + 1) . $e;
				}
				if ($value !== NULL) {
					$value = $this->getEm()->getReference($e, $value);
				}
			}
			if (isset($annotations['ORM\ManyToMany'])) {
				$e = $annotations['ORM\ManyToMany'][0]['targetEntity'];
				if (!Strings::startsWith($e, substr($class, 0, strrpos($class, '\\') + 1))) {
					$e = substr($class, 0, strrpos($class, '\\') + 1) . $e;
				}
				$this->setManyToMany($entity, $key, $value, $e);
				continue;
			}
			if (isset($annotations['ORM\OneToMany'])) {
				$e = $annotations['ORM\OneToMany'][0]['targetEntity'];
				if (!Strings::startsWith($e, substr($class, 0, strrpos($class, '\\') + 1))) {
					$e = substr($class, 0, strrpos($class, '\\') + 1) . $e;
				}
				$this->fillEntityCollection($entity, $e, $key, (array) $value);
				continue;
			}
			$entity->{$key} = $value;
		}
	}

	/**
	 * @param IEntity $entity
	 * @param string $target
	 * @param string $key
	 * @param array $collection
	 * @throws \Doctrine\ORM\ORMException
	 */
	protected function fillEntityCollection(IEntity & $entity, $target, $key, array $collection)
	{
		foreach ($entity->$key as $ent) {
			if (! in_array($ent->getId(), $collection)) {
				$fn = 'remove' . ucfirst(substr($key, -1) === 's' ? substr($key, 0, substr($key, -1) === 'ies' ? -3 : -1) : $key);
				$entity->$fn($ent);
			}
		}
		foreach ($collection as $ent) {
			if (! $ent instanceof IEntity) {
				$ent = $this->getEm()->getReference($target, $ent);
			}
			$fn = 'add' . ucfirst(substr($key, -1) === 's' ? substr($key, 0, substr($key, -1) === 'ies' ? -3 : -1) : $key);
			$entity->$fn($ent);
		}
	}

	/**
	 * update Many to Many collection
	 *
	 * @param IEntity $entity
	 * @param string $field collection identifier
	 * @param array $collection collection values
	 * @param string $class foreign entity
	 */
	public function setManyToMany(IEntity $entity, $field, array $collection, $class)
	{
		/** @var IEntity $ent */
		foreach ($entity->$field as $ent) {
			if (! in_array($ent->getId(), $collection)) {
				$fn = 'remove' . ucfirst(substr($field, -1) === 's' ? substr($field, 0, substr($field, -1) === 'ies' ? -3 : -1) : $field);
				$entity->$fn($ent);
			}
		}
		foreach ($collection as $id) {
			$e = $this->getReference($id, $class);
			if (! $entity->$field->contains($e)) {
				$fn = 'add' . ucfirst(substr($field, -1) === 's' ? substr($field, 0, substr($field, -1) === 'ies' ? -3 : -1) : $field);
				$entity->$fn($e);
			}
		}
	}

	/**
	 * update entity collection
	 *
	 * @param IEntity $entity
	 * @param string $field collection identifier
	 * @param array $collection collection values
	 * @param string $class foreign entity
	 * @param string $type
	 */
	public function setCollection(IEntity $entity, $field, array $collection, $class, $type)
	{
		foreach ($collection as $key => $fields) {
			if (! isset($entity->$field[$key])) {
				$e = new $class;
				$e->$type = $entity;
				$fn = 'add' . ucfirst(substr($field, -1) === 's' ? substr($field, 0, substr($field, -1) === 'ies' ? -3 : -1) : $field);
				$entity->$fn($e);
			} else {
				$e = $entity->$field[$key];
			}
			$this->fillEntityReferences($e, $fields);
		}
	}

}
