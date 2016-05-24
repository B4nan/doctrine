<?php

namespace B4nan\Enums;

use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Platforms\AbstractPlatform;

/**
 * Class EnumType
 *
 * @author Martin Adámek <martinadamek59@gmail.com>
 */
abstract class EnumType extends Type
{

	/** @var string */
	const NAME = NULL;

	/** @var array */
	public static $values = [];

	/**
	 * @param array $fieldDeclaration
	 * @param AbstractPlatform $platform
	 * @return string
	 */
	public function getSQLDeclaration(array $fieldDeclaration, AbstractPlatform $platform)
	{
		$values = array_map(function($val) {
			return "'$val'";
		}, static::$values);

		return sprintf("ENUM(%s) COMMENT '(DC2Type:%s)'", implode(', ', $values), $this->getName());
	}

	/**
	 * @param mixed $value
	 * @param AbstractPlatform $platform
	 * @return mixed
	 */
	public function convertToPHPValue($value, AbstractPlatform $platform)
	{
		return $value;
	}

	/**
	 * @param mixed $value
	 * @param AbstractPlatform $platform
	 * @return mixed
	 */
	public function convertToDatabaseValue($value, AbstractPlatform $platform)
	{
		if (!in_array($value, static::$values)) {
			throw new \InvalidArgumentException("Invalid '$this->name' value.");
		}
		return $value;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return static::NAME;
	}

}