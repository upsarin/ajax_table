<?php
class DBConnect
{
	private static $_instance = NULL;
	private $_conn;
	private $_connect = array(
		'host' => 'localhost',
		'port' => '3306',
		'user' => 'mvlad9b9_ajax',
		'pass' => 'Rhfcysqlzntk91',
		'name' => 'mvlad9b9_ajax',
		'charset' => 'utf8',
		'debug' => false
	);

	private function __construct($connect)
	{
		$this->_connect = array_merge($this->_connect, $connect);
		try
		{
			$this->_conn = new PDO(
				'mysql:dbname=' . $this->_connect['name'] . ';host=' . $this->_connect['host'] . ';port=' . $this->_connect['port'],
				$this->_connect['user'],
				$this->_connect['pass']
			);

			$this->_conn->exec('SET NAMES ' . $this->_connect['charset']);

			if ($this->_connect['debug'])
			{
				$this->_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			}
		}
		catch (PDOException $e)
		{
			throw new Exception('Подключение к БД не удалось: ' . $e->getMessage());
		}
	}

	/**
	 * @throws Exception
	 */
	public static function init(array $connect = []): ?DBConnect
	{
		if (self::$_instance === null)
		{
			self::$_instance = new DBConnect($connect);
		}
		return self::$_instance;
	}

	public static function getAllMessages(string $table, ?array $filter = null, ?array $sort = null, int $page = null, int $page_limit = null): bool|array
	{
		// определение страницы
		if(is_int($page) && is_int($page_limit))
		{
			$limit_from = ($page * $page_limit) - $page_limit;
		}
		else
		{
			$limit_from = 0;
		}
		$limit = ' limit '. $limit_from .', '. $page_limit;

		// подготовка фильтра и проверка на его наличие вообще
		if (is_array($filter))
		{
			$where = "where (";
			$cur_param = 0;
			foreach ($filter as $key => $val)
			{
				if ($cur_param == 0)
					$where .= "(`$key` = :$key)";
				else
					$where .= " AND (`$key` = :$key)";

				$cur_param++;
			}
			$where .= ")";
		}
		else
		{
			$where = 'where (`id` != :id)';
			$filter = ['id' => 0];
		}

		$sort_fields = "";
		if (is_array($sort))
			$sort_fields = "order by $sort[field] $sort[sort_type]";

		$sql = /** @lang text */
			"select * from $table $where $sort_fields $limit";
		$sth = self::$_instance->_conn->prepare($sql);
		foreach ($filter as $key => $val)
		{
			$sth->bindValue(":$key", $val);
		}
		if($sth->execute())
			return $sth->fetchAll(PDO::FETCH_ASSOC);
		else
			return false;
	}

	public static function updateMessage(string $table, ?array $data, ?int $id) :int|bool
	{
		if(is_array($data))
		{
			$cur_param = 0;
			foreach($data as $key => $val)
			{
				if($cur_param != 0)
					$set .= ", `". $key ."` = '". $val ."'";
				else
					$set = "`". $key ."` = '". $val ."'";

				$cur_param = ++$cur_param;
			}
		}
		else
			return false;

		if(is_int($id) && ($id !== NULL))
			$where = 'where `id` = '. $id;
		else
			return false;

		$sql = /** @lang text */
			"update $table set $set $where";
		$sth = self::$_instance->_conn->prepare($sql);

		if($sth->execute())
			return true;
		else
			return false;
	}

	public static function addMessage(string $table, ?array $data = null) :int|bool
	{

		// подготовка данных
		$keys = "(";
		$vals = "(";
		$cur_num = 0;
		foreach($data as $key => $val)
		{
			if($cur_num == 0){
				$keys .= '`'. $key .'`';
				$vals .= ":" .$key;
			} else {
				$keys .= ", `" .$key .'`';
				$vals .= ", :" .$key;
			}
			$cur_num = $cur_num + 1;
		}
		$keys .= ")";
		$vals .= ")";

		// подготовка запроса
		$sql = /** @lang text */
			"insert $table $keys values $vals";
		$sth = self::$_instance->_conn->prepare($sql);
		foreach($data as $key => $val)
		{
			$sth->bindValue(':' .$key, $val);
		}

		// выполнение запроса и возрвщение ID сообщения ну или false
		if($sth->execute())
			return self::$_instance->_conn->lastInsertId();
		else
			return false;
	}

	public function countElements(string $table): int
	{
		$sql = "select COUNT(*) as count from $table";
		$sth = $this->_conn->prepare($sql);

		$sth->execute();
		$result = $sth->fetch(PDO::FETCH_ASSOC);

		return $result['count'] ?? 0; // Возвращаем количество элементов
	}

	public function __destruct()
	{
		self::$_instance->_conn = null;
	}

}