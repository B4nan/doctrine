<?php

namespace B4nan\Utils;

use Doctrine\ORM\EntityManagerInterface;
use Nette\Utils\FileSystem;
use PDO;

/**
 * @author Martin Adámek <martinadamek59@gmail.com>
 */
final class DatabaseBackup
{

	/** @var PDO */
	private $pdo;

	/**
	 * @param EntityManagerInterface $em
	 */
	public function __construct(EntityManagerInterface $em)
	{
		$params = $em->getConnection()->getParams();
		$dsn = "mysql:host=$params[host];dbname=$params[dbname]";
		$this->pdo = new PDO($dsn, $params['user'], $params['password']);
		$this->pdo->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL);
	}

	/**
	 * backup single database table
	 *
	 * @param string $table
	 * @return string
	 */
	public function backupTable(string $table) : string
	{
		$result = $this->pdo->query("SELECT * FROM `$table` ORDER BY `id`");
		$create = $this->pdo->query("SHOW CREATE TABLE $table")->fetchColumn(1);

		$sql = "DROP TABLE IF EXISTS `$table`;\n";
		$sql .= "$create;\n\n";

		foreach ($result->fetchAll(PDO::FETCH_NAMED) as $row) {
			$sql .= "INSERT INTO `$table` VALUES (";
			foreach ($row as $f => $item) {
				if (is_numeric($item)) {
					$sql .= "$item";
				} elseif ($item) {
					$item = $this->pdo->quote($item);
					$item = str_replace("\n", "\\n", $item);
					$sql .= $item;
				} elseif ($item === NULL) {
					$sql .= 'NULL';
				} else {
					$sql .= "''";
				}
				$sql .= ",\t";
			}
			$sql = substr($sql, 0, -2) . ");\n";
		}

		return $sql;
	}

	/**
	 * backup single database table to file
	 *
	 * @param string $table
	 * @param string $path
	 */
	public function backupTableToFile(string $table, string $path)
	{
		$sql = $this->getHeader();
		$sql .= $this->backupTable($table);
		FileSystem::createDir($path);
		$path .= '/' . $table . '_' . date('Y-m-d_H-i-s') . '.sql';
		FileSystem::write($path, $sql);
	}

	/**
	 * @return string
	 */
	private function getHeader() : string
	{
		$sql = "SET NAMES utf8;\n";
		$sql .= "SET time_zone = '+00:00';\n";
		$sql .= "SET foreign_key_checks = 0;\n";
		$sql .= "SET sql_mode = 'NO_AUTO_VALUE_ON_ZERO';\n\n\n";
		return $sql;
	}

}
