<?php
namespace TM\Types;

/**
 * Объект класса stdClass
 */
class _Object extends \TM\Column
{
	public $type_sql = "jsonb";
	public $type_php = "object";
	public $lite = false;

	public static function check($value, \TM\Column $column = null): bool
	{
		if (!is_object($value) || !get_class($value) !== \stdClass::class)
			throw new \Exception("Не является объектом класса «stdClass».");

		return true;
	}

	public static function prepare($value, \TM\Column $column = null): string
	{
		return json_encode($value, \TM\PREPARE_JSON_ENCODE);
	}

	public static function process(string $value, \TM\Column $column = null)
	{
		return json_decode($value, false, 512, JSON_THROW_ON_ERROR);
	}
}

/* Алиас */
class Obj extends _Object {};
?>