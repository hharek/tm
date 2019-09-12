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
	 * Показать код создания класса потомка Table
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



		return $code;
	}
}
?>