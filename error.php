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
	private $_error;

	/**
	 * Наименование схемы
	 *
	 * @var string
	 */
	private $_schema;

	/**
	 * Наименование таблицы в базе
	 *
	 * @var string
	 */
	private $_table;

	/**
	 * Наименование
	 *
	 * @var string
	 */
	private $_name;

	/**
	 * Столбец
	 *
	 * @var Column
	 */
	private $_column;

	/**
	 * Конструктор
	 *
	 * @param string $error
	 * @param string $schema
	 * @param string $table
	 * @param string $name
	 * @param Column $column
	 */
	public function __construct(string $error, string $schema, string $table, string $name, Column $column = null)
	{
		$this->_error = $error;
		$this->_schema = $schema;
		$this->_table = $table;
		$this->_name = $name;
		$this->_column = $column;

		parent::__construct("Поле «{$column->name}» задано неверно. " . $error);
	}

	/**
	 * При преобразовании в строку
	 *
	 * @return string|void
	 */
	public function __toString()
	{
		return $this->getName() . ". " . $this->getColumn()->name . ". " . $this->getError();
	}

	/* get методы */
	public function getError(): string { return $this->_error; }
	public function getSchema(): string { return $this->_schema; }
	public function getTable(): string { return $this->_table; }
	public function getName(): string  { return $this->_name; }
	public function getColumn(): Column { return $this->_column; }
}

/**
 * Исключение содержащие список ошибок
 */
class Exception_Many extends Exception
{
	/**
	 * Список ошибок
	 *
	 * @var Exception[]
	 */
	public $_err = [];

	/**
	 * Конструктор
	 *
	 * @var Exception[] $err
	 */
	public function __construct(array $err)
	{
		$this->_err = $err;

		$e = current($err);

		parent::__construct($e->getError(), $e->getSchema(), $e->getTable(), $e->getName(), $e->getColumn());
	}

	public function __toString()
	{
		return parent::__toString();
	}

	/* Получить список ошибок */
	public function getErr() { return $this->_err; }
}
?>