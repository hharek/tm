<?php
namespace TM\Method;

/**
 * Назначить ресурс соединения
 */
trait DB_Conn
{
	/**
	 * Ресурс соединения
	 *
	 * @var resource
	 */
	protected static $db_conn;

	/**
	 * Назначить ресурс соединения
	 *
	 * @param resource $resource
	 */
	public static function db_conn ($resource)
	{
		if (!is_resource($resource))
			throw new \Exception("Ресурс соединения указан неверно. Не является ресурсом.");

		if (get_resource_type($resource) !== "pgsql link")
			throw new \Exception("Ресурс соединения указан неверно. Не является ресурсом «pgsql link».");

		static::$db_conn = $resource;
	}
}
?>