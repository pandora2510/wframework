<?php
/**
 * @desc       Базовый класс для обработки запросов к серверу
 * @package    w framework
 * @category   engine/request
 * @author     Checha Andrey
 * @copyright  ((c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, ArrayAccess, RequestException, Authorisation, Model, Actions, Input, Output
 */
class Request extends Object implements ArrayAccess {

 const R_AJAX = 'ajax_enable';
 protected $data = array('input'=>null,'output'=>null,'session'=>null,'auth'=>null,'model'=>null,'actions'=>null,'document'=>null,'url'=>null);
 protected $clean = array();

    public function __init($a) {}

    public function checkDep() {
        try {
            if(!($this->PRO['request']['auth'] instanceof Authorisation))
                throw new RequestException('Object:request/auth - IS NOT AN INSTANCEOF OF CLASS: "Authorisation"',E_USER_ERROR,0,__FILE__,__LINE__);
            if(!($this->PRO['request']['model'] instanceof Model))
                throw new RequestException('Object:request/model - IS NOT AN INSTANCEOF OF CLASS: "Model"',E_USER_ERROR,0,__FILE__,__LINE__);
            if(!($this->PRO['request']['actions'] instanceof Actions))
                throw new RequestException('Object:request/actions - IS NOT AN INSTANCEOF OF CLASS: "Actions"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(RequestException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }

    public function response() {
        try {

            $inp = $this->PRO['request']['input'];

            $sync = (empty($inp['server']['HTTP_X_REQUESTED_WITH']) and empty($inp['get']['iframe']))?0:1;
            $def = $sync?$this->SETT['aDefCtrl']:$this->SETT['defCtrl'];

            $ctrl = $this->SETT['nameCtrl'];
            $act = $this->SETT['nameActs'];
            $inp['get'][$ctrl] = $inp['get'][$ctrl]?$inp['get'][$ctrl]:$def;
            //$inp['get'][$act] = $inp['get'][$act]?$inp['get'][$act]:null;

            if(!isset($this->SETT['controllers'][$inp['get'][$ctrl]])) $inp['get'][$ctrl] = $def;
            if(!isset($this->SETT['controllers'][$inp['get'][$ctrl]]))
                throw new RequestException('Controller - "'.$inp['get'][$ctrl].'" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
            
            $data = $this->SETT['controllers'][$inp['get'][$ctrl]];

            $q = strtoupper($data['query']);
            $this['document'] = new $q($this->PRO);
            if(!$this['document'] instanceof Document)
                throw new RequestException('Object:request/document - IS NOT AN INSTANCEOF OF CLASS: "Document"',E_USER_ERROR,0,__FILE__,__LINE__);
            $this['document']->scheme($data['scheme']);// учитывать тип документа
        
            $this->PRO['request']['output']->setCookie($this->SETT['localeName'],$this->PRO['request']['auth']['locale']);
            
            // кооректировка списка экшенов
            $tmp = array(); $acts = array();
            if($inp['get'][$act] instanceof Traversable) {
                $tmp = iterator_to_array($inp['get'][$act]);
            } elseif($inp['get'][$act]) {
                $tmp = array((string)$inp['get'][$act]);
            }
            if($data['actions'] instanceof Traversable) {
                $acts = iterator_to_array($data['actions']);
            } elseif($data['actions']) {
                $acts = array((string)$data['actions']);
            }
            $acts = sizeof($tmp)>0?array_intersect($tmp,$acts):$acts;
            
            if($this->PRO['cache']['actions'] instanceof Cache and $this->SETT['cache']) {
                $this->_req_cache($acts,$q);
            } elseif($this->SETT['cache']) {
                if(!$this->PRO['cache']['actions'] instanceof Cache)
                    throw new RequestException('Object: cache/actions - IS NOT AN INSTANCEOF OF CLASS: "Cache"',E_USER_ERROR,0,__FILE__,__LINE__);
            } else {
                $this->_req_default($acts);
            }
            
            // ajax
            $this->setAjax();

        } catch(RequestException $e) {
            $this->PRO['error']->handlerror($e);
        }

        return true;
    }

    protected function _req_default($acts) {
        $this['actions']->prepare($acts,array());
        foreach($this['actions'] as $k=>$val) {
            $this['document']->param(array('cache'=>false));
            $this['document'][$k] = $this['model']->get($k.D.$val['model']);
        }
    }

    protected function _req_cache($acts,$q) {// разобратся с кэшированием!!!
        $c = $this->PRO['cache']['actions'];

        $this['actions']->prepare($acts,array('prefix'=>Cache::SPACE.Cache::D.$c->space().Cache::D,
            'ps'=>$this->PRO['request']['auth']['privilege'],
            'pd'=>$this->PRO['request']['auth']['dPrivilege'],
            'type'=>'server'
        ));

        //$c->dec(!($q == 'JSON'));
        $keys = $this['actions']->gets();
        $gets = $c->gets($keys,false,!($q == 'JSON')); // multi get из cache

        // перебор экшенов
        foreach($this['actions'] as $k=>$val) {
            if($val['back-end-cache'] == 'get') {
                if(isset($keys[$k],$gets[$keys[$k]])) {
                    // gets -> doc
                    $this['document']->param(array('cache'=>true));
                    $this['document'][$k] = $gets[$keys[$k]];
                    //echo 'GET';
                } else {
                    // mod -> doc
                    // set
                    $this['document']->param(array('cache'=>false));//!!!
                    $d = $this['model']->get($k.D.$val['model']);
                    $c->set($val['name'],$val['hash'],$d,true);
                    $this['document'][$k] = $d;
                    //echo 'SET';
                }
            } elseif($val['back-end-cache'] == 'set') {
                // mod -> doc
                // clean
                $this['document']->param(array('cache'=>false));
                $this['document'][$k] = $this['model']->get($k.D.$val['model']);
                foreach($val['back-dep-end'] as $val1) {
                    $this->clean[] = $val1;
                }
            } else {
                // default
                // mod -> doc
                $this['document']->param(array('cache'=>false));
                $this['document'][$k] = $this['model']->get($k.D.$val['model']);
            }
        }
        // clean
        $this->cacheClean();
    }

    protected function cacheClean() {
        // очистка кэша
        if(sizeof($this->clean) > 0 and $this->PRO['cache']['actions'] instanceof Cache and $this->SETT['cache']) {
            // очистка кэша
            $this->clean = array_unique($this->clean);
            $this->PRO['cache']['actions']->clean($this->clean,true);
            $this->clean = array();
        }
    }

    // проверка домена
    public function checkDomain() {
        if(!$this->PRO['request']['input']['server']['HTTP_REFERER'] or !$this->PRO['request']['input']['server']['HTTP_HOST']) return false;
        return parse_url($this->PRO['request']['input']['server']['HTTP_REFERER'],PHP_URL_HOST)==$this->PRO['request']['input']['server']['HTTP_HOST']?true:false;
    }

    // проверка ajax
    public function checkAjax() {
        return ($this->PRO['request']['session'][$this->SETT['nameSystemAjax']] == self::R_AJAX)?true:false;
    }

    protected function setAjax() {
        $this->PRO['request']['session']['SYSTEM_SESSION_AJAX'] = self::R_AJAX;
    }

    public function offsetSet($offset,$data) {
        if($offset === null) {
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

    public function __destruct() {
        $this->cacheClean();
    }
    
}
?>
