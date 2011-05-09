<?php
/**
 * @desc       Класс для измерения затраченных ресурсов в работе экшенов
 * @package    w framework
 * @category   engine/debug
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Request
 */
class RequestDebug extends Request {

 const GET = 'read-cache';
 const N = 'execution';
 const SET = 'execution, clean-cache';
 const NGET = 'execution, write-cache';
 private $res = null;
 private $log = array('acts'=>array(),'clean'=>0,'prepare'=>0);

    public function __init($a) {// большее количество данных при кэщировании записывать!!!
        try {
            if($a instanceof Test) $this->res = $a;
             else throw new RequestException('Object: "$a" - IS NOT AN INSTANCEOF OF CLASS: "Test"',E_USER_ERROR,0,__FILE__,__LINE__);
            
            // копировать настройки класса Request
            if(!($this->PRO['settings']['Request'] instanceof Prototype))
                throw new RequestException('Object: "$this->PRO[\'settings\'][\'Request\']" - IS NOT AN INSTANCEOF OF CLASS: "Prototype"',E_USER_ERROR,0,__FILE__,__LINE__);
            foreach($this->PRO['settings']['Request'] as $k=>$val) {
                $this->PRO['settings']['RequestDebug'][$k] = $val;
            }
        } catch(RequestException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }

    protected function gettime($stime) {
        $stime = explode(' ',$stime);
        $stime = $stime[0]+$stime[1];
        $ftime = explode(' ',microtime());
        $ftime = $ftime[0]+$ftime[1];
        $ftime = $ftime-$stime;
        return $this->SETT['f']?number_format($ftime,6,'.',''):$ftime;
    }

    protected function _req_default($acts) {
        $stime = microtime();
        $this['actions']->prepare($acts,array());
        $this->log['prepare'] = $this->gettime($stime);
        foreach($this['actions'] as $k=>$val) {
            $stime = microtime();
            $this['document']->param(array('cache'=>false));
            $this['document'][$k] = $this['model']->get($k.D.$val['model']);
            $this->log['acts'][] = array('name'=>$k,'type'=>self::N,'time'=>$this->res->gettime($stime),'keys'=>array());
        }
        // запись данных в кэш
        $this->tofile($this->SETT['filelog'],$this->generate());
    }

    protected function _req_cache($acts,$q) {
        $stime = microtime();
        $c = $this->PRO['cache']['actions'];

        $this['actions']->prepare($acts,array('prefix'=>Cache::SPACE.Cache::D.$c->space().Cache::D,
            'ps'=>$this->PRO['request']['auth']['privilege'],
            'pd'=>$this->PRO['request']['auth']['dPrivilege'],
            'type'=>'server'
        ));

        //$c->dec(!($q == 'JSON'));
        $keys = $this['actions']->gets();
        $gets = $c->gets($keys,false,!($q == 'JSON')); // multi get из cache

        $this->log['prepare'] = $this->res->gettime($stime);
        // перебор экшенов
        foreach($this['actions'] as $k=>$val) {
            $stime = microtime();
            if($val['back-end-cache'] == 'get') {
                if(isset($keys[$k],$gets[$keys[$k]])) {
                    // gets -> doc
                    $this['document']->param(array('cache'=>true));
                    $this['document'][$k] = $gets[$keys[$k]];
                    $this->log['acts'][] = array('name'=>$val['name'],'type'=>self::GET,
                        'time'=>$this->res->gettime($stime),
                        'keys'=>array($keys[$k])
                    );
                } else {
                    // mod -> doc
                    // set
                    $this['document']->param(array('cache'=>false));//!!!
                    $d = $this['model']->get($k.D.$val['model']);
                    $c->set($val['name'],$val['hash'],$d,true);
                    $this['document'][$k] = $d;
                    $this->log['acts'][] = array('name'=>$val['name'],'type'=>self::NGET,
                        'time'=>$this->res->gettime($stime),
                        'keys'=>array('(PREFIX)'.Cache::D.$val['name'],'(PREFIX)'.Cache::D.$val['name'].Cache::D.$val['hash'])
                    );
                }
            } elseif($val['back-end-cache'] == 'set') {
                // mod -> doc
                // clean
                $this['document']->param(array('cache'=>false));
                $this['document'][$k] = $this['model']->get($k.D.$val['model']);
                $keys = array();
                foreach($val['back-dep-end'] as $val1) {
                    $this->clean[] = $val1;
                    $keys[] = '(PREFIX)'.Cache::D.$val1;
                }
                $this->log['acts'][] = array('name'=>$val['name'],'type'=>self::SET,
                    'time'=>$this->res->gettime($stime),
                    'keys'=>$keys
                );
            } else {
                // default
                // mod -> doc
                $this['document']->param(array('cache'=>false));
                $this['document'][$k] = $this['model']->get($k.D.$val['model']);
                $this->log['acts'][] = array('name'=>$k,'type'=>self::N,'time'=>$this->res->gettime($stime),'keys'=>array());
            }
        }
        // clean
        $stime = microtime();
        $this->cacheClean();
        $this->log['clean'] = $this->res->gettime($stime);
        // запись данных в кэш
        $this->tofile($this->SETT['filelog'],$this->generate());
    }

    protected function generate() {
        $str = '/*-begin----'.str_pad('-',90,'-').'*/'."\n";
        $str .= date('Y/m/d H:i:s')."\n".$this->res->geturl()."\n";
        $str .= '/*-prepare--'.str_pad('-',90,'-').'*/'."\n";
        $str .= (isset($this->log['prepare'])?$this->log['prepare']:0)."\n";
        $str .= '/*-actions--'.str_pad('-',90,'-').'*/'."\n";
        for($i=0, $n=sizeof($this->log['acts']); $i<$n; $i++) {
            $str .= $this->log['acts'][$i]['name'].Test::D.$this->log['acts'][$i]['type'].Test::D.$this->log['acts'][$i]['time']."\n";
            if(sizeof($this->log['acts'][$i]['keys']) > 0) $str .= ' * '.implode("\n * ",$this->log['acts'][$i]['keys'])."\n";
        }
        $str .= '/*-clean----'.str_pad('-',90,'-').'*/'."\n";
        $str .= (isset($this->log['clean'])?$this->log['clean']:0)."\n";
        $str .= '/*-end------'.str_pad('-',90,'-').'*/'."\n";
        $str .= "\n\n";

        return $str;
    }

    protected function tofile($file=false,$str=null) {
        if($file === false or $str === null)  return null;
        if(!is_file($file)) {
            if(!file_exists(dirname($file))) mkdir(dirname($file),'0777',true);
	}
        $f = fopen($file,'a');
        flock($f,LOCK_EX);
        $st = fwrite($f,$str);
        flock($f,LOCK_UN);
        fclose($f);
	return (bool)$st;
    }

}
?>
