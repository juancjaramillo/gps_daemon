<?php

//die("Estamos realizando un mantenimiento.<br>\nPor favor disculpe los inconvenientes.");

/* encender el reporte de errores (15) para ver todos los errores
y advertencias */
#error_reporting(15);
ini_set("session.gc_maxlifetime", "18000");
date_default_timezone_set('America/Bogota');
ini_set('default_charset', "ISO-8859-1");

//Verificar que exista la librer�a xdebug:
//if(!function_exists("xdebug_call_file")){
//    die("Por favor instale la librer�a XDEBUG as�:<br>\n# pear install xdebug-beta<br>\nVer <a href=\"http://www.xdebug.org/install.php\">http://www.xdebug.org/install.php</a>");
//}

/* define un objeto gen�ico */
//class object {};

/* cambia la configuracin del nuevo object */
$CFG = new \stdClass;

$CFG->dirroot = dirname(__FILE__);

if (!isset($_SERVER["SERVER_ADDR"])) {
    $server = "127.0.0.1";
} else {
    $server = $_SERVER["SERVER_ADDR"];
}

$CFG->wwwroot = 'http://xxx.xxx.xxx.xxx/aidadmin';

$CFG->common_libdir = $CFG->dirroot . "/lib";
$CFG->adminroot = $CFG->dirroot . "/admin";
$CFG->templatedir = "$CFG->adminroot/templates";
$CFG->templatePublicdir = $CFG->dirroot . "/templates";
$CFG->admin_dir = $CFG->wwwroot . "/admin/";
$CFG->admin_img_dir = $CFG->wwwroot . "/images";
$CFG->filesdir = "/files";
$CFG->tmpdir = "$CFG->dirroot/tmp";
$CFG->libdir = "$CFG->dirroot/lib";
$CFG->modulesdir = "$CFG->adminroot/modules";
$CFG->jsdir = "$CFG->dirroot/js";
$CFG->imagedir = "$CFG->admin_dir/images";
$CFG->offset_gmt = 0;
$CFG->gmtoffset = 0;
$CFG->offset = 0.0025;
$CFG->sesion = "promosession";
$CFG->resultados = 10;
$CFG->siteTitle = "PA ESP";
$CFG->nombreSitioCompleto = "Promoxxxx";
$CFG->siteLogo = $CFG->wwwroot . "/images/logo1.png";
$CFG->objectPath = $CFG->common_libdir . "/entidades_v_1.3";
require $CFG->common_libdir . "/stdlib.php";
require $CFG->common_libdir . "/funciones_mtto.php";
require $CFG->common_libdir . "/funciones_informes.php";
require $CFG->common_libdir . "/funciones_opera.php";

$CFG->mainPage = "frames.php";
$CFG->metrosXgrado = 0.0000089827;

//Para sobreescribir algunas variables dependiendo de donde est� instalado.
if (file_exists($CFG->dirroot . "/application." . $server . ".php")) {
    require $CFG->dirroot . "/application." . $server . ".php";
}

/* define el comportamiento de los errores de la base de datos, como es en periodo de dise�o,
 * se prenden todos los debugging  */
$DB_DEBUG = true;
$DB_DIE_ON_FAIL = true;

$CFG->dbhost = "localhost";
$CFG->dbname = "xxxx";
$CFG->dbuser = "xxxx";
$CFG->dbpass = "xxxxxx**";

$CFG->dbhost_geo_postgres = "localhost";
$CFG->dbuser_geo_postgres = "xxxxx";
$CFG->dbpass_geo_postgres = "xxxxx**";
$CFG->dbname_geo_postgres = "xxxx";

$CFG->dbhost_osm = "localhost";
$CFG->dbuser_osm = "xxx";
$CFG->dbpass_osm = "xxxxx**";
$CFG->dbname_osm = "xxx";

$CFG->dbhost_routing = "localhost";
$CFG->dbuser_routing = "xxxx";
$CFG->dbpass_routing = "xxxx**";
$CFG->dbname_routing = "xxxx";

/*** HASTA AQUI***/

/****NUEVAS CONEXIONES**/

$CFG->dbhost_promoambiental = "xxxxxx.com";
$CFG->dbname_promoambiental = "xxxxx";
$CFG->dbuser_promoambiental = "xxxxx";
$CFG->dbpass_promoambiental = "xxxxx";

$CFG->dbhost_dbnacionalproduccion = "xxx.xxx.xxx.xxx";
$CFG->dbname_dbnacionalproduccion = "xxxxx";
$CFG->dbuser_dbnacionalproduccion = "xxxxx";
$CFG->dbpass_dbnacionalproduccion = "xxxxx";

