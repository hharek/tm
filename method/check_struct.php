<?php
namespace TM\Method;

use TM\Exception;

/**
 * Создание таблицы
 */
trait Check_Struct
{
	use _Meta;

	/**
	 * Наименование схемы
	 *
	 * @var string
	 */
	public static $schema = "public";

	/**
	 * Наименование таблицы в базе
	 *
	 * @var string
	 */
	public static $table;

	/**
	 * Наименование
	 * Добавляется в комментарий к таблице и используется при выводе сообщений об ошибках
	 *
	 * @var string
	 */
	public static $name;

	/**
	 * Столбцы таблицы. Массив объектов Column
	 *
	 * @var \TM\Column[]
	 */
	public static $columns = [];

	/**
	 * Сведения по первичному ключу
	 * Может быть только один столбец
	 *
	 * @var \TM\Column
	 */
	private static $_primary = [];

	/**
	 * Уникальные ключи
	 *
	 * @var \TM\Column[]
	 */
	private static $_unique = [];

	/**
	 * Проверка структуры таблицы и класса
	 */
	public static function check_struct ()
	{
		/* Общая проверка. А вдруг? */
		if (empty(static::$schema) || !is_string(static::$schema))
			throw new \Exception("class " . static::class . ". «Наименование схемы» не указано или указано неверно.");

		if (empty(static::$table) || !is_string(static::$table))
			throw new \Exception("class " . static::class . ". «Наименование таблицы» не указано или указано неверно.");

		if (empty(static::$name) || !is_string(static::$name))
			throw new \Exception("class " . static::class . ". «Наименование» не указано или указано неверно.");

		if (empty(static::$columns))
			throw new \Exception(static::$name . ". Не указаны столбцы.");

		/* Проверка отдельно столбцов */
		foreach (static::$columns as $c)
		{
			/* Наименование столбца и наименование */
			if (empty($c->column))
				throw new \Exception(static::$name . ". У одного из столбцов не указан параметр «column».");

			if (!is_string($c->column))
				throw new \Exception(static::$name . ". У одного из столбцов параметр «column» задан неверно.");

			if (empty($c->name))
				throw new Exception("Не указано наименование.", static::$schema, static::$table, static::$name, $c);

			if (!is_string($c->name))
				throw new Exception("Наименование задано неверно.", static::$schema, static::$table, static::$name, $c);

			/* SQL тип столбца */
			if (empty($c->type_sql))
				throw new Exception("SQL тип (type_sql) не указан.", static::$schema, static::$table, static::$name, $c);

			/* PHP тип */
			if (empty($c->type_php))
				throw new Exception("PHP тип (type_php) не указан.", static::$schema, static::$table, static::$name, $c);

			if (!in_array($c->type_php, \TM\PHP_TYPES))
				throw new Exception("PHP тип (type_php) указан неверно. Допустимные значения: \"" . implode('", "', \TM\PHP_TYPES) . "\".", static::$schema, static::$table, static::$name, $c);

			/* Default */
			if ($c->require && (!empty($c->default) || !empty($c->default_sql)))
				throw new Exception("Если столбец обязательный (require), то не нужно указывать значения по умолчанию (default, default_sql).", static::$schema, static::$table, static::$name, $c);
			elseif (!$c->require && (empty($c->default) && empty($c->default_sql)))
				throw new Exception("Если столбец необязательный (require), то необходимо указать поля по умолчанию (default, default_sql).", static::$schema, static::$table, static::$name, $c);

			if (!empty($c->default) && !empty($c->default_sql))
				throw new Exception("Нужно указать только один параметр «default» или «default_sql».", static::$schema, static::$table, static::$name, $c);

			if (!empty($c->default))
			{
				try
				{
					call_user_func(get_class($c) . "::check", [$c->default, $c]);
				}
				catch (\Exception $e)
				{
					throw new Exception("Значение по умолчанию (default) указано неверно. " . $e->getMessage(), static::$schema, static::$table, static::$name, $c);
				}
			}

			if ($c->default_sql !== null && !is_string($c->default_sql))
				throw new Exception("Значение по умолчанию (default_sql) указано неверно.", static::$schema, static::$table, static::$name, $c);


		}

		/* Первичный ключ */
		$primary_count = 0;
		foreach (static::$columns as $c)
		{
			if ($c->primary)
				$primary_count++;
		}

		if ($primary_count === 0)
			throw new \Exception("Не указан первичный ключ для таблицы.");

		if ($primary_count > 1)
			throw new \Exception("Указано более одного первичного ключа.");

		/* Уникальные ключи */
		foreach (static::$columns as $c)
		{
			if (!empty($c->unique_key) && !$c->unique)
				throw new \Exception("При указании параметра «unique_key» поле «unique» должно быть указано как «true».");
		}
	}
}



?>