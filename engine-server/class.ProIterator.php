<?php
/**
 * @desc       Класс для перебора элементов Prototype
 * @package    w framework
 * @category   engine
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Iterator	
 * @version    0.1.0
 */
class ProIterator implements Iterator {

 protected $data = array();
 private $each = array();

    public function  __construct(array $data) {
        $this->data = $data;
    }

    public function rewind() {
        reset($this->data);
        $this->each = array(0=>null,1=>null,'key'=>null,'value'=>null);
    }

    public function current() {
        return $this->each['value'];
    }

    public function key() {
    	return $this->each['key'];
    }

    public function next() {

    }

    public function valid() {
    	$this->each = each($this->data);
    	return is_array($this->each);
    }

    public function __toString() {
    	return '[ProIterator]';
    }

}
?>
