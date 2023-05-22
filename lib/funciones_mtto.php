<?
function obtenerSemana($fecha)
{     
	foreach(array(1=>'Monday',7=>'Sunday') as $a=>$b)
		$c[$b]=date('Y-m-d',strtotime('last '.$b,strtotime($fecha.'+'.$a.'day')));
	return $c;
}

function obtenerSemanaCompleta($fecha)
{     
	foreach(array(1=>'Monday', 2=>"Tuesday", 3=>"Wednesday", 4=>"Thursday", 5=>"Friday", 6=>"Saturday", 7=>'Sunday') as $a=>$b)
		$c[$a]=date('Y-m-d',strtotime('last '.$b,strtotime($fecha.'+'.$a.'day')));
	return $c;
}

function ultimoDia($mes,$ano)
{
	$ultimo_dia=28;
	while (checkdate($mes,$ultimo_dia + 1,$ano)){
		$ultimo_dia++;
	}
	return $ultimo_dia;
}

function calcula_numero_dia_semana($dia,$mes,$ano)
{   
	$numerodiasemana = date('w', mktime(0,0,0,$mes,$dia,$ano));
	if ($numerodiasemana == 0)
		$numerodiasemana = 6;
	else
		$numerodiasemana--;
	return $numerodiasemana;
}   

function obtenerIdsGrupos($idGrupo,&$datos)
{
	global $db;

	$qid = $db->sql_query("SELECT id,id_superior FROM mtto.grupos WHERE id=".$idGrupo);
	$query = $db->sql_fetchrow($qid);
	if($query["id_superior"] != "")
	{
		$datos[]=$query["id_superior"];
		return obtenerIdsGrupos($query["id_superior"],$datos);
	}
	return ($datos);
}

function obtenerIdsGruposAbajo($idGrupo,&$datos)
{
	global $db;

	$strQuery="SELECT id, nombre FROM mtto.grupos WHERE id_superior=$idGrupo ORDER BY nombre";
	$qid = $db->sql_query($strQuery);
	while ($result =  $db->sql_fetchrow($qid)) {
		$datos[]= $result["id"];
		if ($result[0] != $idGrupo)
		{
			obtenerIdsGruposAbajo($result["id"],$datos);
		}
	}
}


function insertarFechaProgramadaOT($idOT,$idPersona,$fecha,$estado=false)
{
	global $db;
	$db->sql_query("INSERT INTO mtto.ordenes_trabajo_fechas_programadas (id_orden_trabajo,id_persona,fecha) VALUES ('".$idOT."','".$idPersona."','".$fecha."')");
	if($estado)
		$db->sql_query("UPDATE mtto.ordenes_trabajo SET id_estado_orden_trabajo='8' WHERE id=".$idOT);
}

function restarFechas($fechaUno,$fechaDos)
{
	list($anio,$mes,$dia)=split("-",$fechaUno);
	$timestamp1 = mktime(0,0,0,$mes,$dia,$anio);

	list($anio2,$mes2,$dia2)=split("-",$fechaDos);
	$timestamp2 = mktime(0,0,0,$mes2,$dia2,$anio2);

	//resto a una fecha la otra
	$segundos_diferencia = $timestamp1 - $timestamp2;

	//convierto segundos en d�as
	$dias_diferencia = $segundos_diferencia / (60 * 60 * 24);

	//obtengo el valor absoulto de los d�as (quito el posible signo negativo)
	$dias_diferencia = abs($dias_diferencia);

	//quito los decimales a los d�as de diferencia
	$dias_diferencia = floor($dias_diferencia);

	return $dias_diferencia; 
}

function restarFechasConHHmmss($fechaUno,$fechaDos,$returnSegundos=false)
{
	if($fechaUno == "" || $fechaDos == "")
		return;

	list($f1,$h1) = split(" ",$fechaUno);
	list($anio,$mes,$dia)=split("-",$f1);
	list($hora,$minuto,$seg)=split(":",$h1);
	$timestamp1 = mktime($hora,$minuto,$seg,$mes,$dia,$anio);
	
	list($f2,$h2) = split(" ",$fechaDos);
	list($anio2,$mes2,$dia2)=split("-",$f2);
	list($hora2,$minuto2,$seg2)=split(":",$h2);
	$timestamp2 = mktime($hora2,$minuto2,$seg2,$mes2,$dia2,$anio2);

	//resto a una fecha la otra
	$segundos_diferencia = abs($timestamp1 - $timestamp2);

	if($returnSegundos)
		return $segundos_diferencia;
	
	//convierto segundos en minutos 
	$minutos_diferencia = $segundos_diferencia / 60;

	//obtengo el valor absoulto 
	$minutos_diferencia = abs($minutos_diferencia);

	//quito los decimales 
	$minutos_diferencia = floor($minutos_diferencia);

	return $minutos_diferencia; 
}

