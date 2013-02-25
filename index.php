<?php
require_once('Player.php');

class PlayerTest extends PHPUnit_Framework_TestCase
{
	public $player;
	public function setUp(){
		$this->player = new Player;
	}

	/**
	* Test a simple score
	*/
	public function testSimples()
	{
		$this->player
				->addRoll(7)
				->addRoll(2);
		$this->assertEquals(9, $this->player->getScore());
	}

	/**
	* Test a strike in the second frame
	*/
	public function testStrike()
	{
		$this->player
				->addRoll(5)
				->addRoll(4)
				->addRoll(10)
				->addRoll(5)
				->addRoll(2);
		$this->assertEquals(33, $this->player->getScore());
	}
	
	/**
	* Test an incomplete strike, 
	* this should return the score before the strike was thrown
	*/
	public function testUnffinishedStrike()
	{
		$this->player
				->addRoll(10)
				->addRoll(5);
		$this->assertEquals(0, $this->player->getScore());
	}
	
	/**
	* Test a spare in the first frame
	*/
	public function testSpare()
	{
		$this->player
				->addRoll(5)
				->addRoll(5)
				->addRoll(5)
				->addRoll(3);
		$this->assertEquals(23, $this->player->getScore());
	}

	/**
	* Test an incomplete spare,
	* This should return the score before the frame was started
	*/
	public function testUnfinishedSpare()
	{
		$this->player
				->addRoll(5)
				->addRoll(5);
		$this->assertEquals(0, $this->player->getScore());
	}
	
	/**
	 * Test a top score game of 300 
	 */
	public function testTopScore()
	{		
		$this->player
				->addRoll(10)
				->addRoll(10)
				->addRoll(10)
				->addRoll(10)
				->addRoll(10)
				->addRoll(10)
				->addRoll(10)
				->addRoll(10)
				->addRoll(10)
				->addRoll(10)
				->addRoll(10)
				->addRoll(10);
		$this->assertEquals(300, $this->player->getScore());
	}
	
	/**
	 * Test a gutter game where no score was acheived
	 */
	public function testGutterGame()
	{		
		$this->player
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0)
				->addRoll(0);
		$this->assertEquals(0, $this->player->getScore());
	}
	
	/**
	 * Test the final frame logic where an extra roll is awarded on rolling a spare
	 */
	public function testFinalFrameSpare()
	{		
		$this->player
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(8)
				->addRoll(2);
		$this->assertEquals(48, $this->player->getScore());
	}
	
	/**
	 * @expectedException InvalidArgumentException
 	 * Test an invalid final frame where a 3rd roll is posted when it's not allowed
	 */
	public function testInvalidFinalFrame()
	{		
			$this->player
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(2)
				->addRoll(3)
				->addRoll(2);
		$this->assertEquals(48, $this->player->getScore());		
	}
} 
