<?php

namespace c00\log;


class LogException extends \Exception {

	public static function new($message, $code = -1, \Exception $e = null){
		$e = new LogException($message, $code, $e);

		return $e;
	}


}