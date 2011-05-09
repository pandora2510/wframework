<?php
/**
 * @desc       Класс для формирования и выдачи json документа
 * @package    w framework
 * @category   engine/request/document
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Document, Input, OutputBrowser
 */
class JSON extends Document {

 const STPL = '<html><head></head><body data-status="200"><style type="text/x-json">%s</style></body></html>';
 private $alt = array(); // маска
 private $keys = array();
 private $secure = false;

    public function __init($a) {
        try {
            if(!($this->PRO['request']['output'] instanceof OutputBrowser))
                throw new DocException('Object:request/output - IS NOT AN INSTANCEOF OF CLASS: "OutputBrowser"',E_USER_ERROR,0,__FILE__,__LINE__);
            if(!$this->PRO['request']['input'] instanceof Input)
                throw new ErrorException('Object:request/input - IS NOT AN INSTANCEOF OF CLASS: "Input"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(DocException $e) {
            $this->PRO['error']->handlerror($e);
        }
        // https!!!
        if($this->PRO['request']['input']['get'][$this->SETT['nameArg']] == 'XMLHttpRequest') $this->secure = true;
        
        $this->PRO['request']['output']->setHeader('Content-Type',($this->secure?'text/html':'application/json').'; charset='.$this->SETT['char']);
        $this->PRO['request']['output']->setHeader('Expires','Mon, 26 Jul 1997 05:00:00 GMT');
        $this->PRO['request']['output']->setHeader('Last-Modified',gmdate('D, d M Y H:i:s').' GMT');
        $this->PRO['request']['output']->setHeader('Cache-Control','no-store, no-cache, must-revalidate');
        $this->PRO['request']['output']->setHeader('Cache-Control','post-check=0, pre-check=0',false);
        $this->PRO['request']['output']->setHeader('Pragma','no-cache');
    }

    public function scheme($schema) {// убрать, если нет надобности
        return null;
    }

    public function offsetSet($offset,$data) {
        if($offset) {
            // decode, cache // dec - false - json, true - not json
            if(!isset($this->p['cache']) or !$this->p['cache']) $data = json_encode($data);
            $this->data[$offset] = $data;
            $this->alt[$offset] = '%'.$offset.'%';
            $this->keys[$offset] = '"%'.$offset.'%"';
        }
        $this->p = null;
    }

    public function  __toString() {
        parent::__toString();
        $str = str_replace($this->keys,$this->data,json_encode($this->alt));
        if($this->secure) {
            return sprintf(self::STPL,$str);// проверить работоспособность
        } else {
            return $str;
        }
    }

}
?>
