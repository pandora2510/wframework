<?php 
/**
 * @desc       Базовый класс для взаимодействия с глобальным объектом
 * @package    w framework
 * @category   engine
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Prototype
 * @version    0.3.0
 */
abstract class Object {
	
 protected $SETT = array();
 protected $PRO = null;
 protected $REQ = null;
	
    final public function __construct(Prototype $o,$a=null) {
	$this->PRO = $o;
	$class = get_class($this);
        $this->SETT = isset($o['settings'][$class])?$o['settings'][$class]:new Prototype(array());
	$this->__init($a);
    }
	
    abstract public function __init($a);
	
}

?>
