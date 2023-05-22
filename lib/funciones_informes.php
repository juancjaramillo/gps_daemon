<?
function tablita_titulos($titulo1, $titulo2,$href="tabladatos")
{
	echo '<table width="98%" align="center">
		<tr><h1 id="'.$href.'"></h1>
			<td class="azul_16" align="center" height="30" valign="bottom">'.$titulo1.'</td>
		</tr>
		<tr>
			<td class="azul_16" align="center" height="40" valign="center">'.$titulo2.'</td>
		</tr>
		</table>';
}

function tabla_titulos_reportes($titulo1, $titulo2,$href="tabladatos")
{
	echo '<table width="15%" align="left">
		<tr><h1 id="'.$href.'"></h1>
			<td class="azul_16" align="left" height="30" valign="bottom">'.$titulo1.'</td>
		</tr>
		<tr>
			<td class="azul_16" align="left" height="40" valign="center">'.$titulo2.'</td>
		</tr>
		</table>';
}

function titulo_grande_xls(&$workbook, &$worksheet, $fila, $fin, $texto)
{
	$style1=& $workbook->addformat(array("size"=>"14","bold"=>"1","align"=>"center"));

	$worksheet->write(0,0,$texto,$style1);
	$worksheet->merge_cells($fila,0,$fila,$fin);
}

function titulos_uno_xls(&$workbook, &$worksheet, &$fila, &$columna, $titulos, $mergeHor=0, $mergeVert = 0)
{
	$style2=& $workbook->addformat(array("size"=>"10","border"=>"1","bold"=>"1","align"=>"center","valign"=>"vcenter"));
	foreach($titulos as $cab)
	{
	 	$cab = str_replace("<br />","\n",$cab);
		$worksheet->write($fila,$columna,$cab,$style2);
		if($mergeHor != 0)
		{
			$merge = $columna+$mergeHor;
			$worksheet->merge_cells($fila,$columna,$fila,$merge);
		}
		if($mergeVert != 0)
		{
			$merge = $fila+$mergeVert;
			$worksheet->merge_cells($fila,$columna,$merge,$columna);
		}
		$columna++;
	}
}

function imprimirLinea($datos,$bgColor="#ffffff", $stilos=array())
{
	echo "<tr>\n";
	$i=1;

	foreach($datos as $val)
	{
		$strong=$fstrong="";
		$extra = "";
		$align="align='right'";
		if(isset($stilos[$i])) {
			$extra = $stilos[$i];

			if(preg_match("/strong/",$extra,$match))
			{
				$strong="<b>";
				$fstrong="</b>";
				$extra = str_replace("strong","",$extra);
			}

			if(preg_match("/align/",$extra,$match))
				$align="";
		}

		if($i==1)
			if(!isset($stilos[1]))
				$align="align='left'";

		if($bgColor != "")
			echo "<td ".$align." bgcolor='".$bgColor."' ".$extra.">".$strong.$val.$fstrong."</td>\n";
		else
			echo "<td ".$align."  ".$extra.">".$strong.$val.$fstrong."</td>\n";
		$i++;
	}

	echo "</tr>\n";
}

function imprimirLinea_xls(&$workbook, &$worksheet, &$fila, &$columna, $linea, $estilos = array(), $mergeHor=array(), $mergeVert=array())
{
	//preguntar($linea);

	$txt_norm=& $workbook->addformat(array("size"=>"10","border"=>"1","align"=>"right", "valign"=>"vcenter"));
	$txt_izq=& $workbook->addformat(array("size"=>"10","border"=>"1","align"=>"left"));
	$txt_center=& $workbook->addformat(array("size"=>"10","border"=>"1","align"=>"center", "valign"=>"vcenter"));

	$txt_norm_verde=& $workbook->addformat(array("size"=>"10","border"=>"1","align"=>"right", "bg_color"=>"green"));
	$txt_norm_amarillo=& $workbook->addformat(array("size"=>"10","border"=>"1","align"=>"right", "bg_color"=>"yellow"));
	$txt_norm_rojo=& $workbook->addformat(array("size"=>"10","border"=>"1","align"=>"right", "bg_color"=>"red"));

	$i = 1;
	foreach($linea as $cab)
	{
		$style = $txt_norm;
		if(isset($estilos[$i]) && $estilos[$i]=="txt_izq")
			$style = $txt_izq;
		if(isset($estilos[$i]) && $estilos[$i]=="txt_center")
			$style = $txt_center;
		if(isset($estilos[$i]) && $estilos[$i]=="txt_norm_verde")
			$style = $txt_norm_verde;
		if(isset($estilos[$i]) && $estilos[$i]=="txt_norm_amarillo")
			$style = $txt_norm_amarillo;
		if(isset($estilos[$i]) && $estilos[$i]=="txt_norm_rojo")
			$style = $txt_norm_rojo;

		if(isset($mergeVert[$i]))
		{
			$merge = $fila+$mergeVert[$i];
			$worksheet->merge_cells($fila,$columna,$merge,$columna);
		}

		$cab = str_replace("<br />","\n",$cab);
		$cab = strip_tags($cab);
		$worksheet->write($fila,$columna,$cab,$style);
		$columna++;
		$i++;
	}
	$fila++;$columna=0;
}

function imprimirLineaAzul_xls(&$workbook, &$worksheet, &$fila, &$columna, $titulos, $estilos=array(), $mergeHor=0, $mergeVert = 0)
{
	$azul_norm=& $workbook->addformat(array("size"=>"10","border"=>"1","valign"=>"vcenter", "align"=>"right", "bg_color"=>"cyan"));
	$azul_izq=& $workbook->addformat(array("size"=>"10","border"=>"1","valign"=>"vcenter", "align"=>"left", "bg_color"=>"cyan"));
	$azul_center=& $workbook->addformat(array("size"=>"10","border"=>"1","valign"=>"vcenter", "align"=>"center", "bg_color"=>"cyan"));

	$i = 1;
	foreach($titulos as $cab)
	{
		$style = $azul_norm;
		if(isset($estilos[$i]) && $estilos[$i]=="azul_izq")
			$style = $azul_izq;
		if(isset($estilos[$i]) && $estilos[$i]=="azul_center")
			$style = $azul_center;

		$worksheet->write($fila,$columna,$cab,$style);
		if($mergeHor != 0)
		{
			$merge = $columna+$mergeHor;
			$worksheet->merge_cells($fila,$columna,$fila,$merge);
		}
		if($mergeVert != 0)
		{
			$merge = $fila+$mergeVert;
			$worksheet->merge_cells($fila,$columna,$merge,$columna);
		}
		$columna++;
		$i++;
	}
}

function dividirEnSemanas($inicio,$fin)
{
	$dias = restarFechas($fin,$inicio);
	list($anio,$mes,$dia)=split("-",$inicio);
	$semanas = array();
	for($i=0; $i<=$dias; $i++)
	{
		$sig = date("Y-m-d",mktime(0,0,0, $mes,$dia,$anio) + $i * 24 * 60 * 60);
		$se = obtenerSemana($sig);
		if($inicio > $se["Monday"])
			$se["Monday"]=$inicio;
		if($se["Sunday"] > $fin)
			$se["Sunday"] = $fin;

		$numSemana = strftime("%V",strtotime($sig));
		$semana[$numSemana]	= $se;
	}
	return $semana;
}



function obtenerIdCargos($idCargo,&$datos)
{
  global $db;

  $strQuery="SELECT id, nombre FROM cargos WHERE id_superior=$idCargo ORDER BY nombre";
  $qid = $db->sql_query($strQuery);
  while ($result =  $db->sql_fetchrow($qid)) {
    $datos[]= $result["id"];
    if ($result[0] != $idCargo)
    {
      obtenerIdsGruposAbajo($result["id"],$datos);
    }
  }
}

function SumaHoras($time1, $time2)
{
  list($hour1, $min1, $sec1) = split(":",$time1);
  list($hour2, $min2, $sec2) = split(":",$time2);


	$horas1 = $hour1*3600;
	$horas2 = $hour2*3600;

	$minutos1 = $min1*60;
	$minutos2 = $min2*60;

	$total = $horas1+$horas2+$minutos1+$minutos2+$sec1+$sec2;
	return conversor_segundos($total);
}


