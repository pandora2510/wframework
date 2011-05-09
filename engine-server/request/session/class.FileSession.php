<?php
/**
 * @desc       Класс для работы с сесиями хранящимися в файловой системе
 * @package    w framework
 * @category   engine/request/session
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Session, ErrorException
 */
class FileSession extends Session {

 private $f = null;
 private $file = null;

    public function __init($a) {
        parent::__init($a);
        // проверка наличия класса Prototype
        // создавать папку если папка отсутствует
        if(!file_exists($this->SETT['folder'])) mkdir($this->SETT['folder'],0777,true);
    }

    protected function _open() {
        $this->file = $this->SETT['folder'].D.'sess_'.$this->id;
        $this->f = fopen($this->file,'a+b');
        flock($this->f,LOCK_EX);
    }

    protected function _gs() {
        // сборщик мусора
        $dir = new DirectoryIterator($this->SETT['folder']);
        foreach($dir as $file) {
            if($file->isDir() or $file->isDot()) continue;
            try {
                if($file->getMTime()+(int)$this->SETT['maxlifetime'] < time()) unlink($file->getPathname());
            } catch(ErrorException $e) {
                // ничего не делать
            }
        }
    }

    protected function _read() {
        if(!is_resource($this->f)) return;
        $s = filesize($this->file);
        if($s > 0) {
            try {
                $this->data = unserialize(fread($this->f,$s));
            } catch(ErrorException $e) {
                $this->data = array();
            }
        } else {
            $this->data = array();
        }
    }

    protected function _write() {
        if(!is_resource($this->f)) return;
        ftruncate($this->f,0);
        fwrite($this->f,serialize($this->data));
    }

    protected function _destroy() {
        if(!is_resource($this->f)) return;
        ftruncate($this->f,0);
        flock($this->f,LOCK_UN);
        fclose($this->f);
        try {
            unlink($this->file);
        } catch(ErrorException $e) {
            // ничего не делать
        }
    }

    protected function _close() {
        if(!is_resource($this->f)) return;
        flock($this->f,LOCK_UN);
        fclose($this->f);
    }

}
?>