function crearOrdenTrabajo($entidad,$idRutina,$idEquipo,$fechaPlaneada, $id_estado_orden_trabajo=7)
{
	global $db,$CFG,$ME;

	$idCreador = $db->sql_row("SELECT id FROM personas WHERE nombre='aidadmin' AND apellido='Autom�tico'");

	$frm = array("id_rutina"=>$idRutina,"id_equipo"=>$idEquipo,"fecha_planeada"=>$fechaPlaneada,"id_creador"=>$idCreador["id"],"id_estado_orden_trabajo"=>$id_estado_orden_trabajo);
	$entidad->loadValues($frm);
	$id=$entidad->insert();
	insertarFechaProgramadaOT($id,$idCreador["id"],$fechaPlaneada);

	return $id;
}

function averiguarFecha($idCentro,$fecha,$primeraVez=false)
{
	global $db;

	//excepciones periodos
	$rangos = $dias = array();
	$qid = $db->sql_query("SELECT * FROM mtto.excepciones_periodos WHERE id_centro = '".$idCentro."' AND fecha_final >= '".$fecha."' ORDER BY fecha_inicio");
	while($query = $db->sql_fetchrow($qid))
	{
		$rangos[] = array("i"=>$query["fecha_inicio"],"f"=>$query["fecha_final"]);
	}
	$qid = $db->sql_query("SELECT * FROM mtto.excepciones_diarias WHERE id_centro = '".$idCentro."'");
	while($query = $db->sql_fetchrow($qid))
	{
		$dias[$query["dia"]] = $query["dia"];
	}

	//anterior  
	$fechaAnt=$fecha;
	sacarFechadeExcepciones($fechaAnt,$rangos,$dias,true);
	$ant = restarFechas($fecha,$fechaAnt);

	//siguiente
	$fechaSig=$fecha;
	sacarFechadeExcepciones($fechaSig,$rangos,$dias);
	$sig = restarFechas($fechaSig,$fecha);

	if($primeraVez)
		return $fechaSig;

	if($sig==0 && $ant==0)
		return $fecha;
	elseif($sig <= $ant)
		return $fechaSig;
	else
		return $fechaAnt;
}

function sacarFechadeExcepciones(&$fecha,$rangos,$dias,$menos=false)
{
	$cambio = false;
	foreach($rangos as $if)
	{
		if($fecha>=$if["i"] && $fecha<=$if["f"])
		{
			if($menos)
			{
				list($anio,$mes,$dia)=split("-",$if["i"]);
				$fecha = date("Y-m-d",mktime(0,0,0, $mes,$dia,$anio) - 1 * 24 * 60 * 60);
			}
			else
			{
				list($anio,$mes,$dia)=split("-",$if["f"]);
				$fecha = date("Y-m-d",mktime(0,0,0, $mes,$dia,$anio) + 1 * 24 * 60 * 60);
			}
			$cambio=true;
		}
	}

	$diaEsc = strftime("%u",strtotime($fecha));
	if(in_array($diaEsc,$dias))
	{
		list($anio,$mes,$dia)=split("-",$fecha);
		if($menos)
			$fecha = date("Y-m-d",mktime(0,0,0, $mes,$dia,$anio) - 1 * 24 * 60 * 60);
		else
			$fecha = date("Y-m-d",mktime(0,0,0, $mes,$dia,$anio) + 1 * 24 * 60 * 60);

		$cambio=true;
	}

	if($cambio)
		sacarFechadeExcepciones($fecha,$rangos,$dias);
}


