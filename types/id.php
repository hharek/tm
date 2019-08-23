<?php
namespace TM\Types;

/**
 * Порядковый номер. Первичный ключ
 * Рекомендуется всегда добавлять в таблицу
 */
class Id extends Serial
{
	public $primary = true;
}
?>