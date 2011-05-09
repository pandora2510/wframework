<?php
/**
 * @desc       Класс для кэширования при помощи memcache
 * @package    w framework
 * @category   engine/cache
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Cache, Temporary, CacheException
 */
class MCache extends Cache {

 const R = '~';
 private $m = null;

    public function  __init($a) {
        try {
            if($this->PRO['storage']['temporary'] instanceof Temporary) $this->m = $this->PRO['storage']['temporary'];
             else throw new CacheException('Object: storage/temporary - IS NOT AN INSTANCEOF OF CLASS: "Temporary"',E_USER_ERROR,0,__FILE__,__LINE__);
            parent::__init($a);
        } catch(CacheException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }

    public function set($key,$tag,$data,$prefix=true) {
        $tkey = ($prefix?self::SPACE.self::D.$this->space.self::D:'').$key;
        $this->m->lock($tkey);
        $this->m->lifetime = $this->SETT['lifetime']*2;
        $this->m->set($tkey,$this->m->get($tkey).self::R.$key.self::D.$tag);
        $this->m->lifetime = $this->SETT['lifetime'];
        $this->m->set($tkey.self::D.$tag,json_encode($data));
        $this->m->unlock($tkey);
    }

    public function gets(array $keys,$prefix=true,$dec=false) {
        if($prefix) {
            for($i=0, $n=sizeof($keys); $i<$n; $i++) $keys[$i] = self::SPACE.self::D.$this->space.self::D.$keys[$i];
        }
        $data = $this->m->gets($keys);
        if($dec and is_array($data)) {
            foreach($data as $k=>$val) {
                $data[$k] = json_decode($val,true);
            }
        }
        return $data;
    }

    public function get($key,$tag,$prefix=true,$dec=false) {
        $key = ($prefix?self::SPACE.self::D.$this->space.self::D:'').$key.self::D.$tag;
        $data = $this->m->get($key);
        if($dec) $data = json_decode($data,true);
        return $data;
    }

    public function clean(array $keys,$prefix=true) {
        for($i=0, $n=sizeof($keys); $i<$n; $i++) {
            $keys[$i] = ($prefix?self::SPACE.self::D.$this->space.self::D:'').$keys[$i];
            $this->m->lock($keys[$i]);
            $data = $this->m->get($keys[$i]);            
            $this->m->unlock($keys[$i]);
            $this->m->del($keys[$i]);// удаляем индексный файл
            if($data) {
                $data = explode(self::R,$data);
                $data = array_unique($data);
                foreach($data as $val) {
                    if($val) $this->m->del(self::SPACE.self::D.$this->space.self::D.$val);
                }
            }            
        }
        // + вызов триггера(сделать в дочернем классе по мере надобности)
    }

    public function gs() {
        // memcache - самостоятельно удаляет мусор
    }

    // удалить потом
    public function  __destruct() {
        parent::__destruct();
    }

}
?>
