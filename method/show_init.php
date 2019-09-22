<?php
namespace TM;

const PHP_TABLE_INIT =
<<<PHP
/**
 * {name}
 */
class {class} extends TM\Table
{
	public static \$schema = "{schema}";
	public static \$table = "{table}";
	public static \$name = "{name}";
	public static \$columns = [];
}
PHP;

namespace TM\Method;

/**
 * Показать код создания класса потомка Table
 */
trait Show_Init
{
	use \TM\Table_Params,
		_Table,
		DB_Conn,
		_Info;

	/**
	 * Показать код создания класса потомка \TM\Table на основании таблицы в базе
	 *
	 * @param string $schema
	 * @param string $table
	 * @return string
	 */
	public static function show_init (string $schema, string $table) : string
	{
		$code = "";

		/* Информация по таблице */
		$info = static::_info($schema, $table);

		/* Определяем наименование таблицы */
		$comment = $info['comment'];
		$comment_explode = explode(\TM\SQL_COMMENT_SEPARATOR, $comment);
		$name = trim($comment_explode[0]);
		$tm_comment = $comment_explode[1] ?? "";

		/* Наименование класса */
		$class = strtolower($table);
		$class_explode = explode("_", $class);
		foreach ($class_explode as &$i)
			$i = ucfirst($i);
		unset($i);
		$class = implode("_", $class_explode);

		/* Код инициализации класса Table */
		$php_code_table_init = strtr(\TM\PHP_TABLE_INIT,
		[
			"{name}" => $name,
			"{class}" => $class,
			"{schema}" => $schema,
			"{table}" => $table,
		]);

		/* Код инициализации столбцов */
		$php_code_column_init = "";
		foreach ($info['column'] as $c)
		{
			$php_code_column_init .= "\n\n" . static::_show_init_column($c, $table);
		}

		return $code;
	}

	/**
	 * Показать код создания объекта-столбца потомка \TM\Column
	 *
	 * @param array $info
	 * @param string $table
	 * @return string
	 */
	private static function _show_init_column (array $info, string $table) : string
	{
		$comment = $info['comment'];
		$comment_explode = explode(\TM\SQL_COMMENT_SEPARATOR, $comment);
		$tm_comment = $comment_explode[1] ?? "";

		if (!empty($tm_comment))
		{
			/* На основании TM-комментария определить тип */
			$column = static::_column_verify_tm_comment($tm_comment);
		}
		else
		{
			/* На основании данных о колонке (information_schema) определить тип */
			$column = static::_column_verify($info, $table);
		}

//		print_r($column);

		return "";
	}

	/**
	 * На основании TM-комментария определить тип
	 *
	 * @param string $tm_comment
	 * @return array
	 */
	private static function _column_verify_tm_comment (string $tm_comment) : array
	{
		return json_decode($tm_comment, true);
	}

	/**
	 * На основании данных о колонке (information_schema) определить тип
	 *
	 * @param array $info
	 * @param string $table
	 * @return array
	 */
	private static function _column_verify (array $info, string $table) : array
	{
		$data = [];

		/* Определяем «name» и «column» */
		$data['column'] = $info['column_name'];
		$data['name'] = $info['column_name'];
		if (!empty($info['comment']))
			$data['name'] = $info['comment'];

		/* Все типы */
		$class_type = static::_class_type_verify();

		/* Запускаем в каждом классе метод verify */
		$data['class'] = "\TM\Column";
		foreach ($class_type as $class)
		{
			if (call_user_func($class . "::verify", $info, $table))
			{
				$data['class'] = $class;
				break;
			}
		}

		/* Столбец */
		/* @var $column \TM\Column */
		$column = new $data['class'];
		$column->column = $data['column'];
		$column->name = $data['name'];

		/* type_sql, type_php */
		if ($data['class'] === "\TM\Column")
		{
			$type = static::_get_type($info);
			$column->type_sql = $type['sql'];
			$column->type_php = $type['php'];
		}

		/* default */
		if ($info['column_default'] !== null)
		{
			if ($data['class'] !== "\TM\Column")
			{
				try
				{
					$result = call_user_func($data['class'] . "::check", $info['column_default'], $column);
					if ($result === false)
						throw new \Exception();

					$column->default = call_user_func($data['class'] . "::process", $info['column_default'], $column);
				}
				catch (\Exception $e)
				{
					$column->default_sql = $info['column_default'];
				}
			}
			else
			{
				$column->default_sql = $info['column_default'];
			}
		}


		return $data;
	}

