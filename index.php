<?php
/**
 * @desc       Файл index.php
 * @package    w framework
 * @category   index.php
 * @author     Checha Andrey
 * @copyright  (c) 2010 - 2011 Checha Andrey
 * @license    http://wframework.com/LICENSE
 * @link       http://wframework.com/
 * @uses       Prototype, Object, Autoloader
 * @version    0.5.2
 */

//ini_set('display_errors','on');
//ini_set('html_errors','on');

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
define('PRJ','projects'); // PRJ - переменная тек проэкта
define('ROOT',true);// используется для защиты прямого запуска php файлов
// константа, содержит или определяет по каким либо параметрам название текущего проекта
define('PROJECT',isset($_GET['PRJ'])?$_GET['PRJ']:'project1'); unset($_GET['PRJ']);

error_reporting(E_ALL);// уровень отображения ошибок

require_once(AP.SD.ENGINE.D.'class.Prototype.php');
require_once(AP.SD.ENGINE.D.'abstract.Object.php');
require_once(AP.SD.ENGINE.D.'class.Autoloader.php');
require_once(AP.SD.ENGINE.D.'class.Accelerateloader.php');

// wframework
$obj = new Prototype(array(// схема, важен порядок и расположение, изменять расположение категорически не рекомендуется!!!
    'settings'=>null,
    'autoloader'=>null,
    'storage'=>array('db'=>null,'temporary'=>null,'fs'=>null),
    'error'=>null,
    'extension'=>array('UTF8'=>null,'validate'=>null),
    'cache'=>array('actions'=>null),
    'request'=>array('input'=>null,'output'=>null,'session'=>null,'auth'=>null,
                     'model'=>null,'actions'=>null,'document'=>null,'url'=>null,
                     'outputmail'=>null)
));

// настройки для классов
$obj['settings'] = require(AP.SD.PRJ.D.PROJECT.D.'configs.php');// путь к файлу с конфигом, для каждого проекта конфиг свой

// пути жесткие!!! Между путями и классами есть зависимости
$obj['autoloader'] = new Accelerateloader($obj);// !!!жестко привязаны к пути
$obj['error'] = new Handling($obj);// !!!жестко привязаны к пути
$obj['test'] = new Test($obj);
$obj['storage']['db'] = new DBQueryMySQLi($obj);
//$obj['storage']['temporary'] = new TMemcache($obj);
//$obj['cache']['actions'] = new MCache($obj,null);

$obj['extension']['UTF8'] = new UTF8($obj);

$obj['request'] = new RequestDebug($obj,$obj['test']);//new Request($obj);

try {

    $obj['request']['input'] = new Input($obj,array('get'=>$_GET,'cookie'=>$_COOKIE,'server'=>$_SERVER,'post'=>$_POST,'files'=>$_FILES));
    if(!empty($_FILES)) $obj['request']['input']->transformation_files('files');
    $obj['request']['output'] = new OutputBrowser($obj);
    $obj['request']['session'] = new FileSession($obj);
    $obj['request']['auth'] = new DAuthorisation($obj);
    $obj['request']['model'] = new Model($obj);
    $obj['request']['actions'] = new Actions($obj);
    $obj['request']['url'] = new URL($obj);

    $obj['request']['url']->prepareFile();
    $obj['request']['input']->clean();
    $obj['request']['session']->begin();
    $obj['request']['auth']->readData();

    $obj['request']->checkDep();
    $obj['request']->response();
    
    $obj['request']['output']->setFragment($obj['request']['document']->__toString());
    $obj['request']['output']->getsHeader();
    $obj['request']['output']->getsBody();

    $obj['request']['auth']->writeData();
    $obj['request']['session']->comit();

} catch(ErrorException $e) {
    $obj['error']->handlerror($e);
}
unset($obj['request']['auth']);// избавление от возможных багов

// запись лога
$obj['test']->tofile();

// проверка mail
//$obj['request']['outputMail'] = new OutputMail($obj);
//$m = new Mail($obj);
//$m->to('chertjaga@mail.ru');
//$m->subject('Hello world!!!');
//$m->message('Проверка правильной работы почты!!!');
//echo $m->send();


?>
