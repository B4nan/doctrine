<?php

namespace B4nan\Tests\Models;

use B4nan\Models\BaseModel;
use B4nan\Tests\TestCase;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Query;
use Nette\InvalidStateException;
use Nette\Utils\DateTime;
use Doctrine\ORM\Mapping as ORM;
use Tester\Assert;

$container = require __DIR__ . '/../bootstrap.container.php';

/**
 * doctrine base repository test
 *
 * @testCase
 * @author Martin Adámek <martinadamek59@gmail.com>
 */
class ModelTest extends TestCase
{

	/** @var Logs */
	private $model;

	/** @var EntityManagerInterface */
	private $em;

	public function setUp()
	{
		parent::setUp();

		$this->em = new EntityManagerMock;
		$this->model = new Logs($this->em);
	}

	public function testGetEm()
	{
		Assert::type(EntityManagerInterface::class, $this->model->getEm());
	}

	public function testGetRepository()
	{
		/** @var ObjectRepository $repo */
		$repo = $this->model->getRepository();
		Assert::type(ObjectRepository::class, $repo);
		Assert::same(Log::class, $repo->getClassName());
	}

	public function testCRUD()
	{
		$proto = [
			'date' => new DateTime,
			'user' => 1,
			'action' => 'testAction',
			'message' => 'test message 1',
			'ip' => '123.321.1.2',
		];

		// insert
		$id1 = $this->model->insert($proto);
		Assert::true($id1 > 0);

		// update
		Assert::exception(function () {
			$this->model->update(['test' => 'value']);
		}, InvalidStateException::class, 'No ID given, cannot update entity');

		$e = $this->model->update([
			'message' => 'new msg',
			'id' => $id1,
		]);
		Assert::type(Log::class, $e);

		// find
		Assert::type(Log::class, $this->model->find($id1));
		Assert::same($id1, $this->model->find($id1)->id);
		Assert::same(NULL, $this->model->find(123456));

		// get
		Assert::type(Log::class, $this->model->get($id1));
		Assert::same($id1, $this->model->get($id1)->id);
		Assert::exception(function() {
			$this->model->get(123456)->id;
		}, EntityNotFoundException::class);

		// delete
		$count = $this->model->getCount();
		$success = $this->model->delete($id1, FALSE);
		Assert::true($success);
		Assert::equal($count, $this->model->getCount());
		$success = $this->model->delete($id1);
		Assert::true($success);
		Assert::equal($count - 1, $this->model->getCount());

		// getAll
		$this->model->insert($proto);
		$id2 = $this->model->insert($proto);
		$this->model->insert($proto);
		$logs = $this->model->getAll();
		Assert::same(3, count($logs));
		Assert::type(Log::class, $logs[1]);
		Assert::type('string', $logs[1]->message);

		// getPairs
		$logs = $this->model->getPairs('message');
		Assert::same(3, count($logs));
		Assert::type('string', $logs[$id2]);

		// getAssoc
		$logs = $this->model->getAssoc();
		Assert::type(Log::class, $logs[$id2]);
		Assert::type('string', $logs[$id2]->message);
	}

	public function testPersistAndFillEntityReferences()
	{
		$log = new Log([
			'date' => new DateTime,
			'user' => 1,
			'action' => 'testAction',
			'message' => 'test message 1',
			'ip' => '123.321.1.1',
		]);
		Assert::equal(1, $log->user);
		$this->model->fillEntityReferences($log, ['user' => 1]);
		$count = $this->model->getCount();
		$id = $this->model->persist($log, FALSE);
		Assert::null($id);
		Assert::equal($count, $this->model->getCount());
		$id = $this->model->persist($log);
		Assert::true($id > 0);
		Assert::equal($count + 1, $this->model->getCount());
		Assert::type(Log::class, $this->model->getReference($id));
	}

	public function testFlush()
	{
		Assert::noError(function() {
			$this->model->flush();
		});
	}

	public function testToPairs()
	{
		$this->model->insert([
			'date' => new DateTime,
			'user' => 1,
			'action' => 'testAction1',
			'message' => 'test message 1',
			'ip' => '10.0.0.106',
		]);
		$id2 = $this->model->insert([
			'date' => new DateTime,
			'user' => 1,
			'action' => 'testAction2',
			'message' => 'test message 2',
			'ip' => '10.0.0.109',
		]);
		$repo = $this->model->getRepository();
		$qb = $repo->createQueryBuilder('l');
		$logs = $this->model->toPairs($qb, 'ip');
		Assert::same(2, count($logs));
		Assert::type('string', $logs[$id2]);
		Assert::same('10.0.0.109', $logs[$id2]);
	}

	public function testFetchAssoc()
	{
		$this->model->insert([
			'date' => new DateTime,
			'user' => 1,
			'action' => 'testAction1',
			'message' => 'test message 1',
			'ip' => '10.0.0.106',
		]);
		$id2 = $this->model->insert([
			'date' => new DateTime,
			'user' => 1,
			'action' => 'testAction2',
			'message' => 'test message 2',
			'ip' => '10.0.0.109',
		]);
		$res = $this->model->getAll();
		$logs = $this->model->fetchAssoc($res, 'id');
		Assert::same(2, count($logs));
		Assert::type(Log::class, $logs[$id2]);
		Assert::type('string', $logs[$id2]->ip);
		Assert::same('10.0.0.109', $logs[$id2]->ip);

		$ip = '10.0.0.109';
		$logsIps = $this->model->fetchAssoc($res, 'ip');
		Assert::type(Log::class, $logsIps[$ip]);
		Assert::type('string', $logsIps[$ip]->ip);
		Assert::same($ip, $logsIps[$ip]->ip);

		$user = 1;
		$logsByUser = $this->model->fetchAssoc($res, 'user[]');
		Assert::type('array', $logsByUser[$user]);
		Assert::type(Log::class, $logsByUser[$user][0]);
		Assert::type('string', $logsByUser[$user][0]->ip);
		Assert::same('10.0.0.106', $logsByUser[$user][0]->ip);
	}

}

/**
 * Class Logs
 * @package B4nan\Tests\Models
 */
class Logs extends BaseModel
{

	/** @var string */
	const ENTITY = Log::class;

}

// run test
run(new ModelTest($container));
