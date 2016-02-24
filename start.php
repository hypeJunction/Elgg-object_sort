<?php

/**
 * Object List Sort
 *
 * @author Ismayil Khayredinov <info@hypejunction.com>
 * @copyright Copyright (c) 2015, Ismayil Khayredinov
 */
require_once __DIR__ . '/autoloader.php';

elgg_register_event_handler('init', 'system', 'object_sort_init');

/**
 * Initialize the plugin
 * @return void
 */
function object_sort_init() {
	elgg_extend_view('elgg.css', 'forms/object/sort.css');
}

/**
 * Returns as list of sort options
 *
 * @param array $params Params to pass to the hook
 * @return array
 */
function object_sort_get_sort_options(array $params = array()) {

	$fields = array();

	$plugin = elgg_get_plugin_from_id('object_sort');
	$settings = $plugin->getAllSettings();
	foreach ($settings as $k => $val) {
		if (!$val) {
			continue;
		}
		list($sort, $option) = explode('::', $k);
		if ($sort && in_array(strtolower($option), array('asc', 'desc'))) {
			$fields[] = $k;
		}
	}

	$fields = elgg_trigger_plugin_hook('sort_fields', 'object', $params, $fields);

	$subtypes = elgg_extract('subtype', $params);
	if (!$subtypes) {
		$subtypes = elgg_extract('subtypes', $params);
	}
	if (!empty($subtypes)) {
		$subtypes = (array) $subtypes;
		$subtype = $subtypes[0];
		$fields = elgg_trigger_plugin_hook('sort_fields', "object:$subtype", $params, $fields);
	}
	return $fields;
}

/**
 * Adds sort options to the ege* options array
 *
 * @param array  $options   ege* options
 * @param string $field     Sort field
 * @param string $direction Sort direction (asc|desc)
 * @return array
 */
function object_sort_add_sort_options(array $options = array(), $field = 'time_created', $direction = 'desc') {

	$dbprefix = elgg_get_config('dbprefix');
	$direction = strtoupper($direction);
	if (!in_array($direction, array('ASC', 'DESC'))) {
		$direction = 'DESC';
	}

	$order_by = explode(',', elgg_extract('order_by', $options, ''));
	array_walk($order_by, 'trim');

	$options['joins']['objects_entity'] = "JOIN {$dbprefix}objects_entity AS objects_entity ON objects_entity.guid = e.guid";

	switch ($field) {

		case 'type' :
		case 'subtype' :
		case 'guid' :
		case 'owner_guid' :
		case 'container_guid' :
		case 'site_guid' :
		case 'enabled' :
		case 'time_created';
		case 'time_updated' :
		case 'access_id' :
			array_unshift($order_by, "e.{$field} {$direction}");
			break;

		case 'last_action' :
			$options['selects']['last_action'] = "GREATEST(e.time_created, e.last_action, e.time_updated) as last_action";
			array_unshift($order_by, "last_action {$direction}");
			break;

		case 'likes_count' :
			$name_id = elgg_get_metastring_id('likes');
			$options['joins']['likes_count'] = "LEFT JOIN {$dbprefix}annotations AS likes ON likes.entity_guid = e.guid AND likes.name_id = $name_id";
			$options['selects']['likes_count'] = "COUNT(likes.id) as likes_count";
			$options['group_by'] = 'e.guid';

			array_unshift($order_by, "likes_count {$direction}");
			break;

		case 'responses_count' :
			$ids = array();
			$ids[] = (int) get_subtype_id('object', 'comment');
			$ids[] = (int) get_subtype_id('object', 'discussion_reply');
			$ids_in = implode(',', $ids);

			$options['joins']['responses_count'] = "LEFT JOIN {$dbprefix}entities AS responses ON responses.container_guid = e.guid AND responses.type = 'object' AND responses.subtype IN ($ids_in)";
			$options['selects']['responses_count'] = "COUNT(responses.guid) as responses_count";
			$options['group_by'] = 'e.guid';

			array_unshift($order_by, "responses_count {$direction}");
			break;
	}

	if ($field == 'alpha') {
		$order_by[] = "objects_entity.title {$direction}";
	} else {
		// Always order by time_created and title for matching fields
		$order_by[] = "e.time_created DESC";
		$order_by[] = "objects_entity.title ASC";
	}

	$options['order_by'] = implode(', ', array_unique(array_filter($order_by)));

	return elgg_trigger_plugin_hook('sort_options', 'object', null, $options);
}

