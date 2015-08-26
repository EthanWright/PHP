<?php
/**
 * Created by PhpStorm.
 * User: Mimorox
 * Date: 7/1/2015
 * Time: 4:06 PM
 */


function maxThreats( $a) {

    # A queen is under threat if there is another queen in the same row.
    # The input language makes this situation impossible

    # A queen is under threat if there is another queen in the same column with no other queens between them
    # This is denoted by having duplicate values in the input array, $a, but with no

    # A queen is under threat if there is another queen in a diagonal
    # This can be found by finding the slope of the line through the 2 queens.
    # Slope = (y2-y1) / (x2-x1)
    # A slope of 1 or -1 is a diagonal (45 degree) line through the 2 points, and the queen is threatened

    $max_threats = 0;
    foreach($a as $y1 => $x1) {
        # Coordinates are $x1, $y1 for this queen

        # Flags for denoting if there was a queen in the same column or diagonal as the queen being investigated.
        # Since queens can not jump over other queens, this value is 0 or 1, and there are only 6 possible
        # ways a queen can be threatened. (8 if the grammar allowed for queens in the same row)
        $same_column_before = 0;
        $same_column_after = 0;
        $diagonal_northeast = 0;
        $diagonal_southeast = 0;
        $diagonal_southwest = 0;
        $diagonal_northwest = 0;

        $threats = 0;
        foreach ($a as $y2 => $x2) {

            # Check for other queens in the same column, before or after the queen being investigated
            if ($x2 == $x1 && $y2 < $y1) {
                $same_column_before = 1;
            }
            if ($x2 == $x1 && $y2 > $y1) {
                $same_column_after = 1;
            }

            # Check for other queens in the diagonals (absolute value of rise equals absolute value of run)
            $rise = $y2-$y1;
            $run = $x2-$x1;
            if (abs($rise) == abs($run) && $run != 0) {
                # Queens can't jump over other queens. Set a flag for each of the 4 diagonals to avoid overcounting
                if ($rise < 0 && $run < 0) {
                    $diagonal_northwest = 1;

                } elseif ($rise < 0 && $run > 0) {
                    $diagonal_northeast = 1;

                } elseif ($rise > 0 && $run < 0) {
                    $diagonal_southwest = 1;

                } elseif ($rise > 0 && $run > 0) {
                    $diagonal_southeast = 1;
                }
            }
        }

        # Add on whether or not there were queens in the same column, before or after
        $threats = $same_column_after + $same_column_before + $diagonal_northeast + $diagonal_southeast +
            $diagonal_southwest + $diagonal_northwest;

        # Keep track of max threats
        if ($threats > $max_threats) {
            $max_threats = $threats;
        }
    }

    return $max_threats;
}



$arr = array(4,5,1,3,7,8,2,7);
$arr = array(1,2,1,2);
#$arr = array(1,1,1,1,1,1,1,1);



$result = maxThreats($arr);
print $result;