<?php

namespace App\Session;

class SessionFile
{

	public static $path = "/Session/tmp/";

	public static function put($session_id, $value)
	{
		file_put_contents(app_path() .self::$path . $session_id, serialize($value));
		return true;
	}

	public static function get($session_id)
	{
		return unserialize(file_get_contents(app_path() .self::$path . $session_id));
	}

	public static function forget($session_id)
	{
		unlink(app_path() .self::$path . $session_id);
		return true;
	}

	public static function isExist($session_id)
	{
		return file_exists(app_path() .self::$path . $session_id);
	}

	public static function clear()
	{
		$list = self::getList();
		if (count($list)) {
			foreach ($list as $session_id) {
				self::forget($session_id);
			}
		}
		return true;
	}

	public static function getList()
	{
		$list = scandir(app_path() . self::$path);
		array_shift($list);
		array_shift($list);
		return $list;
	}

}
