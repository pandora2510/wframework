<?php
/**
 * @desc       Класс для вывода данных в виде електронного письма
 * @package    w framework
 * @category   engine/request/puts
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Output, OutputException
 */
class OutputMail extends Output {

 const DEF = '...';
 const D = "\r\n";
 protected $boundary = null;
 private $stack = array();
 private $i = 0;

    public function __init($a) {
        try {
            if(!function_exists('mail')) throw new OutputException('Function - "mail" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
        } catch(OutputException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }

    public function setHeader($name,$value,$add=true) {// протестировать
        $n = strtolower($name);
        $i = null;
        if($add) {
            $i = array_search($n,$this->stack);
        }
        $this->stack[$this->i] = $n;
        if($n == 'subject') {
            $subject = '';
            if(is_array($value) and !empty($value['char'])) {
                $subject = '=?'.$value['char'].'?B?'.base64_encode(empty($value['subject'])?self::DEF:$value['subject']).'?=';
            } else {
                $subject = (empty($value)?self::DEF:$value);
            }
            $this->h[($i===false?$this->i:$i)] = $name.': '.$subject.self::D;
        } elseif($n == 'content-type') {
            if(is_array($value) and isset($value['boundary'])) {
                $this->boundary = $value['boundary'];
                $this->h[(is_null($i)?$this->i:$i)] = $name.': '.(empty($value['mtype'])?'text/plain':$value['mtype']).self::D;
            } else {
                $matches = null;
                preg_match('#boundary="([^"]+)"#',$value,$matches);
                $this->boundary = isset($matches[1])?$matches[1]:null;
                $this->h[($i===false?$this->i:$i)] = $name.': '.(empty($value)?self::DEF:$value).self::D;
            }
        } else {
            $h = null;
            if(is_array($value)) {
                if(isset($value['name'],$value['adres'],$value['char'])) {
                    $h = (empty($value['name'])?'':'=?'.$value['char'].'?B?'.base64_encode($value['name']).'?= ').'<'.$value['adres'].'>';
                } elseif(isset($value['arg'],$value['char']) and is_array($value['arg'])) {
                    $str = array();
                    foreach($value['arg'] as $k=>$val) {
                        $str[] = (empty($k)?'':'=?'.$value['char'].'?B?'.base64_encode($k).'?= <').$val.(empty($k)?'':'>');
                    }
                    $h = (sizeof($str)>0?implode(', ',$str):self::DEF);
                } else {
                    $str = array();
                    foreach($value as $val) {
                        if(empty($val)) continue;
                        $str[] = $val;
                    }
                    $h = (sizeof($str)>0?implode(', ',$str):self::DEF);
                }
            } else {
                $h = (empty($value)?self::DEF:$value);
            }
            $this->h[($i===false?$this->i:$i)] = $name.': '.$h.self::D;
        }
        $this->i++;
    }

    public function setCookie($name,$value='',$exp=0,$path='',$domain='',$secure=false,$http=false) {}

    public function setFragment($str,$arg=null) {// test
        if($this->boundary) {
            if(empty($this->body)) {
                $this->body = '--'.$this->boundary.'--'.self::D.self::D;// self::D ???
            }
            $str0 = '--'.$this->boundary.self::D;
            if(is_array($arg)) {
                foreach($arg as $k=>$val) {
                    $str0 .= $k.': '.(string)$val.self::D;
                }
            }
            $str0 .= self::D;// добавление переносов, выяснить надо ли
            $str0 .= $str;
            $this->body = str_replace('--'.$this->boundary.'--',$str0.'--'.$this->boundary.'--',$this->body);
        } else {
            $this->body .= $str;
        }
    }

    public function getsHeader() {}

    public function getsBody() {
        try {
	    $to = '';
	    $subject = '';
	    for($i=0, $n=sizeof($this->h); $i<$n; $i++) {
                $m = null;
		if(preg_match('#(to|subject): ([^'.self::D.']*)#i',$this->h[$i],$m)) {
		    if(isset($m[1]) and strtolower($m[1]) == 'to') {
			$to = isset($m[2])?$m[2]:self::DEF;
		    } elseif(isset($m[1]) and strtolower($m[1]) == 'subject') {
			$subject = isset($m[2])?$m[2]:self::DEF;
		    }
			unset($this->h[$i]);
		}
	    }
            return mail($to,$subject,$this->body,implode('',$this->h));
        } catch(ErrorException $e) {
            $this->PRO['error']->handlerror($e);
            return false;
        }
    }

}
?>
