<?php
/**
 * Ethan Wright
 * Date: 7/20/2015
 * Laser maze coding challenge
 */


Class Maze
{
    private $width;
    private $height;
    private $start_x;
    private $start_y;
    private $start_direction;
    private $mirrors;

    function Maze($x, $y)
    {
        $this->width = $x;
        $this->height = $y;

        # Create mirror arrays
        $this->forward_mirrors = array();
        $this->backward_mirrors = array();

        # Null out the mirror array
        for ($mirror_x = 0; $mirror_x <= $x; $mirror_x++) {
            $this->mirrors[$mirror_x] = array();
            for ($mirror_y = 0;  $mirror_y < $y; $mirror_y++) {
                $this->mirrors[$mirror_x][$mirror_y] = '';
            }
        }
    }

    function forwardTransform($direction) {
        # Forward slash is swap N<->E and S<->W
        if ($direction == 'N') {
            return 'E';
        } elseif ($direction == 'E') {
            return 'N';
        } elseif ($direction == 'S') {
            return 'W';
        } elseif ($direction == 'W') {
            return 'S';
        }
        return '';
    }

    function backwardTransform($direction) {
        # Back slash is swap N<->W and S<->E
        if ($direction == 'N') {
            return 'W';
        } elseif ($direction == 'E') {
            return 'S';
        } elseif ($direction == 'S') {
            return 'E';
        } elseif ($direction == 'W') {
            return 'N';
        }
        return '';
    }

    function hasBackwardMirror($x, $y)
    {
        if ($this->mirrors[$x][$y] == '\\') {
            return true;
        } else {
            return false;
        }
    }

    function hasForwardMirror($x, $y)
    {
        if ($this->mirrors[$x][$y] == '/') {
            return true;
        } else {
            return false;
        }
    }

    public function setStart($x, $y, $direction)
    {
        $this->start_x = $x;
        $this->start_y = $y;
        $this->start_direction = $direction;
    }

    public function getStart()
    {
        return array($this->start_x, $this->start_y, $this->start_direction);
    }


    public function setMirror($x, $y, $type)
    {
        $this->mirrors[$x][$y] = $type;
    }

    public function shootLaser()
    {
        $direction = $this->start_direction;
        $x = $this->start_x;
        $y = $this->start_y;
        $count = 0;

        # Continue until an edge is crossed
        while (($x >= 0 && $x < $this->width) && ($y >= 0 && $y < $this->height)) {

            # Is there a mirror at these coordinates?
            if ($this->hasForwardMirror($x, $y)) {
                # Swap N/E and S/W
                $direction = $this->forwardTransform($direction);
            }

            if ($this->hasBackwardMirror($x, $y)) {
                # Swap N/W and S/E
                $direction = $this->backwardTransform($direction);
            }

            # Move the position
            if ($direction == 'N') {
                $y += 1;
            } elseif ($direction == 'E') {
                $x += 1;
            } elseif ($direction == 'S') {
                $y -= 1;
            } elseif ($direction == 'W') {
                $x -= 1;
            }

            # Are we stuck in a loop?
            # We will know if we are back on the starting coordinates heading in the starting direction
            if ($x == $this->start_x && $y == $this->start_y && $direction == $this->start_direction) {
                return array($count, -1, -1);
            }

            $count += 1;
        }

        # We went one square too far in order to find the grid exit, so go back one square
        $count -= 1;
        $x = $x < 0 ? 0 : $x;
        $x = $x >= $this->width ? $this->width - 1 : $x;
        $y = $y < 0 ? 0 : $y;
        $y = $y >= $this->height ? $this->height - 1 : $y;

        return array($count, $x, $y);
    }

};

########################
# Code entry block #
########################
if (!count(debug_backtrace())) {

    if (count($argv) > 2) {
        $input_file = $argv[1];
        $output_file = $argv[2];
    } else {
        # No input file or output file provided
        print "Usage: php maze ./path/to/input/file ./path/to/output/file\n";
        exit();
    }

    $input = fopen($input_file, 'r');
    while ($line = fgets($input)) {
        $coordinates = explode(' ', trim($line));
        $x = $coordinates[0];
        $y = $coordinates[1];

        # Grid dimensions
        if (count($coordinates) == 2) {
            $maze = new Maze($x, $y);

            # Player coordinates
        } elseif ($coordinates[2] == 'S') {
            $maze->setStart($x, $y, $coordinates[2]);

            # Mirror
        } elseif ($coordinates[2] == '/' || $coordinates[2] == '\\') {
            $maze->setMirror($x, $y, $coordinates[2]);

            # Invalid
        } else {
            print "Usage: php maze ./path/to/input/file ./path/to/output/file\n";
            fclose($input);
            exit();
        }
    }

# SHOOT!
    $result = $maze->shootLaser();

# Check if they got stuck in a loop and write to output file
    $output = fopen($output_file, 'w');
    if ($result[1] == -1) {
        fwrite($output, "{$result[0]}\r\nStuck in a loop");
    } else {
        fwrite($output, "{$result[0]}\r\n{$result[1]} {$result[2]}");
    }
    fclose($output);
    exit();
}