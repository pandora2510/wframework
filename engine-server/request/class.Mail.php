<?php
/**
 * @desc       класс для работы с почтой
 * @package    w framework
 * @category   engine/request
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, OutputMail, ErrorException
 */
class Mail extends Object {

 protected $res = null;

    public function  __init($a) {
        try {
            if(!($this->PRO['request']['outputMail'] instanceof OutputMail))
                throw new ErrorException('Object:request/outputMail - IS NOT AN INSTANCEOF OF CLASS: "OutputMail"',E_USER_ERROR,0,__FILE__,__LINE__);
            $this->res = $this->PRO['request']['outputMail'];
        } catch(ErrorException $e) {
            $this->PRO['error']->handlerror($e);
        }
        
    }

    public function to($adres,$name='') {
        $this->res->setHeader('To',array('name'=>$name,'adres'=>$adres,'char'=>$this->SETT['char']));
    }

    public function subject($str) {
        $this->res->setHeader('Subject',array('subject'=>$str,'char'=>$this->SETT['char']));
    }

    public function addHeader($name,$value) {
        $this->res->setHeader($name,$value);
    }

    public function message($str) {
        $this->res->setFragment($str);
    }

    public function send() {
        $this->res->setHeader('MIME-Versin','1.0');
        if($this->SETT['from']) {
	    if($this->SETT['from'] instanceof Traversable) {
		$this->res->setHeader('From',array('arg'=>iterator_to_array($this->SETT['from'],true),'char'=>$this->SETT['char']));
	    } else {
		$this->res->setHeader('From',array('adres'=>(string)$this->SETT['from'],'char'=>$this->SETT['char']));
	    }
	}
        $this->contentType();
        $this->res->setHeader('X-Mailer','W framework');
        return $this->res->getsBody();
    }

    protected function contentType() {
        $this->res->setHeader('Content-Type','text/plain; charset='.$this->SETT['char']);
    }

    public function clean() {
        $this->res->clean();
    }

    public function  __destruct() {
        $this->clean();
    }

}
?>
