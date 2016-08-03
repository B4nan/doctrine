<?php

namespace B4nan\Entities;

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

	use MagicAccessors;

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

	/**
	 * @param bool $collectionKeys when field contains collection, return only array of PKs
	 * @param bool $includeCollections
	 * @param array $ignoreKeys
	 * @return ArrayHash
	 */
	public function toArray($collectionKeys = TRUE, $includeCollections = TRUE, $ignoreKeys = [])
	{
		$ret = get_object_vars($this);
		$unset = [];

		if (method_exists($this, 'getId')) {
			$ret['id'] = $this->getId();
		}

		foreach ($ret as $key => & $value) {
			if (in_array($key, $ignoreKeys)) {
				continue;
			}
			if ($value instanceof BaseEntity) {
				$value = $value->getId();
			} elseif ($value instanceof PersistentCollection) {
				if ($includeCollections && $collectionKeys) {
					$keys = [];
					foreach ($value as $subEntity) {
						$keys[] = $subEntity->getId();
					}
					$value = $keys;
				} else {
					$unset[] = $key;
				}
			}
		}

		if (! $includeCollections) {
			foreach ($unset as $key) {
				unset($ret[$key]);
			}
		}

		$ret = Common::unsetKeys($ret, $ignoreKeys);
		return ArrayHash::from($ret, FALSE);
	}

}