function actualizarKmyHoro($campo, $value, $idEquipo="", $idVehiculo="")
{
	global $db,$CFG,$ME;

	if($value == "")
		$value = "NULL";

	if($idEquipo != "")
	{
		$equipo = $db->sql_row("SELECT * FROM mtto.equipos WHERE id=".$idEquipo);
		$db->sql_query("UPDATE mtto.equipos SET ".$campo."=".$value." WHERE id=".$idEquipo);
		if($equipo["id_vehiculo"] != "")
		{
			$tiene = $db->sql_row("SELECT tiene_gps FROM vehiculos  WHERE id=".$equipo["id_vehiculo"]);
			if($campo=="kilometraje"){
				$db->sql_query("UPDATE vehiculos SET kilometraje='".$value."', km_virtual='".$value."' WHERE id='" . $equipo["id_vehiculo"] . "'");
				$db->sql_query("UPDATE llta.llantas SET km=".$value." WHERE id_vehiculo=".$equipo["id_vehiculo"]);
			}
			elseif($campo=="horometro"){
				$db->sql_query("UPDATE vehiculos SET horometro='".$value."', horometro_virtual='".$value."' WHERE id='".$equipo["id_vehiculo"]."'");
			}
			if($tiene["tiene_gps"] == "f")
				actualizarHistoricoRecorrido($value,$equipo[$campo],$campo,$idEquipo);	
		}
		else
			actualizarHistoricoRecorrido($value,$equipo[$campo],$campo,$idEquipo);
	}else{
		$veh=$db->sql_row("SELECT * FROM vehiculos WHERE id='".$idVehiculo."'");
		$db->sql_query("UPDATE vehiculos SET ".$campo."=".$value." WHERE id=".$idVehiculo);
		$db->sql_query("UPDATE mtto.equipos SET ".$campo."=".$value." WHERE id_vehiculo=".$idVehiculo);
		if($campo=="kilometraje"){
			$db->sql_query("UPDATE llta.llantas SET km=".$value." WHERE id_vehiculo=".$idVehiculo);
			$db->sql_query("UPDATE vehiculos SET km_virtual=".$value." WHERE id=".$idVehiculo);
		}
		elseif($campo=="horometro"){
			$db->sql_query("UPDATE vehiculos SET horometro_virtual=".$value." WHERE id=".$idVehiculo);
		}

		$tiene = $db->sql_row("SELECT tiene_gps FROM vehiculos WHERE id='".$idVehiculo."'");
		if($tiene["tiene_gps"] == "f")
			actualizarHistoricoRecorrido($value,$veh[$campo],$campo,"",$idVehiculo);	

	}
}

function actualizarKmDesdeMovODes($idMov="", $idDesp="")
{
	global $db, $CFG, $ME;
		
	if($idMov != "")
		$datos = $db->sql_row("SELECT km_final as km, id_vehiculo, inicio FROM rec.movimientos WHERE id=".$idMov);
	else
		$datos = $db->sql_row("SELECT km, id_vehiculo, inicio FROM rec.desplazamientos d LEFT JOIN rec.movimientos m ON m.id=d.id_movimiento WHERE d.id=".$idDesp);
	
	if($datos["km"] != "" && $datos["km"] != "0")
	{
		$ayer = date("Y-m-d",mktime (0,0,0,date("m"),date("d")-1, date("Y")));
		if(strftime("%Y-%m-%d",strtotime($datos["inicio"])) == date("Y-m-d") || strftime("%Y-%m-%d",strtotime($datos["inicio"])) == $ayer )
			actualizarKmyHoro("kilometraje", $datos["km"], "", $datos["id_vehiculo"]);
	}
}


function actualizarHoroDesdeMovODes($idMov="", $idDesp="")
{ 
	global $db, $CFG, $ME;

	if($idMov != "")
		$datos = $db->sql_row("SELECT horometro_final as horo, id_vehiculo, inicio FROM rec.movimientos WHERE id=".$idMov);
	else
		$datos = $db->sql_row("SELECT horometro as horo, id_vehiculo, inicio FROM rec.desplazamientos d LEFT JOIN rec.movimientos m ON m.id=d.id_movimiento WHERE d.id=".$idDesp);

	if($datos["horo"] != "" && $datos["horo"] != "0")
	{
		$ayer = date("Y-m-d",mktime (0,0,0,date("m"),date("d")-1, date("Y")));
		if(strftime("%Y-%d-%m",strtotime($datos["inicio"])) == date("Y-m-d") || strftime("%Y-%d-%m",strtotime($datos["inicio"])) == $ayer )
			actualizarKmyHoro("horometro", $datos["horo"], "", $datos["id_vehiculo"]);
	}
}





