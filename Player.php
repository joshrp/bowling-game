<?php
class Player{
	private $_scores		= array();		
	private $_currentFrame	= 0;
	private $_currentRoll	= 0;
	
	/**
	 * Get cumulative score at a given frame
	 * Rules: 
		For each given frame, add both rolls together,
		If the first roll is a strike
			We need to get the next two rolls
				(which could cross 2 frames if the next roll is a strike too)
			And add those too (max of 30 per frame, for a stike followed by 2 more \
			strikes)
		If the frame is a spare (both rolls total 10)
			We need to grab the next roll and add that too
		
		A special case exists in the last frame whereby if the second roll creates a \
		spare or is a strike itself, a 3rd roll can be played this is then ONLY used \
		to add to the previous spare/strike and not added itself.

		If, when trying to get a future roll, it doesn't exist then we should return \
		the score before this frame, as this frame is "incomplete"
			
	 * @param int $frame frame number, zero indexed, default to all frames
	 * @return int score
	 */
	public function getScore($frame=false){
		if ($frame === false)
			$frame = count($this->_scores);
		
		// Get the score set to work with
		$scores = array_slice($this->_scores, 0, $frame);
		$score = 0;
		foreach($scores as $frameNum => $rolls)
		{				
			// If frame is Strike
			// If it's a strike, we don't have a second roll so skip that
			if ($rolls[0] === 10)
			{
				// If we're in the final frame, check for the next two rolls
				if ( $frameNum === 9 )
				{					
					// If they don't exists, this frame is unfinished, return current score only
					// Otherwise, add them
					if ( $rolls[1] === NULL || $rolls[2] === NULL )
						return $score;
					else
					{
						$score += $rolls[1];
						$score += $rolls[2];
					}
				}
				// Otherwise, grab the next two rolls, and add those too
				else
				{
					// Check for next frame existence		
					$nextFrame = $frameNum+1;
					if ( isset($scores[$nextFrame]) )
					{
						// Grab the next frame and set the first roll
						$nextFrame = $scores[$nextFrame];
						$firstRoll = $nextFrame[0];

						// If the first roll was a strike, or the second roll in this frame is null
						// And there's a frame after this one
						// Then use the next frames first roll
						// Otherwise use this frames second roll 
						if ( $firstRoll === 10 && isset($scores[$frameNum+2]) )
							$secondRoll = $scores[$frameNum+2][0];
						else
							$secondRoll = $nextFrame[1];

						// Only add the scores if we have them both,
						// If we're missing one, we should exit here to prevent further scores adding
						// No half jobs :)
						if ( $firstRoll !== NULL && $secondRoll !== NULL )
						{						
							$score += $firstRoll;
							$score += $secondRoll;
						}
						else
							return $score;					
					}
					else
						return $score;
				}
			}
			// Else, add first roll
			else
			{
				// If frame is a spare then grab the next roll and add that
				if ( $rolls[0] + $rolls[1] === 10 )
				{
					if ( $frameNum === 9 )
					{
						if ( $rolls[2] === NULL )
							return $score;
						else
							$score += $rolls[2];
					}
					else
					{
						// If we have a score, add it,
						// Otherwise drop out before adding anything from this frame
						if ( isset($scores[$frameNum+1]) && $scores[$frameNum+1] !== NULL )
						{
							$score += $scores[$frameNum+1][0];
						}
						else
							return $score;
					}
				}
				$score += $rolls[1];				
			}
			
			$score += $rolls[0];
		}
		return $score;
	}
	
	/**
	* @param int $pins - The number of Pins knocked down
	* @throws InvalidArgumentException - Argument exception are thrown if invalid $frame parameter is passed
	* @return Player $this
	*/
	public function addRoll($pins){
		// Verify valid score
		if ( !is_int($pins) || $pins > 10 || $pins < 0 )
			throw new InvalidArgumentException('Can only score between 0 and 10 inclusive in a given roll, received:'.$pins);
		
		// If we're on the last frame, check the last score hasn't been posted
		if ( $this->_currentFrame >= 9 && $this->_currentRoll === 2 && $this->_scores[$this->_currentFrame][2] !== NULL )			
			throw new InvalidArgumentException('Already at end of game');
		
		// If we're in the last frame, check we're allowed to post the final score
		// Only when the second roll is a strike, or a spare can we post the 3rd
		if ( 
				$this->_currentFrame >= 9 
				&& 
				$this->_currentRoll === 2 
				&& 
				!(		$this->_scores[$this->_currentFrame][1] === 10
						||
						$this->_scores[$this->_currentFrame][0] + $this->_scores[$this->_currentFrame][1] === 10
				) 
			)
			throw new InvalidArgumentException('Already at end of game, cannot post 3rd score in final frame unless a strike or spare was posted');
		
		$this->_addScore($pins);		
		
		// Check the given score for this roll doesn't take the frame score over 10
		if ( $this->_currentFrame !== 9 && $this->_currentRoll === 1 && ($this->_scores[$this->_currentFrame][0] + $pins > 10) )
			throw new InvalidArgumentException('Current Frame will have more than 10 Points, First Roll: '.$this->_scores[$this->_currentFrame][0].' Plus Argument: '.$pins);
		
		// Check the given score for this roll doesn't take the frame score over 10 if we're in frame 10 and on a new "sub-frame" after a strike
		if ( $this->_currentRoll === 2 && $this->_scores[$this->_currentFrame][1] !== 10 && $this->_scores[$this->_currentFrame][0] === 10 && ($this->_scores[$this->_currentFrame][1] + $pins > 10) )
			throw new InvalidArgumentException('Current Frame will have more than 10 Points, First Roll: '.$this->_scores[$this->_currentFrame][0].' Plus Argument: '.$pins);
		
		// If we're on the last frame, just increment the role by 1
		if ( $this->_currentFrame === 9 )
		{
			$this->_currentRoll++;
		}
		// Otherwise, if its a strike, or we're on the second roll
		// Then progress a frame and reset the role counter
		elseif ( $pins === 10 || $this->_currentRoll === 1 )
		{			
			$this->_currentFrame++;
			$this->_currentRoll = 0;			
		}		
		// Otherwise progress to second roll
		else
			$this->_currentRoll = 1;
		
		return $this;
	}
	
	/**
	 * Internal helper function to add score to current roll
	 * Creates any frame that doesn't exist
	 * @param int $score 
	 */
	private function _addScore($score)
	{
		$frame = $this->_currentFrame;
		$roll = $this->_currentRoll;
		if ( !isset($this->_scores[$frame]) )
			$this->_scores[$frame] = array(null, null);
		
		if ($frame === 9)
			$this->_scores[$frame][] = null;
		
		$this->_scores[$frame][$roll] = $score;
	}
}
