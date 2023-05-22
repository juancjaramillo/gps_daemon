<?
include("../application.php");
header('Content-Type: text/html;charset=iso-8859-1');

$referer=parse_url($_SERVER["HTTP_REFERER"]);
$serverName=$_SERVER["SERVER_NAME"];
if($serverName!=$referer["host"]) die("Error: " . __FILE__ . ":" . __LINE__);

$frm=$_GET;

if(isset($frm["divid"])) echo "<div id='$frm[divid]'>\n";


if($frm["tipo"]=="turnoxcentro")
{
	echo "Turno &nbsp;&nbsp;";
	echo "<select id=\"id_turno\" name=\"id_turno\" style=\"width:100px\"><option value=''>Todos</option>";
	$qidTur = $db->sql_query("SELECT t.id, t.turno FROM turnos t LEFT JOIN centros c ON c.id_empresa = t.id_empresa WHERE c.id=".$frm["id_centro"]);
	while($tur = $db->sql_fetchrow($qidTur))
	{
		echo '<option value="'.$tur["id"].'">'.$tur["turno"].'</option>';
	}
	echo "</select>";
}elseif($frm["tipo"]=="asexcentro")
{
	echo "Ase &nbsp;&nbsp;";
	echo "<select id=\"id_ase\" name=\"id_ase\" style=\"width:150px\">";
	$qidTur = $db->sql_query("SELECT a.id, a.ase FROM ases a WHERE a.id_centro=".$frm["id_centro"]);
	while($tur = $db->sql_fetchrow($qidTur))
	{
		echo '<option value="'.$tur["id"].'">'.$tur["ase"].'</option>';
	}
	echo "</select>";
}elseif($frm["tipo"]=="asexcentro_dos")
{
	echo "Ase &nbsp;&nbsp;";
	echo "<select id=\"id_ase_dos\" name=\"id_ase\" style=\"width:150px\"><option value=''>Todas</option>";
	$qidTur = $db->sql_query("SELECT a.id, a.ase FROM ases a WHERE a.id_centro=".$frm["id_centro"]);
	while($tur = $db->sql_fetchrow($qidTur))
	{
		echo '<option value="'.$tur["id"].'">'.$tur["ase"].'</option>';
	}
	echo "</select>";
}elseif($frm["tipo"]=="puntocontrolxcentro")
{
	echo "Punto Control &nbsp;&nbsp;";
	echo "<select id=\"id_punto_control\" name=\"id_punto_control\" style=\"width:150px\"><option value='%'>Seleccione</option>";
	$qidTur = $db->sql_query("SELECT p.id, p.punto||' ('||nombre||')' as control 
		FROM puntos_interes p 
		LEFT JOIN categorias_puntos_interes c ON c.id = p.id_categoria
		WHERE id_centro= ".$frm["id_centro"]."
		ORDER BY p.punto");
	while($tur = $db->sql_fetchrow($qidTur))
	{
		echo '<option value="'.$tur["id"].'">'.$tur["control"].'</option>';
	}
	echo "</select>";
}elseif($frm["tipo"]=="vehiculoxcentro")
{
	$recarga = "";
	if(nvl($frm["recargaMicros"]))
		$recarga = 'onChange="updateRutasXVehiculo()"';

	echo "Vehiculo &nbsp;&nbsp;";
	echo "<select id=\"id_vehiculo\" name=\"id_vehiculo\" style=\"width:150px\" ".$recarga."><option value=''>Todos</option>";
	$qidTur = $db->sql_query("SELECT id, codigo||'/'||placa as vehiculo
		FROM vehiculos
		WHERE id_centro=".$frm["id_centro"]." and idgps IS NOT NULL
		ORDER BY codigo,placa");
	while($tur = $db->sql_fetchrow($qidTur))
	{
		echo '<option value="'.$tur["id"].'">'.$tur["vehiculo"].'</option>';
	}
	echo "</select>";
}elseif($frm["tipo"]=="vehiculoxcentro_pesos")
{
	echo "Vehiculo &nbsp;&nbsp;";
	echo "<select id=\"id_vehiculo\" name=\"id_vehiculo\" style=\"width:150px\" onChange=\"updateRutasXVehiculo()\"><option value=''>Todos</option>";
	$qidTur = $db->sql_query("SELECT distinct(v.id), v.codigo, v.placa
		FROM rec.pesos p
		LEFT JOIN vehiculos v ON v.id = p.id_vehiculo
		WHERE v.id_centro =".$frm["id_centro"]." AND  fecha_entrada::date>='".$frm["f_inicio"]."' AND fecha_entrada::date<= '".$frm["f_final"]."'
		ORDER BY codigo,placa");
	while($tur = $db->sql_fetchrow($qidTur))
	{
		echo '<option value="'.$tur["id"].'">'.$tur["codigo"]." / ".$tur["placa"].'</option>';
	}
	echo "</select>";
}
elseif($frm["tipo"]=="rutaxcentro_pesos")
{
	echo "Ruta &nbsp;&nbsp;";
	echo '<select  name="id_micro" id="id_micro"  style="width:150px">	<option value="">Todas</option>';
	$qidRutas = $db->sql_query("SELECT distinct(i.id),i.codigo 
		FROM rec.movimientos_pesos mp
		LEFT JOIN rec.pesos p ON p.id = mp.id_peso
		LEFT JOIN rec.movimientos m ON m.id = mp.id_movimiento
		LEFT JOIN micros i ON i.id = m.id_micro
		LEFT JOIN ases a ON a.id=i.id_ase
		WHERE a.id_centro = '".$frm["id_centro"]."' AND  p.fecha_entrada::date>='".$frm["f_inicio"]."' AND p.fecha_entrada::date<= '".$frm["f_final"]."'
		ORDER BY codigo");
	while($queryRuta = $db->sql_fetchrow($qidRutas)){
		echo '<option value="'.$queryRuta["id"].'">'.$queryRuta["codigo"].'</option>';
	}
	echo "</select>";
}
elseif($frm["tipo"]=="rutaxvehiculo_pesos")
{
	echo "Ruta &nbsp;&nbsp;";
	echo '<select  name="id_micro" id="id_micro"  style="width:150px">	<option value="">Todas</option>';
	$qidRutas = $db->sql_query("SELECT distinct(i.id),i.codigo 
		FROM rec.movimientos_pesos mp 
		LEFT JOIN rec.pesos p ON p.id = mp.id_peso
		LEFT JOIN rec.movimientos m ON m.id = mp.id_movimiento
		LEFT JOIN micros i ON i.id = m.id_micro
		WHERE p.id_vehiculo = '".$frm["id_vehiculo"]."' AND p.fecha_entrada::date>='".$frm["f_inicio"]."' AND p.fecha_entrada::date<= '".$frm["f_final"]."'
		ORDER BY codigo");
	while($queryRuta = $db->sql_fetchrow($qidRutas)){
		echo '<option value="'.$queryRuta["id"].'">'.$queryRuta["codigo"].'</option>';
	}
	echo "</select>";
}elseif($frm["tipo"]=="personasxmovimiento")
{
	echo "Operario &nbsp;&nbsp;";
	echo '<select  name="id_persona" id="id_persona"  style="width:150px">	<option value="">Todos</option>';
	$qidRutas = $db->sql_query("
		SELECT distinct(id), nombre
		FROM(
			SELECT p.id, p.nombre||' '||p.apellido||' ('||p.cedula||')' as nombre 
			FROM rec.movimientos_personas mp 
			LEFT JOIN personas p ON p.id=mp.id_persona
			WHERE  mp.hora_inicio::date >= '".$frm["f_inicio"]."' AND mp.hora_inicio::date<='".$frm["f_final"]."' AND mp.id_persona IN (SELECT id_persona FROM personas_centros WHERE id_centro = '".$frm["id_centro"]."')
			UNION
			SELECT p.id, p.nombre||' '||p.apellido||' ('||p.cedula||')' as nombre 
			FROM bar.movimientos_personas mp 
			LEFT JOIN personas p ON p.id=mp.id_persona
			WHERE  mp.hora_inicio::date >= '".$frm["f_inicio"]."' AND mp.hora_inicio::date<='".$frm["f_final"]."' AND mp.id_persona IN (SELECT id_persona FROM personas_centros WHERE id_centro = '".$frm["id_centro"]."')
		) AS foo
		ORDER BY nombre");
	while($queryRuta = $db->sql_fetchrow($qidRutas)){
		echo '<option value="'.$queryRuta["id"].'">'.$queryRuta["nombre"].'</option>';
	}
	echo "</select>";
}elseif($frm["tipo"]=="vehiculosxdiaMov")
{
	$user=$_SESSION[$CFG->sesion]["user"];
	echo '<select  name="id_vehiculo" id="id_vehiculo"  style="width:150px" onchange="updateRecursive_id_movimiento(this)"><option value="0">Seleccione</option>';
	$qidVeh = $db->sql_query("
		SELECT distinct(v.id), v.codigo || '/'||v.placa as vehiculo, codigo, placa
		FROM ".$frm["esquema"].".movimientos m
		LEFT JOIN vehiculos v ON v.id = m.id_vehiculo
		WHERE m.inicio::date = '".$frm["fecha"]."' AND id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$user["id"]."') 
		ORDER BY codigo, placa");
	while($veh = $db->sql_fetchrow($qidVeh))
	{
		echo '<option value="'.$veh["id"].'">'.$veh["vehiculo"].'</option>';
	}
	echo "</select>";
}elseif($frm["tipo"]=="movimientosxdiaxvehiculo")
{
	echo '<select  name="id_movimiento" id="id_movimiento"  style="width:150px" onchange="updateRecursive_personas(this)"><option value="0">Seleccione</option>';
	$qidMov = $db->sql_query("
		SELECT m.id, i.codigo
		FROM ".$frm["esquema"].".movimientos m
		LEFT JOIN micros i ON i.id = m.id_micro
		WHERE m.inicio::date = '".$frm["fecha"]."' AND m.id_vehiculo='".$frm["id_vehiculo"]."'
		ORDER BY codigo");
	while($mov = $db->sql_fetchrow($qidMov))
	{
		echo '<option value="'.$mov["id"].'">'.$mov["codigo"].'</option>';
	}
	echo "</select>";
}elseif($frm["tipo"] == "personasxmovimiento_tabla")
{
	echo "<table width='100%' border=1 bordercolor='#7fa840' align='center'>
		<tr>
			<td align='center'>OPERARIO</td><td align='center'>CEDULA</td><td align='center'>HORA INICIO</td><td align='center'>HORA FIN</td><td align='center'>TIEMPO TOTAL</td>
		</tr>";
	$qid = $db->sql_query("
			SELECT p.id, p.nombre||' '||p.apellido as nombre, p.cedula, mp.hora_inicio as inicio, mp.hora_fin as fin
			FROM rec.movimientos_personas mp
			LEFT JOIN personas p ON p.id=mp.id_persona
			WHERE mp.id_movimiento='".$frm["id_movimiento"]."'
			ORDER BY p.nombre, p.apellido");
	while($per = $db->sql_fetchrow($qid))
	{
		$tt = conversor_segundos(restarFechasConHHmmss($per["fin"], $per["inicio"], true));
		echo "<tr>
			<td>".$per["nombre"]."</td>
			<td>".$per["cedula"]."</td>
			<td>".$per["inicio"]."</td>			
			<td>".$per["fin"]."</td>
			<td>".$tt."</td>
		</tr>";
	}
	echo "</table>";
}



if(isset($frm["divid"])) echo "</div>\n";
?>