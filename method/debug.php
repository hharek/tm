<?php
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
	}
}
?>