<?php

class Clui_View {

	public $window;
	public $frame;
	public $border = false;
	public $parent = null;
	public $padding = array(0, 0, 0, 0);

	public function __construct($x=null, $y=null, $w=null, $h=null)
	{
		if ( ! is_null($x))
		{
			$this->setFrame($x, $y, $w, $h);
		}
	}

	public static function make($x=null, $y=null, $w=null, $h=null)
	{
		return new self($x, $y, $w, $h);
	}

	public function clear()
	{
		ncurses_wclear($this->window);

		if ($this->border)
		{
			// This is kinda hacky
			$this->setBorder(false);
			$this->setBorder(true);
		}

		return $this;
	}

	public function draw()
	{
		ncurses_wrefresh($this->window);

		return $this;
	}

	public function setTitle($title)
	{
		// Needs a border
		! $this->border && $this->setBorder(true);

		ncurses_wattron($this->window, NCURSES_A_REVERSE);
		ncurses_mvwaddstr($this->window, 0, 1, ' '.$title.' ');
		ncurses_wattroff($this->window, NCURSES_A_REVERSE);

		return $this;
	}

	// Don't know how to remove border...
	public function setBorder($bool = true)
	{
		// Refuse to set it twice.. may cause issues if border setting isn't unset
		if ($bool && $this->border) return $this;

		$this->border = $bool;

		if ($bool)
		{
			ncurses_wborder($this->window, 0,0, 0,0, 0,0, 0,0);

			$this->setPadding($this->padding[0], $this->padding[1], $this->padding[2], $this->padding[3]);
		}
		else
		{
			$this->setPadding($this->padding[0]-1, $this->padding[1]-1, $this->padding[2]-1, $this->padding[3]-1);
		}

		return $this;
	}

	// Uses CSS-style clockwise ordering
	public function setPadding($top, $right=null, $bottom=null, $left=null)
	{
		// All sides
		if ( ! is_null($top) && is_null($right))
		{
			$right = $bottom = $left = $top;
		}

		// Two-axis
		elseif ( ! is_null($right) && is_null($bottom))
		{
			$bottom = $top;
			$left = $right;
		}

		// Implied left
		elseif ( ! is_null($bottom) && is_null($left))
		{
			$left = $right;
		}

		if ($this->border)
		{
			$top++;
			$right++;
			$bottom++;
			$left++;
		}

		$this->padding = array($top, $right, $bottom, $left);

		return $this;
	}

	public function getFrame($param = null)
	{
		if ( ! $this->window)
		{
			return null;
		}
		ncurses_getmaxyx($this->window, $h, $w);

		if ( ! is_null($param))
		{
			switch($param)
			{
				case 'x': return $this->origin[0];
				case 'y': return $this->origin[1];
				case 'w': return $w;
				case 'h': return $h;
			}
		}
		return array($this->origin[0], $this->origin[1], $w, $h);
	}

	public function setFrame($x, $y, $w, $h)
	{
		isset($this->window) && ncurses_delwin($this->window);

		if ($this->parent)
		{
			$x += $this->parent->getFrame('x');
			$x += $this->parent->padding[3];
			$y += $this->parent->getFrame('y');
			$y += $this->parent->padding[0];

			if ($w === true)
			{
				$w = $this->parent->getWidth(true);
			}
		}

		$this->window = ncurses_newwin($h, $w, $y, $x);
		$this->origin = array($x, $y);

		return $this;
	}

	public function getWidth($inner = false)
	{
		$w = $this->getFrame('w');
		if ($inner)
		{
			$w -= ($this->padding[1] + $this->padding[3]);
		}

		return $w;
	}

	public function getHeight($inner = false)
	{
		$h = $this->getFrame('h');

		if ($inner && $this->border)
		{
			$h -= ($this->padding[0] + $this->padding[2]);
		}

		return $h;
	}

	public function addSubview($view)
	{
		$view->setParent($this);

		return $this;
	}

	public function addString($x, $y, $string)
	{
		$x += $this->getFrame('x');
		$x += $this->padding[3];
		$y += $this->origin[1];
		$y += $this->padding[0];

		$lines = explode("\n", $string);

		foreach ($lines as $line)
		{
			ncurses_mvaddstr($y, $x, $line);
			$y++;
		}

		return $this;
	}

	public function setParent($view)
	{
		$this->parent = $view;

		if ($this->getFrame())
		{
			$frame = $this->getFrame();
			$this->setFrame($frame[0], $frame[1], $frame[2], $frame[3]);
		}

		return $this;
	}
}
