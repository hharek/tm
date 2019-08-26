<?php
namespace TM\Types;

/**
 * Урл
 */
class Url extends \TM\Column
{
	public $type_sql = "varchar(255)";
	public $type_php = "string";
	public $prepare = "mb_strtolower";

	public static function check($value, \TM\Column $column = null): bool
	{
		if (!is_scalar($value))
			throw new \Exception("Не является строкой.");

		$parse_url = parse_url($value);
		if ($parse_url === false)
			throw new \Exception("Некорректный урл");

		if (!empty($parse_url['path']))
			Url_Path::check($parse_url['path']);

		if (!empty($parse_url['query']))
			_String::check($parse_url['query']);

		return true;
	}
}
?>