<?php
/* Константы */
require "const.php";

/**
 * Автозагрузчик
 */
spl_autoload_register(function ($class)
{
	/* Общие класы */
	switch ($class)
	{
		case "TM\Exception":
			require "error.php";
			break;

		case "TM\Table":
			require "table.php";
			break;

		case "TM\Column":
			require "column.php";
			break;
	}

	/* Трейты-методы Table */
	if (substr($class, 0, 9) === "TM\Method")
	{
		$method = strtolower(substr($class, 10));
		require "method/" . $method . ".php";
	}

	/* Типы */
	if (substr($class, 0, 7) === "TM\Type")
	{
		$type = substr($class, 8);

		switch ($type)
		{
			/* Исключения */
			case "_Array":
			case "Arr":
				require "type/array.php";
				break;

			case "_Bool":
			case "Boolean":
				require "type/boolean.php";
				break;

			case "_Int":
			case "Integer":
			case "Number":
			case "Num":
				require "type/int.php";
				break;

			case "_Object":
			case "Obj":
				require "type/object.php";
				break;

			case "_String":
			case "Str":
				require "type/string.php";
				break;

			/* Обычные */
			default:
				require "type/" . strtolower($type) . ".php";
				break;
		}
	}
});
?>