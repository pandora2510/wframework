<?php
/**
 * @desc       Класс для работы со списком екшенов
 * @package    w framework
 * @category   engine/request
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, IteratorAggregate, Input
 */
class Actions extends Object implements IteratorAggregate {

 const D = '.';
 const D1 = '..';
 const D2 = ',';
 protected $data = array();
 protected $l10n = array();
 protected $gets = array();
 private $pr = array('PRIVILEGE'=>0,'DPRIVILEGE'=>0);

    public function __init($a) {
        try {
            if(!$this->PRO['request']['input'] instanceof Input)
                throw new ActionException('Object: request/input - IS NOT AN INSTANCEOF OF CLASS: "Input"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(ActionException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }

    //
    public function prepare(array $keys,array $arg) {// prefix(cache), ps, pd | type[client,server]
        foreach($this->SETT['actions'] as $k=>$val) {
            if(!in_array($k,$keys)) continue;
            /*
             *  action:
             * (name),
             * active=>true/false,
             * view=>string,
             * model=>string,
             * l10n=>array('lang'=>string,...),
             * timeInterval=>integer,
             * front-dep-end=>array(string,...),
             * limits=>array('name'=>array(rule(range['int','bool','range']),arg(mixed)),nec(bool)...), // PRIVILEGE, DPRIVILEGE ??????
             * back-end-cache=>range['set','get','n'],
             * back-dep-end=>array(string,array(string(name),string(virtual))),
             * virtual=>string
             */
            $act = include($this->SETT['folder'].$k.D.$val);
            if(!is_array($act) or !isset($act['active']) or !$act['active']) continue;

            if(isset($arg['type']) and $arg['type'] == 'client') {
                if($this->SETT['fix']) {
                    $act['view'] = !isset($act['view'])?null:$act['view'];
                    $act['l10n'] = !isset($act['l10n'])?null:$act['l10n'];
                    $act['timeInterval'] = !isset($act['timeInterval'])?null:$act['timeInterval'];
                    $act['front-dep-end'] = !isset($act['front-dep-end'])?array():(!is_array($act['front-dep-end'])?(array)$act['front-dep-end']:$act['front-dep-end']);
                }
                // удалить лишнее
                unset($act['model'],$act['limits'],$act['back-dep-end'],$act['back-end-cache'],$act['virtual']);
            } else {
                if($this->SETT['fix']) {
                    $act['model'] = !isset($act['model'])?'':$act['model'];
                    $act['l10n'] = !isset($act['l10n'])?array():(!is_array($act['l10n'])?(array)$act['l10n']:$act['l10n']);
                    $act['limits'] = (!isset($act['limits']) or !is_array($act['limits']))?array():$act['limits'];
                    $act['back-end-cache'] = !isset($act['back-end-cache'])?'n':strtolower($act['back-end-cache']);
                    $act['back-dep-end'] = !isset($act['back-dep-end'])?array():(!is_array($act['back-dep-end'])?(array)$act['back-dep-end']:$act['back-dep-end']);
                    $act['virtual'] = !isset($act['virtual'])?null:$act['virtual'];
                }
                // удалить лишнее
                unset($act['view'],$act['timeInterval'],$act['front-dep-end']);

                // занести привилегии
                $this->pr['PRIVILEGE'] = isset($arg['ps'])?$arg['ps']:0;
                $this->pr['DPRIVILEGE'] = isset($arg['pd'])?$arg['pd']:0;
                
                $this->l10n[$k] = $act['l10n'];// мультиязычность на сервере

                // приведение типов
                $c = true;
                foreach($act['limits'] as $k1=>$val1) {
                    $tmp = $this->_limits($k1,is_array($val1)?$val1:array());
                    if(!$tmp) $c = false;
                }
                // разбор с кэшами
                if(isset($arg['prefix']) and $act['back-end-cache'] == 'get' and $c) {
                    $hash = $this->_hash($act['limits']);
                    $act['hash'] = $hash;
                    $act['virtual'] = $this->PRO['request']['input']['get'][$act['virtual']];
                    $this->gets[$k] = (isset($arg['prefix'])?$arg['prefix']:'').$k.self::D.($act['virtual']?$act['virtual'].self::D:'').$hash;
                } elseif(isset($arg['prefix']) and $act['back-end-cache'] == 'set') {
                    foreach($act['back-dep-end'] as $k1=>$val1) {
                        // получать virtual                        
                        if(is_array($val1)) {
                            if(!isset($val1[0])) $val1[0] = null;
                            if(!isset($val1[1])) $val1[1] = null;
                            $tmp = $this->PRO['request']['input']['get'][$val1[1]];
                            $act['back-dep-end'][$k1] = $val1[0].($tmp?self::D.$tmp:'');
                        } else {
                            $act['back-dep-end'][$k1] = $val1;
                        }
                    }
                } else {
                    $act['back-end-cache'] = 'n';// кэширование не задействованно
                }
                $act['name'] = $k.($act['virtual']?self::D.$act['virtual']:'');
            }            
            $this->data[$k] = $act;
        }
        return $this;
    }

    public function getL10n() {
        return $this->l10n;
    }

    public function get() {
        return $this->data;
    }
    
    protected function _limits($name,array $param) {
        if(sizeof($param) < 1) return true;// кэширование активно
        $rule = isset($param[0])?$param[0]:null;
        $arg = isset($param[2])?$param[2]:null;
        $nec = (isset($param[1]) and $param[1]);
        if($name == 'PRIVILEGE' or $name == 'DPRIVILEGE') {// только $arg[0]
            $tmp = $this->pr[$name];
            if(!is_array($rule)) $arg = explode(self::D1,(string)$rule); else $arg = (array)$rule;
            if(isset($arg[1])) {
                if($arg[1] < $tmp) {
                    $this->pr[$name] = (int)$arg[1];
                }
                if($arg[0] > $tmp) {
                    $this->pr[$name] = 0;
                }
            } else {
                if($arg[0] < $tmp) {
                    $this->pr[$name] = (int)$arg[0];
                }
            }
        } elseif($rule == 'bool' or $rule == 'boolean') {
            $tmp = $this->PRO['request']['input']['get'][$name];
            if(!$tmp) return false;
            if($nec) $this->PRO['request']['input']['get'][$name] = (bool)$tmp;
        } elseif($rule == 'int' or $rule == 'integer') {
            $tmp = $this->PRO['request']['input']['get'][$name];
            if(!is_array($arg)) $arg = explode(self::D1,(string)$arg); else $arg = (array)$arg;
            if(isset($arg[1])) {
                if($arg[1] < $tmp) {
                    if($nec) $this->PRO['request']['input']['get'][$name] = (int)$arg[1];
                    else return false;
                }
                if($arg[0] > $tmp) {
                    if($nec) $this->PRO['request']['input']['get'][$name] = (int)$arg[0];
                    else return false;
                }
            } else {
                if($arg[0] > $tmp) {
                    if($nec) $this->PRO['request']['input']['get'][$name] = (int)$arg[0];
                    else return false;
                }
            }
        } elseif($rule == 'range') {
            $tmp = $this->PRO['request']['input']['get'][$name];
            if(!is_array($arg)) $arg = explode(self::D2,(string)$arg);
            if(!in_array($tmp,$arg)) {
                if($nec) $this->PRO['request']['input']['get'][$name] = $arg[0];
                else return false;
            }
        }
        return true;
    }

    private function _hash($name) {
        $p = array('p'=>array());
        foreach($name as $k=>$val) {
            if($this->PRO['request']['input']['get'][$k]) $p['p'][$k] = $this->PRO['request']['input']['get'][$k];
        }
        $p['ps'] = $this->pr['PRIVILEGE'];
        $p['pd'] = $this->pr['DPRIVILEGE'];
        return $this->SETT['encodeParam']?md5(json_encode($p)):json_encode($p);
    }

    public function gets() {
        return $this->gets;
    }

    public function  getIterator() {
        return new ProIterator($this->data);
    }

}
?>
