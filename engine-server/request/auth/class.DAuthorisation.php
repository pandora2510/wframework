<?php
/**
 * @desc       Класс для авторизации пользователей с динамическими привелегиями
 * @package    w framework
 * @category   engine/request/auth
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, ErrorException, Authorisation
 * @version    0.3.4
 */
class DAuthorisation extends Authorisation {

    protected function  _default() {
        parent::_default();
        $this->data['dPrivilege'] = $this->getDPrivilege();
        $this->data['dPrivilege'] = $this->data['dPrivilege'] < 0?0:(int)$this->data['dPrivilege'];
    }

    private function getDPrivilege() {// Подключаемый файл должен возвращать привилегию
        $obj = $this->PRO;
        try {// проверить перехват ошибки!!!
            return include($this->SETT['dPrivilege']);
        } catch(ErrorException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }
    
    public function checkPrivilege($level,$dynamic=false) {
        $pr = $dynamic?$this->data['dPrivilege']:$this->data['privilege'];
        return ((int)$pr>=(int)$level);
    }

    public function writeData() {
        unset($this->data['dPrivilege']);
        parent::writeData();
    }

}
?>
