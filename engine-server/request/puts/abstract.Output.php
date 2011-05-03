<?php
/**
 * @desc       Базовый класс для формирования и вывода данных в поток
 * @package    w framework
 * @category   engine/request/puts
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object
 * @version    0.2.0
 */
abstract class Output extends Object {

 protected $body = '';
 protected $h = array();
 protected $c = array();

    abstract public function setHeader($name,$value,$add=true);

    abstract public function setCookie($name,$value='',$exp=0,$path='',$domain='',$secure=false,$http=false);

    abstract public function setFragment($str,$arg=null);

    abstract public function getsHeader();

    abstract public function getsBody();

    public function clean() {
        $this->body = '';
        $this->h = array();
        $this->c = array();
    }

}
?>
