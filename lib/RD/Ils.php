<?
class RD_Ils
{
	private function __construct() {} //prevent use of new

    /**
     * Initalize proper ILS object based on config setting
     *
     * @param  void
     * @return void
    */	
	public static function initILS()
	{		
		global $g_ils;
		
		$class_name = $g_ils['class_name'];
		$path = str_replace('_', '/', $class_name) . ".php";
		
		require_once("lib/$path");
		return new $class_name(null);
	}
}
?>