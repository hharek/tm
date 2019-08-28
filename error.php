<?php
namespace TM;

/**
 * Исключение
 */
class Exception extends \Exception
{
	/**
	 * Наименование схемы
	 *
	 * @var string
	 */
	public $schema;

	/**
	 * Наименование таблицы в базе
	 *
	 * @var string
	 */
	public $table;

	/**
	 * Наименование
	 *
	 * @var string
	 */
	public $name;

	/**
	 * Столбец
	 *
	 * @var Column
	 */
	public $column;

	/**
	 * Конструктор
	 *
	 * @param string $schema
	 * @param string $table
	 * @param string $name
	 * @param Column $column
	 * @param string $message
	 */
	public function __construct(string $message, string $schema, string $table, string $name, Column $column)
	{
		$this->schema = $schema;
		$this->table = $table;
		$this->name = $name;
		$this->column = $column;

		parent::__construct($message);
	}

	/**
	 * При преобразовании в строку
	 *
	 * @return string|void
	 */
	public function __toString()
	{
		return $this->name . ". " . $this->column->name . ". " . $this->message;
	}

	/* get методы */
	public function getSchema(): string { return $this->schema; }
	public function getTable(): string { return $this->table; }
	public function getName(): string  { return $this->name; }
	public function getColumn(): Column { return $this->column; }
}
?>