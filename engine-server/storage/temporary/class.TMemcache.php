<?php
/**
 * @desc       Класс-обертка класса memcache
 * @package    w framework
 * @category   engine/storage/temporary
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, MemoryException, Memcache
 * @version    0.2.1
 */
class TMemcache extends Object {

 protected $mc = null;

    public function __init($a) {
        try {
            if(!class_exists('Memcache'))
                throw new MemoryException ('Class: "Memcache" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
            $this->mc = new Memcache;
            // организовать pool
            $i = 0;
            foreach($this->SETT['pool'] as $val) {
                if($i == 0) {
                    $this->SETT['host'] = $val['host'];
                    $this->SETT['port'] = $val['port'];
                }
                ++$i;
                $this->mc->addserver($val['host'],$val['port']);
            }
            $this->mc->connect($this->SETT['host'],$this->SETT['port']);
        } catch (TemporaryException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }

    public function lock($key) {
        while(!$this->mc->add('t.'.md5($key),1,false,10)) usleep((int)$this->SETT['timeoutlock']);
    }

    public function unlock($key) {
        $this->mc->delete('t.'.md5($key),0);
    }

    public function get($key) {
        return $this->mc->get((string)$key);
    }

    public function gets(array $keys) {
        return $this->mc->get($keys);
    }

    public function set($key,$data,$time=90) {
        return $this->mc->set($key,$data,false,(int)$time);
    }

    public function sets(array $arg,$time=90) {
        $st = array();
        foreach($arg as $k=>$val) {
            $st[$k] = $this->mc->set($k,$val,false,(int)$time);
        }
        return $st;
    }

    public function del($key) {
        return $this->mc->delete($key,0);
    }

    public function decrement($key,$value=1) {
        return $this->mc->decrement($key,(int)$value);
    }

    public function increment($key,$value=1) {
        return $this->mc->increment($key,(int)$value);
    }

    public function  __destruct() {
        // глючи через close
        $this->mc->close();
    }

}
?>
