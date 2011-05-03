<?php
/**
 * @desc       Класс для работы с tar архивами
 * @package    w framework
 * @category   engine/archive
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, ArchiveException
 * @version    0.1.1
 */
class Tar extends Object {//@

 const BLOCK_LITE = 512;
 const BLOCK_BIG = 524288;
 protected $block_big = 0;
 protected $f = null;
 protected $read = false;

 protected $header = array();
 protected $readBody = 0; // количество байт которое нужно прочитать

 protected $topath = null;
 protected $i = null;

    public function __init($a) {
        $this->block_big = self::BLOCK_BIG;
    }

    public function add($path,$prefix='') {// сделать нормализацию путей!!!
        if(empty($prefix)) {
            $prefix = str_replace('\\','/',dirname($path).D);
            $path = $this->normalizePath($path);
        }
        if(!file_exists($path)) throw new ArchiveException('Object "'.$path.'" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
        $pathn = str_replace($prefix,'',$path);
        
        if(is_dir($path)) {
            // dir
            $h = $this->getHeader($path,array('filename'=>$this->normalizePath($pathn.D),'typeflag'=>5));
            $this->writeBlock($h);

            $dir = new DirectoryIterator($path);
            foreach($dir as $file) {
                if($dir->isDot()) continue;
                $this->add($file->getPathname(),$prefix);
            }            
        } elseif(is_link($path)) {
            $h = $this->getHeader($path,array('filename'=>$this->normalizePath($pathn),'typeflag'=>2,'linkname'=>readlink($path)));
            $this->writeBlock($h);
            // без тела файла, наверное
        } else {
            $f = fopen($path,'rb');
            flock($f,LOCK_EX);

            $h = $this->getHeader($path,array('filename'=>$this->normalizePath($pathn),'typeflag'=>0));
            $this->writeBlock($h);
            
            $size = (int)filesize($path);
            while($size > $this->block_big) {
                $this->_write(fread($f,$this->block_big));
                $size = $size - (int)$this->block_big;
            }
            $this->_write(fread($f,$size));
            $null = ceil($size/self::BLOCK_LITE)*self::BLOCK_LITE - $size;
            $null = 'a'.(int)$null;
            $this->_write(pack($null,''));

            flock($f,LOCK_UN);
            fclose($f);
        }
    }

    private function normalizePath($path) {
        if(empty($path)) return $path;
        $path = str_replace('\\','/',$path);
        $path = preg_replace('#(/+)#i','/', $path);
        if(in_array($path,array('.','..','../','./','/')) or strpos($path,':/') !== false)
            throw new ArchiveException('Incorrect path - "'.$path.'"',E_USER_ERROR,0,__FILE__,__LINE__);
        return $path;
    }

    protected function setHeader($bindata) {
        $h = array();
        if(strlen($bindata) != 512) return $h;
        // calcute checksum
        $checksum = 0;
        for($i=0; $i<148; $i++) $checksum += ord(substr($bindata,$i,1));
        for($i=148; $i<156; $i++) $checksum += ord(' ');
        for($i=156; $i<512; $i++) $checksum += ord(substr($bindata,$i,1));

        $data = unpack('a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor',$bindata);

        // проверка checksum
        $h['checksum'] = OctDec(trim($data['checksum']));
        // высчитывать правильный хэш
        if($h['checksum'] == 0) return $h;
        if($h['checksum'] != $checksum) throw new ArchiveException('Checksums do not match',E_USER_ERROR,0,__FILE__,__LINE__);

        $h['filename'] = $data['filename']; // преобразовывать слешы в пути при необходимости
        $h['filemode'] = OctDec(trim($data['mode']));
        $h['uid'] = OctDec(trim($data['uid']));
        $h['gid'] = OctDec(trim($data['gid']));
        $h['size'] = OctDec(trim($data['size']));
        $h['mtype'] = OctDec(trim($data['mtime']));
        $h['typeflag'] = $data['typeflag'];
        if($h['typeflag'] == 5) $h['size'] = 0;
        $h['linkname'] = $data['link'];
        //$h['magic'] = trim($data['magic']);
        //$h['version'] = trim($data['version']);
        //$h['uname'] = trim($data['uname']);
        //$h['gname'] = trim($data['gname']);
        //$h['devmajor'] = trim($data['devmajor']);
        //$h['devminor'] = trim($data['devminor']);

        return $h;
    }

    protected function getHeader($path,array $arg) {
        $info = lstat($path);
        $h = array(
            'filename'=>'',
            'filemode'=>'',
            'uid'=>'',
            'gid'=>'',
            'size'=>'',
            'mtype'=>'',
            'checksum'=>'',
            'typeflag'=>'',
            'linkname'=>'',
            'magic'=>'',
            'version'=>'',
            'uname'=>'',
            'gname'=>'',
            'devmajor'=>'',
            'devminor'=>'',
            'prefix'=>'',
            'other'=>''
        );
        
        if(strlen($arg['filename']) > 99) {
            $h['size'] = sprintf("%11s ",DecOct(strlen($arg['filename'])));
            $h['typeflag'] = 'L';
            $h['filename'] = '././@LongLink';
            $h['filemode'] = 0;
            $h['uid'] = 0;
            $h['gid'] = 0;
            $h['mtype'] = 0;
        } elseif($arg['typeflag'] == 2) {
            $info = lstat($path);
            $h['filename'] = $arg['filename'];
            $h['uid'] = sprintf("%07s",DecOct($info[4]));
            $h['gid'] = sprintf("%07s",DecOct($info[5]));
            $h['filemode'] = sprintf("%07s",DecOct($info['mode'] & 000777));
            $h['mtype'] = sprintf("%011s",DecOct($info['mtime']));
            $h['typeflag'] = '2';
            $h['linkname'] = $arg['linkname'];//readlink($path);// ????????????????????????????????
            $h['size'] = sprintf("%011s",DecOct(0));
            $h['magic'] = 'ustar ';
            if(function_exists('posix_getpwuid')) {
                $uinfo = posix_getpwuid($info[4]);
                $ginfo = posix_getgrgid($info[5]);
                $h['uname'] = $uinfo['name'];
                $h['gname'] = $ginfo['name'];
            }
        } elseif($arg['typeflag'] == 5) {
            $info = lstat($path);
            $h['filename'] = $arg['filename'];
            $h['uid'] = sprintf("%07s",DecOct($info[4]));
            $h['gid'] = sprintf("%07s",DecOct($info[5]));
            $h['filemode'] = sprintf("%07s",DecOct($info['mode'] & 000777));
            $h['mtype'] = sprintf("%011s",DecOct($info['mtime']));
            $h['typeflag'] = '5';
            $h['size'] = sprintf("%011s",DecOct(0));
            $h['magic'] = 'ustar ';
            if(function_exists('posix_getpwuid')) {
                $uinfo = posix_getpwuid($info[4]);
                $ginfo = posix_getgrgid($info[5]);
                $h['uname'] = $uinfo['name'];
                $h['gname'] = $ginfo['name'];
            }
        } else {
            $info = lstat($path);
            $h['filename'] = $arg['filename'];
            $h['uid'] = sprintf("%07s",DecOct($info[4]));
            $h['gid'] = sprintf("%07s",DecOct($info[5]));
            $h['filemode'] = sprintf("%07s",DecOct($info['mode'] & 000777));
            $h['mtype'] = sprintf("%011s",DecOct($info['mtime']));
            $h['typeflag'] = '0';
            $h['size'] = sprintf("%011s",DecOct($info['size']));
            $h['magic'] = 'ustar ';
            if(function_exists('posix_getpwuid')) {
                $uinfo = posix_getpwuid($info[4]);
                $ginfo = posix_getgrgid($info[5]);
                $h['uname'] = $uinfo['name'];
                $h['gname'] = $ginfo['name'];
            }
        }

        // упаковка и подсчет checksum
        $first = pack('a100a8a8a8a12a12',$h['filename'],$h['filemode'],$h['uid'],$h['gid'],$h['size'],$h['mtype']);
        // checksum
        $last = pack('a1a100a6a2a32a32a8a8a155a12',$h['typeflag'],$h['linkname'],$h['magic'],$h['version'],$h['uname'],$h['gname'],$h['devmajor'],$h['devminor'],$h['prefix'],$h['other']);
        $checksum = pack('a8',sprintf('%06s ',DecOct($this->checksum($first,$last))));

        return array($first,$checksum,$last);
    }

    private function checksum($first,$last) {
        $chs = 0;
        for($i=0; $i<148; $i++) $chs += ord(substr($first,$i,1));
        for($i=148; $i<156; $i++) $chs += ord(' ');
        for($i=156, $j=0; $i<512; $i++, $j++) $chs += ord(substr($last,$j,1));
        return $chs;
    }

    // корректировка путей!!!

    protected function readBlock($length='') {
        if(empty($length)) $length = self::BLOCK_LITE;
        return $this->_read($length);
    }

    protected function writeBlock($arg) {
        if(is_array($arg)) {
            foreach($arg as $val) $this->writeBlock($val);
        } else {
            $this->_write($arg);
        }
    }    

    public function openRead($file) {
        if(!file_exists($file) or !is_file($file)) throw new ArchiveException('File - "'.$file.'" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
        $this->close();
        $this->_open($file,'rb');
        $this->read = true;
    }

    public function openWrite($file) {
        if(file_exists($file) and is_file($file)) throw new ArchiveException('File - "'.$file.'" - ALREADY EXISTS',E_USER_ERROR,0,__FILE__,__LINE__);
        $this->close();
        $this->_open($file,'wb');
        $this->_lock();
        $this->read = false;
    }

    protected function _open($file,$mode) {
        $this->f = fopen($file,$mode);
    }

    public function close() {
        if(is_resource($this->f)) {
            if(!$this->read) $this->_unlock();
            $this->_close();
            $this->read = false;
        }
    }

    protected function _close() {
        if(!$this->read) {
            // запись null 1024
            $this->_write(pack('a1024',''));
        }
        fclose($this->f);
    }

    protected function _read($length) {
        return fread($this->f,(int)$length);
    }

    protected function _write($data) {
        return fwrite($this->f,$data);
    }

    protected function _lock() {
        flock($this->f,LOCK_EX);
    }

    protected function _unlock() {
        flock($this->f,LOCK_UN);
    }

    public function extractHead() {
        if($this->readBody > 0) {
            $s = $this->readBody;
            while($s > $this->block_big) {
                $s = $s - strlen($this->readBlock($this->block_big));
            }
            $this->readBlock($s);
            $this->readBody = 0;
        }
        $data = $this->readBlock();
        $h = $this->setHeader($data);
        if(!isset($h['filename'])) return false;
        $h['filename'] = $this->normalizePath2($h['filename']);
        $this->header = $h;
        if(!isset($h['typeflag'])) return false;
        if(!in_array($h['typeflag'],array('L',2,5)) and (isset($h['size']) and $h['size'] > 0)) {
            $this->readBody = ceil($h['size']/self::BLOCK_LITE)*self::BLOCK_LITE;// - $h['size'];
        }
        return $h;
    }

    public function extract($topath) {// $this->SETT['ow'] - перезапись уже существующих файлов и папок
        if($topath != $this->i) {
            $this->i = $topath;
            $this->topath = $topath;
            $this->topath = $this->normalizePath2($this->topath);
            if(!is_dir($this->topath)) throw new ArchiveException('Directory - "'.$this->topath.'" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);            
        }
        clearstatcache();
        if($this->header['typeflag'] == 'L') {
            throw new ArchiveException('header of the current block contains errors',E_USER_ERROR,0,__FILE__,__LINE__);
        } elseif($this->header['typeflag'] == 2) {
            // link
            // с сылками пока не работает!!!
        } elseif($this->header['typeflag'] == 5) {
            $create = false;
            if(file_exists($this->topath.D.$this->header['filename'])) {
                if($this->SETT['ow']) $create = true;
            } else {
                $create = true;
            }
            if($create) return mkdir($this->topath.D.$this->header['filename'],0777,true); else return false;
        } else {
            $create = false;
            if(file_exists($this->topath.D.$this->header['filename'])) {
                if($this->SETT['ow']) $create = true;
            } else {
                $create = true;
            }
            if($create) {
                $f = fopen($this->topath.D.$this->header['filename'],'wb');
                flock($f,LOCK_EX);
                $ost = $this->readBody - $this->header['size'];
                $s = $this->header['size'];
                while($s > $this->block_big) {
                    fwrite($f,$this->readBlock($this->block_big));
                    $s = $s - $this->block_big;
                }
                fwrite($f,$this->readBlock($s));
                flock($f,LOCK_UN);
                fclose($f);
                $this->readBlock($ost);
                $this->readBody = 0;
                // touch
                touch($this->topath.D.$this->header['filename'],$this->header['mtype']);
                if ($this->header['filemode'] & 0111) {
                    $mode = fileperms($this->topath.D.$this->header['filename']) | (~umask() & 0111);
                    chmod($this->topath.D.$this->header['filename'],$mode);
                }
                return (filesize($this->topath.D.$this->header['filename']) == $this->header['size']);
            } else {
                return false;
            }
        }        
    }

    protected function normalizePath2($path) {
        // решить проблемы с ./ ../ и C:\ и /
        $path = str_replace('/',D,$path);
        $path = str_replace('\\',D,$path);
        if(in_array($path,array('.','..','..'.D,'.'.D,D)) or strpos($path,':'.D) !== false)
            throw new ArchiveException('Incorrect path - "'.$path.'"',E_USER_ERROR,0,__FILE__,__LINE__);
        return $path;
    }

    public function __destruct() {
        $this->close();
    }

}
?>
