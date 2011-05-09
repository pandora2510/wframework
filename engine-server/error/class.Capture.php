<?php 
/**
 * @desc       Класс для захвата и возможности дальнейшей обработки возникнувших ошибок
 * @package    w farmework
 * @category   engine/error
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       ErrorException
 */
class Capture {// запоминать предидущий обработчик
	
 private $error = false;
	
    public function __construct() {
	$this->error = set_error_handler(array($this,'__capture'),error_reporting());
    }
	
    final public function __capture($errno,$errmsg,$filename,$linenum) {
	throw new ErrorException($errmsg,$errno,0,$filename,$linenum);
    }
	
    public function __destruct() {
	if($this->error)set_error_handler($this->error);
    }
	
}

?>
