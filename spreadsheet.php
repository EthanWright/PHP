<?php

/*
 * Ethan Wright
 * 6/1/2015
 * Simple spreadsheet calculator
 * Usage: php spreadsheet.php <input_file_name>
 * Example: >>> php spreadsheet.php input.txt
*/

define('BINARYOPERANDS', '+-*/'); # Operands in use for this calculator
define('AASCII', 65); # Value of 'A' in ascii
define('DEBUG', 0);


###############################################################################
# class Expression
# Constructor takes a reference to the array of expressions in the spreadsheet,
# the number of rows, the number of columns, and the terms for the current cell.
###############################################################################
class Expression {

    private $state;
    private $terms;
    private $result;
    private $termQueue;
    private $stringTerms;
    private $rows;
    private $columns;
    private $expr_array;

    function Expression(&$expr_array, $rows, $columns, $stringTerms)
    {
        $this->terms = explode(' ', $stringTerms);
        $this->SetState('READY');
        $this->stringTerms = $stringTerms;
        $this->termQueue = new SplQueue();
        $this->rows = $rows;
        $this->columns = $columns;
        $this->expr_array = &$expr_array;
    }

    function SetState($state) {
        if (in_array($state, array('READY', 'EVALUATING', 'DONE'))) {
            $this->state = $state;
        } else {
            throw new Exception("Attempt to set invalid state");
        }
    }

    function EvaluateExpression() {
        $this->SetState('EVALUATING');

        foreach($this->terms as $term) {
            $this->ParseTerm($term);
            array_shift($this->terms);
        }

        # There should only be one value left in the queue
        if ($this->termQueue->count() != 1) {
            throw new Exception("There was not a proper amount of terms to evaluate this expression. Got: '{$this->stringTerms}'");
        }
        $this->result = $this->termQueue->pop();
        $this->SetState('DONE');
    }

    function ParseTerm($term) {

        # Term types are numeric value, operand (+, -, *, /), and a reference to another cell
        # Reverse Polish Notation for term grammar

        if ($this->IsNumeric($term)) {
            $this->termQueue->push($term);

        } elseif ($this->IsBinaryOperand($term)) {
            $result = $this->PerformBinaryOperation($term);
            $this->termQueue->push($result);

        } elseif ($this->IsReference($term)) {
            $result = $this->Dereference($term);
            $this->termQueue->push($result);

        } else {
            throw new Exception("Received invalid term: {$term}");
        }
    }

    function IsNumeric($term){
        if ($term[0] == '-') { # Negative
            return is_numeric((int)substr($term, 1));
        } else {
            return is_numeric($term);
        }
    }

    function IsBinaryOperand($term) {
        if (strpos(BINARYOPERANDS, $term) !== false) {
            return true;
        } else {
            return false;
        }
    }

    function IsReference($term){
        # Cells are referenced as <row><column>f
        # Rows can only be A-Z, therefore one character long. Columns have no constraints (1-n)

        if (strlen($term) < 2) {
            return false;
        }

        $row = $term[0];
        $column = substr($term, 1);

        if (ctype_alpha($row) && is_numeric($column)) {
            return true;
        } else {
            return false;
        }
    }

    function PerformBinaryOperation($operand){

        # Binary operands require two parameters, so pop twice
        $secondary = $this->termQueue->pop();
        $primary  = $this->termQueue->pop();

        switch ($operand) {
            case '+':
                return $primary + $secondary;
                break;
            case '-':
                return $primary - $secondary;
                break;
            case '*':
                return $primary * $secondary;
                break;
            case '/':
                return $secondary == 0 ? 0 : $primary / $secondary; # Division by 0 handling
                break;
            default:
                throw new Exception("Received invalid operand: {$operand}");
        }
    }

    function Dereference($cell) {
        # Dereference a reference to another cell

        # First, find the location in the array of cells
        $row = ord($cell[0]) - AASCII;
        $column = (int)substr($cell, 1);
        $position = ($row * $this->columns) + ($column - 1);

        # Get the value of the cell.
        $value = $this->expr_array[$position]->GetResult();
        return $value;

    }

    public function GetResult() {
        # Get the value of the cell. If it has not been evaluated, it will be done as needed

        # If the status is set to evaluating, that means there are cyclic dependencies in the spreadsheet,
        # as the previous values have not yet resolved.
        if ($this->state == 'EVALUATING') {
            throw new Exception("There are cyclic dependencies in this spreadsheet", 1);
        }

        if ($this->state == 'READY') {
            $this->EvaluateExpression();
        }

        if ($this->state == 'DONE') {
            return $this->result;
        } else {
            throw new Exception("Failure evaluating cell");
        }
    }

};

###############################################################################
# evaluateSpreadsheet takes pointer to file contain spreadsheet info
# Read from the file line by line and fill out the array of expressions.
# Then trigger the evaluation of each cell of the spreadsheet
###############################################################################

function evaluateSpreadsheet($file_name) {

    $handle = fopen($file_name, 'r');
    if (!$handle) {
        print "Error opening file.\n";
        return 0;
    }

    # Get the first line, which contains the dimensions of the spreadsheet
    if (($buffer = fgets($handle)) !== false) {
        $dimensions = explode(' ', $buffer);
        $columns = $dimensions[0];
        $rows = rtrim($dimensions[1]);
    } else {
        print "Error reading first line of spreadsheet. Expected '<num_columns> <num_rows>'\n";
        return 0;
    }

    $expr_array = array();
    while (($buffer = fgets($handle)) !== false) {
        $expr_array[] = new Expression($expr_array, $rows, $columns, rtrim($buffer));
    }
    fclose($handle);

    # Print out the results by lazy evaluation of the cells' expressions
    $output = "{$columns} {$rows}\n";
    foreach ($expr_array as $cell) {
        try {
            $result = $cell->GetResult();
        } catch (Exception $e) {
            print 'Caught exception: ' . $e->getMessage() . "\n";
            if ($e->GetCode() == 1) {
                return 1;
            } else {
                return 0;
            }
        }
        $output = $output .  sprintf("%.5f", $result) . "\n";
    }

    print rtrim($output);
    return 1;
}

###############################################################################
# Script entry
###############################################################################

# Get file name from command line
if (count($argv) > 1) {
    $file_name = $argv[1];
} else {
    print "No file name provided.\n";
    return 0;
}

# Call the evaluation function
return evaluateSpreadsheet($file_name);
