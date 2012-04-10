<?php

require_once 'clui_list.php';

class Clui_Table extends Clui_List {

	public $columns = array();
	public $column_widths;
	public $offset_y = 0;

	public static function make($x=null, $y=null, $w=null, $h=null)
	{
		return new self($x, $y, $w, $h);
	}
	
	/* Multiple columns in the sense that Clui_List uses them
	 * doesn't really make sense for tabular data, and this
	 * override conveniently disables that functionality.
	 */
	public function setColumns($columns)
	{
		$this->columns = array();

		foreach ($columns as $key => $col)
		{
			! is_array($col) && $col = array(
				'value' => $col,
			);

			if (is_string($key) && strpos($key, '::') !== false)
			{
				list($key, $width) = explode('::', $key);
				$col['width'] = $width;
			}

			$this->columns[$key] = $col;
		}

		$this->calculateColumnWidths();

		return $this;
	}

	protected function itemLabel($i)
	{
		$label = '';
		foreach ($this->columns as $key => $col)
		{
			$value = is_callable($col['value']) ? call_user_func($col['value'], $this->items[$i]) : $col['value'];
			$width = $this->column_widths[$key];
			if (strlen($value) > $width)
			{
				$value = substr($value, 0, $width-3).'..';
			}
			$label .= str_pad($value, $width);
		}
		return ' '.$label.' ';
	}

	protected function calculateColumns($width = null)
	{
		parent::calculateColumns($width);

		$this->calculateColumnWidths();
	}

	protected function calculateColumnWidths()
	{
		$this->column_widths = array();
		$auto = array();
		$total_width = $this->column_width - 2;

		foreach ($this->columns as $key => $col)
		{
			$width = $col['width'];

			if ($width === true)
			{
				$auto[] = $key;
				$this->column_widths[$key] = null;
				continue;
			}

			if (substr($width, -1) == '%')
			{
				$width = $total_width * substr($width, 0, -1) / 100;
			}

			$this->column_widths[$key] = (int) $width;
		}

		// Distribute the extra to "auto" columns
		$extra = $total_width - array_sum($this->column_widths);
		foreach ($auto as $i => $key)
		{
			$extra -= $this->column_widths[$key] = floor($extra/count($auto));
			// Last one gets the runoff
			if ($key == count($auto)-1)
			{
				$this->column_widths[$key] += $extra;
			}
		}
	}

	public function setFrame($x, $y, $w, $h = true)
	{
		// Since there is one column, autoHeight is easy
		if ($h === true)
		{
			// How much room do we have to play with?
			$max_height = $this->parent->getHeight(true) - $y;
			$h = min(count($this->items), $max_height);
		}

		parent::setFrame($x, $y, $w, $h);

		$this->calculateColumnWidths();

		return $this;
	}

	protected function itemPosition($i)
	{
		$x = $this->padding[3];
		$y = $this->padding[0] + ($i - $this->offset_y);

		return array($x, $y);
	}

	public function layout()
	{
		// Adjust the Y offset if necessary
		if (isset($this->selected))
		{
			if ($this->selected < $this->offset_y)
			{
				$this->offset_y = $this->selected;
			}
			elseif ($this->selected >= $this->offset_y + $this->getHeight(true))
			{
				$this->offset_y = $this->selected - ($this->getHeight(true) - 1);
			}
		}

		foreach ($this->items as $i => $item)
		{
			list($x, $y) = $this->itemPosition($i);

			if ( ! $this->isVisible($i))
			{
				continue;
			}


			ncurses_mvwaddstr($this->window, $y, $x, $this->itemLabel($i));
		}

		return $this;
	}

	protected function isVisible($i)
	{
		list($x, $y) = $this->itemPosition($i);
		$offset = $this->offset_y;
		$padding = $this->padding[0];
		$height = $this->getHeight(true);
		
		if ($i < $offset || $i >= $offset+$height)
		{
			return false;
		}

		return true;
	}

}