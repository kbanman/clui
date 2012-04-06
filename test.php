<?php

require_once('clui.php');

Clui::init(array(
	'cursor' => false,
	'color' => true,
	'border' => true,
	'title' => 'Test Title',
));

list($W, $H) = Clui::size();

// Add the menu
$items = array('Add Cases', 'Add Frozen Cases', 'Mark as Shipped', 'Remove Cases', 'Add to Notes', 'Adjust Product');
$menu = new Clui_Menu($items);
$menu->columns = 2;
$menu->setFrame(1, 1, $W-2, count($items)+2);
$menu->draw();

// Add the product list
$list = new Clui_Window(1, $menu->rows+1, $W-2, $H-$menu->rows-2);
$list->border();
$list->draw();


$menu->focus();