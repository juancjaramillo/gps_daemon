<?
function evaluarSiHaDejadoPeso($idVehiculo)
{
	global $db, $CFG;

	//se evalua el tipo de desplazamiento que se va a insertar, si es recoge_peso se ven los desplazamientos anteriores de ese vehiculo y se mira si hay un recoge_peso y descarga_peso equivalente,  si no hay descarga_peso

	$haDejadoPeso = 0;
	$qidDesAnterior = $db->sql_query("SELECT hora_inicio, t.recoge_peso, t.descarga_peso
			FROM rec.desplazamientos d 
			LEFT JOIN rec.tipos_desplazamientos t ON t.id=d.id_tipo_desplazamiento
			LEFT JOIN rec.movimientos m ON m.id=d.id_movimiento 
			WHERE m.id_vehiculo='".$idVehiculo."' AND hora_inicio > (SELECT d2.hora_inicio FROM rec.desplazamientos d2 LEFT JOIN rec.movimientos m2 ON m2.id=d2.id_movimiento WHERE d2.peso IS NOT NULL AND m2.id_vehiculo='".$idVehiculo."' ORDER BY hora_inicio DESC LIMIT 1) AND (t.recoge_peso OR t.descarga_peso) 
			ORDER BY hora_inicio");
	while($qidAnt = $db->sql_fetchrow($qidDesAnterior))
	{
		if($qidAnt["recoge_peso"])
			$haDejadoPeso++;
		elseif($qidAnt["descarga_peso"])
			$haDejadoPeso--;
	}

	return $haDejadoPeso;
}


function actualizarUsuarioCerroMovimiento($squema, $idMov)
{
	global $db, $CFG;

	$userId = $_SESSION[$CFG->sesion]["user"]["id"];
	$db->sql_query("UPDATE ".$squema.".movimientos SET id_persona_cerro =".$userId." WHERE id=".$idMov);
}


function ingresarLogMovimiento($squema, $idMovimiento, $accion)
{
	global $db, $CFG;

  $user=$_SESSION[$CFG->sesion]["user"];
  $dirip=$_SESSION[$CFG->sesion]["ip"];
  $perfil=$_SESSION[$CFG->sesion]["nivel"];
  $accion = "Fecha: ".date("Y-m-d H:i:s")."\n Direccion Origen: ".$dirip."\n Nivel Usuario: ".$perfil."\n Usuario:".nvl($user["nombre"])." ".nvl($user["apellido"])."\n".$accion."\n\n";

#	$user=$_SESSION[$CFG->sesion]["user"];
#	$accion = "Fecha: ".date("Y-m-d H:i:s")."\nUsuario:".nvl($user["nombre"])." ".nvl($user["apellido"])."\n".$accion."\n\n";

	$db->sql_query("UPDATE ".$squema.".movimientos SET log = COALESCE(log,'') || '".$accion."' WHERE id=".$idMovimiento);
}


function actualizarPesoAsignado($id_mov_peso, $borrado=false, $idPeso=0)
{
	global $db, $CFG;

	if(!$borrado)
	{
		$idPeso = $db->sql_row("SELECT id_peso FROM rec.movimientos_pesos WHERE id=".$id_mov_peso);
		$db->sql_query("UPDATE rec.pesos SET asignado= true WHERE id=".$idPeso["id_peso"]);
	}else
	{
		$qid = $db->sql_row("SELECT count(id) as num FROM rec.movimientos_pesos WHERE id_peso=".$idPeso);
		if($qid["num"] == 0)
		$db->sql_query("UPDATE rec.pesos SET asignado= false WHERE id=".$idPeso);
	}
}






?>
