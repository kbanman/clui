<?php

require_once('clui.php');

Clui::init(array(
	'cursor' => false,
	'color'  => true,
	'border' => true,
	'title'  => 'Test Title',
));

// Add the menu
$items = array('Add Cases', 'Add Frozen Cases', 'Mark as Shipped', 'Remove Cases', 'Add to Notes', 'Adjust Product', 'Combo breaker!');
$menu = Clui_List::make($items)
	->setColumns(2)
	->setParent(Clui::rootView())
	->setPadding(1)
	->setFrame(0, 0, $autoWidth = true, $autoHeight = true);


// Add the detail view
$detail = Clui_View::make()
	->setParent(Clui::rootView())
	->setFrame(0, $menu->getHeight(), $autoWidth = true, Clui::getHeight(true)-$menu->getHeight())
	->setPadding(0, 1)
	->setBorder(true)
	->addString(0,0, "Testing\n\tmultiline rendering\n\n(And it works! :D)")
	->draw();

$menu->setAction(function($i) use ($items, $detail)
{
	$detail->clear()->draw()->addString(0, 0, 'Selected: '.$items[$i]);
});

$menu->focus();

Clui::end();