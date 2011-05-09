<?php
/**
 * @desc       Класс для формирования url
 * @package    w framework
 * @category   engine/request
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, Input, URLException, ErrorException
 */
class URL extends Object {

 protected $c = array();
 protected $str = '';
 protected $secure = false;
 protected $protocol = 'http';

    public function __init($a) {
        try {
            if(!$this->PRO['request']['input'] instanceof Input)
                throw new URLException('Object:request/input - IS NOT AN INSTANCEOF OF CLASS: "Input"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(URLException $e) {
            $this->PRO['error']->handlerror($e);
        }
        if($this->PRO['request']['input']['server'][$this->SETT['SecureName']] == $this->SETT['SecureValue']) {
            $this->protocol = 'https';
            $this->secure = true;
        }
    }

    public function getProtocol() {
        return $this->protocol;
    }

    public function __call($name,$arg) {// name - name template, arg - дополнительные еще незамененные параметры
        $arr = isset($arg[0])?$arg[0]:array();
        $str = $this->replace(isset($this->c[$name])?$this->c[$name]:$this->SETT['scheme'][$name],$arr);
        if(!empty($str)) $this->str = preg_replace('#\{[^\}]+\}#i','',$str);
        return $this;
    }

    protected function replace($str,array $arg) {
        if(!is_string($str) or !$str) return '';
        foreach($arg as $k=>$val) {
            if(is_array($val)) $val = $this->handleArray($val);
            $str = preg_replace('#\{([^<]*)<'.$k.'>([^\}]*)\}|<'.$k.'>#i','$1'.$val.'$2',$str);
        }
        return $str;
    }

    protected function handleArray(array $arg) {
        return http_build_query($arg);
    }

    public function prepare($ntpl,array $arg) {
        if(!isset($this->SETT['scheme'][$ntpl])) return;
        $this->c[$ntpl] = $this->replace(isset($this->c[$ntpl])?$this->c[$ntpl]:$this->SETT['scheme'][$ntpl],$arg);
        return $this;
    }

    public function prepareFile() {
        if($this->SETT['file'] and file_exists($this->SETT['file'])) {
            try {
                $url = $this; $obj = $this->PRO;
                include($this->SETT['file']);
            } catch(URLException $e) {
                $this->PRO['error']->handlerror($e);
            } catch(ErrorException $e) {
                $this->PRO['error']->handlerror($e);
            }
        }
    }

    public function clean(array $arg=null) {
        if(is_null($arg)) {
            $this->c = array();
        } else {
            if(!is_array($arg) or $arg instanceof Traversable) $arg = (array)$arg;
            foreach($arg as $k=>$val) unset($this->c[$val]);
        }
    }

    public function __toString() {
        return $this->str;
    }

}
?>
