<?php

require_once 'clui_view.php';

class Clui_List extends Clui_View {

	public $items = array();
	public $column_width;
	public $num_columns = 1;
	public $num_rows;
	public $selected;
	public $escape_keys = array(Clui::KEY_ESCAPE);
	public $action;

	public function __construct($x=null, $y=null, $w=null, $h=null)
	{
		if (is_array($x))
		{
			return $this->setItems($x);
		}
		parent::__construct($x, $y, $w, $h);
	}

	public static function make($x=null, $y=null, $w=null, $h=null)
	{
		return new self($x, $y, $w, $h);
	}

	public function setItems($items, $num_columns = null)
	{
		$this->items = $items;

		if ( ! is_null($num_columns))
		{
			$this->setColumns($num_columns);
		}

		return $this;
	}

	public function setFrame($x, $y, $w, $h = true)
	{
		// Auto-calculate height
		if ($h === true)
		{
			// But maybe the width is being auto-calculated...
			if ($w === true)
			{
				$w = $this->parent->getWidth(true);
				$this->calculateColumns($w - ($this->padding[1] + $this->padding[3]));
			}
			else
			{
				$this->calculateColumns($w);
			}
			$h = $this->num_rows + $this->padding[0] + $this->padding[2];
		}

		parent::setFrame($x, $y, $w, $h);

		return $this;
	}

	public function setColumns($num_columns)
	{
		$this->num_columns = $num_columns;

		return $this;
	}

	public function layout()
	{
		foreach ($this->items as $i => $item)
		{
			list($x, $y) = $this->itemPosition($i);
			ncurses_mvwaddstr($this->window, $y, $x, $this->itemLabel($i));
		}

		return $this;
	}

	protected function calculateColumns($width = null)
	{
		is_null($width) && $width = $this->getWidth(true);
		$this->column_width = floor($width/$this->num_columns);
		$this->num_rows = ceil(count($this->items)/$this->num_columns);
	}

	protected function itemPosition($i)
	{
		$col = floor($i/$this->num_rows) * $this->column_width;
		$row = $i - floor($i/$this->num_rows) * $this->num_rows;

		$col += $this->padding[3];
		$row += $this->padding[0];

		return array($col, $row);
	}

	protected function itemLabel($i)
	{
		return str_pad(' '.trim($this->items[$i]).' ', $this->column_width);
	}

	public function focus()
	{
		$this->selected = 0;
		$this->draw();
		$key = null;

		while ( ! in_array($key, $this->escape_keys))
		{
			$key = ncurses_getch($this->window);

			if ($key == NCURSES_KEY_UP)
			{
				$this->selected--; 
				if ($this->selected < 0)
				{
					$this->selected = 0;
				}
			}
			elseif ($key == NCURSES_KEY_DOWN)
			{
				$this->selected++;
				if ($this->selected >= count($this->items))
				{
					$this->selected = count($this->items)-1;
				}
			}
			elseif ($key == NCURSES_KEY_LEFT && ($this->selected - $this->num_rows) >= 0)
			{
				// Move left a column
				$this->selected = $this->selected - $this->num_rows;
			}
			elseif ($key == NCURSES_KEY_RIGHT && ($this->selected + $this->num_rows) < count($this->items))
			{
				// Move right a column
				$this->selected = $this->selected + $this->num_rows;
			}
			elseif ($key == Clui::KEY_ENTER)
			{
				// Trigger the action for the selected item
				if (call_user_func($this->action, $this->selected))
				{
					// Allow action to stop the loop
					return;
				}
			}

			$this->draw();
		}
	}

	public function setEscape($keys)
	{
		! is_array($keys) and $keys = array($keys);
		$this->escape_keys = $keys;
	}

	protected function selectItem($i)
	{
		if ($this->selected < 0) $this->selected = 0;
		if ($this->selected >= count($this->items)) $this->selected = count($this->items)-1;

		$label = $this->itemLabel($i);

		list($x, $y) = $this->itemPosition($i);
		ncurses_wattron($this->window, NCURSES_A_REVERSE);
		ncurses_mvwaddstr($this->window, $y, $x, $label);
		ncurses_wattroff($this->window, NCURSES_A_REVERSE);
	}

	public function draw()
	{
		$this->layout();
		if (isset($this->selected)) $this->selectItem($this->selected);
		parent::draw();

		return $this;
	}

	public function setAction($action)
	{
		$this->action = $action;
	}
}
