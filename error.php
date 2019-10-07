<?php
namespace TM;

/**
 * Исключение
 */
class Exception extends \Exception
{
	/**
	 * Текст сообщения об ошибке
	 *
	 * @var string
	 */
	public $error;

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
	 * @param string $error
	 */
	public function __construct(string $error, string $schema, string $table, string $name, Column $column = null)
	{
		$this->schema = $schema;
		$this->table = $table;
		$this->name = $name;
		$this->column = $column;
		$this->error = $error;

		parent::__construct("Поле «{$column->name}» задано неверно. " . $error);
	}

	/**
	 * При преобразовании в строку
	 *
	 * @return string|void
	 */
	public function __toString()
	{
		return $this->name . ". " . $this->column->name . ". " . $this->error;
	}

	/* get методы */
	public function getError(): string { return $this->error; }
	public function getSchema(): string { return $this->schema; }
	public function getTable(): string { return $this->table; }
	public function getName(): string  { return $this->name; }
	public function getColumn(): Column { return $this->column; }
}
?>