<?
class Container extends eventListener
{
	use HitCache;
	
	private static $_instance=null;
	
	protected $userList=array();
	protected $gameList=array();
	protected $eventList=array();
	
	const LOG_LENGTH=100;
	const E_SYSTEM='system';
	const E_USER_ADD='new_user';
	const E_GAME_ADD='new_game';
	const E_GAME_STEP='game_step';               // send data from user to game
	const E_GAME_PROGRESS='game_progress';        // send data from game to users
	const E_CHAT_MESS='chatMess';
	
	private function __construct()
	{
	}
	
	public static function getInstance()
	{
		if(self::$_instance==null)
		{
			self::$_instance=new self();
			self::$_instance->setEvent(container::E_SYSTEM, 'systemEvent');
			self::$_instance->setEvent(container::E_USER_ADD, 'addUser');
			self::$_instance->setEvent(container::E_GAME_ADD, 'addGame');
		}
		return self::$_instance;
	}
	
	
	protected function systemEvent()
	{
	}
	
	
	
	protected function addUser(&$user)
	{
		$this->userList[$user->getLogin()]=$user; 
	}
	
	protected function addGame(&$game)
	{
		$this->gameList[$game->getId()]=$game; 
	}
	
	
	public function getUserList()
	{
		return array_keys($this->userList);
	}
	
	
	public function registerEvent($object, $type)
	{
		$this->eventList[$type][]=$object;
	}
	
	
	public function unRegisterEvent($object, $type=null)
	{
		if(!$type)
			$type=array_keys($this->eventList);
		else
			$type=array($type);
		foreach($type as $eventName)
		{
			foreach($this->eventList[$eventName] as $key=>$event)
			{
				if($event==$object)
					unset($this->eventList[$eventName][$key]);
			}
		}
	}
		
	public function addEvent($type, &$mess, $fromUser=null)
	{
		$event=array('mess'=>$mess, 'type'=>$type);
		if($fromUser)
			$event['user']=$this->userList[$fromUser];
		
			
		//print_r($event);
		if(count($this->eventList[$type])>0)
			foreach($this->eventList[$type] as $object)			
				$object->listenEvent($event);

	}
	
	
	public function getUserByLogin($login)	
	{
		return $this->userList[$login];
	}
}
?>
