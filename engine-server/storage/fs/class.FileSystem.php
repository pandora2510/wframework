<?php
/**
 * @desc       Класс для манипуляции с файловой системой
 * @package    w framework
 * @category   engine/storage/fs
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Object, FSException
 */
class FileSystem extends Object {
    
 private $AP = null;
 private $arg = array();

    public function  __init($a) {
        $this->AP = $this->SETT['AP'];
    }

    public function selfdir() {
        return $this->AP;
    }

    public function realPath($path) {
        $path = str_replace(D,'/',$path);
        $path = preg_replace('#\/(\.\/)+#i','/',$path);
        $path = preg_replace('#(\/){2,}#i','/',$path);
        $i = 0;
        while(preg_match('#\/[^\/]+\/\.\.\/#i',$path)) {
            if($i >= 3) break;
            $path = preg_replace('#\/[^\/]+\/\.\.\/#i','/',$path);
            $i++;
        }
        $path = str_replace('/',D,$path);
        return strpos($path,$this->SETT['AP'])!==false?$path:false;
    }

    /*
     * Все пути относительные!!!
     */

    public function copy($from,$to,$ow=false) {
        try {
            $ow = $ow?true:$this->SETT['ow'];
            $from = $this->realPath($this->SETT['AP'].$from);
            $to = $this->realPath($this->SETT['AP'].$to);
            if(is_file($from)) {
                if(is_dir($to)) return false;
                $to = $to.D.basename($from);
                $fe = file_exists($to);
                if($fe and !$ow) {
                    return false;
                } else {
                    return copy($from,$to,$ow);
                }
            } elseif(is_dir($from)) {
                $dir = new RecursiveDirectoryIterator($from);
                $this->_copy($dir,$to.D.basename($from),$ow);
                return true;
            } else {
                throw new FSException('File/folder - "'.$from.'" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
            }
        } catch(FSException $e) {
            $this->PRO['error']->handlerror($e);
            //return false;
        } catch(ErrorException $e) {
            $this->PRO['error']->handlerror($e);
            return false;
        }
    }

    protected function _copy($obj,$to,$ow) {
        foreach($obj as $file) {
            if($obj->isDot()) continue;
            if($obj->hasChildren()) {
                $this->_copy($obj->getChildren(),$to);                
                $path = $to.D.$obj->getSubPathName();
                if(!file_exists($path)) mkdir($path,0777,true);
            } elseif($obj->isFile()) {
                $path = $to.D.$obj->getSubPathName();
                $p = dirname($path);
                if(!file_exists($p)) mkdir($p,0777,true);
                $fe = file_exists($path);
                if($fe and !$ow) {
                    continue;
                } else {
                    copy($file->getPathname(),$path,$ow);
                }
            }
        }
    }

    public function remove($path) {
        try {
            $path = $this->realPath($this->SETT['AP'].$path);
            if(is_file($path)) return unlink($path);
            elseif(is_dir($path)) {
                $dir = new RecursiveDirectoryIterator($path);
                $this->_remove($dir);
                unset($dir);
                return rmdir($path);
            } else {
                throw new FSException('File/folder - "'.$path.'" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
            }
        } catch(FSException $e) {
            $this->PRO['error']->handlerror($e);
            return false;
        } catch(ErrorException $e) {
            $this->PRO['error']->handlerror($e);
            return false;
        }
    }

    protected function _remove($obj) {
        foreach($obj as $file) {
            if($obj->isDot()) continue;
            if($obj->hasChildren()) {
                $this->_remove($obj->getChildren());
                rmdir($file->getPathname());
            } elseif($obj->isFile()) {
                unlink($file->getPathname());
            }
        }
    }

    public function rename($path,$nname) {
        try {
            $path = $this->realPath($this->SETT['AP'].$path);
            if(file_exists($path)) {
                return rename($path,dirname($path).D.$nname);
            } else {
                throw new FSException('File/folder - "'.$path.'" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
            }
            return true;
        } catch(FSException $e) {
            $this->PRO['error']->handlerror($e);
            return false;
        } catch(ErrorException $e) {
            $this->PRO['error']->handlerror($e);
            return false;
        }
    }

    public function move($from,$to,$ow=false) {
        $ow = $ow?true:$this->SETT['ow'];
        if($this->copy($from,$to,$ow)) return $this->remove($from);
    }

    public function create($path,$f=true,$mode=0777,$ow=false) {
        try {
            $ow = $ow?true:$this->SETT['ow'];
            $path = $this->realPath($this->SETT['AP'].$path);
            $fe = file_exists($path);
            if($f and $fe) {
                if($ow) {
                    return file_put_contents($path,'',LOCK_EX);
                } else {
                    return -1;
                }
            } elseif($f and !$fe) {
                return touch($path);
            } elseif(!$f and !$fe) {
                return mkdir($path,(int)$mode);
            }
            return true;
        } catch(FSException $e) {
            $this->PRO['error']->handlerror($e);
            return false;
        } catch(ErrorException $e) {
            $this->PRO['error']->handlerror($e);
            return false;
        }
    }

    public function mode($path,$mode=0777,$rec=false) {
        try {
            $path = $this->realPath($this->SETT['AP'].$path);
            if(is_file($path)) {
                return chmod($path,(int)$mode);
            } elseif(is_dir($path)) {
                if($rec) {
                    $dir = new RecursiveDirectoryIterator($path);
                    $this->_mode($dir,(int)$mode);
                }
                return chmod($path,(int)$mode);
            } else {
                throw new FSException('File/folder - "'.$path.'" - NOT FOUND',E_USER_ERROR,0,__FILE__,__LINE__);
            }
        } catch(FSException $e) {
            $this->PRO['error']->handlerror($e);
            return false;
        } catch(ErrorException $e) {
            $this->PRO['error']->handlerror($e);
            return false;
        }
    }

    public function _mode($obj,$mode) {
        foreach($obj as $file) {
            if($obj->isDot()) continue;
            if($obj->hasChildren()) {
                $this->_mode($obj->getChildren(),$mode);
            }
            chmod($file->getPathname(),(int)$mode);
        }
    }

    public function __list($path,$rec=false) {
        $path = $this->realPath($this->SETT['AP'].$path.D);
        $this->arg = array('file'=>array(),'dir'=>array(),'path'=>$path);
        if($rec) {
            $dir = new RecursiveDirectoryIterator($path);
            $this->__dir($dir,true);
        } else {
            $dir = new DirectoryIterator($path);
            $this->__dir($dir,false);
        }
        return $this->arg;
    }

    protected function __dir($obj,$rec) {
        foreach($obj as $file) {
            if($obj->isDot()) continue;
            if($rec and $obj->hasChildren()) $this->__dir($obj->getChildren(),$rec);
            if($obj->isDir()) $this->arg['dir'][] = $rec?$obj->getSubPathName():$file->getFilename();
            elseif($obj->isFile()) $this->arg['file'][] = $rec?$obj->getSubPathName():$file->getFilename();
        }
    }

    public function fget($path) {
        $path = $this->realPath($this->SETT['AP'].$path);
        if(is_file($path)) return file_get_contents($path); else return false;
    }

    public function fput($path,$data,$ow=false) {
        $ow = $ow?true:$this->SETT['ow'];
        $path = $this->realPath($this->SETT['AP'].$path);
        if(is_file($path) and $ow) {
            return false;
        } else {
            return file_put_contents($path,$data,LOCK_EX);
        }
    }

    public function clear() {
        clearstatcache();
    }

}
?>
