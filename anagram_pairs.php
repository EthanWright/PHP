<?php

/*
Ethan Wright
8/21/15
Usage: php anagram_pairs_cc.php <filename>


Create a function that finds two words in a text that are anagrams of another two words in that text. For example:
Happy eaters always heat their yappers.
Would yield: happy eaters and heat yappers
Rules:
- Treat all letters as lowercase.
- Ignore any words less than 4 characters long.
- Treat all non-alpha-numeric characters as whitespace.
- So "Surely. And they're completely right!" becomes four words: "surely  they completely right"
- Neither of the words in the first pair can be repeated in the second pair.
*/

function get_letter_array($word) {
    $len = strlen($word);
    $letter_count = array();
    for ($pos = 0; $pos < $len; $pos++) {
        $letter = strtolower($word[$pos]);
        if (array_key_exists($letter, $letter_count)) {
            $letter_count[$letter] += 1;
        } else {
            $letter_count[$letter] = 1;
        }
    }
    return $letter_count;
}

function combine_letters($array_1, $array_2) {

    foreach($array_1 as $letter => $amount) {
        if (array_key_exists($letter, $array_2)) {
            $array_2[$letter] += $amount;
        } else {
            $array_2[$letter] = $amount;
        }
    }
    return $array_2;
}

# Function to compare 2 letter arrays to determine if they are equal
function letter_array_diff($letter_array_1, $letter_array_2) {

    foreach($letter_array_1 as $letter => $count) {
        if (array_key_exists($letter, $letter_array_2)) {
            if ($letter_array_2[$letter] != $count) {
                return false;
            }
        } else {
            return false;
        }
    }
    return true;
}

function find_anagram_pairs($text) {
    # Parse each phrase correctly and read each word into $letter_array
    # Compute each combo letter count array and store it in $combo_array[$index_1][$index_2]
    # Check each combo against all other combos that can be made with the remaining words.

    # Remove punctuation and any words that are not long enough.
    $letter_array = array();
    $punctuation = array('\'', ',', '.', ';', "\n", '!', '?');
    $words = explode(' ', str_replace($punctuation, ' ', $text));

    foreach ($words as $index => $word) {
        if (strlen($word) < 4) {
            unset($words[$index]);
        }
    }

    foreach ($words as $index => $word) {
        $letter_array[$index] = get_letter_array($word);
    }
    $combo_array = array();
    # Combine all the pairs of 2 words.
    # Only combine each pair once (i.e., combine words 2 and 3 but not also 3 and 2), index_1 must be less than $index_2
    foreach ($letter_array as $index_1 => $letter_array_1) {
        foreach ($letter_array as $index_2 => $letter_array_2) {
            if ($index_1 < $index_2) {
                $combined_letters = combine_letters($letter_array[$index_1], $letter_array[$index_2]);
                $combo_array[$index_1][$index_2] = $combined_letters;
            }
        }
    }

    # Quit out if there are no words
    if (count($combo_array) == 0) {
        return 0;
    }

    # Now search for 2 duplicate pairs.
    # indexes refer to the word's position in the parsed string. They also correspond to the multidimensional array
    # $combo_array, where $combo_array[$index_1][$index_2] will give a letter array of the combined letters of the
    # words at position $index_1 and $index_2. I didn't compute word combinations more than once, so $index_1 must be
    # less than $index_2 in order to exist in $combo_array

    foreach ($combo_array as $index_1 => $word_array_1) {
        foreach ($word_array_1 as $index_2 => $letters_1) {

            foreach ($combo_array as $index_3 => $word_array_2) {
                foreach ($word_array_2 as $index_4 => $letters_2) {

                    # Verify that the indexes meet the criteria: 4 != 3 != 2 != 1 and 1 < 2 and 1 < 3 and 3 < 4
                    if ($index_1 < $index_2 && $index_1 < $index_3 && $index_3 < $index_4 && $index_3 != $index_2 && $index_4 != $index_2) {
                        # Check if they match
                        if (letter_array_diff($combo_array[$index_1][$index_2], $combo_array[$index_3][$index_4])) {
                            return array($words[$index_1] . ' ' . $words[$index_2], $words[$index_3] . ' ' . $words[$index_4]);
                        }
                    }
                }
            }
        }
    }

    # Nothing was found
    return 0;
}

########################
# Main execution block #
########################

# If no file is provided, read from stdin
if (count($argv) > 1) {
    $input = fopen($argv[1], 'r');
} else {
    $input = fopen('php://stdin', 'r');
}

while($line = fgets($input)) {
    $string = trim($line);
    $result = find_anagram_pairs($string);
    if ($result) {
        print "$result[0] and $result[1]\n";
    } else {
        print "No anagram pairs found\n";
    }
}
fclose($input);