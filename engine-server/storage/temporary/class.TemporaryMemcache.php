<?php
/**
 * @desc       Класс-обертка класса memcache
 * @package    w framework
 * @category   engine/storage/temporary
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       TemporaryException, Memcache, Temporary
 */
class TemporaryMemcache extends Temporary {

 protected $mc = null;

    public function __init($a) {
        try {
            if(!class_exists('Memcache'))
                throw new TemporaryException('Class: "Memcache" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
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
        while(!$this->mc->add('t.'.md5($key),1,false,(int)$this->SETT['timeoutlock'])) usleep(10);
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

    public function set($key,$data) {
        return $this->lifetime < 1?false:$this->mc->set($key,$data,false,(int)$this->lifetime);
    }

    public function sets(array $arg) {
        $st = array();
        if($this->lifetime < 1) return array();
        foreach($arg as $k=>$val) {
            $st[$k] = $this->mc->set($k,$val,false,(int)$this->lifetime);
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
        $this->mc->close();
    }

}
?>
