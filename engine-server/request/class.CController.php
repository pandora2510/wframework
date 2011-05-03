<?php
/**
 * @desc       Класс для генерирования настроек клиентскому контроллеру
 * @package    w framework
 * @category   engine/request
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, ArrayAccess, Prototype, CCtrlException
 * @version    0.3.3
 */
class CController extends Object implements ArrayAccess {

 protected $data = array();

    public function  __init($a) {}

    public function checkDep() {
        try {
            if(!$this->PRO['request']['actions'] instanceof Actions)
                throw new CCtrlException('Object: actions - IS NOT AN INSTANCEOF OF CLASS: "Actions"',E_USER_ERROR,0,__FILE__,__LINE__);
            if(!$this->PRO['request']['output'] instanceof Output)
                throw new CCtrlException('Object: output - IS NOT AN INSTANCEOF OF CLASS: "Output"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(CCtrlException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }

    public function request() {
        if($this->PRO['settings']['Actions']['actions'] instanceof Prototype) {
            $keys = array_keys(iterator_to_array($this->PRO['settings']['Actions']['actions']));
            $this->PRO['request']['actions']->prepare($keys,array('type'=>'client'));
            $this->data['actions'] = $this->PRO['request']['actions']->get();
        } else {
            $this->data[''] = array();
        }
        // controllers - из sett
        if($this->PRO['settings']['Request']['controllers'] instanceof Prototype) {
            $r = $this->PRO['settings']['Request'];
            foreach($r['controllers'] as $k=>$val) {
                $this->data['controllers'][$k] = array(
                    'name'=>$k,
                    'actions'=>isset($val['actions'])?iterator_to_array($val['actions'],false):array()
                );
            }
            // остальные настройки из config
            $this->data['aDefCtrl'] = $r['aDefCtrl'];
            $this->data['nameCtrl'] = $r['nameCtrl'];
            $this->data['nameActs'] = $r['nameActs'];
        } else {
            $this->data['controllers'] = array();
        }

        $this->PRO['request']['output']->setHeader('Content-Type','text/javascript; charset='.$this->SETT['char']);
        $this->PRO['request']['output']->setHeader('Expires','Mon, 26 Jul 1997 05:00:00 GMT');
        $this->PRO['request']['output']->setHeader('Last-Modified',gmdate('D, d M Y H:i:s').' GMT');
        $this->PRO['request']['output']->setHeader('Cache-Control','no-store, no-cache, must-revalidate');
        $this->PRO['request']['output']->setHeader('Cache-Control','post-check=0, pre-check=0',false);
        $this->PRO['request']['output']->setHeader('Pragma','no-cache');
    }

    protected function getContent() {
        return json_encode($this->data);
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

    public function  __toString() {
        $str = '';
        try {
            $str = file_get_contents($this->SETT['index']);
            // замена по шаблону
            $str = str_replace(array('{'.$this->SETT['settings'].'}','{'.$this->SETT['addsettings'].'}'),
                               array($this->getContent(),file_get_contents($this->SETT['config'])),$str);
            // запись строки в кэш
            if(!is_file($this->SETT['fcache'])) file_put_contents($this->SETT['fcache'],$str);
            
        } catch(ErrorException $e) {
            $this->PRO['error']->handlerror($e);
        }
        return $str;
    }

}
?>
