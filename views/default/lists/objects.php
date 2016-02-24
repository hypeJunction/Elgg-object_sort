<?php

$options = (array) elgg_extract('options', $vars);
$callback = elgg_extract('callback', $vars, 'elgg_list_entities');

if (!is_array($options) || empty($options) || !is_callable($callback)) {
	return;
}

$subtypes = elgg_extract('subtype', $options);
unset($options['subtype']);
if (!$subtypes) {
	$subtypes = elgg_extract('subtypes', $options);
}
if (!$subtypes) {
	$subtypes = elgg_extract('subtype', $vars, get_input('subtype'));
}

$vars['subtype'] = $subtypes ? : ''; // filter
$options['subtypes'] = $subtypes ? (array) $subtypes : ELGG_ENTITIES_ANY_VALUE;

$rel = elgg_extract('rel', $vars, get_input('rel'));
$vars['rel'] = $rel;

if ($rel) {
	$list_class = (array) elgg_extract('list_class', $options, array());
	$list_class[] = "elgg-list-objects-$rel";
	$options['list_class'] = implode(' ', $list_class);
}

$query = elgg_extract('query', $vars, get_input('query'));
$vars['query'] = $query;

$sort = elgg_extract('sort', $vars, get_input('sort', 'time_created::desc'));
$vars['sort'] = $sort;

$base_url = elgg_extract('base_url', $options);
if (!$base_url) {
	$base_url = current_page_url();
}

$base_url = elgg_http_remove_url_query_element($base_url, 'query');
$base_url = elgg_http_remove_url_query_element($base_url, 'sort');
$base_url = elgg_http_remove_url_query_element($base_url, 'limit');
$base_url = elgg_http_remove_url_query_element($base_url, elgg_extract('offset_key', $options, 'offset'));

$form = elgg_view_form('object/sort', array(
	'action' => $base_url,
	'method' => 'GET',
	'disable_security' => true,
		), $vars);

$user = elgg_extract('user', $vars, elgg_get_page_owner_entity());
$options['user'] = $user ? : null;
$options = object_sort_add_rel_options($options, $rel, $user ? : null);

list($sort_field, $sort_direction) = explode('::', $sort);
$options = object_sort_add_sort_options($options, $sort_field, $sort_direction);

if (!empty($query) && elgg_is_active_plugin('search')) {
	$options['query'] = $query;
	if (version_compare(elgg_get_version(true), '2.1', '>=')) {
		// search hooks in earlier versions reset 'joins' and 'wheres' and 'order_by'
		$results = elgg_trigger_plugin_hook('search', 'object', $options, array());
		$entities = elgg_extract('entities', $results);
		$list = elgg_view_entity_list($entities, $options);
	} else {
		$options = object_sort_add_search_query_options($options, $query);
		$list = call_user_func($callback, $options);
	}
} else {
	$list = call_user_func($callback, $options);
}

// make sure it's not an empty list with no results <p>
if (empty($query) && !preg_match_all("/<ul.*>.*<\/ul>/s", $list)) {
	echo $list;
	return;
}

$id = elgg_extract('list_id', $options);
if (!$id) {
	$id = md5($base_url);
}

echo elgg_format_element('div', [
	'id' => "object-sort-$id",
	'class' => 'object-sort-list',
		], $form . $list);
