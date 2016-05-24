<?php

namespace B4nan\Tests\Models;

use B4nan\Entities\BaseEntity;
use Doctrine\ORM\Query;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Log extends BaseEntity
{

	/**
	 * @ORM\Column(type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="IDENTITY")
	 */
	protected $id;

	/**
	 * @ORM\Column(type="datetime")
	 */
	protected $date;

	/**
	 * @ORM\Column(type="string", length=15)
	 */
	protected $ip;

	/**
	 * @ORM\Column(type="string", length=50)
	 */
	protected $action;

	/**
	 * @ORM\Column(type="string", length=1000, nullable=true)
	 */
	protected $message;

	/**
	 * @ORM\ManyToOne(targetEntity="B4nan\Entities\User")
	 * @ORM\JoinColumns({
	 *   @ORM\JoinColumn(referencedColumnName="id")
	 * })
	 */
	protected $user;

}
