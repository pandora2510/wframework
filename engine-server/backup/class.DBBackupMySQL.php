<?php
/**
 * @desc       Класс для создания резервной копии БД MySQL
 * @package    w framework
 * @category   engine/backup
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Backup, DBQuery
 * @version    0.1.1
 */
class DBBackupMySQL extends Backup {

 protected $exp = 'mysql';

    public function __init($a) {
        if(!($a instanceof DBQuery)) trigger_error('Object: "$a" - IS NOT AN INSTANCEOF OF CLASS: "DBQuery"',E_USER_ERROR);
        $this->res = $a;
    }

    public function create($arg) {
        if(!$this->path) return false;
        if(!is_array($arg)) $arg = array($arg);
        $tables = array();
        $res = $this->res->query('SHOW TABLE STATUS');
        foreach($res as $row) {// Name, Rows
            if(!in_array($row['Name'],$arg)) continue;
            $tables[] = array('name'=>$row['Name'],'rows'=>$row['Rows']);
        }
        $f = fopen($this->path,'wb');
        flock($f,LOCK_EX);

        for($i=0, $n=sizeof($tables); $i<$n; $i++) {
            $res = $this->res->query('SHOW CREATE TABLE %s',$tables[$i]['name']);
            foreach($res as $row) {
                fwrite($f,"\r\n\n".str_replace('CREATE TABLE','CREATE TABLE IF NOT EXISTS',$row['Create Table']).';'."\n");
                // inserts
                if($this->SETT['rows'] < 1) continue;
                $rows = $tables[$i]['rows'];
                $j = 0;
                while($rows > 0) {
                    $res1 = $this->res->query('SELECT * FROM %s LIMIT %d OFFSET %d',$tables[$i]['name'],$this->SETT['rows'],$this->SETT['rows']*$j);
                    $sql = 'INSERT INTO `'.$tables[$i]['name'].'` ';
                    $col = null;
                    $sqla = array();
                    foreach($res1 as $row1) {
                        if(!$col) {
                            $arr = array();
                            foreach($row1 as $k=>$v) {
                                $arr[] = $k;
                            }
                            $sql .= '(`'.implode('`,`',$arr).'`) VALUES ';
                            $col = true;
                        }
                        foreach($row1 as $k=>$v) {
                            // остальные условия проверки
                            if(!is_numeric($v)) $row1[$k] = '\''.$v.'\'';
                        }
                        $sqla[] = '('.implode(', ',$row1).')';
                    }

                    $sql .= implode(',',$sqla);

                    // write
                    fwrite($f,"\r\n".$sql.';'."\n");

                    $j++;
                    $rows = $rows - $this->SETT['rows'];
                }
            }
        }

        flock($f,LOCK_UN);
        fclose($f);
        return true;
    }

}
?>
