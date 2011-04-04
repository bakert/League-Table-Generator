<?php

/**
 * Sort an array of associative arrays by one or more keys in the data.
 * Do some rudimentary detection of "type" to get correct numeric/lexical
 * sort automatically.
 *
 * Example usage:
 *
 * $data = array(
 *     array('flavor' => 'lemon',                                                                              *           'food' => 'chicken'),
 *     array('flavor' => 'orange',
 *           'food' => 'duck'),
 *     array('flavor' => 'lemon',
 *           'food' => 'sherbert'),
 *     array('flavor' => 'orange',
 *           'food' => 'juice')
 * );
 * masort($arr, 'flavor_a,food_d'); # "_a" for ascending, "_d" for descending
 *
 * $data is now:
 *
 * {
 *   { 'flavor': 'lemon',
 *     'food'  : 'sherbert' },
 *   { 'flavor': 'lemon',
 *     'food'  : 'chicken' },
 *   { 'flavor': 'orange',
 *     'food'  : 'juice' },
 *   { 'flavor': 'orange',
 *     'food'  : 'duck' }
 * }
 */

// Expects Multidimensional associative array for the first param.
// Second param is "field_a,field_d,field_a" as first param - _a for sort
// ascending, _d for sort descending.
function masort(&$data, $sort) {
    if (!$sort || !$data) {
        return $data;
    }
    $function = create_sort_function($sort, $data);
    return uasort($data, $function);
}

function create_sort_function($sort, $data) {
    $f = '';
    foreach (explode(",", $sort) as $raw) {
        $ending = substr($raw, -strlen("_d"), strlen("_d"));
        if ($ending !== '_a' && $ending !== '_d') {
            $ending = '';
        }
        $key  = substr($raw, 0, strlen($raw) - strlen($ending));
        $desc = ($ending === "_d");
        $cmp  = get_comparison_function($key, $data);
        $f .= '$res = ' . $cmp . '($a["' . $key . '"], $b["' . $key . '"]); '
            . 'if ($res != 0) { '
                . 'return ' . ($desc ? '-$res' : '$res') . '; '
            . '} ';
    }
    $f .= 'return $a;';
    return create_function('$a, $b', $f);
}

//Look at the data and guess what the best comparator is.
function get_comparison_function($key, $data) {
    foreach ($data as $row) {
        $value = $row[$key];
        if (is_numeric($value)) {
            return 'numcmp';
        }
    }
    return 'strcasecmp';
}

function numcmp($a, $b) {
    if ($a > $b) {
        return 1;
    } elseif ($b > $a) {
        return -1;
    } else {
        return 0;
    }
}

/* test data
$data = array(
    array('A' => 'lemon',
          'B' => 'chicken'),
    array('A' => 'orange',
          'B' => 'duck'),
    array('A' => 'lemon',
          'B' => 'sherbert'),
    array('A' => 'orange',
          'B' => 'juice')
);

print "<pre>";
masort("A_a,B_a", $data);
print_r($data);
*/

/*
Copyright (c) 2004 Thomas David Baker

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.
*/
