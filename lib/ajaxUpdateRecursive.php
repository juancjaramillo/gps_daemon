<?
include("../application.php");

$referer=parse_url($_SERVER["HTTP_REFERER"]);
$serverName=$_SERVER["SERVER_NAME"];
if($serverName!=$referer["host"]) die("Error: " . __FILE__ . ":" . __LINE__);

$frm=$_GET;
$fileModule=$CFG->modulesdir . "/" . $frm["module"] . ".php";
if(!file_exists($fileModule)) die("Error: " . __FILE__ . ":" . __LINE__);
include($fileModule);
$att=$entidad->getAttributeByName($frm["field"]);
unset($att->parent);
$query = $att->qsQuery;
$query = str_replace("__%idARemp%__",$frm["id"],$query);
$onChange = "";
if($att->onChange!="") $onChange=" onChange=\"" . $att->onChange . "\"";

$width=$frm["width"];
if(!preg_match("/px\$/i",$width)) $width.="px";



$qid = $db->sql_query($query);
if(isset($frm["divid"])) echo "<div id='$frm[divid]'>\n";
echo "<select id=\"" . $frm["field"] . "\" name=\"" . $frm["field"] . "\" style=\"width:" . $width . "\" ".$onChange.">";
echo "<option value=\"%\">Seleccione...</option>";
while($result=$db->sql_fetchrow($qid)){
	/**/
	//para el módulo rec.pesos, poner como opcion predeterminada el lugar de descargue predeterminado
	if($frm["module"] == "rec.pesos" && $frm["field"] == "id_lugar_descargue")
	{
		$selected = "";
		$lugar = $db->sql_row("SELECT id FROM lugares_descargue WHERE predeterminada AND id_centro =  (SELECT id_centro FROM vehiculos WHERE id=".$frm["id"]." )");
		if($result[0] == nvl($lugar["id"])) $selected = " selected"; 
		echo "<option value=\"" . $result[0] . "\" ".$selected.">" . htmlentities($result[1]);
	}else
		echo "<option value=\"" . $result[0] . "\">" . htmlentities($result[1]);
}
echo "</select>";
if(isset($frm["divid"])) echo "</div>\n";
?>
