<?php 
/**
 * @desc       Класс для автозагрузки классов ядра w framework
 * @package    w framework
 * @category   engine
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, RecursiveDirectoryIterator
 * @version    0.1.2
 */
class Autoloader extends Object {
	
 const NOT_FOUND = '.CLASS_NOT_FOUND';
 protected $paths = array();
 private $cache = false;
	
    public function __init($a) {
        if(!file_exists(dirname($this->SETT['cpath']))) mkdir(dirname($this->SETT['cpath']),0777,true);
	if($this->SETT['cache']) {
            if(is_file($this->SETT['cpath'])) {
		$this->paths = require($this->SETT['cpath']);
            } else {
		$st = true;
		$this->cache = true;
            }
	} else {
            $st = true;
            $this->cache = false;
	}
	if(isset($st)) {
            $obj = new RecursiveDirectoryIterator($this->SETT['bpath']);
            $this->__dir($obj);
	}
	spl_autoload_register(array($this,'__import'));
    }
	
    protected function __dir($obj) {
        foreach($obj as $file) {
            if($obj->hasChildren()) {
		$this->__dir($obj->getChildren());
            } elseif($obj->isFile()) {
		$f = explode('.',basename($file));
		if(!isset($f[2]) or $f[2] != 'php' or empty($f[0])) continue;
		$this->paths[$f[1]] = str_replace($this->SETT['bpath'],'',$file);
            }
	}
    }
	
    final public function __import($name) {
        require($this->SETT['bpath'].(isset($this->paths[$name])?$this->paths[$name]:$name));
    }
	
    public function __destruct() {
	if($this->cache) {
            $data = '';
            foreach($this->paths as $k=>$val) {
		if(!empty($data)) $data .= ','."\n";
		$data .= '\''.$k.'\'=>\''.$this->paths[$k].'\'';
            }
            file_put_contents($this->SETT['cpath'],'<?php return array('.$data.'); ?>',LOCK_EX);
	}
	spl_autoload_unregister(array($this,'__import'));
    }
	
}

?>
