<?php

namespace B4nan\Entities;

use App\Entities\ArraySerializer;
use B4nan\Utils\Common;
use Doctrine\ORM\PersistentCollection;
use Kdyby\Doctrine\Entities\MagicAccessors;
use Nette\Utils\ArrayHash;

/**
 * @property-read int $id
 * @author Martin Adámek <martinadamek59@gmail.com>
 */
abstract class BaseEntity implements IEntity
{

	use MagicAccessors, ArraySerializer;

	/** @var int */
	protected $id;

	/**
	 * Fills entity with given values, converts key from under_scores to camelCase
	 *
	 * @param \Traversable|array|NULL $values
	 */
	public function __construct($values = NULL)
	{
		if ($values) {
			foreach ($values as $key => $value) {
				$key = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $key))));
				if (property_exists($this, $key)) {
					$this->{$key} = $value;
				}
			}
		}
	}

	/**
	 * @return string
	 */
	public function __toString()
	{
		return (string) $this->id;
	}

	/**
	 * provides clonable interface
	 */
	public function __clone()
	{
		$this->id = NULL;
	}

	public function setId($id)
	{
		$this->id = (int) $id;
	}

	/**
	 * @return int
	 */
	final public function getId()
	{
		return $this->id;
	}

}
