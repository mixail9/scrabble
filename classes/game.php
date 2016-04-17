<?
class Game extends eventListener
{
	use HitCache;
	
	const MAP_FILE='scrabble';

	protected $id=0;
	protected $userList=array();
	protected $container;
	protected $eventToFunc=array(container::E_SYSTEM=>'systemEvent', container::E_GAME_STEP=>'step');
	protected $map=array();
	protected $letters=array();
	protected $scoreList=array();
	protected $wordList=array();
	
	function __construct($id, $userList)
	{
		$this->container=container::getInstance();
		$this->id=$id;
		foreach($userList as $user)
		{
			if($user->goToGame($this))
				$this->userList[$user->getLogin()]=$user;
		}
		$this->setEvent(container::E_SYSTEM, 'systemEvent');
		$this->setEvent(container::E_GAME_STEP.'_'.$this->id, 'step');
		
		$this->initMap();
		
		foreach($this->userList as $userName=>&$user)
			$this->scoreList[$userName]=0;
	}
	
	
	/*
	* map keys
	* 		l => current letter
	* 		p => cell price
	* 		gp => whole word price
	*/
	protected function initMap()
	{
		if(!file_exists($_SERVER['DOCUMENT_ROOT'].'/maps/'.static::MAP_FILE.'.map'))
			return false;
			
		$map=json_decode(file_get_contents($_SERVER['DOCUMENT_ROOT'].'/maps/'.static::MAP_FILE.'.map'), true);
		$this->map=$map['map'];
		$this->letters=$map['letters'];
	}
	
	
	public function getId()
	{
		return $this->id;
	}
	
	
	
	protected function step($mess)
	{		
		if(empty($mess['user']))
			return new Result(1, 'not isset user');
			
		if(empty($mess['letters']))
			return new Result(1, 'haven\'t letters');
			
		if(!$this->reduceLetters($mess['letters'], 1))
			return new Result(1, 'haven\'t letters');
			
		$map=$this->map;
			
		foreach($mess['letters'] as $letter)
		{
			if(!empty($map[$letter['x']][$letter['y']]['l']))
				return new Result(1, 'cell must to be empty');
				
			$map[$letter['x']][$letter['y']]['l']=$letter['l'];
		}
		
		$currentWords=array();
		
		foreach($mess['letters'] as $letter)
		{
			// horizontal
			$word=$map[$letter['x']][$letter['y']]['l'];
			$score=$map[$letter['x']][$letter['y']]['p'] * $this->letters[$letter['l']]['p'];
			$multWord=1;
			
			$i=1;
			while(!empty($map[$letter['x']+$i][$letter['y']]['l']))
			{
				$word=$word . $map[$letter['x']+$i][$letter['y']]['l'];
				$score+=$map[$letter['x']+$i][$letter['y']]['p'] * $this->letters[$letter['l']]['p'];
				if(!empty($map[$letter['x']+$i][$letter['y']]['gp']))
					$multWord=$map[$letter['x']+$i][$letter['y']]['gp'];
				$i++;
			}
			
			$i=1;
			while(!empty($map[$letter['x']-$i][$letter['y']]['l']))
			{
				$word=$map[$letter['x']-$i][$letter['y']]['l'] . $word;
				$score+=$map[$letter['x']-$i][$letter['y']]['p'] * $this->letters[$letter['l']]['p'];
				if(!empty($map[$letter['x']-$i][$letter['y']]['gp']))
					$multWord=$map[$letter['x']-$i][$letter['y']]['gp'];
				$i++;
			}
			if(strlen($word)>1)	
			{
				if(in_array($word, $this->wordList))
					return new Result(1, 'word already exist');
				$currentWords[$word]=$score * $multWord;
			}
				
			// vertical
			$word=$map[$letter['x']][$letter['y']]['l'];
			$score=$map[$letter['x']][$letter['y']]['p'] * $this->letters[$letter['l']]['p'];
			$multWord=1;
			
			$i=1;
			while(!empty($map[$letter['x']][$letter['y']+$i]['l']))
			{
				$word=$word . $map[$letter['x']][$letter['y']+$i]['l'];
				$score+=$map[$letter['x']][$letter['y']+$i]['p'] * $this->letters[$letter['l']]['p'];
				if(!empty($map[$letter['x']][$letter['y']+$i]['gp']))
					$multWord=$map[$letter['x']][$letter['y']+$i]['gp'];
				$i++;
			}
			
			$i=1;
			while(!empty($map[$letter['x']][$letter['y']-$i]['l']))
			{
				$word=$map[$letter['x']][$letter['y']-$i]['l'] . $word;	
				$score+=$map[$letter['x']][$letter['y']-$i]['p'] * $this->letters[$letter['l']]['p'];
				if(!empty($map[$letter['x']][$letter['y']-$i]['gp']))
					$multWord=$map[$letter['x']][$letter['y']-$i]['gp'];
				$i++;
			}
			if(strlen($word)>1)	
			{
				if(in_array($word, $this->wordList))
					return new Result(1, 'word already exist');
				$currentWords[$word]=$score * $multWord;
			}
		}
		
		$this->map=$map;
		$this->reduceLetters($mess['letters'], 0);	
		
		$this->wordList=array_merge($this->wordList, $currentWords);
		foreach($currentWords as $score)
			$this->addScore($mess['user'], $score);
		
		$this->container->addEvent(container::E_GAME_PROGRESS.'_'.$this->id, $mess);
	}
	
	
	protected function reduceLetters($letters, $check=1)
	{
		$curLetters=array();
		foreach($letters as $letter)
			$curLetters[$letter['l']]++;
			
		foreach($curLetters as $letter=>$count)
		{
			if($check)
			{
				if($this->letters[$letter]['c']<$count)
					return false;
			}
			else
				$this->letters[$letter]['c']-=$count;
		}
		return true;
	}
	
	
	protected function addScore($user, $score)
	{
		$this->scoreList[$user]+=$score;
	}
	
	
	public function getScoreList()
	{
		return $this->scoreList;
	}
	
	public function renderMap($mode='l')
	{
		print '<table border=1>';
		foreach($this->map as $row)
		{
			print '<tr>';
				foreach($row as $cell)
					print '<td>' . (((!empty($cell[$mode]))&&($cell[$mode]!='')) ? $cell[$mode] : '&nbsp;' ) . '</td>';
			print '</tr>';
		}
		print '</table>';
	}
	
	
	protected function systemEvent()
	{
	}
	
}

?>
