<?php
/**
 * @desc       Класс для выполнения 'пользовательского кода' при обработке запросов
 * @package    w framework
 * @category   engine/request
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, ErrorException
 * @version    0.3.1
 */
class Model extends Object {

    public function __init($a) {}

    public function get($path) {
        $obj = $this->PRO;
        // подключение файла
        try {// проверить перехват ошибки!!!
            return include($this->SETT['bpath'].$path);
        } catch(ErrorException $e) {
            $this->PRO['error']->handlerror($e);
        }
    }

}
?>
