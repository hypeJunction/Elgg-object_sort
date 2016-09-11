Object List Sorting for Elgg
============================
![Elgg 2.0](https://img.shields.io/badge/Elgg-2.0.x-orange.svg?style=flat-square)

*** NO LONGER MAINTAINED ***
The functionality of this plugin has been moved to [hypeLists](https://github.com/hypeJunction/hypeLists)

## Features

 * Implements generic API and UI for sorting object lists
 * By default, provides sorting by Title, Time created, Time updated, Likes count, Comments count
 * Provides a filter to list owned content, friends' content, group content
 * Extendable via hooks

![Object Sort](https://raw.github.com/hypeJunction/Elgg-object_sort/master/screenshots/object_sort.png "Object List Search and Sort")

## Notes

### Limitations

Even though you can pass multiple subtypes to the ege* options, filter and sorting options will be determined
by the first subtype in the array. So, if you are listing blogs with discussions, only general "object" and "object:blog"
hooks will fire.

## Usage

### List objects

```php

echo elgg_view('lists/objects', array(
	'options' => array(
		'types' => 'object',
		'subtypes' => 'discussion',
	),
	'callback' => 'elgg_list_entities',
));
```

### Custom sort fields

Use `'sort_fields','object'` and `'sort_fields',"$object:$subtype"` plugin hooks to add new fields to the sort select input.
Use `'sort_relationships','object'` and `'sort_relationships',"object:$subtype"` plugin hook to add new relationship/metadata filter options.

Use `'rel_options', 'object'` to add custom queries to ege* options for specici sort field and direction.
Use `'sort_options', 'object'` to add custom queries to ege* options for specific relationship/metadata filter option.
