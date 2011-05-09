<?php
/**
 * @desc       Класс для создания резервной копии БД PostgreSQL :EXPERIMENTAL
 * @package    w framework
 * @category   engine/backup
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Backup, DBQuery
 */
class DBBackupPostgreSQL extends Backup {

 protected $exp = 'pgsql';

    public function __init($a) {
        if(!($a instanceof DBQuery)) trigger_error('Object: "$a" - IS NOT AN INSTANCEOF OF CLASS: "DBQuery"',E_USER_ERROR);
        $this->res = $a;
    }
    
    /*
     * @link http://www.phpclasses.org/browse/file/21351.html
     */
    public function create($arg) {
        if(!$this->path) return false;
        if(!is_array($arg)) $arg = array($arg);
        $tables = array();

        $sql = 'SELECT relname AS tablename FROM pg_class WHERE relkind IN (\'r\') AND relname NOT LIKE \'pg_%\' AND relname NOT LIKE \'sql_%\' ORDER BY tablename';
        $res = $this->res->query($sql);
        foreach($res as $row) {
            if(!in_array($row['tablename'],$arg)) continue;
            $tables[] = array($this->escape_keyword($row['Name']));
        }

        $this->_open();

        // кол строк в таблице
        for($i=0, $n=sizeof($tables); $i<$n; $i++) {
            $_sequences = array();
            $SQL = 'CREATE TABLE '.$tables[$i].' (';
            
            $sql = 'SELECT attnum, attname, typname, atttypmod-4 AS atttypmod, attnotnull, atthasdef, adsrc AS def ';
            $sql .= 'FROM pg_attribute, pg_class, pg_type, pg_attrdef ';
            $sql .= 'WHERE pg_class.oid=attrelid ';
            $sql .= 'AND pg_type.oid=atttypid AND attnum>0 AND pg_class.oid=adrelid AND adnum=attnum ';
            $sql .= 'AND atthasdef=\'t\' AND lower(relname)=\'%1$s\' UNION ';
            $sql .= 'SELECT attnum, attname, typname, atttypmod-4 AS atttypmod, attnotnull, atthasdef, \'\' AS def ';
            $sql .= 'FROM pg_attribute, pg_class, pg_type WHERE pg_class.oid=attrelid ';
            $sql .= 'AND pg_type.oid=atttypid AND attnum>0 AND atthasdef=\'f\' AND lower(relname)=\'%1$s\'';

            $res = $this->res->query($sql,$tables[$i]);
            foreach($res as $row) {
                $attname = $this->escape_keyword($row['attname']);
                if(preg_match("/^nextval/",$row['def'])) {
                    $_t = explode("'",$row['def']);
                    if(isset($_t[1])) $_sequences[] = $_t[1];
                }
                $SQL .= $attname.' '.$row['typname'];
                if ($row['typname'] == "varchar") $SQL .= '('.$row['atttypmod'].')';
                if ($row['attnotnull'] == "t") $SQL .= ' NOT NULL';
                if ($row['atthasdef'] == "t") $SQL .= ' DEFAULT '.$row['def'];
                $SQL .= ",";
            }
            $SQL = rtrim($SQL,',');
            $SQL .= ');'."\n";

            if(sizeof($_sequences) > 0) {
                for($j=0, $m=sizeof($_sequences); $j<$m; $j++) {
                    $res = $this->res->query('SELECT * FROM %s',$_sequences[$j]);
                    foreach($res as $row) {
                        $str = 'CREATE SEQUENCE '.$_sequences[$j].' INCREMENT '.$row['increment_by'].' MINVALUE '.$row['min_value'];
                        $str .= ' MAXVALUE '.$row['max_value'].' START '.$row['last_value'].' CACHE '.$row['cache_value'].';'."\n";
                        $this->_write($str);
                    }
                }
            }

            $this->writeSQL($SQL);

            // запись данных
            if($this->SETT['rows'] < 1) continue;
            $rows = 0;
            $J = 0;
            $res = $this->res->query('SELECT COUNT(*) as count FROM %s',$tables[$i]);
            foreach($res as $row) $rows = $row['count'];

            while($rows > 0) {
                $SQL = 'INSERT INTO '.$tables[$i];
                $res = $this->res->query('SELECT * FROM %s LIMIT %d OFFSET $d',$tables[$i],$this->SETT['rows'],$this->SETT['rows']*$J);
                $col = null;
                $sqlt = array();
                $sqla = array();
                foreach($res as $row) {
                    if(!$col) {
                        $arr = array();
                        foreach($row as $k=>$v) {
                            $arr[] = $k;
                            $sqlt[$k] = pg_field_type($this->res->res(),pg_field_num($k));
                        }
                        $SQL .= ' ('.implode(',',$arr).') VALUES ';
                    }
                    // проверять поля и их содержимое
                    foreach($row as $k=>$v) {
                        if(isset($sqlt[$k]) and $sqlt[$k] == 'bytea') {
                            $row[$k] = '\''.addcslashes(pg_escape_bytea($v),"\$").'\'';
                        } else {
                            if($v != '') {
                                $v = preg_replace("/\x0a/","",$v);
                                $v = preg_replace("/\x0d/","\r",$v);
                                $row[$k] = '\''.pg_escape_string(trim($v)).'\'';
                            } else {
                                $row[$k] = (isset($sqlt[$k]) and $sqlt[$k] == 't')?'\'\'':'NULL';
                            }                            
                        }
                    }
                    $sqla[] = '({'.implode('},{',$row).'})';
                }
                $this->_write($SQL.'('.implode('),(',$sqla).');'."\n");
                
                // индексы
                

                $J++;
                $rows = $rows - $this->SETT['rows'];
            }

            // запись индекса
            
            
        }

        return true;
    }

    private function escape_keyword($keyword) {
        if (in_array($keyword,array('desc'))) return('"'.$keyword.'"');
         else return($keyword);
    }

}
?>
