<?php
namespace TM\Method;

use TM\Exception;

/**
 * Проверка класса таблицы
 */
trait TCheck
{
	use \TM\Table_Params;

	/**
	 * Проверка класса таблицы
	 */
	public static function tcheck ()
	{
		/* Общая проверка. А вдруг? */
		if (static::$schema === null || !is_string(static::$schema))
			throw new \Exception("class " . static::class . ". «Наименование схемы» не указано или указано неверно.");

		if (static::$table === null || !is_string(static::$table))
			throw new \Exception("class " . static::class . ". «Наименование таблицы» не указано или указано неверно.");

		if (static::$name === null || !is_string(static::$name))
			throw new \Exception("class " . static::class . ". «Наименование» не указано или указано неверно.");

		if (empty(static::$columns) || !is_array(static::$columns))
			throw new \Exception(static::$name . ". Не указаны столбцы.");

		/* Проверка отдельно столбцов */
		foreach (static::$columns as $c)
		{
			if (!($c instanceof \TM\Column))
				throw new \Exception(static::$name . ". Столбец не является классом «Column».");

			/* Наименование столбца и наименование */
			if ($c->column === null)
				throw new \Exception(static::$name . ". У одного из столбцов не указан параметр «column».");

			if (!is_string($c->column))
				throw new \Exception(static::$name . ". У одного из столбцов параметр «column» задан неверно.");

			if ($c->name === null)
				throw new Exception("Не указано наименование.", static::$schema, static::$table, static::$name, $c);

			if (!is_string($c->name))
				throw new Exception("Наименование указано неверно.", static::$schema, static::$table, static::$name, $c);

			/* SQL тип столбца */
			if ($c->type_sql === null)
				throw new Exception("SQL тип (type_sql) не указан.", static::$schema, static::$table, static::$name, $c);

			if (!is_string($c->type_sql))
				throw new Exception("SQL тип (type_sql) указан неверно. Не является строкой.", static::$schema, static::$table, static::$name, $c);

			/* PHP тип */
			if ($c->type_php === null)
				throw new Exception("PHP тип (type_php) не указан.", static::$schema, static::$table, static::$name, $c);

			if (!is_string($c->type_sql))
				throw new Exception("PHP тип (type_php) указан неверно. Не является строкой.", static::$schema, static::$table, static::$name, $c);

			if (!in_array($c->type_php, \TM\PHP_TYPES))
				throw new Exception('PHP тип (type_php) указан неверно. Допустимные значения: "' . implode('", "', \TM\PHP_TYPES) . '".', static::$schema, static::$table, static::$name, $c);

			/* default, default_sql, require, null, empty */
			if ($c->require !== true && $c->require !== false)
				throw new Exception("Параметр «require» указан неверно. Можно указать только «true» или «false».", static::$schema, static::$table, static::$name, $c);

			if ($c->null !== true && $c->null !== false)
				throw new Exception("Параметр «null» указан неверно. Можно указать только «true» или «false».", static::$schema, static::$table, static::$name, $c);

			if ($c->require)
			{
				if ($c->default !== null || $c->default_sql !== null)
					throw new Exception("Если столбец обязательный (require), то не нужно указывать значения по умолчанию (default или default_sql).", static::$schema, static::$table, static::$name, $c);
			}
			elseif (!$c->require)
			{
				if (!$c->null && $c->type_sql != "serial") /* NULL и SERIAL можно без DEFAULT */
				{
					if ($c->default === null && $c->default_sql === null)
						throw new Exception("Если столбец необязательный (require), то необходимо указать поля по умолчанию (default или default_sql).", static::$schema, static::$table, static::$name, $c);
				}
			}

			if ($c->default !== null && $c->default_sql !== null)
				throw new Exception("Нужно указать только один параметр «default» или «default_sql».", static::$schema, static::$table, static::$name, $c);

			if ($c->default !== null && $c->check !== null && is_callable($c->check))
			{
				try
				{
					call_user_func(get_class($c) . "::check", $c->default, $c);
				}
				catch (\Exception $e)
				{
					throw new Exception("Значение по умолчанию (default) указано неверно. " . $e->getMessage(), static::$schema, static::$table, static::$name, $c);
				}
			}

			if ($c->default_sql !== null && !is_string($c->default_sql))
				throw new Exception("Значение по умолчанию (default_sql) указано неверно. Не является строкой.", static::$schema, static::$table, static::$name, $c);

			if ($c->empty !== true && $c->empty !== false)
				throw new Exception("Параметр «empty» указан неверно. Можно указать только «true» или «false».", static::$schema, static::$table, static::$name, $c);

			/* Первичные ключи */
			if ($c->primary === true && in_array($c->type_php, ["array", "object"]))
				throw new Exception("Параметр «primary» указан неверно. Первичный ключ не может быть задан как «array» или «object».", static::$schema, static::$table, static::$name, $c);

			/* Уникальные ключи */
			if ($c->unique !== true && $c->unique !== false)
				throw new Exception("Параметр «unique» указан неверно. Можно указать только «true» или «false».", static::$schema, static::$table, static::$name, $c);

			if ($c->unique_key !== null)
			{
				if (!is_string($c->unique_key))
					throw new Exception("Параметр «unique_key» указан неверно. Не является строкой.", static::$schema, static::$table, static::$name, $c);

				if (!$c->unique)
					throw new Exception("При указании параметра «unique_key» поле «unique» должно быть указано как «true».", static::$schema, static::$table, static::$name, $c);
			}

			/* Индексы */
			if ($c->index !== true && $c->index !== false)
				throw new Exception("Параметр «index» указан неверно. Можно указать только «true» или «false».", static::$schema, static::$table, static::$name, $c);

			if ($c->index_key !== null)
			{
				if (!is_string($c->index_key))
					throw new Exception("Параметр «index_key» указан неверно. Не является строкой.", static::$schema, static::$table, static::$name, $c);

				if (!$c->index)
					throw new Exception("При указании параметра «index_key» поле «index» должно быть указано как «true».", static::$schema, static::$table, static::$name, $c);
			}

			/* trim */
			if ($c->trim !== true && $c->trim !== false)
				throw new Exception("Параметр «trim» указан неверно. Можно указать только «true» или «false».", static::$schema, static::$table, static::$name, $c);

			/* lite */
			if ($c->lite !== true && $c->lite !== false)
				throw new Exception("Параметр «lite» указан неверно. Можно указать только «true» или «false».", static::$schema, static::$table, static::$name, $c);

			/* equal */
			if (!in_array($c->equal, \TM\EQUAL_ALLOW))
				throw new Exception('Параметр «equal» указан неверно. Допустимные значения: "' . implode('", "', \TM\EQUAL_ALLOW) . '".', static::$schema, static::$table, static::$name, $c);

			/* check, prepare, process */
			if ($c->check !== null && !is_callable($c->check))
				throw new Exception("Параметр «check» указан неверно. Необходимо указать строку с названием функции или назначить функцию обратного вызова.", static::$schema, static::$table, static::$name, $c);

			if ($c->prepare !== null && !is_callable($c->prepare))
				throw new Exception("Параметр «prepare» указан неверно. Необходимо указать строку с названием функции или назначить функцию обратного вызова.", static::$schema, static::$table, static::$name, $c);

			if ($c->process !== null && !is_callable($c->process))
				throw new Exception("Параметр «process» указан неверно. Необходимо указать строку с названием функции или назначить функцию обратного вызова.", static::$schema, static::$table, static::$name, $c);
		}

		/* order */
		$order_index_ar = [];
		foreach (static::$columns as $c)
		{
			if ($c->order !== null && !in_array($c->order, ["asc", "desc"]))
				throw new Exception("Параметр «order» указан неверно. Допускается только «asc» или «desc».", static::$schema, static::$table, static::$name, $c);

			if ($c->order_index !== null)
			{
				if (!is_int($c->order_index) || $c->order_index < 0)
					throw new Exception("Параметр «order_index» указан неверно. Необходимо указать число от «0» и выше.", static::$schema, static::$table, static::$name, $c);

				if (in_array($c->order_index, $order_index_ar))
					throw new Exception("Указано два или более столбца с одинаковым параметром «order_index».", static::$schema, static::$table, static::$name, $c);

				$order_index_ar[] = $c->order_index;
			}
		}
	}
}
?>