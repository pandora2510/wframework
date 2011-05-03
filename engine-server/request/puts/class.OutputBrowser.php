<?php
/**
 * @desc       Класс для вывода данных в браузер
 * @package    w framework
 * @category   engine/request/puts
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Output
 * @version    0.1.0
 */
class OutputBrowser extends Output {

    public function __init($a) {}

    public function setHeader($name,$value,$add=true) {
        $this->h[] = array('name'=>$name,'value'=>$value,'add'=>$add);
    }

    public function setCookie($name,$value='',$exp=0,$path='',$domain='',$secure=false,$http=false) {
        $this->c[] = array('name'=>$name,'value'=>$value,'exp'=>$exp,'path'=>$path,'domain'=>$domain,'secure'=>$secure,'http'=>$http);
    }

    public function getsHeader() {
        for($i=0, $n=sizeof($this->h); $i<$n; $i++) {
            header($this->h[$i]['name'].': '.$this->h[$i]['value'],$this->h[$i]['add']);
        }
        for($i=0, $n=sizeof($this->c); $i<$n; $i++) {
            setcookie($this->c[$i]['name'],$this->c[$i]['value'],$this->c[$i]['exp'],$this->c[$i]['path'],$this->c[$i]['domain'],$this->c[$i]['secure'],$this->c[$i]['http']);
        }
        return true;
    }

    public function setFragment($str,$arg=null) {
        $this->body .= $str;
    }

    public function getsBody() {
        echo $this->body;
        return true;
    }

}
?>
