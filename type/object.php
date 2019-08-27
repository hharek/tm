<?php
namespace TM\Type;

/**
 * Объект класса stdClass
 */
class _Object extends \TM\Column
{
	public $type_sql = "jsonb";
	public $type_php = "object";
	public $lite = false;
}

/* Алиас */
class Obj extends _Object {};
?>