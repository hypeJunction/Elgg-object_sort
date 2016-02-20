<?php

$fields = '';

if (elgg_extract('show_rel', $vars, false)) {
	$rel_options = object_sort_get_rel_options($vars);
	if (!empty($rel_options)) {
		$rel_options_values = array();
		foreach ($rel_options as $rel_option) {
			$rel_options_values[$rel_option] = elgg_echo("object:rel:$rel_option");
		}
		$fields .= elgg_view_input('select', array(
			'name' => 'rel',
			'value' => elgg_extract('rel', $vars, ''),
			'options_values' => $rel_options_values,
			'class' => 'object-sort-select',
			'label' => elgg_echo('object:rel:label'),
			'field_class' => 'object-sort-select-field',
		));
	}
}

if (elgg_is_active_plugin('search') && elgg_extract('show_search', $vars, false)) {
	$fields .= elgg_view_input('text', array(
		'name' => 'query',
		'value' => elgg_extract('query', $vars),
		'class' => 'object-sort-query',
		'label' => elgg_echo('object:sort:search:label'),
		'field_class' => 'object-sort-query-field',
		'placeholder' => elgg_echo('object:sort:search:placeholder'),
	));
}

if (elgg_extract('show_sort', $vars, false)) {
	$sort_options = object_sort_get_sort_options($vars);
	if (!empty($sort_options)) {
		$sort_options_values = array();
		foreach ($sort_options as $sort_option) {
			$sort_options_values[$sort_option] = elgg_echo("object:sort:$sort_option");
		}
		$fields .= elgg_view_input('select', array(
			'name' => 'sort',
			'value' => elgg_extract('sort', $vars, 'time_created::desc'),
			'options_values' => $sort_options_values,
			'class' => 'object-sort-select',
			'label' => elgg_echo('object:sort:label'),
			'field_class' => 'object-sort-select-field',
		));
	}
}

if (!$fields) {
	return;
}

echo elgg_format_element('div', [
	'class' => 'object-sort-fieldset',
		], $fields);

echo elgg_view_input('submit', array(
	'class' => 'hidden',
));
?>
<script>
	require(['forms/object/sort']);
</script>