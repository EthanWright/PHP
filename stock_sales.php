<?php
/**
Ethan Wright
8/21/15
Usage: php stock_sales.php <filename>


1. Stock Prices

Given a list of integers representing stock prices by day, return the optimum buy and sell points to maximize profit.
(You can only make one buy and one sell.) Your function should take a list as input and return the two indexes
of the buy and sell points.

*/

# Takes an array of stocks
# returns as array of optimal buy point, sell point
function stock_prices($stocks) {

    # Iterate through the stock prices and start with the first price as your starting buy
    # While iterating through the stock prices, check how much you would make if you were to sell at each price point,
    # And keep track of which buy/sale combo nets the highest gain
    # If the sale is ever negative, then make that price as the purchase price and continue, since the net gain of
    # the preceding elements is negative
    # In the event there are several buy/sell combinations that net the same value, the first one found will be returned

    $buy = 0;
    $max_sale = 0;
    $sell = 0;

    foreach($stocks as $index => $price) {

        $sale_amount = $price - $stocks[$buy];
        if ($sale_amount < 0) {
            $buy = $index;

        } elseif ($sale_amount > $max_sale) {
            $sell = $index;
            $max_sale = $sale_amount;
        }
    }
    # If you found nothing, $sell is the same as $buy
    if ($sell == 0 && $max_sale == 0) {
        $sell = $buy;
    }
    return array($buy, $sell);
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
    $stocks = explode(',', trim($line));
    $result = stock_prices($stocks);
    print "Buy index: $result[0] Sell index: $result[1]\n";
}
fclose($input);