<?php

namespace B4nan\Entities;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Nette\Utils\ArrayHash;
use Nette\Utils\Strings;

/**
 * Class TranslationTrait
 *
 * @use add translations field with @ORM\OneToMany(targetEntity="...", mappedBy="...", cascade={"persist", "remove"}, indexBy="lang") annotation
 * @author Martin Adámek <martinadamek59@gmail.com>
 */
trait TranslationTrait
{

	/**
	 * Translated entity constructor.
	 */
	public function __construct()
	{
		$this->translations = new ArrayCollection;
	}

	/**
	 * @param int $lang language id
	 * @return string
	 */
	public function getName($lang = NULL)
	{
		return $this->getTranslatedField('name', $lang);
	}

	/**
	 * @param string $field
	 * @param int $lang language id
	 * @return string
	 */
	public function getTranslatedField($field, $lang = NULL)
	{
		if (!$this->translations->isEmpty()) {
			if ($lang) {
				if (isset($this->translations[$lang])) {
					return $this->translations[$lang]->$field;
				}
			} else {
				return $this->translations->first()->$field;
			}
		}
		return NULL;
	}

	/**
	 * @return Collection
	 */
	public function getTranslations()
	{
		return $this->translations;
	}

	/**
	 * @param int $lang
	 * @return string
	 */
	public function getTranslation($lang = NULL)
	{
		if (!$this->translations->isEmpty()) {
			if ($lang) {
				if (isset($this->translations[$lang])) {
					return $this->translations[$lang];
				}
			} else {
				return $this->translations->first();
			}
		}
		return NULL;
	}

	/**
	 * update all translation fields
	 *
	 * @param array $translations
	 * @param array $langs
	 * @param string $type
	 */
	public function setTranslations(array $translations, array $langs, $type)
	{
		foreach ($translations as $lang => $fields) {
			if (! isset($this->translations[$lang])) {
				$cls = static::TRANSLATION_CLASS;
				$trans = new $cls;
				$trans->lang = $langs[$lang];
				$trans->$type = $this;
				$this->translations[] = $trans;
			} else {
				$trans = $this->translations[$lang];
			}
			unset($fields['lang']);
			foreach ($fields as $field => $value) {
				$trans->$field = $value;
			}
		}
	}

	/**
	 * process translation values from form values
	 *
	 * @param array|ArrayHash $values
	 * @param array $langs
	 * @param string $type
	 */
	public function setTranslationsForm($values, array $langs, $type)
	{
		$trans = [];

		foreach ($values as $key => $value) {
			$match = Strings::match($key, '~^([\w_]+)_([0-9]+)$~');
			if (!$match) {
				continue;
			}
			$field = $match[1];
			$lang = (int) $match[2];
			if (! isset($trans[$lang])) {
				$trans[$lang] = [];
			}
			$trans[$lang][$field] = $value;
		}

		$this->setTranslations($trans, $langs, $type);
	}

}
