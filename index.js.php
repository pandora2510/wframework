<?php
/**
 * @desc       Файл для генерации параметров контроллера
 * @package    w framework
 * @category   index.js.php
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Prototype, Object, Autoloader, Handling, Actions, CController, Input, Output, CCtrlException
 * @version    0.3.0
 */

// метки для вычисление производительности
define('STARTTIME',microtime());
define('STARTMEMORY',memory_get_usage());
define('STARTPEAKMEMORY',0);

// объяление системных констант
define('D',DIRECTORY_SEPARATOR); // файловый разделитель
define('SD','.'.D); // положение текущего файла относительно корневой папки на сервере, папка указывается в конфиге web сервера
define('AP',dirname(__FILE__).D); // абсолютный путь к текущей папке
define('CHAR','UTF-8'); // название кодировки, которая используется по умолчанию
define('ENGINE','engine-server'); // папка с серверной частью frameworka
define('CENGINE','engine-client'); // папка с клиентской частью frameworka
define('APP','applications'); // папка с приложениями(экшенами)
define('SYSTEM','system'); // папка в которой хранится какая либо системная информация
define('PRJ','projects'); // PRJ - переменная тек проэкта
// константа, содержит или определяет по каким либо параметрам название текущего проекта
define('PROJECT',isset($_GET['PRJ'])?$_GET['PRJ']:'project1'); unset($_GET['PRJ']);

error_reporting(E_ALL);// уровень отображения ошибок

require_once(AP.SD.ENGINE.D.'class.Prototype.php');
require_once(AP.SD.ENGINE.D.'abstract.Object.php');
require_once(AP.SD.ENGINE.D.'class.Autoloader.php');

$obj = new Prototype(array(// важен порядок
    'settings'=>null,
    'autoloader'=>null,
    'error'=>null,
    'request'=>array('input'=>null,'output'=>null,'actions'=>null,'cctrl'=>null)
));

$obj['settings'] = require(AP.SD.PRJ.D.PROJECT.D.'configs.php');// путь к файлу с конфигом!!!

// пути жесткие!!! Между путями и классами есть зависимости
$obj['autoloader'] = new Autoloader($obj);// !!!жестко привязаны к пути
$obj['error'] = new Handling($obj);// !!!жестко привязаны к пути

try {

    $obj['request']['input'] = new Input($obj,array('get'=>$_GET));
    $obj['request']['input']->clean();
    $obj['request']['output'] = new OutputBrowser($obj);
    $obj['request']['actions'] = new Actions($obj);
    $obj['request']['cctrl'] = new CController($obj);

    $obj['request']['cctrl']['engine-client'] = CENGINE;

    $obj['request']['cctrl']->checkDep();
    $obj['request']['cctrl']->request();

    $obj['request']['output']->setFragment($obj['request']['cctrl']->__toString());
    $obj['request']['output']->getsHeader();
    $obj['request']['output']->getsBody();

} catch(ErrorException $e) {
    $obj['error']->handlerror($e);
}

//$fs = new FileSystem($obj);
//$fs->remove('./temp');

?>
