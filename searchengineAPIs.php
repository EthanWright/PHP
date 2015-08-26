<?php

/*
Problem definition: The lazy engineer Alex is tired of searching on multiple engines to get the best result.
Help Alex by making a program that given a search term goes into X amount of search engines, do the search and
then return a list of the result.

He wants to have two different ways to sort the results, and the ability to add more ways later without much problems.

Definition: (Given a engine count of 2) List of results ( [Response 0 for engine 0], [Response 0 for engine 1],
[Response 1 for engine 0]. [Response 1 for engine 1] ..... [Response n for engine 2] )

For the second algorithm he wants to introduce random weighting of the results, for example: 60% priority for engine 1
and 40% priority for engine 2. (this could for example mean that engine 1 got the first two results in the list).

Any search engine can be used (minimum 2) (Google and Bing have good and simple API's)
Focus should be spent on: Modularity, Extensibility and Readability.
*/

abstract class Search {
    private $position;
    private $reverse_position;

    private $results;
    abstract public function perform_search($search_query);
    abstract public function return_next_result($reverse);
};

class GoogleSearch extends Search {
    private $position = 0;
    private $reverse_position = 0;
    private $results = array();

    # Perform the search
    public function perform_search($search_query) {
        $search_query_enc = urlencode($search_query);
        $url = "https://ajax.googleapis.com/ajax/services/search/web?v=1.0&q={$search_query_enc}";
        $query_result = file_get_contents($url);
        $json = json_decode($query_result);

        $this->results = $json->responseData->results;
        $reverse_position = count($this->results);
        return $this->results;
    }

    # Get the next highest result from the results object
    public function return_next_result($reverse) {
        if ($reverse) {
            if ($this->reverse_position > 0) {
                $return_value = $this->results[$this->position];
                $this->position -= 1;
                return $return_value;
            } else {
                return false;
            }
        } else {

            if (count($this->results) > $this->position) {
                $return_value = $this->results[$this->position];
                $this->position += 1;
                return $return_value;
            } else {
                return false;
            }
        }
    }
};

class BingSearch extends Search {
    private $position = 0;
    private $reverse_position = 0;

    private $results = array();

    # Perform the search
    public function perform_search($search_query) {
        $acctKey = '5uZKxv4C12/wIyYFY+K6iuDSUJsi0Gggvf3tE8rooto';
        $rootUri = 'https://api.datamarket.azure.com/Bing/Search';

        // Encode the query and the single quotes that must surround it.
        $search_query_enc = urlencode("'{$search_query}'");

        // Construct the full URI for the query.
        $requestUri = "{$rootUri}/Web?\$format=json&Query={$search_query_enc}";

        // Encode the credentials and create the stream context.
        $auth = base64_encode("$acctKey:$acctKey");
        $data = array(
            'http' => array(
                'request_fulluri' => true,
                'ignore_errors' => true,
                'header' => "Authorization: Basic $auth")
        );

        $context = stream_context_create($data);
        $response = file_get_contents($requestUri, 0, $context);

        $json = json_decode($response);

        $this->results = $json->d->results;
        $reverse_position = count($this->results);
        return $this->results;
    }

    # Get the next highest result from the results object
    public function return_next_result($reverse) {
        if ($reverse) {
            if ($this->reverse_position > 0) {
                $return_value = $this->results[$this->position];
                $this->position -= 1;
                return $return_value;
            } else {
                return false;
            }
        } else {

            if (count($this->results) > $this->position) {
                $return_value = $this->results[$this->position];
                $this->position += 1;
                return $return_value;
            } else {
                return false;
            }
        }
    }

};


/*
 * Takes an array of search engines to use
 * Only 'google' and 'bing' are implemented.
 */

function get_search_engine_results($search_query, $engine_array, $weight_array = null, $reverse = 0)
{

    foreach ($engine_array as $engine_type) {
        switch ($engine_type) {
            case 'google':
                $engine_object = new GoogleSearch();
                break;

            case 'bing':
                $engine_object = new BingSearch();
                break;

            default:
                return 0;
        }

        $res = $engine_object->perform_search($search_query);
        $object_array[] = $engine_object;
    }

    # Construct the return array

    $return_array = array();
    $no_more_results = 0;

    if ($weight_array) {
        # Return results in a weighted fashion
        while ($no_more_results == 0) {
            $rand = mt_rand(1, (int)array_sum($weight_array));
            foreach ($weight_array as $key => $value) {
                $rand -= $value;
                if ($rand <= 0) {
                    $result = $object_array[$key]->return_next_result($reverse);
                    break;
                }
            }
            if ($result) {
                $return_array[] = $result;
            } else {
                $no_more_results = 1;
            }
        }

    } else {
        # Return normal list of results ( [Response 0 for engine 0], [Response 0 for engine 1],
        # [Response 1 for engine 0]. [Response 1 for engine 1] ..... [Response n for engine 2] )
        while ($no_more_results == 0) {
            foreach ($object_array as $search_object) {
                $result = $search_object->return_next_result($reverse);
                if ($result) {
                    $return_array[] = $result;
                } else {
                    $no_more_results = 1;
                }
            }
        }
    }

    return $return_array;
}

$engine_array = array('google', 'bing');
$weight_array = array(1, 4);

$result = get_search_engine_results('test', $engine_array, $weight_array);
print_r($result);
