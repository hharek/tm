<?php
namespace TM\Method;

/**
 * Получить информацию по таблице
 */
trait _Info
{
	use \TM\Table_Params,
		_Table,
		DB_Conn;

	/**
	 * Получить информацию по таблице и по колонкам табилицы
	 *
	 * @param string $schema
	 * @param string $table
	 * @return array
	 */
	private static function _info (string $schema, string $table) : array
	{
		$data = [];
		$data['schema'] = $schema;
		$data['table'] = $table;

		if (!static::$db_conn)
			throw new \Exception("INFO. Невозможно выполнить запрос. Не назначен ресурс подключения.");

		/* Данные по колонкам */
		$query =
<<<SQL
SELECT 
	*
FROM 
	"information_schema"."columns"
WHERE
	"table_schema" = $1 AND
	"table_name" = $2
SQL;

		$result = pg_query_params(static::$db_conn, $query, [$schema, $table]);
		$column = pg_fetch_all($result);
		if (empty($column))
			throw new \Exception("INFO. Указана несуществующая таблица.");
		pg_free_result($result);

		/* Комментарии по таблице и по колонкам */
		$query =
<<<SQL
SELECT
	*
FROM
	"pg_catalog"."pg_description"
WHERE
	"objoid" = $1::regclass::oid
SQL;
		$result = pg_query_params(static::$db_conn, $query, [$schema . "." . $table]);
		$comment = pg_fetch_all($result);
		pg_free_result($result);

		/* Комментарий к таблице */
		$data['comment'] = "";
		foreach ($comment as $c)
		{
			if ((int)$c['objsubid'] == 0)
			{
				$data['comment'] = $c['description'];
			}
		}

		/* Добавляем к колонкам комментарии */
		foreach ($column as &$col)
		{
			$col['comment'] = "";
			foreach ($comment as $c)
			{
				if ($col['ordinal_position'] == $c['objsubid'])
					$col['comment'] = $c['description'];
			}
		}
		unset($col);
		$data['column'] = $column;

		return $data;
	}
}
?>