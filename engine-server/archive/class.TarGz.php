<?php
/**
 * @desc       Класс для работы с tar архивами сжатыми gzip
 * @package    w framework
 * @category   engine/archive
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Tar
 */
class TarGz extends Tar {

    public function __init($a) {
        parent::__init($a);
        // проверка наличия Zlib
        if(!function_exists('gzcompress')) throw new ArchiveException('Extension "Zlib" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
    }

    protected function _open($file,$mode) {
        // проверка формата файла
        if(file_exists($file)) {
            $f = fopen($file,'rb');
            if(fread($f,2) != "\37\213") throw new ArchiveException('File("'.$file.'") unknown format',E_USER_WARNING,0,__FILE__,__LINE__);
            fclose($f);
        }
        $this->f = gzopen($file,$mode);
    }

    protected function _close() {
        if(!$this->read) {
            // запись null 1024
            $this->_write(pack('a1024',''));
        }
        gzclose($this->f);
    }

    protected function _read($length) {
        return gzread($this->f,(int)$length);
    }

    protected function _write($data) {
        return gzwrite($this->f,$data);
    }

}
?>
