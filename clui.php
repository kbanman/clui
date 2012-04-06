<?php

if ( ! function_exists('ncurses_init'))
{
	die ('Clui requires the ncurses php extension.');
}

class Clui {

	protected $instance;

	public static $screen;
	public static $windows = array();

	const WHITE = 1,
		  GREEN = 2,
		  YELLOW = 3,
		  RED = 4,
		  BLUE = 5,
		  KEY_ESCAPE = 6;

	// Singleton instantiation only
	protected function __construct() {}
	
	public static function init($config = array())
	{
		$instance = new self;
		
		ncurses_init();

		extract($config);

		// Window size
		empty($columns) && $columns = 80;
		empty($rows) && $rows = 25;
		self::$screen = ncurses_newwin(0, 0, 0, 0);

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

		//ncurses_noecho();
		ncurses_timeout(DEFAULT_TIMEOUT);

		if ( ! empty($border))
		{
			ncurses_border(0,0, 0,0, 0,0, 0,0);
			if ( ! empty($title))
			{
				self::title($title);
			}
		}
	}

	public static function draw($window = null)
	{
		if ( ! is_null($window))
		{
			return self::$windows[$window]->draw();
		}
		ncurses_refresh();
	}

	public static function title($title, $window = null)
	{
		if ( ! is_null($window))
		{
			return self::$windows[$window]->title($title);
		}
		ncurses_border(0,0, 0,0, 0,0, 0,0);
		ncurses_attron(NCURSES_A_REVERSE);
		ncurses_mvaddstr(0, 2, ' '.$title.' ');
		ncurses_attroff(NCURSES_A_REVERSE);

		self::draw();
	}

	public static function size()
	{
		ncurses_getmaxyx(self::$screen, $h, $w);
		return array($w, $h);
	}

}

class Clui_Window {

	public $id;

	public $origin;

	public function __construct($x, $y, $w, $h)
	{
		$this->id = ncurses_newwin($h, $w, $y, $x);
		$this->origin = array($x, $y);
	}

	public function draw()
	{
		ncurses_wrefresh($this->id);
	}

	public function title($title)
	{
		$this->border();
		ncurses_attron(NCURSES_A_REVERSE);
		ncurses_mvwaddstr($this->id, 0, 1, ' '.$title.' ');
		ncurses_attroff(NCURSES_A_REVERSE);

		$this->draw();
	}

	public function border()
	{
		ncurses_wborder($this->id, 0,0, 0,0, 0,0, 0,0);
	}

	public function frame()
	{
		ncurses_getmaxyx($this->id, $h, $w);
		return array($this->origin[0], $this->origin[1], $w, $h);
	}
}

class Clui_Menu {

	public $items = array();
	public $selected = 0;
	public $window;
	public $columns = 1;
	public $column_width;
	public $rows;

	public function __construct($items, $x = null, $y = null, $w = null, $h = null)
	{
		// Pad the items
		$this->items = $items;
		if ( ! is_null($x))
		{
			$this->setFrame($x, $y, $w, $h);
		}
	}

	public static function make($items = array(), $x = null, $y = null, $w = null, $h = null)
	{
		return new self($items, $x, $y, $w, $h);
	}

	public function setFrame($x, $y, $w, $h)
	{
		if ($this->window)
		{
			ncurses_wclear($this->window);
		}
		$this->window = new Clui_Window($x, $y, $w, $h);
	}

	public function draw()
	{
		// Calculate column width
		list($x, $y, $w, $h) = $this->window->frame();
		$this->column_width = floor(($w-2)/$this->columns);
		$this->rows = ceil(count($this->items)/$this->columns);

		foreach ($this->items as $k => $label)
		{
			list($x, $y) = $this->itemPosition($k);
			ncurses_mvwaddstr($this->window->id, $y, $x, str_pad(' '.trim($label).' ', $this->column_width));
		}

		$this->select($this->selected);
		$this->window->draw();
	}

	public function focus()
	{
		$this->draw();
		$key = null;

		while ($key != Clui::KEY_ESCAPE && $key != NCURSES_KEY_LEFT)
		{
			$key = ncurses_getch($this->window->id);

			if ($key == NCURSES_KEY_UP)
			{
				$this->selected--; 
				if ($this->selected < 0)
				{
					$this->selected = 0;
				}
				$this->draw();
			}
			elseif ($key == NCURSES_KEY_DOWN)
			{
				$this->selected++;
				if ($this->selected >= count($this->items))
				{
					$this->selected = count($this->items)-1;
				}
				$this->draw();
			}
		}
	}

	protected function select($i)
	{
		if ($this->selected < 0) $this->selected = 0;
		if ($this->selected >= count($this->items)) $this->selected = count($this->items)-1;

		$label = str_pad(' '.trim($this->items[$i]).' ', $this->column_width);

		list($x, $y) = $this->itemPosition($i);
		ncurses_wattron($this->window->id, NCURSES_A_REVERSE);
		ncurses_mvwaddstr ($this->window->id, $y, $x, $label);
		ncurses_wattroff($this->window->id, NCURSES_A_REVERSE);
	}

	protected function itemPosition($i)
	{
		$col = $i ? floor($i/$this->rows) : 0;
		$row = $i - floor($i/$this->rows)*$this->rows;

		return array($col*$this->column_width+1, $row);
	}

}