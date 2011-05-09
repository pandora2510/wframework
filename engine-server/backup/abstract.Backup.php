<?php
/**
 * @desc       Базовый класс для создания резервных копий системы
 * @package    w framework
 * @category   engine/backup
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, DirectoryIterator
 */
abstract class Backup extends Object {

 protected $res = null; 
 protected $exp = 'backup';
 private $path = null;
 private $f = null;

    public function __init($a) {
        $this->res = $a;
    }

    public function tofolder($path) {
        if(!file_exists($path.D)) mkdir($path.D,0777,true);
        $name = get_class($this).'.'.date('Y-m-d-H-i-s').'.'.$this->exp;
        $this->path = $path.D.$name;
    }

    abstract public function create($arg);

    public function gs() {// папка должна уже быть задана
        if(!$this->path) return false;
        if(mt_rand(1,100) > $this->SETT['probability']) return null;
        $dir = new DirectoryIterator(dirname($this->path).D);
        $arr = array();
        foreach($dir as $file) {
            if($dir->isFile() and strpos($file->getFilename(),get_class($this)) !== false) $arr[] = $file->getPathname();
        }
        // сортировка
        sort($arr);
        while(sizeof($arr) > $this->SETT['total']) {
            unlink(array_shift($arr));
        }
    }

    protected function _open() {
        $this->f = fopen($this->path,'wb');
        flock($this->f,LOCK_EX);
    }

    protected function _write($data) {
        return fwrite($this->f,$data);
    }

    protected function _close() {
        flock($this->f,LOCK_UN);
        return fclose($this->f);
    }

}
?>
