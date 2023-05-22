<?
include("../application.php");

$frm=$_GET;
echo "<div id='id_equipo'>\n";
echo "<select id=\"id_equipo\" name=\"id_equipo\" ";
echo "<option value=\"%\">Seleccione...</option>";
$db->build_recursive_tree_path("mtto.equipos",$opciones,"","id","id_superior","nombre","-1","","id_centro=".$frm["id"]);
echo $opciones;
echo "</select>";
echo "</div>\n";
?>
