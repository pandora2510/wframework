<?php
/**
 * @desc       Базовый класс для формирования выходных данных
 * @package    w framework
 * @category   engine/request/document
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, ArrayAccess, DocException, Output
 * @version    0.4.2
 */
abstract class Document extends Object implements ArrayAccess {

 protected $data = array();
 protected $p = array();

    public function __init($a) {
        try {
            if(!($this->PRO['request']['output'] instanceof Output))
                throw new DocException('Object:request/output - IS NOT AN INSTANCEOF OF CLASS: "Output"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(DocException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }
    
    final public function param(array $arg) {
        $this->p = $arg;
    }
    
    abstract public function scheme($schema);

    // array access
    public function offsetSet($offset,$data) {
        if($offset) {
            $this->data[$offset] = $data;
        }
        $this->p = null;
    }

    public function offsetGet($offset) {
    	return isset($this->data[$offset])?$this->data[$offset]:null;
    }

    public function offsetExists($offset) {
    	return isset($this->data[$offset]);
    }

    public function offsetUnset($offset) {
        trigger_error('Appeal to the method - "offsetUnset" - banned',E_USER_ERROR);
    }

    public function  __toString() {
        return '[Document]';
    }

}
?>
