<?php
/**
 * @desc       Класс для создания резервной копии файловой структуры
 * @package    w framework
 * @category   engine/backup
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Backup, Tar
 * @version    0.1.0
 */
class FSBackup extends Backup {

 protected $exp = 'tar.gz';

    public function __init($a) {
        if(!($a instanceof Tar)) trigger_error('Object: "$a" - IS NOT AN INSTANCEOF OF CLASS: "Tar"',E_USER_ERROR);
        $this->res = $a;
    }

    public function create($arg) {
        if(!$this->path) return false;
        if(!is_array($arg)) $arg = array($arg);
        $this->res->openWrite($this->path);
        foreach($arg as $k=>$val) {
            $this->res->add($val);
        }
        $this->res->close();
        return true;
    }

}
?>
