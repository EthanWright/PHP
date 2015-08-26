<?php

/*
 * Ethan Wright
 * 6/22/2015
 * https://www.thumbtack.com/challenges/simple-database
 * Reads from stdin unless a file is provided
 * Usage: php thumbtack.php <filename>
*/

define('DEBUG', 0);

Class Database {

    # Multi level array for transaction recording.
    private $history;

    # Array of Variables => Values
    private $database;

    private $currentTransactionBlock;

    function Database() {
        $this->history = array();
        $this->history[0] = array();
        $this->database = array();
        $this->currentTransactionBlock = 0;
    }

    public function execute($command) {

        $params = explode(' ', $command);

        switch ($params[0]) {
            case 'SET':
                $this->setValue($params[1], $params[2]);
                break;

            case 'GET':
                print $this->getValue($params[1]);
                break;

            case 'UNSET':
                $this->setValue($params[1], NULL);
                break;

            case 'NUMEQUALTO':
                print $this->getNumberEqual($params[1]);
                break;

            case 'BEGIN':
                $this->beginBlock();
                break;

            case 'ROLLBACK':
                $this->rollbackBlock();
                break;

            case 'COMMIT':
                $this->commitAllBlocks();
                break;

            default:
                print "Error parsing command {$command}";
                break;
        }
        print "\n";

        return;
    }

    private function getValue($name) {
        if (array_key_exists($name, $this->database)) {
            return $this->database[$name];
        } else {
            return 'NULL';
        }
    }

    private function setValue($name, $value)
    {
        # Record transaction history if we are in a transaction block
        if ($this->currentTransactionBlock > 0) {
            if (array_key_exists($name, $this->database)) {
                if (!array_key_exists($name, $this->history[$this->currentTransactionBlock])) {
                    # If it's in the DB but has no transaction history for this block, add the initial value
                    $this->history[$this->currentTransactionBlock][$name] = $this->database[$name];
                }
            } else {
                # Put null if it didn't exist before this block
                $this->history[$this->currentTransactionBlock][$name] = NULL;
            }
        }

        $this->dbWrite($name, $value);
        return;
    }

    private function dbWrite($name, $value) {
        # Set the new value in the "DB"
        if ($value) {
            $this->database[$name] = $value;
        } else {
            unset($this->database[$name]);
        }
    }

    private function getNumberEqual($target) {
        # How many entries have the target as their value?
        $found = 0;
        foreach($this->database as $value) {
            if ($value == $target) {
                $found += 1;
            }
        }
        return $found;
    }

    private function beginBlock() {
        # Start a new array in the history block
        $this->currentTransactionBlock += 1;
        $this->history[$this->currentTransactionBlock] = array();
    }

    private function rollbackBlock() {
        # Quit out if there are no transaction blocks going on
        if ($this->currentTransactionBlock == 0) {
            print "NO TRANSACTION";
            return;
        }

        # Rollback to a previous array in the history block
        foreach($this->history[$this->currentTransactionBlock] as $name => $value) {
            $this->dbWrite($name, $value);
        }
        unset($this->history[$this->currentTransactionBlock]);
        $this->currentTransactionBlock -= 1;
    }

    private function commitAllBlocks() {
        # Commit all changes by resetting the history block
        $this->history = array();
        $this->currentTransactionBlock = 0;
    }
};

########################
# Main execution block #
########################

$db = new Database();

# If no file is provided, read from stdin
if (count($argv) > 1) {
    $input = fopen($argv[1], 'r');
} else {
    $input = fopen('php://stdin', 'r');
}

while($line = fgets($input)) {
    $command = trim($line);
    if ($command == 'END') {
        fclose($input);
        exit();
    } elseif ($command) {
        $db->execute($command);
    }
}
