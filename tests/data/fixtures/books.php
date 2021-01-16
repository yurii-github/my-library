<?php
// dataset to return
$books = [ 'inserted' => [], 'expected' => [] ];

// for dataset. maintain column order!
$defaults = [
	'book_guid' => '?', 
	'created_date' => '?',
	'updated_date' => '?',
	'book_cover' => null,
	'favorite' => '0.0',
	'read' => 'no',
	'year' => null,
	'title' => null,
	'isbn13' => null,
	'author' => null,
	'publisher' => null,
	'ext' => null,
	'filename' => '?'
];


// - - - - - - - - - - - - - - - - - - - - insert - - - - - - - -
$books['inserted'] = [
	[
		'book_guid' => '1',
		'created_date' => '2014-01-01 00:00:00',
		'updated_date' => '2014-01-01 00:00:00',
		'favorite' => '1.0',
		'title' => 'title book #1',
		'filename' => 'filename-1'
	],
	[
		'book_guid' => '2',
		'created_date' => '2014-01-01 00:00:00',
		'updated_date' => '2014-01-01 00:00:00',
		'favorite' => '1.0',
		'title' => 'title book #2',
		'filename' => 'filename-2'
	],
	[
		'book_guid' => '3',
		'created_date' => '2014-01-01 00:00:00',
		'updated_date' => '2014-01-01 00:00:00',
		'favorite' => '0.0',
		'filename' => 'filename-3',
	]
];
// - - - - - - - - - - - - - - - - - - - - expected - - - - - - - -
foreach ($books['inserted'] as $book) {
	$books['expected'][] = array_merge($defaults, $book);
}


return $books;