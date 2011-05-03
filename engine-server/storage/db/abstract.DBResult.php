<?php 
/**
 * @desc       Базовый класс для обработки результатов выборки из реляционной БД
 * @package    w framework
 * @category   engine/storage/db
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, IteratorAggregate
 * @version    0.2.0
 */
abstract class DBResult extends Object implements IteratorAggregate {
	
 protected $res = null;
	
    final public function __init($a) {
	$this->res = $a;
    }

    abstract public function all();

    abstract public function bool($arg);

    abstract public function  __destruct();
	
}
?>
