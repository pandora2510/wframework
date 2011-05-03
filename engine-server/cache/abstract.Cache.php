<?php
/**
 * @desc       Базовый класс для кэширования
 * @package    w framework
 * @category   engine/cache
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object
 * @version    0.2.0
 */
abstract class Cache extends Object {

 const SPACE = 'C';
 const D = '.';
 protected $space = '';

    public function __init($a) {
        $this->space($a);
    }

    public function space($space=null) {
        if(is_null($space)) return $this->space;
        $this->space = (string)$space;
    }

    abstract public function set($key,$tag,$data,$prefix=true);

    abstract public function gets(array $keys,$prefix=true);

    abstract public function get($key,$tag,$prefix=true);

    abstract public function clean(array $keys,$prefix=true);

    abstract public function gs();

    public function  __destruct() {//echo 'cacheclose';
        if(mt_rand(1,100) <= $this->SETT['gs']) $this->gs();
    }

}
?>
