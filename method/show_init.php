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
			$column = static::_column_verify_tm_comment($tm_comment);
		}
		else
		{
			$column = static::_column_verify($info, $table);
		}

		print_r($column);

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
	 * На основании данных о колонке определить тип
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
		$class_type = static::_class_type();

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

		return $data;
	}

	/**
	 * Получить список всех доступных типов-классов, у которых есть ненаследуемый статический метод verify()
	 *
	 * @return array
	 */
	private static function _class_type () : array
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
		$class_type = [];
		foreach ($class_declared as $class)
		{
			if (substr($class, 0, 7) === "TM\Type")
				$class_type[] = $class;
		}

		/* Оставляем типы, у которых есть ненаследуемый статический метод verify() */
		foreach ($class_type as $i => $class)
		{
			$ref_class = new \ReflectionClass($class);
			$method_verify = $ref_class->getMethod("verify");

			if (!$method_verify->isStatic() || $method_verify->getDeclaringClass()->getName() !== $class)
				unset($class_type[$i]);
		}

		/* Сортировка, примитивные типы вниз */
		usort($class_type, function ($a, $b)
		{
			if (substr($a, 0, 9) === "TM\Type\_")
				return 1;
			else
				return -1;
		});

		return $class_type;
	}
}
?>