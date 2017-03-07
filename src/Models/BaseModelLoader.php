<?php

namespace B4nan\Models;

use Kdyby\Doctrine\EntityManager;
use Nette\DI\Container;

/**
 * Model loader
 *
 * @author Martin Adámek <martinadamek59@gmail.com>
 */
class BaseModelLoader
{

	/** @var Container */
	protected $container;

	/** @var EntityManager */
	private $em;

	/**
	 * @param Container $container system DI container
	 * @param EntityManager $em
	 */
	public function __construct(Container $container, EntityManager $em)
	{
		$this->container = $container;
		$this->em = $em;
	}

	/**
	 * @return EntityManager
	 */
	public function getEm()
	{
		return $this->em;
	}

	/**
	 * getter for specified model
	 *
	 * @param string $name name of model
	 * @return BaseRepository
	 * @throws \InvalidArgumentException
	 */
	public function getModel($name)
	{
		if ($this->container->hasService($name)) {
			return $this->container->getService($name);
		}

		throw new \InvalidArgumentException("Model '$name' not found.");
	}

	/**
	 * shorthand for models
	 *
	 * @param string $name name of model
	 * @return BaseRepository
	 */
	public function __get($name)
	{
		return $this->getModel($name);
	}

}
