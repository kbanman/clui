<?php

if ( ! function_exists('ncurses_init'))
{
	die ('Clui requires the ncurses php extension.');
}

require_once 'clui_view.php';
require_once 'clui_list.php';
require_once 'clui_table.php';

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

		ncurses_curs_set(0);

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

		set_error_handler(function($code, $error, $file, $line)
		{
			Clui::end();
			die('Error: '.$error."\nin $file (line $line)");
		});

		ncurses_refresh();
		self::draw();
	}

	public static function instance()
	{
		return self::$instance;
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
		if ($instance = self::instance())
		{
			ncurses_end();
			self::$instance = null;
		}
	}

	public static function debug($var)
	{
		self::end();
		var_dump($var);
		die();
	}

	public static function log($str, $clear = false)
	{
		static $contents;

		$clear && $contents = '';

		$contents .= $str."\n";

		list($x, $y, $w, $h) = self::getFrame();

		$width = $w/2;
		$height = count(explode("\n",$contents))+1;
		$x = $w/2 - $width/2;
		$y = $h - $height - 2;

		$alert = Clui_View::make()
			->setParent(Clui::rootView())
			->setFrame($x, $y, $width, $height)
			->setBorder(true)
			->draw()
			->addString(1,0, $contents)
			->draw();

		return $alert;
	}

}
