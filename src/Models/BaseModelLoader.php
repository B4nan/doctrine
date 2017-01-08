<?php

namespace B4nan\Models;

use Kdyby\Doctrine\EntityManager;
use Nette\Application\LinkGenerator;
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

	/** @var LinkGenerator */
	private $linkGenerator;

	/** @var EntityManager */
	private $em;

	/**
	 * @param Container $container system DI container
	 * @param LinkGenerator $linkGenerator
	 * @param EntityManager $em
	 */
	public function __construct(Container $container, LinkGenerator $linkGenerator, EntityManager $em)
	{
		$this->container = $container;
		$this->linkGenerator = $linkGenerator;
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
	 * Shorthand for generating links
	 */
	public function link()
	{
		return call_user_func_array($this->linkGenerator->link, func_get_args());
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
