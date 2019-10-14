<?php
namespace TM;

const ERROR_COLUMN = "Поле «{column}» задано неверно. {error}";

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
	 * @param string $error
	 * @param string $schema
	 * @param string $table
	 * @param string $name
	 * @param Column $column
	 */
	public function __construct(string $error, string $schema, string $table, string $name, Column $column = null)
	{
		$this->error = $error;
		$this->schema = $schema;
		$this->table = $table;
		$this->name = $name;
		$this->column = $column;

		parent::__construct(strtr(ERROR_COLUMN, ["{column}" => $column->name, "{error}" => $error]));
	}

	/**
	 * Преобразование в строку
	 *
	 * @return string
	 */
	public function __toString() : string
	{
		return strtr(ERROR_COLUMN, ["{column}" => $this->column->name, "{error}" => $this->error]);
	}

	/* get методы */
	public function getError(): string { return $this->error; }
	public function getSchema(): string { return $this->schema; }
	public function getTable(): string { return $this->table; }
	public function getName(): string  { return $this->name; }
	public function getColumn(): Column { return $this->column; }
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
	public $err = [];

	/**
	 * Конструктор
	 *
	 * @var Exception[] $err
	 */
	public function __construct(array $err)
	{
		$this->err = $err;

		$e = current($err);

		parent::__construct($e->error, $e->schema, $e->table, $e->name, $e->column);
	}

	public function __toString() : string
	{
		$err_str = [];
		foreach ($this->err as $e)
			$err_str[] = strtr(ERROR_COLUMN, ["{column}" => $e->column->name, "{error}" => $e->error]);

		return implode("\n", $err_str);
	}

	/* get метод */
	public function getErr() { return $this->err; }
}
?>