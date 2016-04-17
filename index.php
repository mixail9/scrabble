<?


trait HitCache
{
	protected function setHitCache($key, $data)
	{
		if(is_array($key))
			$key=implode('_', $key);
		$this->hisCacheData[$key]=$data;
	}
	
	protected function getHitCache($key)
	{
		return $this->hisCacheData[$key];
	}
	
	protected function clearHitCache()
	{
		$this->hisCacheData=array();
	}
}


abstract class EventListener
{
	protected $myEvents=array();	
	
	
	public function listenEvent($event)
	{
		if(!isset($this->myEvents[$event['type']]))
			return false;
		
		$mess=$event['mess'];
		$func=$this->myEvents[$event['type']];
		$this->$func($mess);
	}
	
		
	public function setEvent($eventName, $func)
	{
		$this->myEvents[$eventName]=$func;
		container::getInstance()->registerEvent($this, $eventName);
	}
	
	
	public function __dectructor()
	{
		$this->container->unRegisterEvent($this);
	}
}



spl_autoload_register(function ($class) {
    include $_SERVER['DOCUMENT_ROOT'].'/classes/' . strtolower($class) . '.php';
});
?>

<pre>
<?
$container=Container::getInstance();
$newUser=User::auth($container, 'mihail', '123');
$newUser=User::auth($container, 'nastya', '456');


$container->getUserByLogin('mihail')->sendChatMess('hello!');
$container->getUserByLogin('nastya')->sendChatMess('hi, mihail!');

$newGame=new Game(1, array($container->getUserByLogin('mihail'), $container->getUserByLogin('nastya')));



//$container->getUserByLogin('nastya')->sendGameStep('e2->e4');


$container->getUserByLogin('nastya')->sendGameStep(array(
	'user'=>'nastya', 
	'letters'=>array(
		array('x'=>10, 'y'=>10, 'l'=>'f'),
		array('x'=>11, 'y'=>10, 'l'=>'u'),
		array('x'=>12, 'y'=>10, 'l'=>'n')
	)
));

$container->getUserByLogin('mihail')->sendGameStep(array(
	'user'=>'mihail', 
	'letters'=>array(
		array('x'=>12, 'y'=>11, 'l'=>'u'),
		array('x'=>12, 'y'=>12, 'l'=>'t'),
		array('x'=>12, 'y'=>13, 'l'=>'s')
	)
));
print_r($newGame->getScoreList());

$newGame->renderMap('l');
?>
</pre>