function formatearHora($hora)
{
  if($hora != "")
  {
    list($hour,$minut,$second)=split(":",$hora);
    return $hour.":".$minut;
  }
  return ;
}


function dividirTiempo($hora,$segmentos)
{
	list($horas,$mins,$seg)=split(":",$hora);

	$segundos = $horas*3600;
	$minutos = $mins*60;

	$total = $segundos + $minutos + $seg;
	$tm = $total / $segmentos;

	return conversor_segundos($tm);
}


function conversor_segundos($totalSegundos) {

	$horas = floor($totalSegundos/3600);
	$totalSegundos = $totalSegundos - ($horas*3600);
	$minutos = floor($totalSegundos/60);
	$segundos = floor($totalSegundos - ($minutos*60));

	if(strlen($horas)==1)
		$horas="0".$horas;
	if(strlen($minutos)==1)
		$minutos="0".$minutos;
	if(strlen($segundos)==1)
		$segundos="0".$segundos;

	return($horas.":".$minutos.":".$segundos);
}



//pesos
function averiguarPeso($fechaInicio="", $idServicio="", $idCentro="", $id_turno="", $id_ase="")
{
	global $db, $CFG;

	$condicion = array("true");
	if($idServicio != "")
		$condicion[] = "id_servicio='".$idServicio."'";
	if($fechaInicio != "")
		$condicion[] = "m.inicio::date='".$fechaInicio."'";
	if($idCentro != "" && $id_ase=="")
		$condicion[] =  " i.id_ase IN (SELECT a.id FROM ases a WHERE a.id_centro = '".$idCentro."')";
	elseif($id_ase == "")
		$condicion[] = " i.id_ase IN (SELECT a.id FROM ases a WHERE a.id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$_SESSION[$CFG->sesion]["user"]["id"]."'))";
	else
		$condicion[] = " i.id_ase = ".$id_ase;

	if($id_turno != "")
		$condicion[] = "m.id_turno = ".$id_turno;

	$peso = 0;
	$qidPr = $db->sql_query("SELECT mp.*, p.peso_inicial, p.peso_final, p.peso_total
			FROM rec.movimientos_pesos mp
			LEFT JOIN rec.movimientos m ON m.id=mp.id_movimiento
			LEFT JOIN rec.pesos p ON p.id=mp.id_peso
			LEFT JOIN micros i ON i.id=m.id_micro
			WHERE ".implode(" AND ",$condicion)." AND m.final IS NOT NULL
				");
	while($queryPeso = $db->sql_fetchrow($qidPr))
	{
		$pesoNeto = 0;
		if($queryPeso["peso_inicial"] != "" && $queryPeso["peso_final"] != "") $pesoNeto = abs($queryPeso["peso_inicial"]-$queryPeso["peso_final"]);
		elseif($queryPeso["peso_total"] != "") $pesoNeto = $queryPeso["peso_total"];

		$peso += ($pesoNeto*$queryPeso["porcentaje"])/100;
	}
	if($db->sql_numrows($qidPr) == 0)
		return;

	return $peso;
}

function averiguarTiqueteXpeso($idMovimiento,$viaje="",$apoyo=true,$id_turno = "", $id_vehiculo="")
{
		global $db, $CFG;

		$tiquete = '';
		$condicion="";
		if($viaje != "")
				$condicion .= " AND viaje=".$viaje;
		if($id_turno != "")
				$condicion .= " AND m.id_turno = ".$id_turno;
		if($id_vehiculo != "")
				$condicion .= " AND p.id_vehiculo = '".$id_vehiculo."'";

		$consulta =  "SELECT mp.*, p.peso_inicial, p.peso_final, p.peso_total,p.tiquete_entrada
									FROM rec.movimientos_pesos mp
									LEFT JOIN rec.movimientos m ON m.id=mp.id_movimiento
									LEFT JOIN rec.pesos p ON p.id=mp.id_peso
									WHERE mp.id_movimiento=".$idMovimiento.$condicion;
		//echo $consulta."<br>";
		$qidPr = $db->sql_query($consulta);
		while($queryPeso = $db->sql_fetchrow($qidPr))
		{
				$tiquete = $queryPeso["tiquete_entrada"];
		}
		//echo "Cliente->".$tiquete;
		return $tiquete;
}

function averiguarPesoXMov($idMovimiento,$viaje="",$apoyo=true,$id_turno = "", $id_vehiculo="")
{
	global $db, $CFG;

	$peso = 0;
	$condicion="";
	if($viaje != "")
		$condicion .= " AND viaje=".$viaje;
	if($id_turno != "")
		$condicion .= " AND m.id_turno = ".$id_turno;
	if($id_vehiculo != "")
		$condicion .= " AND p.id_vehiculo = '".$id_vehiculo."'";

	$consulta =  "SELECT mp.*, p.peso_inicial, p.peso_final, p.peso_total
			FROM rec.movimientos_pesos mp
			LEFT JOIN rec.movimientos m ON m.id=mp.id_movimiento
			LEFT JOIN rec.pesos p ON p.id=mp.id_peso
			WHERE mp.id_movimiento=".$idMovimiento.$condicion;
	//echo $consulta."<br>";
	$qidPr = $db->sql_query($consulta);
	while($queryPeso = $db->sql_fetchrow($qidPr))
	{
		$pesoNeto = 0;
		if($queryPeso["peso_inicial"] != "" && $queryPeso["peso_final"] != "") $pesoNeto = abs($queryPeso["peso_inicial"]-$queryPeso["peso_final"]);
		elseif($queryPeso["peso_total"] != "") $pesoNeto = $queryPeso["peso_total"];

		$peso += ($pesoNeto*$queryPeso["porcentaje"])/100;
	}

	/*
	//el apoyo no cuenta en el peso del movimiento....  Jun02/2012
	if($apoyo)
		$peso+=averiguarPesoApoyoxMov($idMovimiento,$id_turno);
	*/

	return $peso;
}

function averiguarPesoApoyoxMov($idMovimiento, $id_turno = "")
{
	global $db, $CFG;

	$condicion = "";
	if($id_turno != "")
		$condicion = " AND mov.id_turno = ".$id_turno;

	$peso = 0;

	$consulta =  "SELECT a.*
			FROM rec.apoyos_movimientos m
			LEFT JOIN rec.apoyos a ON a.id=m.id_apoyo
			LEFT JOIN rec.movimientos mov ON mov.id=m.id_movimiento
			WHERE m.id_movimiento=".$idMovimiento.$condicion;
	$qid = $db->sql_query($consulta);
	while($apo = $db->sql_fetchrow($qid))
	{
		$num = $db->sql_row("SELECT count(id) as num FROM rec.apoyos_movimientos WHERE id_apoyo=".$apo["id"]);
		$peso+= $apo["peso"]/$num["num"];
	}
	return $peso;
}

function averiguarNumeroViajes($fechaInicio="", $idServicio="" , $idCentro="",$id_turno="", $id_ase="")
{
	global $db, $CFG;

	$condicion = array("true");
	if($idServicio != "")
		$condicion[] = "id_servicio='".$idServicio."'";
	if($fechaInicio != "")
		$condicion[] = "m.inicio::date='".$fechaInicio."'";
	if($idCentro != "" && $id_ase=="")
		$condicion[] =  " i.id_ase IN (SELECT a.id FROM ases a WHERE a.id_centro = '".$idCentro."')";
	elseif($id_ase == "")
		$condicion[] = " i.id_ase IN (SELECT a.id FROM ases a WHERE a.id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$_SESSION[$CFG->sesion]["user"]["id"]."'))";
	else
		$condicion[] = " i.id_ase = ".$id_ase;
	if($id_turno != "")
		$condicion[] = "m.id_turno = ".$id_turno;

	$viajes = 0;
	$cons = "SELECT max(numero_viaje) as num
		FROM rec.desplazamientos d
		LEFT JOIN rec.movimientos m ON m.id = d.id_movimiento
		LEFT JOIN micros i ON i.id=m.id_micro
		WHERE ".implode(" AND ",$condicion)." AND m.final IS NOT NULL
		GROUP BY id_movimiento";
	$qidV = $db->sql_query($cons);
	while($queryV = $db->sql_fetchrow($qidV))
	{
		$viajes+=$queryV["num"];
	}
	if($db->sql_numrows($qidV) == 0) return;

	return $viajes;
}

function averiguarViajeXMov($idMovimiento, $id_turno="")
{
	global $db, $CFG;

	$condicion ="";
	if($id_turno != "")
		$condicion = " AND m.id_turno = ".$id_turno;

/*
		SELECT max(d.numero_viaje) as num
		FROM rec.desplazamientos d
		LEFT JOIN rec.movimientos m ON m.id = d.id_movimiento
		WHERE id_movimiento='".$idMovimiento."' $condicion
*/
	$qidV = $db->sql_row("
		SELECT COUNT(*) as num
		FROM rec.movimientos_pesos mp
		LEFT JOIN rec.movimientos m ON m.id = mp.id_movimiento
		WHERE mp.id_movimiento='".$idMovimiento."' $condicion
	");
	return nvl($qidV["num"],0);
}

function averiguarDesplazamientos($fechaInicio="", $idServicio="" , $idCentro="", $id_turno="", $id_ase="")
{
	global $db, $CFG;

	$condicion = array("true");
	if($idServicio != "")
		$condicion[] = "id_servicio='".$idServicio."'";
	if($fechaInicio != "")
		$condicion[] = "m.inicio::date='".$fechaInicio."'";
	if($idCentro != "" && $id_ase=="")
		$condicion[] =  " i.id_ase IN (SELECT a.id FROM ases a WHERE a.id_centro = '".$idCentro."')";
	elseif($id_ase == "")
		$condicion[] = " i.id_ase IN (SELECT a.id FROM ases a WHERE a.id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$_SESSION[$CFG->sesion]["user"]["id"]."'))";
	else
		$condicion[] = " i.id_ase = ".$id_ase;

	if($id_turno != "")
		$condicion[] = "m.id_turno = ".$id_turno;

	$kms = 0;
	$qidDes = $db->sql_query("SELECT max(d.km) as maxkm, min(d.km) as minkm
			FROM rec.desplazamientos d
			LEFT JOIN rec.movimientos m ON m.id = d.id_movimiento
			LEFT JOIN micros i ON i.id=m.id_micro
			WHERE ".implode(" AND ",$condicion)." AND m.final IS NOT NULL
			GROUP BY id_movimiento");
	while($des = $db->sql_fetchrow($qidDes))
	{
		$kms+=$des["maxkm"]-$des["minkm"];
	}
	if($db->sql_numrows($qidDes) == 0) return;

	return $kms;
}

function averiguarCombustible($dia="", $idServicio="", $idCentro="", $id_turno="", $id_ase="")
{
	global $db, $CFG;

	$condicion = array("true");
	if($idServicio != "")
		$condicion[] = "id_servicio='".$idServicio."'";
	if($dia != "")
		$condicion[] = "m.inicio::date='".$dia."'";
	if($idCentro != "" && $id_ase=="")
		$condicion[] =  " i.id_ase IN (SELECT a.id FROM ases a WHERE a.id_centro = '".$idCentro."')";
	elseif($id_ase == "")
		$condicion[] = " i.id_ase IN (SELECT a.id FROM ases a WHERE a.id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$_SESSION[$CFG->sesion]["user"]["id"]."'))";
	else
		$condicion[] = " i.id_ase = ".$id_ase;
	if($id_turno != "")
		$condicion[] = "m.id_turno = ".$id_turno;

	$qidCom = $db->sql_row("SELECT sum(combustible) as comb
		FROM rec.movimientos m
		LEFT JOIN micros i ON i.id=m.id_micro
		WHERE ".implode(" AND ",$condicion)." AND m.final IS NOT NULL");
	$comb = nvl($qidCom["comb"],"");

	if($comb == "") return;

	return $comb;
}


function getIntersection($a1,$a2,$b1,$b2)
{
	$a1 = strtotime($a1);
	$a2 = strtotime($a2);
	$b1 = strtotime($b1);
	$b2 = strtotime($b2);
	if($b1 > $a2 || $a1 > $b2 || $a2 < $a1 || $b2 < $b1)
	{
		return false;
	}
	$start = $a1 < $b1 ? $b1 : $a1;
	$end = $a2 < $b2 ? $a2 : $b2;

	return array('start' => $start, 'end' => $end);
}

function tiempoOperacionxDia($fecha, $idCentro="", $idTurno="", $id_ase="")
{
	global $db, $CFG;

	$cond = " a.id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$_SESSION[$CFG->sesion]["user"]["id"]."')";
	if($idCentro != "")
		$cond = " a.id_centro = ".$idCentro;
	if($id_ase != "")
		$cond = " a.id = '".$id_ase."'";

	$condTurno = "";
	if($idTurno!="")
		$condTurno = " AND m.id_turno=".$idTurno;

	$tiempo = 0;
	$qidOpera = $db->sql_query("SELECT m.*
			FROM rec.movimientos m
			WHERE m.inicio::date='".$fecha."' AND m.final IS NOT NULL AND id_micro IN (SELECT i.id FROM micros i LEFT JOIN ases a ON a.id=i.id_ase WHERE ".$cond." ) $condTurno");
	while($opera = $db->sql_fetchrow($qidOpera))
	{
		$tiempo+=restarFechasConHHmmss($opera["inicio"],$opera["final"]);
	}

	return $tiempo;
}

function tiempoOperacionxvehi($inicio, $final, $idCentro="", $idTurno="", $id_ase="")
{
	global $db, $CFG;

	$cond = " a.id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$_SESSION[$CFG->sesion]["user"]["id"]."')";
	if($idCentro != "")
		$cond = " a.id_centro = ".$idCentro;
	if($id_ase != "")
		$cond = " a.id = '".$id_ase."'";

	$condTurno = "";
	if($idTurno!="")
		$condTurno = " AND m.id_turno=".$idTurno;

	unset($tiempo);

	$qidOpera = $db->sql_query("SELECT m.*
			FROM rec.movimientos m
			left join vehiculos v on m.id_vehiculo=v.id
			WHERE m.inicio::date>='".$inicio."' AND m.inicio::date<='".$final."' AND m.final IS NOT NULL
			AND id_micro IN (SELECT i.id FROM micros i LEFT JOIN ases a ON a.id=i.id_ase WHERE ".$cond." ) $condTurno order by v.codigo");
	$idvehi = $tiempoopera= 0;
	while($opera = $db->sql_fetchrow($qidOpera))
	{
		if ($idvehi==$opera["id_vehiculo"])
			$tiempoopera+=restarFechasConHHmmss($opera["inicio"],$opera["final"]);
		else {
			$tiempo[$idvehi]=$tiempoopera;
			$tiempoopera= 0;
			$tiempoopera+=restarFechasConHHmmss($opera["inicio"],$opera["final"]);}
		$idvehi = $opera["id_vehiculo"];
	}
	$tiempo[$idvehi]=$tiempoopera;
	return $tiempo;
}

function tiempoTeoricoOperacionxDia($fecha, $idCentro = "", $idTurno="", $id_ase="")
{
	global $db, $CFG;

	$cond = " a.id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$_SESSION[$CFG->sesion]["user"]["id"]."')";
	if($idCentro != "")
		$cond = " a.id_centro = ".$idCentro;
	if($id_ase != "")
		$cond = " a.id = '".$id_ase."'";

	$condTurno = "";
	if($idTurno!="")
		$condTurno = " AND f.id_turno=".$idTurno;

	$tiempo = 0;
	$qidOpera = $db->sql_query("SELECT f.*
		FROM micros_frecuencia f
		WHERE f.dia='".strftime("%u",strtotime($fecha))."' AND f.id_micro IN (SELECT i.id FROM micros i LEFT JOIN ases a ON a.id=i.id_ase WHERE  ".$cond.") $condTurno");
	while($opera = $db->sql_fetchrow($qidOpera))
	{
		$hi = $fecha." ".$opera["hora_inicio"];
		if($opera["hora_fin"]<$opera["hora_inicio"])
		{
			list($anio,$mes,$dia)=split("-",$fecha);
			$hf = date("Y-m-d",mktime(0,0,0, $mes,$dia,$anio) + 1 * 24 * 60 * 60) ." ".$opera["hora_fin"];
		}
		else
			$hf = $fecha." ".$opera["hora_fin"];

		$tiempo+=restarFechasConHHmmss($hf,$hi);
	}

	return $tiempo;
}

function kmsOperacionxDia($fecha, $idCentro="", $idTurno="", $id_ase="")
{
	global $db, $CFG;

	$cond = " a.id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$_SESSION[$CFG->sesion]["user"]["id"]."')";
	if($idCentro != "")
		$cond = " a.id_centro = ".$idCentro;
	if($id_ase != "")
		$cond = " a.id = '".$id_ase."'";

	$condTurno = "";
	if($idTurno!="")
		$condTurno = " AND m.id_turno=".$idTurno;

	$km = 0;
	$qid = $db->sql_query("SELECT km_final, (SELECT d.km FROM rec.desplazamientos d WHERE d.id_movimiento=m.id ORDER BY hora_inicio LIMIT 1) AS km_inicio
			FROM rec.movimientos m
			WHERE m.inicio::date='".$fecha."' AND m.final IS NOT NULL AND id_micro IN (SELECT i.id FROM micros i LEFT JOIN ases a ON a.id=i.id_ase WHERE ".$cond.") $condTurno");
	while($rec = $db->sql_fetchrow($qid))
	{
		$km+=abs($rec["km_final"] - $rec["km_inicio"]);
	}
	return $km;
}

function kmsTeoricoOperacionxDia($fecha, $idCentro="", $idTurno="", $id_ase="")
{
	global $db, $CFG;

	$cond = " a.id_centro IN (SELECT id_centro FROM personas_centros WHERE id_persona='".$_SESSION[$CFG->sesion]["user"]["id"]."')";
	if($idCentro != "")
		$cond = " a.id_centro = ".$idCentro;
	if($id_ase != "")
		$cond = " a.id = '".$id_ase."'";

	$condTurno = "";
	if($idTurno!="")
		$condTurno = " AND f.id_turno=".$idTurno;

	$km = 0;
	$qidOpera = $db->sql_query("SELECT i.km
			FROM micros_frecuencia f
			LEFT JOIN micros i ON i.id=f.id_micro
			WHERE f.dia='".strftime("%u",strtotime($fecha))."' AND i.id_ase IN (SELECT a.id FROM ases a WHERE ".$cond.") $condTurno");
	while($opera = $db->sql_fetchrow($qidOpera))
	{
		$km+=$opera["km"]	;
	}
	return $km;
}

function kmsRecorridoPorMov($idMov)
{
	global $db, $CFG;

	if($kmini = $db->sql_row("SELECT km FROM rec.desplazamientos WHERE id_movimiento=".$idMov." ORDER BY hora_inicio LIMIT 1")){
		$kmfin = $db->sql_row("SELECT km_final FROM rec.movimientos WHERE id = '".$idMov."'");
		$recorrido = $kmfin["km_final"] - nvl($kmini["km"],0);
	}
	else $recorrido="0";//Quiere decir que no hubo desplazamientos, en consecuencia no debe registrar km recorrido.

	return $recorrido;
}


function sacarTiemposDisponibilidadFlota($dxSem)
{
	global $db, $CFG;

	$ttt = 0;

	foreach($dxSem as $dia => $vh)
	{
		foreach($vh as $idVehiculo => $fechas)
		{
			if(count($fechas) == 1)
			{
				$pe = key($fechas);
				$ttt+=restarFechasConHHmmss($fechas[$pe][0],$fechas[$pe][1]);
			}
			else
			{
				$new = array();
				//ordenarlas por fecha
				foreach($fechas as $dx)
				{
					$new[strtotime($dx[0])][] = $dx;
				}
				ksort($new);
				$ordenada = array();
				foreach($new as $dx)
				{
					foreach($dx as $dos)
					{
						$ordenada[] = $dos;
					}
				}

				$res = array();
				foreach($ordenada as $idNew => $dxPrin)
				{
					$hi = $dxPrin[0];
					$hf = $dxPrin[1];
					$segunda = $ordenada;
					unset($segunda[$idNew]);

					foreach($segunda as $idComp => $dxSec)
					{
						if($dxSec[0] < $hi && $hi < $dxSec[1])
						{
							$hi = $dxSec[0];
							$ordenada[$idNew][0]=$hi;
						}
						if($dxSec[1] > $hf && $hf > $dxSec[0])
						{
							$hf = $dxSec[1];
							$ordenada[$idNew][1]=$hf;
						}
					}

					$clave = $hi."/".$hf;
					$res[$clave] = array($hi,$hf);
				}

				foreach($res as $dx)
				{
					$ttt+=restarFechasConHHmmss($dx[0],$dx[1]);
				}
			}
		}
	}
	return $ttt;
}



function bolsasXMovimiento($idMov)
{
	global $db;

	$bolsas=0;

	$qidBolsas = $db->sql_query("SELECT numero_inicio-numero_fin as num, id_tipo_bolsa FROM bar.movimientos_bolsas WHERE  id_movimiento=".$idMov);
	while($queryBol=$db->sql_fetchrow($qidBolsas))
	{
		$bolsas+=$queryBol["num"];
	}

	return $bolsas;
}


function costoXOrdenTrabajo($idOrden)
{
	global $db;

	$total = 0;

	//personas
	$qidPer=$db->sql_query("SELECT oc.tiempo as tiempo_ejecucion, c.valor
			FROM mtto.ordenes_trabajo_actividades_cargos oc
			LEFT JOIN mtto.ordenes_trabajo_actividades a ON a.id=oc.id_orden_trabajo_actividad
			LEFT JOIN cargos c ON c.id=oc.id_cargo
			WHERE a.id_orden_trabajo='".$idOrden."'");
	while($quePer = $db->sql_fetchrow($qidPer))
	{
		$total += ($quePer["tiempo_ejecucion"]*$quePer["valor"])/60;
	}

	//elementos
	$qidEleExis = $db->sql_query("
			SELECT x.cantidad, ex.precio
			FROM mtto.ordenes_trabajo_elementos x
			LEFT JOIN mtto.elementos e ON e.id=x.id_elemento
			LEFT JOIN mtto.elementos_existencias ex ON ex.id_elemento=e.id
			WHERE x.id_orden_trabajo='".$idOrden."' ");
	while($ele =  $db->sql_fetchrow($qidEleExis))
	{
		$total += $ele["precio"]*$ele["cantidad"];
	}

	//talleres
	$cons = "SELECT t.costo
		FROM mtto.ordenes_trabajo_talleres t
		WHERE t.id_orden_trabajo=".$idOrden;
	$qidOEx = $db->sql_query($cons);
	while($taller = $db->sql_fetchrow($qidOEx))
	{
		$total+=$taller["costo"];
	}

	return $total;
}

function costoXRutina($idRutina, $idCentro)
{
	global $db;

	$total = 0;

	//personas
	$qidPer=$db->sql_query("SELECT r.tiempo, c.valor
			FROM mtto.rutinas_actividades_cargos r
			LEFT JOIN mtto.rutinas_actividades a ON a.id=r.id_actividad
			LEFT JOIN cargos c ON c.id=r.id_cargo
			WHERE a.id_rutina='".$idRutina."'");
	while($quePer = $db->sql_fetchrow($qidPer))
	{
		$total += ($quePer["tiempo"]*$quePer["valor"])/60;
	}

	//elementos
	$qidEleExis = $db->sql_query("SELECT x.cantidad, e.id as id_elemento
			FROM mtto.rutinas_elementos x
			LEFT JOIN mtto.elementos e ON e.id=x.id_elemento
			WHERE x.id_rutina='".$idRutina."'");
	while($ele =  $db->sql_fetchrow($qidEleExis))
	{
		$exist = $db->sql_row("SELECT e.precio
				FROM mtto.elementos_existencias e
				LEFT JOIN mtto.bodegas b ON b.id=e.id_bodega
				WHERE e.id_elemento = '".$ele["id_elemento"]."' AND b.id_centro=".$idCentro);
		$existencia = nvl($exist["precio"],0);
		$total += $existencia*$ele["cantidad"];
	}

	//talleres
	$cons = "SELECT t.costo
		FROM mtto.rutinas_talleres t
		WHERE t.id_rutina=".$idRutina;
	$qidOEx = $db->sql_query($cons);
	while($taller = $db->sql_fetchrow($qidOEx))
	{
		$total+=$taller["costo"];
	}

	return $total;
}

function devolverIniciales($dato)
{
	$iniciales = array();
	$dato = explode(" ",$dato);
	foreach($dato as $nm)
	{
		$iniciales[] = substr($nm, 0, 1) ;
	}
	return implode(".",$iniciales);
}

function costoRecoleccion($vehiculos, $fechaInicio, $fechaFinal)
{
	global $dbOracle, $CFG;

	$costo = 0;
	foreach($vehiculos as $placa)
	{
		$cons =  "SELECT ccc.FECHA_GEN, ccc.ID_EMP, ccc.ID_CO, ccc.LAPSO_DOC, ccc.ID_CUENTA, ccc.id_cconiv4, ccc.saldos_final_real, cta.id_ctaniv3, cta.DESCRIPCION as ctades, cos.DESCRIPCION as cosdesc
			FROM CGRESUMEN_CUENTA_CCOSTO ccc
			LEFT JOIN CUENTAS_CONTAB cta ON cta.codigo = ccc.id_cuenta
			LEFT JOIN centro_costo cos ON ccc.id_cconiv4 = cos.codigo
			WHERE cta.codigo like '75%' AND ID_CCONIV4 = '".trim(str_replace("-","",$placa))."' AND FECHA_GEN>='".str_replace("-","",$fechaInicio)."' AND FECHA_GEN<='".str_replace("-","",$fechaFinal)."'
			ORDER BY lapso_doc";
		$qid=$dbOracle->sql_query($cons);
		while($co = $dbOracle->sql_fetchrow($qid))
		{
			$costo+=$co["SALDOS_FINAL_REAL"];
		}
	}

	//327275 => misc
	$cons =  "SELECT ccc.FECHA_GEN, ccc.ID_EMP, ccc.ID_CO, ccc.LAPSO_DOC, ccc.ID_CUENTA, ccc.id_cconiv4, ccc.saldos_final_real, cta.id_ctaniv3, cta.DESCRIPCION as ctades, cos.DESCRIPCION as cosdesc
			FROM CGRESUMEN_CUENTA_CCOSTO ccc
			LEFT JOIN CUENTAS_CONTAB cta ON cta.codigo = ccc.id_cuenta
			LEFT JOIN centro_costo cos ON ccc.id_cconiv4 = cos.codigo
			WHERE cta.codigo = '75709001' AND (ID_CCONIV4 = 'CIERRE' OR ID_CCONIV4 = '327275' OR ID_CCONIV4 = '322222')   AND FECHA_GEN>='".str_replace("-","",$fechaInicio)."' AND FECHA_GEN<='".str_replace("-","",$fechaFinal)."'
	";
	$qid=$dbOracle->sql_query($cons);
	while($co = $dbOracle->sql_fetchrow($qid))
	{
		//preguntar($co);
		$costo+=$co["SALDOS_FINAL_REAL"];
	}

	return $costo;
}

function costoBarridoPersona($idMovimiento, $inicio, $final)
{
	global $db;

	$valor = 0;
	$tiempo=restarFechasConHHmmss($inicio,$final);
	$qidP = $db->sql_query("SELECT c.*
		FROM bar.movimientos_personas mp
		LEFT JOIN personas p ON p.id = mp.id_persona
		LEFT JOIN cargos c ON c.id = p.id_cargo
		WHERE mp.id_movimiento=".$idMovimiento);
	while($carg = $db->sql_fetchrow($qidP))
	{
		$valor += ($carg["valor"] * $tiempo) / 60;
	}

	return $valor;
}

function otrosCostosBarrido($fechaInicio, $fechaFinal)
{
	global $dbOracle, $CFG;

	$costo = 0;
	$cons =  "
			SELECT ccc.FECHA_GEN, ccc.ID_EMP, ccc.ID_CO, ccc.LAPSO_DOC, ccc.ID_CUENTA, ccc.id_cconiv4, ccc.saldos_final_real, cta.id_ctaniv3, cta.DESCRIPCION as ctades, cos.DESCRIPCION as cosdesc
			FROM CGRESUMEN_CUENTA_CCOSTO ccc
			LEFT JOIN CUENTAS_CONTAB cta ON cta.codigo = ccc.id_cuenta
			LEFT JOIN centro_costo cos ON ccc.id_cconiv4 = cos.codigo
			WHERE cta.codigo like '755012%' AND FECHA_GEN>='".str_replace("-","",$fechaInicio)."' AND FECHA_GEN<='".str_replace("-","",$fechaFinal)."'
			UNION
			SELECT ccc.FECHA_GEN, ccc.ID_EMP, ccc.ID_CO, ccc.LAPSO_DOC, ccc.ID_CUENTA, ccc.id_cconiv4, ccc.saldos_final_real, cta.id_ctaniv3, cta.DESCRIPCION as ctades, cos.DESCRIPCION as cosdesc
			FROM CGRESUMEN_CUENTA_CCOSTO ccc
			LEFT JOIN CUENTAS_CONTAB cta ON cta.codigo = ccc.id_cuenta
			LEFT JOIN centro_costo cos ON ccc.id_cconiv4 = cos.codigo
			WHERE cta.codigo = '75950503' AND FECHA_GEN>='".str_replace("-","",$fechaInicio)."' AND FECHA_GEN<='".str_replace("-","",$fechaFinal)."'
			UNION
			SELECT ccc.FECHA_GEN, ccc.ID_EMP, ccc.ID_CO, ccc.LAPSO_DOC, ccc.ID_CUENTA, ccc.id_cconiv4, ccc.saldos_final_real, cta.id_ctaniv3, cta.DESCRIPCION as ctades, cos.DESCRIPCION as cosdesc
			FROM CGRESUMEN_CUENTA_CCOSTO ccc
			LEFT JOIN CUENTAS_CONTAB cta ON cta.codigo = ccc.id_cuenta
			LEFT JOIN centro_costo cos ON ccc.id_cconiv4 = cos.codigo
			WHERE cta.codigo = '75952103' AND FECHA_GEN>='".str_replace("-","",$fechaInicio)."' AND FECHA_GEN<='".str_replace("-","",$fechaFinal)."'

			";
	$qid=$dbOracle->sql_query($cons);
	while($co = $dbOracle->sql_fetchrow($qid))
	{
		$costo+=$co["SALDOS_FINAL_REAL"];
	}
	return $costo;
}


function tiempoMovimientoRuta($id_movimiento, $returnProm=true, $viaje="")
{
	global $db, $CFG;

	$cond = "";
	if($viaje != "")
		$cond = " AND numero_viaje='".$viaje."'";

	$dx = array();
	$qid = $db->sql_query("SELECT numero_viaje, hora_inicio, hora_fin, id_tipo_desplazamiento
		FROM rec.desplazamientos
		WHERE id_movimiento='".$id_movimiento."' AND id_tipo_desplazamiento IN (3,4) $cond
		ORDER BY hora_inicio");
	while($query = $db->sql_fetchrow($qid))
	{
		if($query["id_tipo_desplazamiento"] == 3)
			$dx[$query["numero_viaje"]]["ini"] = $query["hora_inicio"];
		else
			$dx[$query["numero_viaje"]]["fin"] = $query["hora_fin"];
	}

	//preguntar($dx);
	$viajes = $suma = 0;
	foreach($dx as $tiempos)
	{
		if(isset($tiempos["ini"]) && isset($tiempos["fin"]))
		{
			$suma+=restarFechasConHHmmss($tiempos["ini"], $tiempos["fin"],true);
			$viajes++;
		}
	}

	if(!$returnProm)
		return $suma;

	@$prom = $suma/$viajes;
	return $prom;
}

function pasarHorasADecimales($hora)
{
	$hora = explode(":", $hora);
	$x =$hora[0] + ($hora[1]/60);

	return $x;
}

function graficaBarras($dxGraf, $titulo, $labelY, $tdUno, $tdDos, $gradosLabelX=90, $sizeLabelX=7)
{
	global $CFG;

	$i=rand();

#require_once($CFG->libdir."/ChartDirector/lib/phpchartdir.php");
	require_once("/var/www/html/ChartDirector/lib/phpchartdir.php");

	$c = new XYChart(500, 350);
	$c->addTitle($titulo, "arialbi.ttf", 12);
	//$c->setPlotArea(60, 40, $c->getWidth() - 65, $c->getHeight() - 105, $c->linearGradientColor(60, 40, 60, 280, 0xb4cae8, 0x0056ca), -1, 0xffffff, 0xffffff);
	$c->setPlotArea(60, 40, $c->getWidth() - 65, $c->getHeight() - 105, 0xffffff, -1, Transparent, 0x7d7d7d);


	# Add a multi-color bar chart layer using the supplied data. Use soft lighting effect with light direction from left.
	$barLayerObj = $c->addBarLayer3($dxGraf["data"]);
	$barLayerObj->setBorderColor(Transparent, softLighting(Left));

	if(count($dxGraf["data"]) < 10)
		$barLayerObj->setBarWidth(10, -1);

	$c->xAxis->setLabels($dxGraf["labels"]);
	$c->xAxis->setTickOffset(0.5);
	$c->yAxis->setTitle($labelY, "arialbi.ttf", 8);
	$c->xAxis->setLabelStyle("Arial", $sizeLabelX, TextColor, $gradosLabelX);
	$c->yAxis->setLabelStyle("Arial", 7, TextColor);
	$c->xAxis->setColors(0x000000);
	$c->yAxis->setColors(0x000000);
	$c->xAxis->setWidth(1);
	$c->yAxis->setWidth(1);

	$tiempo = md5( rand(254696, microtime(true)));
	$ruta_grafica = $CFG->tmpdir."/charDirector_".$tiempo.".png";
	$file = fopen($ruta_grafica, "w");
	if($file)
	{
		fwrite($file, $c->makeChart2(PNG));
		fclose($file);
	}

	# Client side Javascript to show detail information "onmouseover"
	$showText = "onmouseover='showInfo_".$i."(\"{xLabel}\", {value});' ";
	# Client side Javascript to hide detail information "onmouseout"
	$hideText = "onmouseout='showInfo_".$i."(null);' ";
	# "title" attribute to show tool tip
	$toolTip = "title='{xLabel} : {value}'";
	# Create an image map for the chart
	$imageMap = $c->getHTMLImageMap("#", "", "$showText$hideText$toolTip");

	?>

	<script type="text/javascript">

		function showInfo_<?=$i?>(dia, dato) {
			var obj_<?=$i?>;
			if (document.getElementById)
				//IE 5.x or NS 6.x or above
				obj_<?=$i?> = document.getElementById('detailInfo_<?=$i?>');
			else
				//IE 4.x
				obj_<?=$i?> = document.all['detailInfo_<?=$i?>'];

			if (!dia) {
				obj_<?=$i?>.style.visibility = "hidden";
				return;
			}

			var content_<?=$i?> = "<table align='center' width='400' style='font-family: Verdana, Arial, Helvetica, sans-serif; font-size: 11px; color:#ffffff; border:solid 1px #ffffff; background-color:#009293;  '>";
			content_<?=$i?> += "<tr><td align='center'><b><?=$tdUno?> : " + dia + "</b></td></tr>";
			content_<?=$i?> += "<tr><td align='center'><b><?=$tdDos?> : " + dato + "</b></td></tr>";
			content_<?=$i?> += "</table>";

			obj_<?=$i?>.innerHTML = content_<?=$i?>;
			obj_<?=$i?>.style.visibility = "visible";
		}
	</script>

<table width='98%' align='center'>
	<tr>
		<td>
			<h1 id="tablagrafica"></h1>
			<img src="<?=$CFG->wwwroot?>/tmp/charDirector_<?=$tiempo?>.png" border="0" usemap="#map1_<?=$i?>">
			<map name="map1_<?=$i?>"> <?php echo $imageMap ?> </map>
			<div id="detailInfo_<?=$i?>" style="margin-left:auto; margin-right:auto; width:60%; "></div>
		</td>
	</tr>
</table>

<?
}

function graficaBarras_21_23($dxGraf, $titulo, $labelY, $inicio, $final, $id_centro, $id_turno)
{
	global $CFG;

	$i=rand();

#require_once($CFG->libdir."/ChartDirector/lib/phpchartdir.php");
	require_once("/var/www/html/ChartDirector/lib/phpchartdir.php");

	$c = new XYChart(500, 350);
	$c->addTitle($titulo, "arialbi.ttf", 12);
	$c->setPlotArea(60, 40, $c->getWidth() - 65, $c->getHeight() - 105, 0xffffff, -1, Transparent, 0x7d7d7d);


	# Add a multi-color bar chart layer using the supplied data. Use soft lighting effect with light direction from left.
	$barLayerObj = $c->addBarLayer3($dxGraf["data"]);
	$barLayerObj->setBorderColor(Transparent, softLighting(Left));

	if(count($dxGraf["data"]) < 10)
		$barLayerObj->setBarWidth(10, -1);

	$c->xAxis->setLabels($dxGraf["labels"]);
	$c->xAxis->setTickOffset(0.5);
	$c->yAxis->setTitle($labelY, "arialbi.ttf", 8);
	$c->xAxis->setLabelStyle("Arial", 7, TextColor, 90);
	$c->yAxis->setLabelStyle("Arial", 7, TextColor);
	$c->xAxis->setColors(0x000000);
	$c->yAxis->setColors(0x000000);
	$c->xAxis->setWidth(1);
	$c->yAxis->setWidth(1);

	$tiempo = md5( rand(254696, microtime(true)));
	$ruta_grafica = $CFG->tmpdir."/charDirector_".$tiempo.".png";
	$file = fopen($ruta_grafica, "w");
	if($file)
	{
		fwrite($file, $c->makeChart2(PNG));
		fclose($file);
	}

	$showText = "onclick='showInfo_".$i."(\"{xLabel}\");'";
	$toolTip = "title='{xLabel} : {value} \n Haga click para ver el detalle diario'";
	$imageMap = $c->getHTMLImageMap("#", "", "$showText$toolTip");
	?>

	<script type="text/javascript">
		function showInfo_<?=$i?>(codigo) {
			url = '<?=$CFG->wwwroot?>/opera/movimientos_rec.php?mode=graficaDetalleVehiculo&inicio=<?=$inicio?>&final=<?=$final?>&id_centro=<?=$id_centro?>&id_turno=<?=$id_turno?>&codigo='+codigo;
			abrirVentanaJavaScript('graficadetalle_<?=$i?>', 600, 440, url );
		}
	</script>

<table width='98%' align='center'>
	<tr>
		<td>
			<h1 id="tablagrafica"></h1>
			<img src="<?=$CFG->wwwroot?>/tmp/charDirector_<?=$tiempo?>.png" border="0" usemap="#map_<?=$i?>">
			<map name="map_<?=$i?>"> <?php echo $imageMap ?> </map>
		</td>
	</tr>
</table>

<?
}


function graficaPieDepth($dxGraf, $titulo)
{
	global $CFG;

#require_once($CFG->libdir."/ChartDirector/lib/phpchartdir.php");
	require_once("/var/www/html/ChartDirector/lib/phpchartdir.php");

# The depths for the sectors
$depths = array(30, 20, 10, 10);

# Create a PieChart object of size 360 x 300 pixels, with a light blue (DDDDFF) background and a 1 pixel 3D border
$c = new PieChart(500, 350, 0xddddff, -1, 1);

# Set the center of the pie at (180, 175) and the radius to 100 pixels
$c->setPieSize(180, 175, 100);

# Add a title box using 15 pts Times Bold Italic font and blue (AAAAFF) as background color
$textBoxObj = $c->addTitle($titulo, "timesbi.ttf", 15);
$textBoxObj->setBackground(0xaaaaff);

# Set the pie data and the pie labels
$c->setData($dxGraf["data"], $dxGraf["labels"]);

# Draw the pie in 3D with variable 3D depths
$c->set3D2($depths);

# Set the start angle to 225 degrees may improve layout when the depths of the sector are sorted in descending order, because it ensures the tallest sector is at the back.
$c->setStartAngle(225);



	$tiempo = md5( rand(254696, microtime(true)));
	$ruta_grafica = $CFG->tmpdir."/charDirector_".$tiempo.".png";
	$file = fopen($ruta_grafica, "w");
	if($file)
	{
		fwrite($file, $c->makeChart2(PNG));
		fclose($file);
	}

?>
<table width='98%' align='center'>
	<tr>
		<td>
			<h1 id="tablagrafica"></h1>
			<img src="<?=$CFG->wwwroot?>/tmp/charDirector_<?=$tiempo?>.png" border="0" >
		</td>
	</tr>
</table>
<?
}

function graficaGradientBar($dxGraf, $titulo, $labelY)
{
	global $CFG;

#require_once($CFG->libdir."/ChartDirector/lib/phpchartdir.php");
	require_once("/var/www/html/ChartDirector/lib/phpchartdir.php");

	# Create a PieChart object of size 600 x 380 pixels.
	$c = new XYChart(600, 300);

	$title = $c->addTitle($titulo, "arialbi.ttf", 12);
	$c->setPlotArea(70, 80, 480, 240, $c->linearGradientColor(0, 0, 0, $c->getHeight(), 0xb4cae8, 0x0056ca), -1, Transparent, 0xffffff);

	# Swap the axis so that the bars are drawn horizontally
	$c->swapXY();

	# Add a multi-color bar chart layer using the supplied data. Use bar gradient # lighting with the light intensity from 0.75 to 2.0
	$barLayerObj = $c->addBarLayer3($dxGraf["data"]);
	$barLayerObj->setBorderColor(Transparent, barLighting(0.75, 2.0));
	$barLayerObj->setAggregateLabelStyle("Arial", 7, TextColor);
	$barLayerObj->setAggregateLabelFormat("{value|2}");

	$c->xAxis->setLabelStyle("Arial", 7, TextColor);
	$c->xAxis->setLabels($dxGraf["labels"]);
	$c->xAxis->setTickColor(Transparent);
	$c->xAxis->setColors(0x0056ca);

	$c->yAxis->setLabelStyle("Arial", 7, TextColor);
	$c->yAxis2->setLabelStyle("Arial", 7, TextColor);
	$c->yAxis->setTitle($labelY, "arialbi.ttf", 8);
	$c->yAxis->setColors(Transparent);
	$c->yAxis2->setColors(Transparent);

	# Show the same scale on the left and right y-axes
	$c->syncYAxis();

	# Adjust the plot area size, such that the bounding box (inclusive of axes) is 30 pixels from the left edge, 8 pixels below the title, 40 pixels from the right # edge, and 10 pixels from the bottom edge.
	$c->packPlotArea(30, $title->getHeight() + 8, $c->getWidth() - 40, $c->getHeight() - 10);

	$tiempo = md5( rand(254696, microtime(true)));
	$ruta_grafica = $CFG->tmpdir."/charDirector_".$tiempo.".png";
	$file = fopen($ruta_grafica, "w");
	if($file)
	{
		fwrite($file, $c->makeChart2(PNG));
		fclose($file);
	}

?>
<table width='98%' align='center'>
	<tr>
		<td>
			<h1 id="tablagrafica"></h1>
			<img src="<?=$CFG->wwwroot?>/tmp/charDirector_<?=$tiempo?>.png" border="0" >
		</td>
	</tr>
</table>
<?
}

function graficaMultiBar($dxGraf, $titulo, $labelY)
{
	global $CFG;

#require_once($CFG->libdir."/ChartDirector/lib/phpchartdir.php");
	require_once("/var/www/html/ChartDirector/lib/phpchartdir.php");

	$c = new XYChart(540, 375);
	$c->addTitle($titulo, "arialbi.ttf", 12);
	//$c->setPlotArea(50, 31, 440, 280, $c->linearGradientColor(60, 40, 60, 280, 0xf9f9ff, 0x6666ff), -1, 0xffffff, 0xffffff);
	$c->setPlotArea(50, 31, 440, 280, 0xffffff, -1, Transparent, 0x7d7d7d);

	# Add a legend box at (50, 28) using horizontal layout. Use 10pts Arial Bold as font, # with transparent background.
	$title = $c->addTitle($titulo, "arialbi.ttf", 12);
	$legendObj = $c->addLegend(50, 10, false, "Arial", 8);
	$legendObj->setBackground(Transparent);

	# Set the x axis labels
	$c->xAxis->setLabels($dxGraf["labels"]);

	$c->xAxis->setLabelStyle("Arial", 7, TextColor, 90);
	$c->yAxis->setLabelStyle("Arial", 7, TextColor);
	$c->xAxis->setColors(0x000000);
	$c->yAxis->setColors(0x000000);
	$c->xAxis->setWidth(1);
	$c->yAxis->setWidth(1);

	# Draw the ticks between label positions (instead of at label positions)
	$c->xAxis->setTickOffset(0.5);

	# Add axis title
	$c->yAxis->setTitle($labelY, "arialbi.ttf", 8);

	# Add a multi-bar layer with 3 data sets
	$layer = $c->addBarLayer2(Side);
	foreach($dxGraf["data"] as $key => $dx)
		$layer->addDataSet($dx, -1, $key);

	# Set bar border to transparent. Use glass lighting effect with light direction from # left.
	$layer->setBorderColor(Transparent, glassEffect(NormalGlare, Left));

	# Configure the bars within a group to touch each others (no gap)
	$layer->setBarGap(0.2, TouchBar);

	$tiempo = md5( rand(254696, microtime(true)));
	$ruta_grafica = $CFG->tmpdir."/charDirector_".$tiempo.".png";
	$file = fopen($ruta_grafica, "w");
	if($file)
	{
		fwrite($file, $c->makeChart2(PNG));
		fclose($file);
	}

?>
<table width='98%' align='center'>
	<tr>
		<td>
			<h1 id="tablagrafica"></h1>
			<img src="<?=$CFG->wwwroot?>/tmp/charDirector_<?=$tiempo?>.png" border="0" >
		</td>
	</tr>
</table>
<?
}


function graficaMultiLine($dxGraf, $titulo, $labelY, $labelUno="", $labelDash="", $labelDos="", $gradosIncl=90)
{
	global $CFG;

	#require_once($CFG->libdir."/ChartDirector/lib/phpchartdir.php");
	require_once("/var/www/html/ChartDirector/lib/phpchartdir.php");

	$c = new XYChart(600, 300);
	$c->addTitle($titulo, "arialbi.ttf", 12);
	//$c->setPlotArea(55, 37, 520, 195, $c->linearGradientColor(60, 40, 60, 280, 0xf9f9ff, 0x6666ff), -1, Transparent, 0x7d7d7d, 0x7d7d7d);
	$c->setPlotArea(55, 37, 520, 195, 0xffffff, -1, Transparent, 0x7d7d7d, 0x7d7d7d);

//$c->setPlotArea(50, 55, 500, 280, $c->linearGradientColor(0, 55, 0, 335, 0xf9fcff, 0xaaccff), -1, Transparent, 0xffffff);
//$c->setPlotArea(55, 58, 520, 195, 0xffffff, -1, -1, 0xcccccc, 0xcccccc);

	$legendObj = $c->addLegend(50, 15, false, "Arial", 9);
	$legendObj->setBackground(Transparent);

	# Add a title to the y axis
	$c->yAxis->setTitle($labelY, "arialbi.ttf", 8);
	$c->yAxis->setLabelStyle("Arial", 7, TextColor);
	$c->yAxis->setColors(0x000000);

	# Set the labels on the x axis.
	$c->xAxis->setLabels($dxGraf["labels"]);
	$c->xAxis->setLabelStyle("Arial", 7, TextColor, $gradosIncl);
	$c->xAxis->setColors(0x000000);

	# Add a line layer to the chart
	$layer = $c->addLineLayer2();
	# Set the default line width to 2 pixels
	$layer->setLineWidth(2);

	# Add the three data sets to the line layer. For demo purpose, we use a dash line # color for the last line
	if(isset($dxGraf["dataDash"]))
		$layer->addDataSet($dxGraf["dataDash"], $c->dashLineColor(0xf48e2b, DashLine), $labelDash);
	$layer->addDataSet($dxGraf["data"], -1,  $labelUno);
	if(isset($dxGraf["dataDos"]))
		$layer->addDataSet($dxGraf["dataDos"], -1,  $labelDos);

	$tiempo = md5( rand(254696, microtime(true)));
	$ruta_grafica = $CFG->tmpdir."/charDirector_".$tiempo.".png";
	$file = fopen($ruta_grafica, "w");
	if($file)
	{
		fwrite($file, $c->makeChart2(PNG));
		fclose($file);
	}

?>
<table width='98%' align='center'>
	<tr>
		<td>
			<h1 id="tablagrafica"></h1>
			<img src="<?=$CFG->wwwroot?>/tmp/charDirector_<?=$tiempo?>.png" border="0" >
		</td>
	</tr>
</table>
<?

}

function graficaIndicadores($dxGraf, $titulo, $bgColor=0xccffcc,$angleDataLabel="")
{
	global $CFG;

#require_once($CFG->libdir."/ChartDirector/lib/phpchartdir.php");
	require_once("/var/www/html/ChartDirector/lib/phpchartdir.php");

	$c = new XYChart(600, 300 );
	$title = $c->addTitle($titulo, "arialbi.ttf", 12);
	$c->setPlotArea(55, 37, 520, 195, $bgColor, -1,  Transparent, Transparent);


	# Set the x axis labels
	$c->xAxis->setLabels($dxGraf["labels"]);

	# Add a legend box at (55, 15) (top of the chart) with horizontal layout. Use 9 pts # Arial Bold font. Set the background and border color to Transparent.
	$legendObj = $c->addLegend(55, 15, false, "arialbd.ttf", 9);
	$legendObj->setBackground(Transparent);

	$c->xAxis->setLabelStyle("Arial", 7, TextColor, 90);
	$c->yAxis->setLabelStyle("Arial", 7, TextColor);
	$c->yAxis->setLabelFormat("{value|1}%");

	$line = $c->addLineLayer2();
	$line->setLineWidth(2);
	$dataSetObj = $line->addDataSet($dxGraf["data"], 0x344b6a);
	$line->setDataLabelFormat("{value|2}%");
	$line->setDataLabelStyle("Arial", 7, TextColor, $angleDataLabel);

	$dataSetObj->setDataSymbol(CircleSymbol, 6, 0x344b6a, 0x344b6a);

	$colors = array(0xecc1c1,  0xffff80 , 0xccffcc);

	$i=0;
	foreach($dxGraf["metas"] as $tit => $dx)
	{
	# Add the three data sets to the area layer, using icons images with labels as data # set names
		$layer = $c->addAreaLayer2();
		$layer->addDataSet($dx,  $colors[$i], $tit);
		$layer->setBorderColor(Transparent);
		$i++;
	}

	$tiempo = md5( rand(254696, microtime(true)));
	$ruta_grafica = $CFG->tmpdir."/charDirector_".$tiempo.".png";
	$file = fopen($ruta_grafica, "w");
	if($file)
	{
		fwrite($file, $c->makeChart2(PNG));
		fclose($file);
	}

?>
<table width='98%' align='center'>
	<tr>
		<td>
			<h1 id="tablagrafica"></h1>
			<img src="<?=$CFG->wwwroot?>/tmp/charDirector_<?=$tiempo?>.png" border="0" >
		</td>
	</tr>
</table>
<?
}

function graficaFactorCargaySobrepeso($dxGraf, $titulo)
{
	global $CFG;

#require_once($CFG->libdir."/ChartDirector/lib/phpchartdir.php");
	require_once("/var/www/html/ChartDirector/lib/phpchartdir.php");

	$c = new XYChart(600, 300 );
	$title = $c->addTitle($titulo, "arialbi.ttf", 12);
	$c->setPlotArea(55, 37, 480, 195, Transparent, -1, Transparent, 0xC0C0C0, 0xC0C0C0);

	# Set the x axis labels
	$c->xAxis->setLabels($dxGraf["labels"]);
	$c->xAxis->setLabelStyle("Arial", 7, TextColor, 90);

	# Add a legend box at (55, 15) (top of the chart) with horizontal layout. Use 9 pts # Arial Bold font. Set the background and border color to Transparent.
	$legendObj = $c->addLegend(55, 15, false, "arialbd.ttf", 9);
	$legendObj->setBackground(Transparent);

	//sobrepeso
	$colorSP = 0x6d403b;
	$c->yAxis->setColors($colorSP);
	$c->yAxis->setLabelStyle("Arial", 7, $colorSP);
	$c->yAxis->setLabelFormat("{value|1}%");
	$c->yAxis->setTitle("Factor Sobrepeso", "Arial", 9, $colorSP);

	$line = $c->addLineLayer2();
	$line->setLineWidth(2);
	$dataSetObj = $line->addDataSet($dxGraf["sobrepeso"], $colorSP);
	$line->setDataLabelFormat("{value|2}%");
	$dataSetObj->setDataSymbol(CircleSymbol, 6, $colorSP, $colorSP);


	//factor de carga
	# Add a multi-color bar layer using the given data.
	$colorCarga = 0x3b536d;
	$barLayer = $c->addBarLayer($dxGraf["carga"],0x3b536d);
	# Set soft lighting for the bars with light direction from the right
	$barLayer->setBorderColor(Transparent, softLighting(Right));
	$barLayer->setUseYAxis2();
	$c->yAxis2->setColors($colorCarga);
	$c->yAxis2->setLabelStyle("Arial", 7, $colorCarga);
	$c->yAxis2->setLabelFormat("{value|1}%");
	$c->yAxis2->setTitle("Factor Carga", "Arial", 9, $colorCarga);

	$tiempo = md5( rand(254696, microtime(true)));
	$ruta_grafica = $CFG->tmpdir."/charDirector_".$tiempo.".png";
	$file = fopen($ruta_grafica, "w");
	if($file)
	{
		fwrite($file, $c->makeChart2(PNG));
		fclose($file);
	}

?>
<table width='98%' align='center'>
	<tr>
		<td>
			<h1 id="tablagrafica"></h1>
			<img src="<?=$CFG->wwwroot?>/tmp/charDirector_<?=$tiempo?>.png" border="0" >
		</td>
	</tr>
</table>
<?
}


function pintarGrafica($string_grafica)
{}

function graficaPieSencilla($dx, $titulo1, $titulo2)
{}

function graficaVariasBarrasXCluster($dxGraf,$dxLabelGraf,$titulo1, $titulo2)
{}


function dejarUnDigitoFecha($fecha)
{
	$anio = strftime("%Y",strtotime($fecha));
	$mes = strftime("%m",strtotime($fecha));
	if($mes < 10) $mes = str_replace("0","",$mes);
	$dia = strftime("%d",strtotime($fecha));

	$hora = strftime("%H",strtotime($fecha));
	if($hora < 10) $hora = str_replace("0","",$hora);
	$minuto = strftime("%M",strtotime($fecha));
	if($minuto == "00") $minuto = 0;
	elseif($minuto < 10) $minuto = str_replace("0","",$minuto);

	return array("anio"=>trim($anio), "mes"=>trim($mes), "dia"=>trim($dia), "hora"=>trim($hora), "minuto"=>trim($minuto));
}

function sacarMeses($fechaInicio, $fechaFinal)
{
	$meses = array();
	while($fechaInicio <= $fechaFinal)
	{
		$meses[strftime("%Y-%m",strtotime($fechaInicio))] = strftime("%Y-%m",strtotime($fechaInicio));
		list($anio,$mes,$dia)=split("-",$fechaInicio);
		$fechaInicio = date("Y-m-d",mktime(0,0,0, $mes,$dia,$anio) + 1 * 24 * 60 * 60);
	}

	return $meses;
}



?>
