<?
include("../application.php");
header('Content-Type: text/html;charset=iso-8859-1');

$referer=parse_url($_SERVER["HTTP_REFERER"]);
$serverName=$_SERVER["SERVER_NAME"];
if($serverName!=$referer["host"]) die("Error: " . __FILE__ . ":" . __LINE__);

$frm=$_GET;

if(isset($frm["divid"])) echo "<div id='$frm[divid]'>\n";

$iconoAgregarRutina=false;

if($frm["tipo"]=="reporto_inspecciones")
{
	echo "<select id=\"id_reporto\" name=\"id_reporto\" style=\"width:250px\">";
	echo "<option value=\"%\">Seleccione...</option>";

	if($frm["id_vehiculo"]=="%")
		echo "";
	else
	{
		$centro = $db->sql_row("SELECT id_centro FROM vehiculos WHERE id=".$frm["id_vehiculo"]);
		$qid=$db->sql_query("SELECT id, nombre||' '||apellido as nombre FROM personas WHERE id IN (SELECT id_persona FROM personas_centros WHERE id_centro='".$centro["id_centro"]."') ORDER BY nombre,apellido");
		while($query = $db->sql_fetchrow($qid))
		{
			echo "<option value=\"".$query["id"]."\">".$query["nombre"]."</option>";
		}
	}
}
elseif($frm["tipo"]=="actualizarEjes")
{
	echo "<select id=\"posicion\" name=\"posicion\" style=\"width:250px\">";
	echo "<option value=\"%\">Seleccione...</option>";

	if($frm["id_vehiculo"]=="%")
		echo "";
	else
		echo opcionesPosicionesLlantas($frm["id_vehiculo"]);
}elseif($frm["tipo"]=="equipos_ordenes")
{
	$rutina = $db->sql_row("SELECT id, id_grupo, id_equipo FROM mtto.rutinas WHERE id=".$frm["id_rutina"]);
	if($rutina["id_grupo"] != "")
	{
		$datos = array($rutina["id_grupo"]);
		obtenerIdsGruposAbajo($rutina["id_grupo"],$datos);
		$condicion = " AND g.id IN (".implode(",",$datos).")";
	}elseif($rutina["id_equipo"] != "")
		$condicion = " AND e.id='".$rutina["id_equipo"]."'";

	echo "<select id=\"id_equipo\" name=\"id_equipo\" style=\"width:250px\">";
	echo "<option value=\"%\">Seleccione...</option>";
		
	$consulta = "SELECT e.id, e.nombre||' ('||getPath(g.id,'mtto.grupos')||')' as nom_equ 
		FROM mtto.equipos e 
		LEFT JOIN mtto.grupos g ON g.id=e.id_grupo
		WHERE true ".$condicion."
		ORDER BY e.nombre";
	$qid=$db->sql_query($consulta);
	while($query = $db->sql_fetchrow($qid))
	{
		echo "<option value=\"".$query["id"]."\">".$query["nom_equ"]."</option>";
	}
}elseif($frm["tipo"]=="responsable_ordenes")
{
	echo "<select id=\"id_responsable\" name=\"id_responsable\" style=\"width:250px\">";
	echo "<option value=\"%\">Seleccione...</option>";

	$idCentros = array();
	$rutina = $db->sql_query("SELECT * FROM mtto.rutinas_centros WHERE id_rutina=".$frm["id_rutina"]);
	while($queryRut = $db->sql_fetchrow($rutina))
	{
		$idCentros[$queryRut["id_centro"]]=$queryRut["id_centro"];
	}

	$qid = $db->sql_query("SELECT distinct(personas.id), nombre||' '||apellido as nombre_completo, nombre,apellido FROM personas_centros LEFT JOIN personas ON personas.id=personas_centros.id_persona WHERE id_centro IN (".implode(",",$idCentros).") AND id IN (SELECT id_persona FROM personas_tareas WHERE id_tarea = 1) ORDER BY nombre,apellido,personas.id");
	while($per = $db->sql_fetchrow($qid))
	{
		echo "<option value=\"".$per["id"]."\">".$per["nombre_completo"]."</option>";
	}
}elseif($frm["tipo"]=="planeador_ordenes")
{
	echo "<select id=\"id_planeador\" name=\"id_planeador\" style=\"width:250px\">";
	echo "<option value=\"%\">Seleccione...</option>";

	$idCentros = array();
	$rutina = $db->sql_query("SELECT * FROM mtto.rutinas_centros WHERE id_rutina=".$frm["id_rutina"]);
	while($queryRut = $db->sql_fetchrow($rutina))
	{
		$idCentros[$queryRut["id_centro"]]=$queryRut["id_centro"];
	}

	$qid = $db->sql_query("SELECT distinct(personas.id), nombre||' '||apellido as nombre_completo, nombre,apellido FROM personas_centros LEFT JOIN personas ON personas.id=personas_centros.id_persona WHERE id_centro IN (".implode(",",$idCentros).") AND id IN (SELECT id_persona FROM personas_tareas WHERE id_tarea = 2) ORDER BY nombre,apellido,personas.id");
	while($per = $db->sql_fetchrow($qid))
	{
		echo "<option value=\"".$per["id"]."\">".$per["nombre_completo"]."</option>";
	}
}elseif($frm["tipo"]=="ingreso_ejecutada_ordenes")
{
	echo "<select id=\"id_ingreso_ejecutada\" name=\"id_ingreso_ejecutada\" style=\"width:250px\">";
	echo "<option value=\"%\">Seleccione...</option>";

	$idCentros = array();
	$rutina = $db->sql_query("SELECT * FROM mtto.rutinas_centros WHERE id_rutina=".$frm["id_rutina"]);
	while($queryRut = $db->sql_fetchrow($rutina))
	{
		$idCentros[$queryRut["id_centro"]]=$queryRut["id_centro"];
	}

	$qid = $db->sql_query("SELECT distinct(personas.id), nombre||' '||apellido as nombre_completo, nombre,apellido FROM personas_centros LEFT JOIN personas ON personas.id=personas_centros.id_persona WHERE id_centro IN (".implode(",",$idCentros).") ORDER BY nombre,apellido,personas.id");
	while($per = $db->sql_fetchrow($qid))
	{
		echo "<option value=\"".$per["id"]."\">".$per["nombre_completo"]."</option>";
	}
}elseif($frm["tipo"]=="vehiculosxcentro")
{
	echo "<select id=\"id_vehiculo\" name=\"id_vehiculo\" style=\"width:250px\" onChange=\"updateRecursive_posicion(this);updateEstado();\">";
	echo "<option value=\"%\">Seleccione...</option>";

	$qid=$db->sql_query("SELECT id, codigo||'/'||placa as nombre FROM vehiculos WHERE id_centro='".$frm["id_centro"]."' ORDER BY codigo,placa");
	while($query = $db->sql_fetchrow($qid))
	{
		echo "<option value=\"".$query["id"]."\">".$query["nombre"]."</option>";
	}
}elseif($frm["tipo"]=="rutinasxsistema")
{
	$user=$_SESSION[$CFG->sesion]["user"];
	$iconoAgregarRutina=true;
	$link=$CFG->wwwroot."/mtto/rutinas.php?mode=agregar&devolver=1&id_sistema=".$frm["id_sistema"];

	echo "<select id=\"id_rutina\" name=\"id_rutina\" style=\"width:250px\" onChange=\"updateRecursive_id_equipo(this), updateRecursive_id_responsable(this), updateRecursive_id_planeador(this), updateRecursive_id_ingreso_ejecutada(this)\">";
	echo "<option value=\"%\">Seleccione...</option>";
	$consulta = "SELECT r.id, case when g.nombre != '' then r.rutina ||' ('||getPath(g.id,'mtto.grupos')||')' when e.nombre != '' then r.rutina ||' ('||e.nombre||')' else r.rutina end as nrut
		FROM mtto.rutinas r 
		LEFT JOIN mtto.grupos g ON g.id=r.id_grupo
		LEFT JOIN mtto.equipos e ON e.id=r.id_equipo
		WHERE r.activa AND r.id_sistema='".$frm["id_sistema"]."'
		ORDER BY r.rutina";
	if($user["nivel_acceso"]!=1)
		$consulta = "SELECT r.id, case when g.nombre != '' then r.rutina ||' ('||getPath(g.id,'mtto.grupos')||')' when e.nombre != '' then r.rutina ||' ('||e.nombre||')' else r.rutina end as nrut
			FROM mtto.rutinas r 
			LEFT JOIN mtto.grupos g ON g.id=r.id_grupo
			LEFT JOIN mtto.equipos e ON e.id=r.id_equipo
			WHERE r.activa AND r.id_sistema='".$frm["id_sistema"]."' AND r.id IN (SELECT id_rutina FROM mtto.rutinas_centros WHERE id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$user["id"]."'))
			ORDER BY r.rutina";
	$qid = $db->sql_query($consulta);
	while($query = $db->sql_fetchrow($qid))
	{
		echo "<option value=\"".$query["id"]."\">".$query["nrut"]."</option>";
	}
}elseif($frm["tipo"]=="proveedoresxcentro")
{
	echo "<select id=\"id_proveedor\" name=\"id_proveedor\" style=\"width:250px\">";
	echo "<option value=\"%\">Seleccione...</option>";

	$qid = $db->sql_query("SELECT p.id, p.razon FROM llta.proveedores_centros pc LEFT JOIN llta.proveedores p ON p.id=pc.id_proveedor WHERE pc.id_centro='".$frm["id_centro"]."' ORDER BY razon");
	while($query = $db->sql_fetchrow($qid))
	{
		echo "<option value=\"".$query["id"]."\">".$query["razon"]."</option>";
	}
}elseif($frm["tipo"]=="actualizarDimensiones")
{
	echo "<select id=\"id_dimension\" name=\"id_dimension\" style=\"width:250px\">";
	echo "<option value=\"%\">Seleccione...</option>";

	$qid=$db->sql_query("SELECT ref.id, ref.dimension as nombre
		FROM llta.dimensiones ref
		LEFT JOIN llta.marcas m ON m.id=ref.id_marca
		WHERE m.id='".$frm["id_marca"]."'
		ORDER BY ref.dimension");
	while($query = $db->sql_fetchrow($qid))
	{
		echo "<option value=\"".$query["id"]."\">".$query["nombre"]."</option>";
	}
}elseif($frm["tipo"]=="rutinasxequipoysistemaytipo")
{
	$user=$_SESSION[$CFG->sesion]["user"];
	$iconoAgregarRutina=true;
	$link=$CFG->wwwroot."/mtto/rutinas.php?mode=agregar&devolver=1";

	echo "<select id=\"id_rutina\" name=\"id_rutina\" style=\"width:250px\">";
	echo "<option value=\"%\">Seleccione...</option>";

	$condicion = $adicional = "";
	if($frm["id_sistema"] != "%")
	{
		$condicion .= " AND r.id_sistema='".$frm["id_sistema"]."'";	
		$link.="&id_sistema=".$frm["id_sistema"];
	}

	if($frm["id_tipo_mantenimiento"] != "%")
	{
		$condicion .= " AND r.id_tipo_mantenimiento='".$frm["id_tipo_mantenimiento"]."'";	
		$link.="&id_tipo_mantenimiento=".$frm["id_tipo_mantenimiento"];
	}
	if($frm["id_equipo"] != "%")
	{
		$gru = $db->sql_row("SELECT id_grupo FROM mtto.equipos WHERE id=".$frm["id_equipo"]);
		$datos=array($gru["id_grupo"]);
		obtenerIdsGrupos($gru["id_grupo"],$datos);
		$condicion .= " AND (r.id_grupo IN (".implode(",",$datos).") OR r.id_equipo='".$frm["id_equipo"]."' )";
		$link.="&id_grupo=".$gru["id_grupo"];
	}

	$consulta = "
			SELECT r.id, case when g.nombre != '' then r.rutina ||' ('||getPath(g.id,'mtto.grupos')||')' when e.nombre != '' then r.rutina ||' ('||e.nombre||')' else r.rutina end as nrut
			FROM mtto.rutinas r
			LEFT JOIN mtto.grupos g ON g.id=r.id_grupo
			LEFT JOIN mtto.equipos e ON e.id=r.id_equipo
			WHERE r.activa AND r.id IN (SELECT id_rutina FROM mtto.rutinas_centros WHERE id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$user["id"]."')) ".$condicion."
			ORDER BY nrut";

	$qid = $db->sql_query($consulta);
	while($query = $db->sql_fetchrow($qid))
	{
		echo "<option value=\"".$query["id"]."\">".$query["nrut"]."</option>";
	}
}elseif($frm["tipo"]=="listar_unidades")
{
	echo "<select id=\"id_unidad\" name=\"id_unidad\">";
	echo "<option value=\"%\">Seleccione...</option>";

	$qid = $db->sql_query("SELECT id, unidad FROM mtto.unidades ORDER BY unidad");
	while($query = $db->sql_fetchrow($qid))
	{
		echo "<option value=\"".$query["id"]."\">".$query["unidad"]."</option>";
	}
}
elseif($frm["tipo"]=="rutinasxtipoysistema")
{
	$user=$_SESSION[$CFG->sesion]["user"];

	echo "<select id=\"id_rutina\" name=\"id_rutina\" style=\"width:250px\">";
	echo "<option value=\"%\">Seleccione...</option>";
	$condicion = "";

	if($frm["id_tipo_mantenimiento"] != "%")
		$condicion .= " AND r.id_tipo_mantenimiento='".$frm["id_tipo_mantenimiento"]."'";	

	if($frm["id_sistema"] != "%")
		$condicion .= " AND r.id_sistema='".$frm["id_sistema"]."'";	
	
	$consulta = "
			SELECT r.id, r.rutina
			FROM mtto.rutinas r
			WHERE r.activa AND r.id IN (SELECT id_rutina FROM mtto.rutinas_centros WHERE id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$user["id"]."')) ".$condicion."
			ORDER BY rutina";

	$qid = $db->sql_query($consulta);
	while($query = $db->sql_fetchrow($qid))
	{
		echo "<option value=\"".$query["id"]."\">".$query["rutina"]."</option>";
	}
}


echo "</select>";
if($iconoAgregarRutina)
	echo "&nbsp;&nbsp;<a href=\"javascript:abrirVentanaJavaScript('rutinafo','1100','500','".nvl($link)."')\"><img  alt=\"Fecha\" src='".$CFG->wwwroot."/admin/iconos/transparente/icon-add.gif' border='0'></a>";
if(isset($frm["divid"])) echo "</div>\n";
?>
