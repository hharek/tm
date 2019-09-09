<?php
namespace TM;

const SQL_DEBUG_PREPARE =
<<<SQL
DEALLOCATE ALL;

PREPARE query AS
{query};

EXECUTE query {params};
SQL;

namespace TM\Method;

/**
 * Отладка запросов
 */
trait Debug
{
	/**
	 * Включена ли отладка запросов
	 * Запросы выполняться не будут, а будут выводиться
	 *
	 * @var bool
	 */
	protected static $debug = false;

	/**
	 * Тип отображение запросов
	 * default - стандартный запрос со значениями
	 * without_value - запрос с параметрами, без значений
	 * prepare - подготовленный запрос с параметрами и значениями
	 * json - {query: $query, params: []}
	 *
	 * @var string
	 * @example "default", "without_value", "prepare", "json"
	 */
	protected static $debug_type = "default";

	/**
	 * Вывод запросов в файл
	 * Если не «null» вывод в указанный файл
	 *
	 * @var string
	 * @example "/var/log/tm_query.log"
	 */
	protected static $debug_file;

	/**
	 * Включение отладки запросов
	 *
	 * @param bool $enable
	 * @param string $type
	 * @param string $file
	 */
	public static function debug (bool $enable = true, string $type = "default", string $file = null)
	{
		static::$debug = $enable;

		if (!in_array($type, \TM\DEBUG_TYPE))
			throw new \Exception('Отладка. Указан неверный тип отображения запросов (debug_type). Допустимые значения: "' . implode('", "', \TM\DEBUG_TYPE) . '".');
		static::$debug_type = $type;

		if ($file !== null)
		{
			$dir = dirname($file);

			if (!is_dir($dir))
				throw new \Exception("Отладка. Указан неверный файл.");

			if (is_file($file) && !is_writable($file))
				throw new \Exception("Отладка. Файл недостуен для записи.");
			elseif (!is_file($file) && !is_writable($dir))
				throw new \Exception("Отладка. Невозможно создать файл.");
		}
		static::$debug_file = $file;
	}

	/**
	 * Показать запрос
	 *
	 * @param string $query
	 * @param array|null $params
	 */
	private static function _debug_query (string $query, array $params = null)
	{
		$debug_query = "";
		switch (static::$debug_type)
		{
			case "default":

				if ($params === null)
				{
					$debug_query = $query;
					break;
				}

				$replace_pairs = [];
				for ($i = 0; $i < count($params); $i++)
					$replace_pairs['$' . ($i + 1)] = "'" . pg_escape_string($params[$i]) . "'";

				$debug_query = strtr($query, $replace_pairs);

				break;

			case "without_value":
				$debug_query = $query;
				break;

			case "prepare":

				$sql_params = "";

				if (!empty($params))
				{
					foreach ($params as &$p)
						$p = pg_escape_string($p);
					unset($p);

					$sql_params = "\n(\n\t'" . implode("',\n\t'", $params) . "'\n)";
				}

				$debug_query = strtr(\TM\SQL_DEBUG_PREPARE,
				[
					"{query}" => $query,
					"{params}" => $sql_params
				]);

				break;

			case "json":
				$debug_query = json_encode
				([
					"query" => $query,
					"params" => $params
				], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
				break;
		}

		if (static::$debug_file !== null)
		{
			$debug_query = "\n\n*** " . date("Y-m-d H:i:s") . " ***\n" . $debug_query;
			file_put_contents(static::$debug_file, $debug_query, FILE_APPEND);
			return;
		}

		echo $debug_query;
	}
}
?>