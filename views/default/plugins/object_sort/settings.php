<?php

$entity = elgg_extract('entity', $vars);

$sort_options = array(
	'time_created::desc',
	'time_created::asc',
	'last_action::asc',
	'last_action::desc',
	'alpha::asc',
	'alpha::desc',
	'likes_count::desc',
	'likes_count::asc',
	'responses_count::desc',
	'responses_count::asc',
);

$inputs = '';
foreach ($sort_options as $option) {
	$input = elgg_view('input/checkbox', array(
		'type' => 'checkbox',
		'name' => "params[$option]",
		'value' => 1,
		'checked' => ($entity->$option == 1),
	));
	$label = elgg_format_element('label', [],  $input . elgg_echo("object:sort:$option"));
	$inputs .= elgg_format_element('li', [], $label);
}

echo elgg_format_element('ul', ['class' => 'elgg-checkboxes'], $inputs);

echo elgg_view_input('select', array(
	'name' => 'params[search_tags]',
	'value' => isset($entity->search_tags) ? $entity->search_tags : true,
	'options_values' => array(
		0 => elgg_echo('option:no'),
		1 => elgg_echo('option:yes'),
	),
	'label' => elgg_echo('object:sort:search_tags'),
	'help' => elgg_echo('object:sort:search_tags:help'),
));