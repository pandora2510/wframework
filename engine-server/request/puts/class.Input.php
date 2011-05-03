<?php
/**
 * @desc       Класс для получения входных данных из потока
 * @package    w framework
 * @category   engine/request/puts
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, Validate, ArrayAccess, ErrorException, Prototype
 * @version    0.2.2
 */
class Input extends Object implements ArrayAccess {

 protected $data = array();

    public function __init($a) {
        try {
            if(!($this->PRO['extension']['validate'] instanceof Validate) and $this->SETT['validate'])
                throw new InputException('Object:extension/validate - IS NOT AN INSTANCEOF OF CLASS: "Validate"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(InputException $e) {
            $this->PRO['error']->handlerror($e);
        }
        if(!is_array($a)) return;
        foreach($a as $key=>$value) $this->offsetSet($key,$value);
    }

    public function offsetSet($offset,$data) {
        if (is_array($data)) $data = new Prototype ($data);
        if ($offset === null) {
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

    public function __toString() {
    	return '[Globals]';
    }

    public function validate() {// type, index, namevalid, default, arg
        $arg = func_get_args();
        if(!isset($arg[2])) return;
        $v = $this->PRO['extension']['validate'];
        if(!isset($arg[1]) or is_null($arg[1])) {
            $this[$arg[0]] = $v->__call($arg[2],array($this->data[$arg[0]],isset($arg[3])?$arg[3]:null,isset($arg[4])?$arg[4]:null));
        } else {
            $this[$arg[0]][$arg[1]] = $v->__call($arg[2],array($this->data[$arg[0]][$arg[1]],isset($arg[3])?$arg[3]:null,isset($arg[4])?$arg[4]:null));
        }
    }

    public function transformation_files($index) {
        $files = $this[$index];
        $files1 = new Prototype(array());
        foreach($files as $k=>$val) {
            $files1[$k] = $this->_tf($val['name'],$val['type'],$val['size'],$val['error'],$val['tmp_name']);
        }
        $this[$index] = $files1;
    }

    protected function _tf($name,$type,$size,$error,$tmp_name) {// name,type,size,error,tmp_name
        if(!is_object($error)) {
            return array('name'=>$name,'type'=>$type,'size'=>$size,'error'=>$error,'tmp_name'=>$tmp_name);
        } elseif(is_array($error) or $error instanceof Prototype) {
            $arg = array();
            foreach($error as $k=>$val) {
                $arg[$k] = $this->_tf(isset($name[$k])?$name[$k]:null,isset($type[$k])?$type[$k]:null,isset($size[$k])?$size[$k]:null,$val,isset($tmp_name[$k])?$tmp_name[$k]:null);
            }
            return $arg;
        }
    }

    public function clean() {
        unset($_GET,$_POST,$_COOKIE,$_ENV,$_FILES,$_REQUEST,$_SERVER,$_SESSION);
    }

}
?>
