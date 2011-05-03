<?php
/**
 * @desc       Базовый класс для работы с сесиями
 * @package    w framework
 * @category   engine/request/session
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, ArrayAccess, Input, Prototype, Output, SessionException
 * @version    0.2.1
 */
abstract class Session extends Object implements ArrayAccess, IteratorAggregate {

 protected $data = array();
 protected $id = null;
 private $comit = false;

    public function __init($a) {
        try {
            if(!$this->PRO['request']['input'] instanceof Input)
                throw new SessionException('Object:request/input - IS NOT AN INSTANCEOF OF CLASS: "Input"',E_USER_ERROR,0,__FILE__,__LINE__);
            if(!$this->PRO['request']['output'] instanceof Output)
                throw new SessionException('Object:request/output - IS NOT AN INSTANCEOF OF CLASS: "Output"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(SessionException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }

    public function getSystemId() {
        return array($this->SETT['name']=>$this->id);
    }

    public function getId() {
        return $this->id;
    }

    public function begin() {
        // получить id cookie или же get иначе генерировать свой
        $inp = $this->PRO['request']['input'];
        if($inp['cookie'][$this->SETT['name']]) {
            $this->id = $inp['cookie'][$this->SETT['name']];
        } elseif($this->SETT['get'] and $inp['get'][$this->SETT['name']]) {
            $this->id = $inp['get'][$this->SETT['name']];
        } else {
            $this->id = $this->generate();
        }
        // отправить id в cookie или get
        $this->PRO['request']['output']->setCookie($this->SETT['name'],$this->id,
                $this->SETT['cookielifetime'],$this->SETT['cookiepath'],$this->SETT['cookiedomain'],
                $this->SETT['cookiesecure'],$this->SETT['cookiehttponly']);

        if(mt_rand(1,100) <= $this->SETT['gs']) $this->_gs();
        $this->_open();
        $this->_read();
    }

    protected function generate() {
        return md5(microtime().mt_rand(0,9999));
    }

    public function destroy() {
        $this->_destroy();
        $this->data = array();
        $this->id = null;// или ''
        $this->PRO['request']['input']['cookie'][$this->SETT['name']] = null;
        if($this->SETT['get']) $this->REQ['input']['get'][$this->SETT['name']] = null;
    }

    public function comit() {
        $this->_write();
        $this->comit = true;
    }

    public function access_once($key) {
        $val = $this->data[$key];
        unset($this->data[$key]);
        return $val;
    }

    public function offsetSet($offset,$data) {
        if(is_array($data)) $data = new Prototype($data);
        if($offset === null) {
            $this->data[] = $data;
        } else {
            $this->data[$offset] = $data;
        }
    }

    public function offsetGet($offset) {
    	return isset($this->data[$offset])?$this->data[$offset]:null;
    }

    public function offsetExists($offset) {
    	return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
    	unset($this->data[$offset]);
    }

    public function  getIterator() {
        return new ProIterator($this->data);
    }

    public function __toString() {
    	return '[Session]';
    }

    abstract protected function _open();

    abstract protected function _gs();

    abstract protected function _read();

    abstract protected function _write();

    abstract protected function _destroy();

    abstract protected function _close();

    public function __destruct() {
        if(!$this->comit) $this->comit();
        $this->_close();
    }

}
?>
