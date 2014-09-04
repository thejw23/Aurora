<?php namespace Aurora;

use \Splinter\Profiler as SplinterProfiler;

class Profiler {

	public static $benchmark;

	public static function start()
	{
		static::$benchmark = SplinterProfiler::start("Query", 'placeholder');
	}

	public static function stop($fullQuery)
	{
		SplinterProfiler::stop(static::$benchmark, $fullQuery);
	}
}