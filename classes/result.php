<?
class Result
{
	protected $error='';
	protected $resultCode;
	
	function __construct($code, $message='')
	{
		$this->resultCode=$code;
		$this->error=$message;
	}	
	
	public function isSuccess()
	{
		return ($this->resultCode==0);
	}
	
	public function getErrorMessage()
	{
		return $this->error;
	}
}
?>
