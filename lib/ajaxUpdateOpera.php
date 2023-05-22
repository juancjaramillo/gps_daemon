<?
include("../application.php");
header('Content-Type: text/html;charset=iso-8859-1');

$referer=parse_url($_SERVER["HTTP_REFERER"]);
$serverName=$_SERVER["SERVER_NAME"];
if($serverName!=$referer["host"]) die("Error: " . __FILE__ . ":" . __LINE__);

$frm=$_GET;

if(isset($frm["divid"])) echo "<div id='$frm[divid]'>\n";

//file_put_contents($CFG->dirroot.'/mtto/ver.log',$frm["tipo"] . "\n=====\n",FILE_APPEND);

if($frm["tipo"]=="listadoVehiculosXAseyServicio")
{
	echo "<select id=\"id_vehiculo\" name=\"id_vehiculo\" style=\"width:250px\">";
	$ase = $db->sql_row("SELECT id_centro FROM ases WHERE id=".$frm["id_ase"]);
	$db->crear_select("SELECT v.id, v.codigo || '/' || v.placa 
		FROM vehiculos v
		LEFT JOIN tipos_vehiculos_servicios tp ON tp.id_tipo_vehiculo=v.id_tipo_vehiculo  
		WHERE v.id_centro = '".$ase["id_centro"]."' AND tp.id_servicio='".$frm["id_servicio"]."'  and v.id_estado<>4
		ORDER BY v.codigo,v.placa",$vehiculos);
	echo $vehiculos;
	echo "</select>";
}elseif($frm["tipo"]=="listadoCuartelilloXAse")
{
	echo "<select id=\"id_cuartelillo\" name=\"id_cuartelillo\" style=\"width:250px\">";
	$ase = $db->sql_row("SELECT id_centro FROM ases WHERE id=".$frm["id_ase"]);
	$db->crear_select("SELECT id, nombre  FROM cuartelillos WHERE id_centro = '".$ase["id_centro"]."' ORDER BY nombre",$cuartelillos);
	echo $cuartelillos;
	echo "</select>";
}elseif($frm["tipo"]=="listadoCoordinadorXAse")
{
	echo "<select id=\"id_coordinador\" name=\"id_coordinador\" style=\"width:250px\">";
	$ase = $db->sql_row("SELECT id_centro FROM ases WHERE id=".$frm["id_ase"]);
	$cargos = array(8);
	obtenerIdCargos(8,$cargos);

	$db->crear_select("SELECT id, nombre||' '||apellido as nombre FROM personas WHERE id IN (SELECT id_persona FROM personas_centros WHERE id_centro='".$ase["id_centro"]."') AND id_cargo IN (".implode(",",$cargos).") ORDER BY nombre,apellido",$coordinador);
	echo $coordinador;
	echo "</select>";
}elseif($frm["tipo"]=="listadoPersonaXCargoXFrecuencia")
{
	echo "<select id=\"id_persona\" name=\"id_persona\" style=\"width:250px\">";
	$db->crear_select("SELECT p.id,COALESCE(p.cedula,'')||'-'|| p.nombre||' '||p.apellido  as nombre 
		FROM personas p
		LEFT JOIN estados_personas ep ON ep.id = p.id_estado
		WHERE ep.activo AND p.id_cargo='".$frm["id_cargo"]."' AND p.id IN (SELECT id_persona FROM personas_centros WHERE id_centro='".$frm["id_centro"]."') 
		ORDER BY cedula,nombre,apellido",$personas);
	//AND id NOT IN (SELECT id_persona FROM frecuencias_operarios LEFT JOIN micros_frecuencia ON micros_frecuencia.id=frecuencias_operarios.id_frecuencia WHERE dia='".$frm["dia"]."')
	echo $personas;
	echo "</select>";
}elseif($frm["tipo"]=="listadoLugaresDescargueXAse")
{
	echo "<select id=\"id_lugar_descargue\" name=\"id_lugar_descargue\" style=\"width:250px\">";
	$ase = $db->sql_row("SELECT id_centro FROM ases WHERE id=".$frm["id_ase"]);
	$db->crear_select("SELECT id, nombre FROM lugares_descargue WHERE id_centro = '".$ase["id_centro"]."' ORDER BY nombre",$descargue);
	echo $descargue;
	echo "</select>";
}elseif($frm["tipo"] == "kmXVehiculo")
{
	$vehc = $db->sql_row("SELECT kilometraje FROM vehiculos WHERE id=".$frm["id_vehiculo"]);
	echo '<input type="text" size="20" class="casillatext" name="kilometraje" id="kilometraje" value="'.$vehc["kilometraje"].'">';
}elseif($frm["tipo"] == "KmXVehiculoNota")
{
	$vehc = $db->sql_row("SELECT kilometraje FROM vehiculos WHERE id=".$frm["id_vehiculo"]);
	echo ' (Km Actual = '.$vehc["kilometraje"].")";
}elseif($frm["tipo"] == "horoXVehiculo")
{
	$vehc = $db->sql_row("SELECT horometro FROM vehiculos WHERE id=".$frm["id_vehiculo"]);
	echo '<input type="text" size="20" class="casillatext" name="horometro" id="horometro" value="'.$vehc["horometro"].'">';
}elseif($frm["tipo"]=="listadoMicrosXTurno")
{
	if($_GET["esquema"] == "rec")
		echo "<select id=\"id_micro_frecuencia\" name=\"id_micro_frecuencia\" style=\"width:150px\"  onChange=\"updateRecursive_id_vehiculo(this), updateRecursive_id_lugar_descargue(this), updateRecursive_hora(this)\">";
	else
		echo "<select id=\"id_micro_frecuencia\" name=\"id_micro_frecuencia\" style=\"width:150px\"  onChange=\"updateRecursive_id_vehiculo(this), updateRecursive_id_persona(this), updateRecursive_hora(this), updateRecursive_bolsas(this)\">";

	$consulta = "SELECT f.id, m.codigo||' / '||case when f.dia=1 then 'Lunes' when f.dia=2 then 'Martes' when f.dia=3 then 'Miércoles' when f.dia=4 then 'Jueves' when f.dia=5 then 'Viernes' when f.dia=6 then 'Sábado' else 'Domingo' end as dia
		FROM micros_frecuencia f
		LEFT JOIN micros m ON m.id=f.id_micro
		LEFT JOIN servicios s ON s.id = m.id_servicio
		LEFT JOIN ases a ON a.id=m.id_ase
		WHERE s.esquema='".$frm["esquema"]."' AND m.fecha_hasta IS NULL AND m.id_ase IN (SELECT id FROM ases WHERE id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$frm["user"]."')) AND f.id_turno='".$frm["id_turno"]."'
		ORDER BY m.codigo, f.dia";
	$db->crear_select($consulta,$micros);
	echo $micros;
	echo "</select>";
}elseif($frm["tipo"]=="listadoVehiculosXFrecuencia")
{
	echo "<select id=\"id_vehiculo\" name=\"id_vehiculo\" style=\"width:150px\" >";
	$micro = $db->sql_row("SELECT m.id, m.id_vehiculo, id_centro, id_servicio
			FROM micros_frecuencia f 
			LEFT JOIN micros m ON m.id=f.id_micro 
			LEFT JOIN ases a ON a.id=m.id_ase 
			WHERE f.id=".$frm["id_frecuencia"]);
	$consulta = "SELECT v.id, v.codigo||'/'||v.placa || CASE WHEN (select count(o.id) FROM mtto.ordenes_trabajo o WHERE o.id_equipo=e.id AND o.fecha_planeada::date = '".$frm["fecha"]."') != 0 then '(Mantenimiento Programado)' else '' end as nombre
		FROM vehiculos v
	 	LEFT JOIN mtto.equipos e ON v.id=e.id_vehiculo	
		LEFT JOIN tipos_vehiculos_servicios tp ON tp.id_tipo_vehiculo=v.id_tipo_vehiculo
		WHERE v.id_centro = '".$micro["id_centro"]."' AND tp.id_servicio='".$micro["id_servicio"]."'  and v.id_estado<>4
		ORDER BY v.codigo,v.placa";
	error_log($consulta);
	$db->crear_select($consulta,$vehiculos, $micro["id_vehiculo"]);
	echo $vehiculos;
	echo "</select>";
}elseif($frm["tipo"]=="listadoDescarguesXFrecuencia")
{
	echo "<select id=\"id_lugar_descargue\" name=\"id_lugar_descargue\" style=\"width:150px\" >";
	$micro = $db->sql_row("SELECT id_centro, id_lugar_descargue
			FROM micros_frecuencia f 
			LEFT JOIN micros m ON m.id=f.id_micro 
			LEFT JOIN ases a ON a.id=m.id_ase 
			WHERE f.id=".$frm["id_frecuencia"]);
	$consulta = "SELECT id, nombre FROM lugares_descargue WHERE id_centro = '".$micro["id_centro"]."'";
	$db->crear_select($consulta,$lugares, $micro["id_lugar_descargue"]);
	echo $lugares;
	echo "</select>";
}elseif($frm["tipo"]=="HoraXFrecuencia")
{
	$frec = $db->sql_row("SELECT hora_inicio FROM micros_frecuencia WHERE id=".$frm["id_frecuencia"]);
	echo '<input type="text" size="10" class="casillatext_fecha" name="hora" id="hora" value="'.$frec["hora_inicio"].'">&nbsp;<a title="Calendario" href="javascript:abrirSoloHora(\'hora\',\'entryform\');"><img alt="Fecha" src="'.$CFG->wwwroot.'/admin/iconos/transparente/icon-clock.png" border="0"></a>';
}elseif($frm["tipo"]=="listadoPersonasXFrecuencia")
{
	$qidO = $db->sql_row("SELECT id_persona FROM frecuencias_operarios WHERE id_frecuencia=".$frm["id_frecuencia"]);
	$micro = $db->sql_row("SELECT id_centro
			FROM micros_frecuencia f 
			LEFT JOIN micros m ON m.id=f.id_micro 
			LEFT JOIN ases a ON a.id=m.id_ase 
			WHERE f.id=".$frm["id_frecuencia"]);

	$db->crear_select("SELECT p.id, p.nombre||' '||p.apellido 
			FROM personas p 
			LEFT JOIN personas_cargos pc ON pc.id_persona=p.id
			WHERE pc.id_cargo = 23 AND p.id IN (SELECT id_persona FROM personas_centros WHERE id_centro='$micro[id_centro]') AND p.id NOT IN (SELECT id_persona FROM bar.movimientos_personas LEFT JOIN bar.movimientos ON bar.movimientos.id=bar.movimientos_personas.id_movimiento WHERE bar.movimientos.inicio::date='".$frm["fecha"]."')
			ORDER BY p.nombre",$operario,$qidO["id_persona"]);

	echo '<select  name="id_persona" id="id_persona" style="width:250px">'.$operario."</select>";
}elseif($frm["tipo"] == "listadoBolsasXFrecuencia")
{
	$bolsas = array();
	$qidB = $db->sql_query("SELECT tipo, b.numero_inicio, b.id_tipo_bolsa 
			FROM frecuencias_bolsas b 
			LEFT JOIN bar.tipos_bolsas t ON t.id=b.id_tipo_bolsa 
			WHERE b.id_frecuencia=".$frm["id_frecuencia"]." 
			ORDER BY tipo");
	while($queryBo = $db->sql_fetchrow($qidB))
		{
		$bolsas[$queryBo["id_tipo_bolsa"]] = $queryBo["numero_inicio"];
	}

	$qid = $db->sql_query("SELECT * FROM bar.tipos_bolsas ORDER BY tipo");
	while($b = $db->sql_fetchrow($qid))
	{
		$valor = 0;
		if(isset($bolsas[$b["id"]])) $valor = $bolsas[$b["id"]];
		echo "<tr>
			<td align='right'>".$b["tipo"]."</td>
			<td align='left'>
				<input type='text' size='6' class='casillatext' name='id_tipo_bolsa_".$b["id"]."' id='id_tipo_bolsa_".$b["id"]."' value='".$valor."'>
			</td>
		</tr>";
	}
}elseif($frm["tipo"] == "id_MovimientoxDia")
{
	$user=$_SESSION[$CFG->sesion]["user"];
	$consulta = "SELECT m.id, i.codigo || ' / '||m.inicio as codigo
			FROM rec.movimientos m 
			LEFT JOIN micros i ON i.id=m.id_micro 
			LEFT JOIN ases a ON a.id=i.id_ase
			WHERE inicio::date = '".$frm["fecha"]."' AND a.id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='$user[id]')
			ORDER BY i.codigo";
	//file_put_contents($CFG->dirroot.'/mtto/ver.log',$consulta . "\n=====\n",FILE_APPEND);

	$db->crear_select($consulta,$movs,"","");
	echo '<select multiple name="id_movimiento[]" id="movimientoin" style="width:150px" SIZE=5 >'.$movs."</select>";
}elseif($frm["tipo"] == "movimientosxpeso")
{
	$vehiculo = $db->sql_row("SELECT id_vehiculo FROM rec.pesos WHERE id=".$frm["id_peso"]);
	$db->crear_select("SELECT mov.id, mov.inicio||' / '||i.codigo||' / '||v.placa||' / '||v.codigo as movimiento
			FROM rec.movimientos mov
			LEFT JOIN vehiculos v ON v.id = mov.id_vehiculo
			LEFT JOIN micros i ON i.id=mov.id_micro
			WHERE mov.id_vehiculo=".$vehiculo["id_vehiculo"]." ORDER BY mov.inicio DESC,i.codigo,v.placa,v.codigo", $movimientos,nvl($frm["id_movimiento"]));
	echo '<select  name="id_movimiento" id="id_movimiento" onChange="updateRecursive_id_peso(this), updateRecursive_viaje(this)">'.$movimientos."</select>";
}elseif($frm["tipo"] == "pesoxmovimiento")
{
	$vehiculo = $db->sql_row("SELECT id_vehiculo FROM rec.movimientos WHERE id=".$frm["id_movimiento"]);
	$db->crear_select("SELECT p.id, v2.placa||' / '||v2.codigo||' / '||p.fecha_entrada ||' / '||l.nombre||' / '||c.centro as peso
			FROM rec.pesos p
			LEFT JOIN vehiculos v2 ON v2.id = p.id_vehiculo
			LEFT JOIN lugares_descargue l ON l.id=p.id_lugar_descargue
			LEFT JOIN centros c ON c.id=l.id_centro
			WHERE p.id_vehiculo='".$vehiculo["id_vehiculo"]."'
			ORDER BY p.fecha_entrada DESC, v2.placa, v2.codigo", $pesos, nvl($frm["id_peso"]));
	echo '<select  name="id_peso" id="id_peso" onChange="updateRecursive_id_movimiento(this)">'.$pesos."</select>";
}elseif($frm["tipo"] == "viajexmovimiento")
{
	$sel = '<select  name="viaje" id="viaje">';
	$qid = $db->sql_query("SELECT distinct(numero_viaje) as num FROM rec.desplazamientos WHERE id_movimiento=".$frm["id_movimiento"]);
	while($v = $db->sql_fetchrow($qid))
	{
		$sel.="<option value='".$v["num"]."'>".$v["num"]."</option>";
	}
	$sel.="</select>";
	echo $sel;
}elseif($frm["tipo"] == "listadoVariosMovimientosSinPesoXVehiculo")
{
	$opciones = array();
	$consulta = "SELECT m.id,  i.codigo||' / '||m.inicio as codigo
		FROM rec.movimientos m 
		LEFT JOIN micros i ON i.id = m.id_micro
		WHERE m.id_vehiculo='".$frm["id_vehiculo"]."' AND m.inicio<= '".$frm["fecha_entrada"]."'  AND m.id NOT IN (SELECT id_movimiento FROM rec.movimientos_pesos)
		ORDER BY inicio DESC";
	$qid = $db->sql_query($consulta);
	while($mov = $db->sql_fetchrow($qid))
	{
		$qidDes = $db->sql_query("SELECT distinct(numero_viaje) as viaje FROM rec.desplazamientos WHERE id_movimiento='".$mov["id"]."'");
		while($des = $db->sql_fetchrow($qidDes))
		{
			$opciones[] = "<option value='".$mov["id"]."_".$des["viaje"]."'>".$mov["codigo"]." / Viaje:".$des["viaje"]."</option>";
		}
	}

	echo '<select multiple name="id_movimientos[]" id="id_movimientos" style="width:250px" SIZE=5>'.implode("",$opciones)."</select>";

}elseif($frm["tipo"] == "listadoUltimoMovimientoSinPesoXVehiculo")
{
	$opciones = array();
	$consulta = "SELECT m.id,  i.codigo||' / '||m.inicio as codigo
		FROM rec.movimientos m 
		LEFT JOIN micros i ON i.id = m.id_micro
		WHERE m.id_vehiculo='".$frm["id_vehiculo"]."' AND m.inicio<= '".$frm["fecha_entrada"]."'  AND m.id NOT IN (SELECT id_movimiento FROM rec.movimientos_pesos)
		ORDER BY inicio DESC
		LIMIT 1";
	$qid = $db->sql_query($consulta);
	while($mov = $db->sql_fetchrow($qid))
	{
		$qidDes = $db->sql_query("SELECT distinct(numero_viaje) as viaje FROM rec.desplazamientos WHERE id_movimiento='".$mov["id"]."'");
		while($des = $db->sql_fetchrow($qidDes))
		{
			$opciones[] = "<option value='".$mov["id"]."_".$des["viaje"]."'>".$mov["codigo"]." / Viaje:".$des["viaje"]."</option>";
		}
	}

	echo '<select name="id_unico_movimiento" id="id_unico_movimiento" style="width:250px" ><option value="%">Seleccione...</option>'.implode("",$opciones)."</select>";
}


if(isset($frm["divid"])) echo "</div>\n";
?>
