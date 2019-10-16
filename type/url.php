<?php
namespace TM\Type;

/**
 * Урл
 */
class Url extends \TM\Column
{
	public $type_sql = "varchar(255)";
	public $type_php = "string";
	public $prepare = "mb_strtolower";

	public function check ($value) : bool
	{
		if (!is_string($value))
			throw new \Exception("Не является строкой.");

		$parse_url = parse_url($value);
		if ($parse_url === false)
			throw new \Exception("Некорректный урл");


		if (!empty($parse_url['path']))
		{
			$url_path = new Url_Path();
			$url_path->check($parse_url['path']);
		}

		if (!empty($parse_url['query']))
		{
			$string = new _String();
			$string->check($parse_url['query']);
		}

		return true;
	}
}
?>