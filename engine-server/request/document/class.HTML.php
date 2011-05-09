<?php
/**
 * @desc       Класс для формирования и выдачи html документа
 * @package    w framework
 * @category   engine/request/document
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Document, OutputBrowser
 */
class HTML extends Document {

 private $tpl = '';
 private $keys = array();

    public function __init($a) {
        try {
            if(!($this->PRO['request']['output'] instanceof OutputBrowser))
                throw new DocException('Object:request/output - IS NOT AN INSTANCEOF OF CLASS: "OutputBrowser"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(DocException $e) {
            $this->PRO['error']->handlerror($e);
        }
        $this->PRO['request']['output']->setHeader('Content-Type','text/html; charset='.$this->SETT['char']);
        $this->PRO['request']['output']->setHeader('Expires','Mon, 26 Jul 1997 05:00:00 GMT');
        $this->PRO['request']['output']->setHeader('Last-Modified',gmdate('D, d M Y H:i:s').' GMT');
        $this->PRO['request']['output']->setHeader('Cache-Control','no-store, no-cache, must-revalidate');
        $this->PRO['request']['output']->setHeader('Cache-Control','post-check=0, pre-check=0',false);
        $this->PRO['request']['output']->setHeader('Pragma','no-cache');
    }

    public function scheme($schema) {
        if(is_file($schema)) {
            $this->tpl = file_get_contents($schema);
        }
    }

    public function offsetSet($offset,$data) {
        if(is_array($data) or is_object($data)) $data = json_encode($data);
        parent::offsetSet($offset,$data);
        $this->keys[$offset] = '{'.$offset.'}';
    }

    public function  __toString() {
        parent::__toString();
        return str_replace($this->keys,$this->data,$this->tpl);
    }

}
?>
