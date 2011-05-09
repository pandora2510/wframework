<?php
/**
 * @desc       Базовый класс для работы с временными данными
 * @package    w framework
 * @category   engine/storage/temporary
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object
 */
abstract class Temporary extends Object {

 public $lifetime = null;// сек

    abstract public function lock($key);

    abstract public function unlock($key);

    abstract public function get($key);

    abstract public function gets(array $key);

    abstract public function set($key,$data);

    abstract public function sets(array $arg);

    abstract public function del($key);

    abstract public function decrement($key,$value=1);

    abstract public function increment($key,$value=1);

    abstract public function __destruct();

}
?>
