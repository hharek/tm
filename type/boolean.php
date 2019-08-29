<?php
namespace TM\Type;

/**
 * Булёвое значение
 */
class _Bool extends \TM\Column
{
	public $type_sql = "boolean";
	public $type_php = "boolean";
}

/* Алиас */
class Boolean extends _Bool {}
?>