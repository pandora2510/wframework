<?php
/**
 * @desc       Расширенный класс для автозагрузки классов ядра w framework
 * @package    w framework
 * @category   engine
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Autoloader
 * @version    0.1.0
 */
class Accelerateloader extends Autoloader {

 private $acache = false;

    public function  __init($a) {
        parent::__init($a);
        if(!file_exists(dirname($this->SETT['apath']))) mkdir(dirname($this->SETT['apath']),0777,true);
        if($this->SETT['acache']) {
            if(file_exists($this->SETT['apath'])) {
                require($this->SETT['apath']);
            } else {
                if($this->SETT['className'] instanceof Traversable) $this->acache = true;
            }
        }
    }

    public function  __destruct() {
        if($this->acache) {
            $f = fopen($this->SETT['apath'],'wb');
            flock($f,LOCK_EX);
            foreach($this->SETT['className'] as $value) {
                if(!isset($this->paths[$value])) continue;
                try {
                    // чистить файл от комментариев и лишних пробелов
                    fwrite($f,file_get_contents($this->SETT['bpath'].$this->paths[$value]));
                } catch(ErrorException $e) {
                    // ничего не делать
                }
            }
            flock($f,LOCK_UN);
            fclose($f);
        }
        parent::__destruct();
    }

}
?>
