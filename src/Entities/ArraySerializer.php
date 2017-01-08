<?php

namespace B4nan\Entities;

use B4nan\Utils\Common;
use Doctrine\ORM\PersistentCollection;
use Nette\Utils\ArrayHash;

/**
 * @author adamek
 */
trait ArraySerializer
{

	/**
	 * @param bool $collectionKeys when field contains collection, return only array of PKs
	 * @param bool $includeCollections
	 * @param array $ignoreKeys
	 * @return ArrayHash
	 */
	public function toArray(bool $collectionKeys = TRUE, bool $includeCollections = TRUE, array $ignoreKeys = []) : ArrayHash
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
			if ($value instanceof IEntity) {
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
