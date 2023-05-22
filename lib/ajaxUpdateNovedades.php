<?
include("../application.php");
header('Content-Type: text/html;charset=iso-8859-1');

$referer=parse_url($_SERVER["HTTP_REFERER"]);
$serverName=$_SERVER["SERVER_NAME"];
if($serverName!=$referer["host"]) die("Error: " . __FILE__ . ":" . __LINE__);

$frm=$_GET;

if(isset($frm["divid"])) echo "<div id='$frm[divid]'>\n";

if($frm["tipo"]=="listar_tipo_novedad")
{
	echo "<select id=\"id_tipo_novedad\" name=\"id_tipo_novedad\" style=\"width:250px\">";
	echo "<option value=\"%\">Seleccione...</option>";

	if($frm["clase"]=="mtto")
		$db->build_recursive_tree_path("tipos_novedades",$tipos,"","id","id_superior","tipos_novedades.nombre","-1","","clase=3");
	else
		$db->build_recursive_tree_path("tipos_novedades",$tipos,"","id","id_superior","tipos_novedades.nombre","-1","","clase!=3");
	echo $tipos;	
}elseif($frm["tipo"]=="listar_reportadcentro")
{
	echo "<select id=\"id_reporta\" name=\"id_reporta\" style=\"width:250px\">";
	$db->crear_select("
			SELECT id, (nombre || ' ' || apellido) FROM personas WHERE id IN (
				SELECT id_persona FROM personas_centros WHERE id_centro = '".$frm["id_centro"]."' 
			)
			ORDER BY nombre,apellido ",$personas_r);
	echo $personas_r;
}elseif($frm["tipo"]=="listar_ingresadcentro")
{
	echo "<select id=\"id_ingresa\" name=\"id_ingresa\" style=\"width:250px\">";
	$db->crear_select("
			SELECT id, (nombre || ' ' || apellido) FROM personas WHERE id IN (
				SELECT id_persona FROM personas_centros WHERE id_centro = '".$frm["id_centro"]."' 
			)
			ORDER BY nombre,apellido ",$personas_r);
	echo $personas_r;
}elseif($frm["tipo"]=="listar_reportadequipo")
{
	echo "<select id=\"id_reporta\" name=\"id_reporta\" style=\"width:250px\">";
	$db->crear_select("
			SELECT id, (nombre || ' ' || apellido) FROM personas WHERE id IN (
				SELECT id_persona FROM personas_centros WHERE id_centro = (SELECT id_centro FROM mtto.equipos WHERE id='$frm[id_equipo]') 
			)
			ORDER BY nombre,apellido ",$personas_r);
	echo $personas_r;
}elseif($frm["tipo"]=="listar_id_vehiculo_apoyo")
{
	echo "<select id=\"id_vehiculo_apoyo\" name=\"id_vehiculo_apoyo\" style=\"width:250px\">";
	$db->crear_select("SELECT id, (codigo || ' / ' || placa) FROM vehiculos WHERE id_centro ='".$frm["id_centro"]."' ORDER BY codigo, placa", $vehiculos);
	echo $vehiculos;
}elseif($frm["tipo"]=="listar_id_equipoxcentro")
{
	echo "<select id=\"id_equipo\" name=\"id_equipo\" style=\"width:250px\">";
	$db->crear_select("SELECT id, nombre FROM mtto.equipos WHERE id_centro ='".$frm["id_centro"]."' ORDER BY nombre",$equipos);
	echo $equipos;
}






echo "</select>";
if(isset($frm["divid"])) echo "</div>\n";
?>
