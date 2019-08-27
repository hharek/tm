<?php
namespace TM\Type;

/**
 * Массив
 */
class _Array extends \TM\Column
{
	public $type_sql = "jsonb";
	public $type_php = "array";
	public $lite = false;
}

/* Алиас */
class Arr extends _Array {};
?>