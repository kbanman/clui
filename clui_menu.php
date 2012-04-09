<?php

require_once 'clui_list.php';
	
class Clui_Menu extends Clui_List {

	public $selected;

	public function focus()
	{
		$this->selected = 0;
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

	public function draw()
	{
		$this->layout();
		if (isset($this->selected)) $this->select($this->selected);
		$this->window->draw();
	}

}