function actualizarHistoricoRecorrido($value,$valorAnterior,$campo,$idEquipo="", $idVehiculo="")
{
	global $db,$CFG,$ME;

	if($value=="NULL") $value="0";
//	error_log("value:" . $value . "::valorAnterior" . $valorAnterior . "::campo:" . $campo . "::idEquipo:" . $idEquipo . "::idVehiculo=" . $idVehiculo);

//Porque debe hacerse con la diferencia, no con el total
	$value=($value-$valorAnterior);
	if($value<0) $value=0;

	$equipos = array();
	if($idVehiculo != "")
	{
		$qid = $db->sql_query("SELECT id FROM mtto.equipos WHERE id_vehiculo=".$idVehiculo);
		while($query = $db->sql_fetchrow($qid))
		{
			$equipos[] = $query["id"];
		}
	}
	else
		$equipos[] = $idEquipo;

	if($campo == "horometro") $campo = "horas";
	else $campo = "km";

	foreach($equipos as $key)	
	{
		$qid = $db->sql_query("SELECT * FROM historico_recorrido WHERE id_equipo=".$key." AND $campo != 0 ORDER BY fecha DESC LIMIT 1");
		if($db->sql_numrows($qid) == 0)
//			$db->sql_query("INSERT INTO historico_recorrido (id_equipo,fecha,$campo) VALUES ('$key',now(),'$value')");
//			Yo creo que aqu� debe ir 0, porque si no mete todo el km / hor de una vez
			$db->sql_query("INSERT INTO historico_recorrido (id_equipo,fecha,$campo) VALUES ('$key',now(),'0')");
		else
		{
			$fecha = $db->sql_fetchrow($qid);
			$dias = restarFechas(date("Y-m-d"),$fecha["fecha"]);
			list($anio,$mes,$dia)=split("-",$fecha["fecha"]);
			if($dias > 0)
			{
				$div = number_format($value/$dias,0,".","");
				for($i=1; $i<=$dias; $i++)
				{
					$newFecha = date("Y-m-d",mktime(0,0,0, $mes,$dia,$anio) + $i * 24 * 60 * 60);	
					$db->sql_query("INSERT INTO historico_recorrido (id_equipo,fecha,$campo) VALUES ('$key','$newFecha','$div')");
				}
			}
		}
	}
}


function duplicar_rutina($frm)
{
	global $db,$CFG,$ME;

	$db->sql_query("INSERT INTO mtto.rutinas (rutina, id_sistema, id_grupo, id_equipo, lugar, id_frecuencia, frec_horas, frec_km, fec_cumplir, tiempo_ejecucion, comentarios, id_tipo_mantenimiento, id_prioridad) SELECT 'Duplicado de '||rutina, id_sistema, id_grupo, id_equipo, lugar, id_frecuencia, frec_horas, frec_km, fec_cumplir, tiempo_ejecucion, comentarios, id_tipo_mantenimiento, id_prioridad FROM mtto.rutinas WHERE id=".$frm["id_rutina"]);
 	$idRutina = $db->sql_nextid();

	//centros
	$qid = $db->sql_query("INSERT INTO mtto.rutinas_centros (id_rutina,id_centro) SELECT '".$idRutina."', id_centro FROM mtto.rutinas_centros WHERE id_rutina=".$frm["id_rutina"]);

	//actividades y cargos
	$qid = $db->sql_query("SELECT * FROM mtto.rutinas_actividades WHERE id_rutina='".$frm["id_rutina"]."'");
	while($act = $db->sql_fetchrow($qid))
	{
		$db->sql_query("INSERT INTO mtto.rutinas_actividades (id_rutina,orden,descripcion,tiempo) VALUES ('".$idRutina."','".$act["orden"]."','".$act["descripcion"]."','".$act["tiempo"]."')");
		$idActividad= $db->sql_nextid();
		$qid_rac = $db->sql_query("SELECT * FROM mtto.rutinas_actividades_cargos WHERE id_actividad=".$act["id"]);
		while($rac = $db->sql_fetchrow($qid_rac))
		{
			$db->sql_query("INSERT INTO mtto.rutinas_actividades_cargos (id_actividad, id_cargo, tiempo) VALUES ('".$idActividad."','".$rac["id_cargo"]."','".$rac["tiempo"]."')");
		}
	}

	//elementos
	$qid = $db->sql_query("SELECT * FROM mtto.rutinas_elementos  WHERE id_rutina='".$frm["id_rutina"]."'");
	while($ele = $db->sql_fetchrow($qid))
	{
		if($ele["id_elemento"] != "")
			$db->sql_query("INSERT INTO mtto.rutinas_elementos (id_rutina, id_elemento, cantidad) VALUES ('".$idRutina."', '".$ele["id_elemento"]."','".$ele["cantidad"]."')");
	}

}

function actualizarIdGrupo($tabla,$idGrupo,$campo,$id)
{
	global $db,$CFG,$ME;

	$qid = $db->sql_row("UPDATE ".$tabla." SET id_grupo='".$idGrupo."' WHERE ".$campo."=".$id);
}