/**
 * Adds relationship/metadata filters to the ege* options array
 *
 * @param array  $options   ege* options
 * @param string $rel       Filter name
 * @param string $user      User entity that relationship is determined for
 * @return array
 */
function object_sort_add_rel_options(array $options = array(), $rel = '', $user = null) {

	$dbprefix = elgg_get_config('dbprefix');

	if (!isset($user)) {
		$user = elgg_get_logged_in_user_entity();
	}

	$guid = ($user) ? (int) $user->guid : 0;

	switch ($rel) {
		case 'mine' :
			$options['wheres'][] = "e.owner_guid = $guid";
			break;

		case 'friends' :
			$options['wheres'][] = "EXISTS (SELECT 1 FROM {$dbprefix}entity_relationships WHERE guid_one=$guid AND relationship='friend' AND guid_two = e.guid)";
			break;
	}

	return elgg_trigger_plugin_hook('rel_options', 'object', null, $options);
}

/**
 * Returns a list of object type/relationsihp filter options
 *
 * @param array $params Params to pass to the hook
 * @return array
 */
function object_sort_get_rel_options(array $params = array()) {

	$options = array(
		'',
	);

	if (elgg_is_logged_in()) {
		$options[] = 'mine';
		$options[] = 'friends';
	}

	$options = elgg_trigger_plugin_hook('sort_relationships', 'object', $params, $options);

	$subtypes = elgg_extract('subtype', $params);
	if (!$subtypes) {
		$subtypes = elgg_extract('subtypes', $params);
	}
	if (!empty($subtypes)) {
		$subtypes = (array) $subtypes;
		$subtype = $subtypes[0];
		$options = elgg_trigger_plugin_hook('sort_relationships', "object:$subtype", $params, $options);
	}

	return $options;
}

/**
 * Adds search query options to the ege* options array
 *
 * @param array  $options   ege* options
 * @param string $query     Query
 * @return array
 */
function object_sort_add_search_query_options(array $options = array(), $query = '') {

	if (!elgg_is_active_plugin('search')) {
		return $options;
	}

	$query = sanitize_string($query);
	
	$dbprefix = elgg_get_config('dbprefix');
	$options['joins']['objects_entity'] = "JOIN {$dbprefix}objects_entity AS objects_entity ON objects_entity.guid = e.guid";

	$wheres = array();

	$fields = array('title', 'description');
	$wheres[] = search_get_where_sql('objects_entity', $fields, ['query' => $query], false);

	$search_tags = elgg_extract('search_tags', $options, elgg_get_plugin_setting('search_tags', 'object_sort'));

	if ($search_tags) {
		$valid_tag_names = elgg_get_registered_tag_metadata_names();
		$options['joins']['tags'] = "JOIN {$dbprefix}metadata tags on e.guid = tags.entity_guid";
		$options['joins']['tag_name'] = "JOIN {$dbprefix}metastrings tag_name on tags.name_id = tag_name.id";
		$options['joins']['tag_value'] = "JOIN {$dbprefix}metastrings tag_value on tags.value_id = tag_value.id";

		$access = _elgg_get_access_where_sql(array('table_alias' => 'tags'));
		$sanitised_tags = array();

		foreach ($valid_tag_names as $tag) {
			$sanitised_tags[] = '"' . sanitise_string($tag) . '"';
		}

		$tags_in = implode(',', $sanitised_tags);

		$wheres[] = "(tag_name.string IN ($tags_in) AND tag_value.string = '$query' AND $access)";
	}

	$options['wheres'][] = '(' . implode(' OR ', $wheres) . ')';

	return $options;
}
