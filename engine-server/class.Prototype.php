<?php 
/**
 * @desc       Класс для реализации дочерними объектами прототипа
 * @package    w framework
 * @category   engine
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       ProIterator, ArrayAccess, IteratorAggregate
 * @version    0.1.0
 */
class Prototype implements ArrayAccess,IteratorAggregate {
	
 protected $data = array();
	
    public function __construct(array $data = array()) {
        foreach ($data as $key=>$value) $this->offsetSet($key,$value);
    }

    public function offsetSet($offset,$data) {
        if (is_array($data)) $data = new self($data);
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

    public function  getIterator() {
        return new ProIterator($this->data);
    }
    
    public function __toString() {
    	return '[Prototype]';
    }
	
}

?>
