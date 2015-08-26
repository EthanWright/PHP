<?php
/**
 * Ethan Wright
 * Date: 7/20/2015
 * Laser maze coding challenge test
 */

include 'maze.php';

class MazeTest extends PHPUnit_Framework_TestCase {

    function testSetMirror() {
        $maze = new Maze(2, 2);

        $maze->setMirror(1, 1, '/');
        $this->assertTrue($maze->hasForwardMirror(1, 1));
        $this->assertFalse($maze->hasBackwardMirror(1, 1));

        $maze->setMirror(1, 2, '\\');
        $this->assertFalse($maze->hasForwardMirror(1, 2));
        $this->assertTrue($maze->hasBackwardMirror(1, 2));

    }

    function testSetStart() {
        $maze = new Maze(2, 2);
        $maze->setStart(2, 2, 'S');
        $results = $maze->getStart();
        $this->assertEquals($results[0], 2);
        $this->assertEquals($results[1], 2);
        $this->assertEquals($results[2], 'S');
    }

    function testForwardTransform() {
        $maze = new Maze(2, 2);

        $this->assertEquals($maze->forwardTransform('N'), 'E');
        $this->assertEquals($maze->forwardTransform('E'), 'N');
        $this->assertEquals($maze->forwardTransform('S'), 'W');
        $this->assertEquals($maze->forwardTransform('W'), 'S');
    }

    function testBackwardTransform() {
        $maze = new Maze(2, 2);

        $this->assertEquals($maze->backwardTransform('N'), 'W');
        $this->assertEquals($maze->backwardTransform('E'), 'S');
        $this->assertEquals($maze->backwardTransform('S'), 'E');
        $this->assertEquals($maze->backwardTransform('W'), 'N');
    }

    function testShootLaser() {

        $maze = new Maze(3, 3);
        $maze->setStart(1, 2, 'S');

        # Test no mirror
        $results = $maze->shootLaser();

        $this->assertEquals(2, $results[0]);
        $this->assertEquals(1, $results[1]);
        $this->assertEquals(0, $results[2]);

        # Test 1 mirror
        $maze->setMirror(1, 1, '\\');
        $results = $maze->shootLaser();

        $this->assertEquals(2, $results[0]);
        $this->assertEquals(2, $results[1]);
        $this->assertEquals(1, $results[2]);

        # Test cycle
        $maze->setStart(1, 2, 'E');
        $maze->setMirror(2, 2, '\\');
        $maze->setMirror(2, 0, '/');
        $maze->setMirror(0, 0, '\\');
        $maze->setMirror(0, 2, '/');
        $results = $maze->shootLaser();

        $this->assertEquals(7, $results[0]);
        $this->assertEquals(-1, $results[1]);
        $this->assertEquals(-1, $results[2]);
    }

}
