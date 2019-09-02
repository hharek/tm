<?php
namespace TM\Type;

/**
 * Порядковый номер. Первичный ключ
 * Рекомендуется всегда добавлять в таблицу
 */
class ID extends Serial
{
	public $primary = true;
}
?>