function opcionesPosicionesLlantas($id_vehiculo,$id_posicion="")
{
	global $db,$CFG,$ME;

	$opc = "";

	$query = $db->sql_row("SELECT t.num_llantas FROM tipos_vehiculos t LEFT JOIN vehiculos v ON v.id_tipo_vehiculo=t.id WHERE v.id=".$id_vehiculo);
	for($i=1; $i<=$query["num_llantas"]; $i++){
		$selected="";
		if($i == $id_posicion) $selected=" selected";
		$opc.= "<option value=\"" . $i . "\" ".$selected.">" . $i."</option>";
	}

	return $opc;
}

function calcular_fecha_planeada($idEquipo, $fecha, $horo, $km, $actual)
{
	global $db,$CFG,$ME;

//	$hist = $db->sql_row("SELECT sum(km)/7 as km, sum(horas)/7 as horo FROM historico_recorrido WHERE id in (SELECT id FROM historico_recorrido WHERE id_equipo='".$idEquipo."' AND fecha BETWEEN (now()::date-integer '7') AND now())");
	$diasPromedio=$db->sql_field("SELECT promedio FROM mtto.variables_mtto WHERE id_centro = (SELECT id_centro FROM mtto.equipos WHERE id='$idEquipo')");
	if($diasPromedio=="") $diasPromedio=7;
	$strSQL="
		SELECT AVG(km) as km, AVG(horas) as horo
		FROM historico_recorrido
		WHERE id_equipo='".$idEquipo."' AND fecha BETWEEN (now()::date - integer '$diasPromedio') AND now()
		and horas < 24 and km < 500
	";
//	error_log($strSQL);
	$hist = $db->sql_row($strSQL);

	$num1=$num2=$num3=exp(exp(getrandmax()));

	if($fecha!="")
	{
		if(preg_match("/^[+]/",$fecha,$match))
			$num1 = str_replace("+","",$fecha);
		else
			$num1 = restarFechas($fecha,$actual["fecha"]);
	}

	if($horo != "")
	{
		if(preg_match("/^[+]/",$horo,$match))
			$dif = str_replace("+","",$horo);
		else
			$dif = $horo-$actual["horo"];

		if($hist["horo"]==0)
			$num2 = $diasPromedio*$dif;//OJO: Verificar esto...
		else
//			$num2 = (7*$dif)/$hist["horo"];
			$num2 = ($dif)/$hist["horo"];
//		error_log("horo: $horo || dif:$dif || num2: $num2 || hist_horo: $hist[horo]");
	}

	if($km != "")
	{
		if(preg_match("/^[+]/",$km,$match))
			$dif = str_replace("+","",$km);
		else
			$dif = $km-$actual["km"];

		if($hist["km"]==0)
			$num3 = (7*$dif);
		else
//			@$num3 = (7*$dif)/$hist["km"];
			@$num3 = ($dif)/$hist["km"];
	}

	$dias = min($num1,$num2,$num3);

	list($anio,$mes,$dia)=split("-",$actual["fecha"]);
	if($dias != "INF")
		$fecha = date("Y-m-d",mktime(0,0,0, $mes,$dia,$anio) + $dias * 24 * 60 * 60);
	else
		$fecha = date("Y-m-d",mktime(0,0,0, $mes,$dia,$anio) + 1 * 24 * 60 * 60);

//	error_log("fecha: $fecha");
	return $fecha;
}


function estaCerradaOT($id)
{
	global $db;

	if($id != "")
	{
		$qid = $db->sql_row("SELECT cerrado FROM mtto.estados_ordenes_trabajo WHERE id = (SELECT id_estado_orden_trabajo FROM mtto.ordenes_trabajo WHERE id=".$id.")");
		if($qid["cerrado"]=="t")
			return true;
	}

	return false;
}


function actualizarTiempoEjecucion($idOT)
{
	global $db,$CFG;

	$db->sql_query("UPDATE mtto.ordenes_trabajo SET tiempo_ejecucion=null WHERE id=".$idOT);
	$tiempos = $db->sql_row("SELECT fecha_ejecucion_inicio, fecha_ejecucion_fin FROM mtto.ordenes_trabajo WHERE id=".$idOT);
	if($tiempos["fecha_ejecucion_inicio"] != "" && $tiempos["fecha_ejecucion_fin"] != "")
	{
		$gasto = restarFechasConHHmmss($tiempos["fecha_ejecucion_fin"],$tiempos["fecha_ejecucion_inicio"]);
		$db->sql_query("UPDATE mtto.ordenes_trabajo SET tiempo_ejecucion='".$gasto."' WHERE id=".$idOT);
	}
}
?>
