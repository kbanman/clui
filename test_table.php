<?php

require_once('clui.php');

Clui::init(array(
	'cursor' => false,
	'color'  => true,
	'border' => true,
	'title'  => 'Test Title',
));

$data = include('fakedata.php');
$columns = array(
	'Name::20%' => function($row){ return $row['name']; },
	'Address::40%' => function($row){ return $row['address'].', '.$row['city']; },
	'Email' => array(
		'width' => true,
		'value' => function($row){ return $row['email']; },
	),
	'Phone::12' => function($row){ return $row['phone']; },
);
$table = Clui_Table::make($data)
	->setColumns($columns)
	->setParent(Clui::rootView())
	->setFrame(0, 10, $autoWidth = true, TRUE)
	//->setBorder(true)
	//->focus();
	->draw();

while(1){
};
