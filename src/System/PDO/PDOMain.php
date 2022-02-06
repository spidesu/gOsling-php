<?php

namespace Spidesu\Gosling\System\PDO;

use PDO;
use Spidesu\Gosling\Exception\RowIsEmpty;

class PDOMain extends PDO {

	/**
	 * Получить одну запись из базы
	 *
	 * @param string $query
	 * @param array  $args
	 *
	 * @return array
	 */
	public function getOne(string $query, array $args):array {

		$stmt = $this->prepare($query);
		$stmt->execute($args);

		$result = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$result) {
			throw new RowIsEmpty();
		}

		return $result;
	}

	/**
	 * Получить множество записей из базы
	 * @param string $query
	 * @param array  $args
	 *
	 * @return array
	 */
	public function getAll(string $query, array $args):array {

		$stmt = $this->prepare($query);
		$stmt->execute($args);

		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	public function insert(string $query, array $args):bool {

		$stmt = $this->prepare($query);
		return $stmt->execute($args);
	}

	public function update(string $query, array $args):bool {

		$stmt = $this->prepare($query);
		return $stmt->execute($args);
	}

	public function insertOrUpdate(string $query, array $args):bool {

		$stmt = $this->prepare($query);
		return $stmt->execute($args);
	}

	public function delete(string $query, array $args):bool {

		$stmt = $this->prepare($query);
		return $stmt->execute($args);
	}
}