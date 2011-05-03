<?php
/**
 * @desc       Класс для вывода графического изображения в поток
 * @package    w framework
 * @category   engine/request/document
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Document, OutputBrowser
 * @version    0.3.1
 */
class IMAGE extends Document {

    public function __init($a) {
        try {
            if(!($this->PRO['request']['output'] instanceof OutputBrowser))
                throw new DocException('Object:request/output - IS NOT AN INSTANCEOF OF CLASS: "OutputBrowser"',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(DocException $e) {
            $this->PRO['error']->handlerror($e);
        }
        $this->PRO['request']['output']->setHeader('Content-Type','image/jpeg');
        //$this->REQ['output']->setHeader('Expires','Mon, 26 Jul 1997 05:00:00 GMT');
        //$this->REQ['output']->setHeader('Last-Modified',gmdate('D, d M Y H:i:s').' GMT');
        //$this->REQ['output']->setHeader('Cache-Control','no-store, no-cache, must-revalidate');
        //$this->REQ['output']->setHeader('Cache-Control','post-check=0, pre-check=0',false);
        //$this->REQ['output']->setHeader('Pragma','no-cache');
    }

    public function scheme($schema) {
        return null;
    }

    public function  __toString() {
        parent::__toString();
        return implode('',$this->data);
    }

}
?>
