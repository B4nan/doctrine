<?php

namespace B4nan\Tests\Models;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\DBAL\Configuration;
use Doctrine\ORM\Cache\DefaultQueryCache;
use Doctrine\ORM\Cache\Region\DefaultRegion;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Mapping as ORM;
use Nette\Caching\Cache;
use Nette\Caching\Storages\FileStorage;

/**
 * Class RepositoryMock
 * @package B4nan\Tests\Models
 */
class RepositoryMock implements ObjectRepository
{

	/** @var EntityManagerInterface */
	private $em;

	/**
	 * RepositoryMock constructor.
	 * @param EntityManagerInterface $em
	 */
	public function __construct(EntityManagerInterface $em)
	{
		$this->em = $em;
	}

	/**
	 * @param array|Doctrine\Common\Collections\Collection|\Traversable
	 */
	function add($entity)
	{
		$this->em->persist($entity);
	}

	/**
	 * @param array|Doctrine\Common\Collections\Collection|\Traversable|NULL
	 */
	function save($entity = NULL)
	{
		$this->em->persist($entity);
	}

	/**
	 * @param array|Doctrine\Common\Collections\Collection|\Traversable
	 * @param boolean $flush
	 */
	function delete($entity, $flush = self::FLUSH)
	{
		$this->em->delete($entity);
		if ($flush) {
			$this->em->flush($entity);
		}
	}

	/**
	 * @param mixed $id The identifier.
	 * @return object The object.
	 */
	public function find($id)
	{
		return $this->em->find(Log::class, $id);
	}

	/**
	 * @return array The objects.
	 */
	public function findAll()
	{
		return $this->em->find(Log::class, NULL);
	}

	/**
	 * @param array $criteria
	 * @param array|null $orderBy
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return array The objects.
	 * @throws \UnexpectedValueException
	 */
	public function findBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
	{
		return array_merge([], $this->em->find(Log::class, NULL));
	}

	/**
	 * @param array $criteria
	 * @param $value
	 * @param array|null $orderBy
	 * @param null $key
	 * @return array The objects.
	 */
	public function findPairs(array $criteria, $value, array $orderBy = NULL, $key = NULL)
	{
		$ret = [];
		$res = array_merge([], $this->em->find(Log::class, NULL));
		foreach ($res as $o) {
			$ret[$o->{$key ?: 'id'}] = $o->{$value};
		}
		return $ret;
	}

	/**
	 * @param array $criteria
	 * @param array|null $orderBy
	 * @param int|null $limit
	 * @param int|null $offset
	 * @return array The objects.
	 * @throws \UnexpectedValueException
	 */
	public function countBy(array $criteria, array $orderBy = NULL, $limit = NULL, $offset = NULL)
	{
		return count($this->em->find(Log::class, NULL));
	}

	/**
	 * @param array $criteria The criteria.
	 * @return object The object.
	 */
	public function findOneBy(array $criteria)
	{
		return $this->em->find(Log::class, $criteria);
	}

	/**
	 * @return string
	 */
	public function getClassName()
	{
		return Log::class;
	}

	public function getReference($id)
	{
		return new Log(['id' => $id]);
	}

	public function createQueryBuilder($alias)
	{
		$res = [
			[
				'id' => 1,
				'date' => new \DateTime,
				'user' => 1,
				'action' => 'testAction1',
				'message' => 'test message 1',
				'ip' => '10.0.0.106',
			],
			[
				'id' => 2,
				'date' => new \DateTime,
				'user' => 1,
				'action' => 'testAction2',
				'message' => 'test message 2',
				'ip' => '10.0.0.109',
			],
		];

		$em = \Mockery::mock(EntityManager::class);
		$conf = \Mockery::mock(Configuration::class);
		$conf->shouldReceive('getDefaultQueryHints')->once()->andReturn([]);
		$conf->shouldReceive('getQueryCacheImpl')->once()->andReturnNull();
		$conf->shouldReceive('isSecondLevelCacheEnabled')->once()->andReturn(FALSE);
		$em->shouldReceive('getConfiguration')->once()->andReturn($conf);
		$q = \Mockery::mock(new Query($em));
		$q->shouldReceive('getArrayResult')->once()->andReturn($res);

		$qb = \Mockery::mock(QueryBuilder::class, [$em, $alias]);
		$qb->shouldReceive('getQuery')->once()->andReturn($q);
		return $qb;
	}

}
