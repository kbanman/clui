<?php

if ( ! function_exists('ncurses_init'))
{
	die ('Clui requires the ncurses php extension.');
}

require_once 'clui_view.php';
require_once 'clui_list.php';

class Clui {

	protected static $instance;

	public $screen;
	public $view;

	const WHITE = 1,
		  GREEN = 2,
		  YELLOW = 3,
		  RED = 4,
		  BLUE = 5,
		  KEY_ESCAPE = 27,
		  KEY_ENTER = 13;

	// Singleton instantiation only
	protected function __construct() {}
	
	public static function init($config = array())
	{
		$instance = self::$instance = new self;
		
		ncurses_init();

		extract($config);

		// Window size
		! isset($width) && $width = 0;
		! isset($height) && $height = 0;

		// Create the root View
		$instance->view = Clui_View::make(0, 0, 0, 0);

		if (empty($cursor))
		{
			ncurses_curs_set(false);
		}
		if ( ! empty($color))	ncurses_start_color();

		ncurses_init_pair(self::WHITE, NCURSES_COLOR_WHITE, NCURSES_COLOR_BLACK);
		ncurses_init_pair(self::GREEN, NCURSES_COLOR_GREEN, NCURSES_COLOR_BLACK);
		ncurses_init_pair(self::YELLOW, NCURSES_COLOR_YELLOW, NCURSES_COLOR_BLACK);
		ncurses_init_pair(self::RED, NCURSES_COLOR_RED, NCURSES_COLOR_BLACK);
		ncurses_init_pair(self::BLUE, NCURSES_COLOR_BLUE, NCURSES_COLOR_BLACK);

		ncurses_noecho();

		if ( ! empty($border))
		{
			self::setBorder(true);
		}
		if ( ! empty($title))
		{
			self::setTitle($title);
		}

		set_error_handler(function($code, $error, $file, $line) use ($instance)
		{
			$instance::end();
			die('Error: '.$error."\nin $file (line $line)");
		});

		ncurses_refresh();
		self::draw();
	}

	public static function rootView()
	{
		return self::$instance->view;
	}

	public function __call($method, $args)
	{
		return call_user_func_array(array(self::$instance->view, $method), $args);
	}

	public static function __callStatic($method, $args)
	{
		return call_user_func_array(array(self::$instance->view, $method), $args);
	}

	public function __get($key)
	{
		return self::$instance->$key;
	}

	public function __set($key, $value)
	{
		return self::$instance->$key = $value;
	}

	public static function end()
	{
		ncurses_end();
	}

}