	/**
	 * Получить список всех доступных типов-классов, у которых есть ненаследуемый статический метод verify()
	 *
	 * @return array
	 */
	private static function _class_type_verify () : array
	{
		/* Подгружаем все классы TM\Type */
		$type_dir = __DIR__ . "/../type";
		$type_file = scandir($type_dir);
		foreach ($type_file as $file)
		{
			if ($file == "." || $file == "..")
				continue;

			require_once $type_dir . "/" . $file;
		}

		/* Определяем классы */
		$class_declared = get_declared_classes();
		$class_type_ref = [];
		foreach ($class_declared as $class)
		{
			if (substr($class, 0, 7) === "TM\Type")
				$class_type_ref[] = new \ReflectionClass($class);
		}

		/* Оставляем типы, у которых есть ненаследуемый статический метод verify() */
		foreach ($class_type_ref as $i => $class)
		{
			/* @var \ReflectionClass $class */
			$method_verify = $class->getMethod("verify");
			if (!$method_verify->isStatic() || $method_verify->getDeclaringClass()->getName() !== $class->getName())
				unset($class_type_ref[$i]);
		}

		/* Сортировка. Классы родители ниже классов потомков */
		usort($class_type_ref, function ($a, $b)
		{
			/* @var \ReflectionClass $a */
			/* @var \ReflectionClass $b */

			$parent = $a->getParentClass();
			while (true)
			{
				if ($parent->getName() === "TM\Column")
					break;

				if ($parent->getName() === $b->getName())
					return -1;

				$parent = $parent->getParentClass();
			}

			$parent = $b->getParentClass();
			while (true)
			{
				if ($parent->getName() === "TM\Column")
					break;

				if ($parent->getName() === $a->getName())
					return +1;

				$parent = $parent->getParentClass();
			}

			return +1;
		});

		/* В массиве сохраняем только имя класса */
		$class_type = [];
		foreach ($class_type_ref as $i)
		{
			/* @var \ReflectionClass $i */
			$class_type[] = $i->getName();
		}

		return $class_type;
	}

	/**
	 * Получить тип столбца на основании "information_schema"."columns"
	 *
	 * @param array $info
	 * @return string
	 */
	private static function _get_type (array $info) : array
	{
		$dtype = $info['data_type'];

		$type_sql = $dtype;
		$type_php = "string";

		if (in_array($dtype, ["character varying", "character", "text"]))
		{
			if (in_array($dtype, ["character varying", "character"]))
				$type_sql = $dtype . "(" . $info['character_maximum_length'] . ")";
			else if ($dtype === "text")
				$type_sql = $dtype . "(" . $info['character_maximum_length'] . ")";

			$type_php = "string";
		}
		elseif (in_array($dtype, ["smallint", "integer", "bigint"]))
		{
			$type_sql = $dtype;
			$type_php = "int";
		}
		elseif (in_array($dtype, ["decimal", "numeric", "real", "double precision"]))
		{
			$type_sql = $dtype . "(" . $info['numeric_precision'] . ", " . $info['numeric_scale'] . ")";
			$type_php = "float";
		}
		elseif ($dtype == "boolean")
		{
			$type_sql = "boolean";
			$type_php = "boolean";
		}
		elseif (in_array($dtype, ["json", "jsonb"]))
		{
			$type_sql = $dtype;
			$type_php = \TM\PGSQL_JSON_VERIFY_TYPE;
		}

		return
		[
			"sql" => $type_sql,
			"php" => $type_php
		];
	}
}
?>