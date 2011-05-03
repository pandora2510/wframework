<?php
/**
 * @desc       Класс для реализации локализации на стороне сервера
 * @package    w framework
 * @category   engine/request
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, Actions, ActionException
 * @version    0.1.0
 */
class SL10n extends Object {

 protected $arg = array();
 protected $strs = array();
 private $name = null;
 private $loc = null;

    public function __init($a) {
        try {
            if(!$this->PRO['request']['actions'] instanceof Actions)
                throw new ActionException('Object: request/actions - IS NOT AN INSTANCEOF OF CLASS: "Actions"',E_USER_ERROR,0,__FILE__,__LINE__);
            $this->arg = $this->PRO['request']['actions']->getL10n();
        } catch(ActionException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }
    // в файле l10n все строки должны быть в двойных кавычках!!!
    public function prepare($name,$loc) {
        $this->space($name,$loc);
        if(!isset($this->strs[$name][$loc])) {
            try {
                $str = file_get_contents($this->SETT['folder'].D.$name.D.$this->arg[$name][$loc]);
                // удаление лишных символов
                $str = preg_replace('#^[^{]*#i','',$str);
                $str = preg_replace('#,*\s*}[^}]*$#i','}',$str);
                // замена одинарных кавычек
                // ...
                // json_decode
                $this->strs[$name][$loc] = json_decode($str,true);
            } catch(ErrorException $e) {
                // ничего неделать
            }
        } elseif($this->strs[$name][$loc] === false) {
            // нет файлов и тд
            return 'l10n-error';
        }
    }

    public function space($name,$loc) {
        $this->name = $name;
        $this->loc = $loc;
    }

    public function l10n($str) {
        return isset($this->strs[$this->name][$this->loc][$str])?$this->strs[$this->name][$this->loc][$str]:$str;
    }

}
?>