$CFG->dbhost_dbcorporativo = "xxx.xxx.xxx.xxx";
$CFG->dbname_dbcorporativo = "xxxxx";
$CFG->dbuser_dbcorporativo = "xxxxx";
$CFG->dbpass_dbcorporativo = "xxxxx**";

/*** HASTA AQUI***/

//require_once($CFG->libdir . "/stdliblocal.php");
require_once $CFG->common_libdir . "/db/postgres7.php";

//preguntar($_SERVER);
$ME = qualified_me();
$URL = $ME;
$db = new sql_db_postgres($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);
$db->logDebug = 0;
$db->logFile = $CFG->dirroot . "/log/log.txt";
$db->logLevel = 1;

/*** NUEVAS CONEXIONES **/
$dbcorporativo = new sql_db_postgres($CFG->dbhost_dbcorporativo, $CFG->dbuser_dbcorporativo, $CFG->dbpass_dbcorporativo, $CFG->dbname_dbcorporativo);
$dbcorporativo->logDebug = 0;
$dbcorporativo->logFile = $CFG->dirroot . "/log/log.txt";
$dbcorporativo->logLevel = 1;

$dbnacionalproduccion = new sql_db_postgres($CFG->dbhost_dbnacionalproduccion, $CFG->dbuser_dbnacionalproduccion, $CFG->dbpass_dbnacionalproduccion, $CFG->dbname_dbnacionalproduccion);
$dbnacionalproduccion->logDebug = 0;
$dbnacionalproduccion->logFile = $CFG->dirroot . "/log/log.txt";
$dbnacionalproduccion->logLevel = 1;

$dbpromo = new sql_db_postgres($CFG->dbhost_promoambiental, $CFG->dbuser_promoambiental, $CFG->dbpass_promoambiental, $CFG->dbname_promoambiental);
$dbpromo->logDebug = 0;
$dbpromo->logFile = $CFG->dirroot . "/log/log.txt";
$dbpromo->logLevel = 1;
/*** HASTA AQUI***/

/* inicializa el manejo de sesiones, s�lo se usar� un arreglo
 * llamado SESSION para almacenar las variables.   */

setlocale(LC_TIME, "es_ES");
setlocale(LC_CTYPE, "es_ES");

if (!isset($_SERVER["REQUEST_METHOD"])) {
    $CFG->cli = 1;
} else {
    $CFG->cli = 0;
}

if (isset($_SERVER["REQUEST_METHOD"])) {
    session_start();
}

if (!isset($_SESSION[$CFG->sesion])) {
    $_SESSION[$CFG->sesion] = array();
}

require "$CFG->libdir/validate.php";
$CFG->defaultModule = "vehiculos";

if (isset($_SERVER["REQUEST_METHOD"])) {
    $CFG->servidor = "https://" . $_SERVER["SERVER_NAME"];
}

if (isset($_GET)) {
    foreach ($_GET as $key => $val) {
        if (is_array($val)) {
            foreach ($val as $subval) {
                if (preg_match("/(select|update|delete) /i", $subval)) {
                    die("Error");
                }

            }
        } elseif (preg_match("/(select|update|delete) /i", $val)) {
            die("Error");
        }
    }
}

if (isset($_POST)) {
    foreach ($_POST as $key => $val) {
        if (is_array($val)) {
            foreach ($val as $subval) {
                if (preg_match("/(select|update|delete) /i", $subval)) {
                    die("Error");
                }

            }
        } else
        if (preg_match("/(select|update|delete) /i", $val)) {
            die("Error");
        }

        if ($key != "GLOBALS") {
        }
    }
}

$inicio = microtime();

require $CFG->common_libdir . "/paginas_permisos.php";

$arrPagsSinLogin = array("login.php", "imagen.php", "file.php");
$arrPagsConLogin = array($CFG->mainPage);
if ((in_array(simple_me($ME), $arrPagsConLogin) || preg_match("/\/admin\//", $ME)) && !in_array(simple_me($ME), $arrPagsSinLogin) && isset($_SERVER["REQUEST_METHOD"])) {
    if (!is_admin()) {
        $_SESSION[$CFG->sesion]["goto"] = $ME;
        if (nvl($_SERVER["QUERY_STRING"]) != "") {
            $_SESSION[$CFG->sesion]["goto"] .= "?" . $_SERVER["QUERY_STRING"];
        }

        $goto = $CFG->admin_dir . "/login.php";
        header("Location: $goto");
        die();
    }

}

if (isset($_SESSION[$CFG->sesion]["user"]["id"])) {
    if (!$user = $db->sql_row("SELECT * FROM personas WHERE id='" . $_SESSION[$CFG->sesion]["user"]["id"] . "'")) {
        unset($_SESSION[$CFG->sesion]);
        $goto = $CFG->admin_dir . "/login.php";
        header("Location: $goto");
        die();
    }
}
