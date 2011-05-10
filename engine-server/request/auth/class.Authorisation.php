<?php
/**
 * @desc       Класс для авторизации пользователей
 * @package    w framework
 * @category   engine/request/auth
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, ArrayAccess, Session, AuthException, Prototype, Input
 */
class Authorisation extends Object implements ArrayAccess {// учитывать IP6 в следующих версиях

 const IP = '999.999.999.999';
 protected $role = array();
 protected $data = array();
 private $oldIP = '0.0.0.0';
 private $write = false;

    public function  __init($a) {
        try {
            if(!$this->PRO['request']['session'] instanceof Session)
            throw new AuthException('Object:request/session - IS NOT AN INSTANCEOF OF CLASS: "Session"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(AuthException $e) {
            $this->PRO['error']->handlerror($e);
        }
        // 
        $this->role = isset($this->SETT['role'])?$this->SETT['role']:array(0=>0);
    }

    public function readData() {
        $this->data = ($this->PRO['request']['session'][$this->SETT['nameSystemData']] instanceof Prototype)?$this->PRO['request']['session'][$this->SETT['nameSystemData']]:new Prototype(array());
        unset($this->PRO['request']['session'][$this->SETT['nameSystemData']]);
        $this->_default();
    }

    protected function _default() {
        $this->oldIP = isset($this->data['ip'])?$this->data['ip']:self::IP;
        if(!isset($this->data['locale'])) $this->data['locale'] = $this->SETT['defLocale'];
        // проверить rememberME
        $this->rememberMe();//
        
        $this->data['ip'] = htmlspecialchars($this->getIP());
        if(!isset($this->data['privilege']) or $this->data['privilege'] < 0) $this->data['privilege'] = 0;
        $this->data['privilege'] = (int)$this->data['privilege'];
    }

    public function writeData() {
        $this->PRO['request']['session'][$this->SETT['nameSystemData']] = $this->data;
        $this->write = true;
    }

    protected function rememberMe() {
        if($this->oldIP === self::IP and is_file($this->SETT['rememberme'])) {
            $obj = $this->PRO;
            $data = include($this->SETT['rememberme']);
            if(!is_array($data) or !($data instanceof Traversable)) $data = (array)$data;
            foreach($data as $k=>$val) {
                $this->data[$k] = $val;
            }
        }
    }

    protected function getIP() {
        $s = $this->PRO['request']['input']['server'];
        if(!empty($s['HTTP_CLIENT_IP'])) {
            return $s['HTTP_CLIENT_IP'];
        } elseif(!empty($s['HTTP_X_FORWARDED_FOR'])) {
            return $s['HTTP_X_FORWARDED_FOR'];
        } elseif(!empty($s['REMOTE_ADDR'])) {
            return $s['REMOTE_ADDR'];
        } else {
            return '0.0.0.0';
        }
    }

    public function checkIP() {// true -  ip соответствует предидущему
        return ($this->data['ip'] == $this->oldIP and $this->oldIP !== self::IP)?true:false;
    }

    public function getListPrivilege() {// $this->role - просто список альтернативных названий
        return $this->role;
    }

    public function checkPrivilege($level,$dynamic=false) {
        if($dynamic) return 0;
        return ((int)$this->data['privilege']>=(int)$level);
    }

    public function getUserAgent() {
        // реализовать возврат браузера и os
        $s = $this->PRO['request']['input']['server'];
        return htmlspecialchars(isset($s['HTTP_USER_AGENT'])?$s['HTTP_USER_AGENT']:'NOT USER AGENT');
    }

    public function offsetSet($offset,$data) {
        if(is_array($data)) $data = new Prototype($data);
        if($offset === null) {
            $this->data[] = (string)$data;
        } else {
            $this->data[$offset] = (string)$data;
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
    	//trigger_error('Appeal to the method - "offsetUnset" - banned',E_USER_ERROR);
    }

    public function  __destruct() {
        if(!$this->write) $this->writeData();
    }

}
?>
