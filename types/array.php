<?php
namespace TM\Types;

/**
 * Массив
 */
class _Array extends \TM\Column
{
	public $type_sql = "jsonb";
	public $type_php = "array";
	public $lite = false;

	public static function check($value, \TM\Column $column = null): bool
	{
		if (!is_array($value))
			throw new \Exception("Не является массивом.");

		return true;
	}

	public static function prepare($value, \TM\Column $column = null): string
	{
		return json_encode($value, \TM\PREPARE_JSON_ENCODE);
	}

	public static function process(string $value, \TM\Column $column = null)
	{
		return json_decode($value, true, 512, JSON_THROW_ON_ERROR);
	}
}

/* Алиас */
class Arr extends _Array {};
?>