<?php
/**
 * Created by PhpStorm.
 * User: Mimorox
 * Date: 7/1/2015
 * Time: 3:04 PM
 */


function longest_chain( $w) {

    # Make new array based on length of strings
    $new_word_array = array();
    foreach($w as $word) {
        $length = strlen($word);
        if (!array_key_exists($length, $new_word_array)) {
            $new_word_array[$length] = array();
        }
        $new_word_array[$length][] = $word;
    }

    # For each word, call the word_chain function
    $max_chain = 0;
    foreach($new_word_array as $tier) {
        foreach($tier as $word) {

            # We should only bother if it's possible for this word's length to have the longest chain.
            if (strlen($word) > $max_chain) {
                $chain = word_chain($word, $new_word_array);
                # Keep track of the max chain that's returned (Which is all we care about)
                if ($chain > $max_chain) {
                    $max_chain = $chain;
                }
            }
        }
    }
    return $max_chain;
}

function word_chain($word, $word_array) {

    # Return if the length is 1
    $length = strlen($word);
    if ($length == 1) {
        return 1;
    }
    $max_chain = 0;

    # Try removing each letter and checking if the word exists in the array.
    # If it does, recursively call word_chain with the new_word and the same array

    for ($position = 0; $position < $length; $position++) {
        $new_word = substr($word, 0, $position) . substr($word, $position+1);

        # There must be a faster way than in_array. Divide and conquer search?
        if (in_array($new_word, $word_array[$length - 1])) {
            $chain = word_chain($new_word, $word_array) + 1;

            # Keep track of the max chain that's returned (Which is all we care about)
            if ($chain > $max_chain) {
                $max_chain = $chain;
            }
        }
    }
    return $max_chain;
}

$board = array(
    array(0, 0, 0, 0, 0, 0, 0, 0, 0),
    array(0, 0, 0, 0, 0, 0, 0, 0, 0),
    array(0, 0, 0, 0, 0, 0, 0, 0, 0),
    array(0, 0, 0, 0, 0, 0, 0, 0, 0),
    array(0, 0, 0, 0, 0, 0, 0, 0, 0),
    array(0, 0, 0, 0, 0, 0, 0, 0, 0),
    array(0, 0, 0, 0, 0, 0, 0, 0, 0),
    array(0, 0, 0, 0, 0, 0, 0, 0, 0),
    array(0, 0, 0, 0, 0, 0, 0, 0, 0)
);



$arr = array('a','b','ba','bca','bda','bdca');

$result = longest_chain($arr);
print $result;
