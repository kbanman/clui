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
$columns = array(
	'Name::30%' => function($row){ return $row['name']; },
	'Address::30%' => function($row){ return $row['address'].', '.$row['city']; },
	'Email' => array(
		'width' => true,
		'value' => function($row){ return $row['email']; },
	),
	'Phone::12',
);
$detail = Clui_View::make()
	->setParent(Clui::rootView())
	->setFrame(0, $menu->getHeight(), $autoWidth = true, Clui::getHeight(true)-$menu->getHeight())
	->setBorder(true)
	->draw();

$menu->setAction(function($i) use ($items, $detail)
{
	$detail->clear()->addString(1, 0, 'Selected: '.$items[$i])->draw();
});

$menu->focus();

Clui::end();