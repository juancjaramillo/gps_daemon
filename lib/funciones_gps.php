<?php
//	FUNCIONES
function clean_string($string){
	$new_string="";
	for($i=0;$i<strlen($string);$i++){
		if(ord($string{$i})>=32) $new_string.=$string{$i};
	}
	return($new_string);
}

function calcular_tiempo_gnss($fecha,$hora){
	if(!preg_match("/^([0-9]{2})([0-9]{2})([0-9]{2})\$/",$fecha,$matches)) return(false);
	$time="20" . $matches[3] . "-" . $matches[2] . "-" . $matches[1];
	if(!preg_match("/^([0-9]{2})([0-9]{2})([0-9]{2}[0-9.]*)\$/",$hora,$matches)) return(false);
	$time.=" " . $matches[1] . ":" . $matches[2] . ":" . round($matches[3]);
	return($time);
}

function grados_gnss($origen,$cardinalidad){
	if(!preg_match("/^([0-9]+)([0-9]{2}\.[0-9]+)\$/",$origen,$matches)) return(false);
	$grados=$matches[1];
	$minutos=$matches[2];
//	60				->	1
//	$minutos	->	x
	$grados=$grados+($minutos/60);
	if($cardinalidad=="S" || $cardinalidad=="W") $grados=$grados*(-1);
	return($grados);
}

function interpretar_trama_gnss($origen){
	$array=explode(",",$origen);
	if(sizeof($array)!=10) return(false);
	if($array[2]!="A") return(false);
//	print_r($array);
	$arreglo["rev"]="01";
	$arreglo["evento"]="1";
	if(!$arreglo["tiempo"]=calcular_tiempo_gnss($array[8],$array[1])) return(false);
	if(!$arreglo["latitud"]=grados_gnss($array[3],$array[4])) return(false);
	if(!$arreglo["longitud"]=grados_gnss($array[5],$array[6])) return(false);
	$arreglo["velocidad"]=round($array[7]);
	$arreglo["azimut"]="0";
	$arreglo["id_vehiculo"]=str_replace("\$","",$array[0]);

	return($arreglo);
}

function interpretar_trama_smartcar($origen){

	if(preg_match("/í[íî]/",$origen)){
		//Reemplazar los caracteres de escape: 0xED 0xEE
//		echo "************************* TRAMA RARA **************************" . chr(7) . "\n";
		$origen=preg_replace("/í([íî])/","\\1",$origen);
	}
	$origenHex=bin2hex($origen);
	if(strlen($origen)!=25){
		echo "------	TAMAÑO RARO	(" . strlen($origen) . ")	----------" . chr(7) . "\n";
		print_r($origenHex);
		echo "\n";
		for($i=0;$i<strlen($origen);$i++){
			echo "$i\t" . bin2hex($origen{$i}) . "\t";
			echo hex2bin(bin2hex($origen{$i})) . "\t";
			echo hexdec(bin2hex($origen{$i})) . "\n";
		}
	}

	$arreglo=interpretar_trama_smartcar_hex($origenHex);
	return($arreglo);
}

function interpretar_trama_16_smartcar($origen){
	GLOBAL $CFG;
	
	if(strlen($origen)!=32) return(FALSE);
	echo "TRAMA\n";
	echo $origen . "\n";

	$arreglo=array();
	$arreglo["evento"]=hexdec(substr($origen,0,2));
	$fecha_hora_hex=substr($origen,2,6);
	$fecha_hora_binary=hex2bin($fecha_hora_hex);
	$segundos_binary=substr($fecha_hora_binary,0,4);
	$minutos_binary=substr($fecha_hora_binary,4,6);
	$horas_binary=substr($fecha_hora_binary,10,5);
	$dia_binary=substr($fecha_hora_binary,15,5);
	$mes_binary=substr($fecha_hora_binary,20,4);
	
	$segundos_dec=str_pad(round(bindec($segundos_binary) / 2),2,"0",STR_PAD_LEFT);
	
	$minutos_dec=str_pad(bindec($minutos_binary),2,"0",STR_PAD_LEFT);
	
	$horas_dec=str_pad(bindec($horas_binary),2,"0",STR_PAD_LEFT);
	
	$dia_dec=str_pad(bindec($dia_binary),2,"0",STR_PAD_LEFT);
	
	$mes_dec=str_pad(bindec($mes_binary),2,"0",STR_PAD_LEFT);

	$tiempo_servidor=time();
	$fecha_gps=$mes_dec . "-" . $dia_dec . " " . $horas_dec . ":" . $minutos_dec . ":" . $segundos_dec;
	$ano_servidor=date("Y");
	$tiempo_gps=strtotime($ano_servidor . "-" . $fecha_gps);
	$diferencia=$tiempo_servidor-$tiempo_gps;
	if($diferencia>(3600*24*30)) $ano_gps=$ano_servidor-1;	//Diferencia de más de un mes
	if($diferencia<(3600*24*30*-1)) $ano_gps=$ano_servidor+1;
	else $ano_gps=$ano_servidor;
	$arreglo["tiempo"]=$ano_gps . "-" . $fecha_gps;
	$arreglo=array_merge($arreglo,interpretar_coordenadas_smartcar(substr($origen,8,15)));
	$arreglo["outputs"]=substr($origen,24,1);
	$arreglo["inputs"]=substr($origen,25,1);
	$arreglo=array_merge($arreglo,interpretar_velocidad_curso_smartcar(substr($origen,26,6)));

	if(preg_match("/^[0-9]{4}-00-/",$arreglo["tiempo"])){

		$arreglo["tiempo"]=date("Y-m-d H:i:s",mktime(date("H")+$CFG->gmtoffset,date("i"),date("s"),date("m"),date("d"),date("Y")));
	}
	return($arreglo);
}

function interpretar_trama_smartcar_hex($origenHex){
	GLOBAL $CFG;

	$arreglo=array();
	$arreglo["id_vehiculo"]=round(substr($origenHex,6,10));
	$i=16;
	$sizeoftrama=32;
	$arreglo["numRegs"]=0;
	while($i+$sizeoftrama<=strlen($origenHex)){
		if(substr($origenHex,$i,4)=="eeee"){

			$i=$i+18;
		}

		$array1=interpretar_trama_16_smartcar(substr($origenHex,$i,$sizeoftrama));
		$i=$i+$sizeoftrama;
		if($arreglo["numRegs"]==0) $arreglo=array_merge($arreglo,$array1);
		else{
			if(!isset($arreglo["regsAdicionales"])) $arreglo["regsAdicionales"]=array();
			array_push($arreglo["regsAdicionales"],$array1);
		}
		$arreglo["numRegs"]++;
	}

	return($arreglo);
}

function interpretar_velocidad_curso_smartcar($hexInput){
//	echo $hexInput . "\n";
	$binInput=hex2bin($hexInput);
	$velocidad_metros=str_pad(sacar_de_bin_to_dec($binInput,0,7),2,"0",STR_PAD_LEFT);
	$velocidad_centesimas_de_metro=str_pad(sacar_de_bin_to_dec($binInput,7,7),2,"0",STR_PAD_LEFT);
	$vel=$velocidad_metros . "." . $velocidad_centesimas_de_metro;
	$arreglo["velocidad"]=round($vel/1000*3600);
//	Así estaba antes:
//	$arreglo["azimut"]=str_pad(sacar_de_bin_to_dec($binInput,14,11),2,"0",STR_PAD_LEFT);
	$arreglo["azimut"]=str_pad(sacar_de_bin_to_dec($binInput,15,9),2,"0",STR_PAD_LEFT);
	echo "hexinput: " . $hexInput . "\n";
	echo "Azimut: " . $arreglo["azimut"] . "\n";
	if($arreglo["azimut"]>360){
		echo "AZIMUT MAYOR QUE 360\n";
		echo $hexInput . "\n";
		echo $binInput . "\n";
		echo "14-11\n";
		echo substr($binInput,14,11) . "\n";
		echo bindec(substr($binInput,14,11)) . "\n";

	}
	return($arreglo);
}

function geo2smartcar($long,$lat){
	$grados=abs($long);
	if($grados!=$long) $card="W";
	else $card="E";
	$grados_cerrados=floor($grados);
	$minutos=($grados-$grados_cerrados)*60;
	$nueva=$grados_cerrados*100 + $minutos;
	$int=str_pad(floor($nueva),5,"0",STR_PAD_LEFT);
	$nueva_formateada=$int . "." . round((($nueva-floor($nueva)) * pow(10,4))) . "," . $card;
	print_r($nueva_formateada . "\n");
	$grados=abs($lat);
	if($grados!=$lat) $card="S";
	else $card="N";
	$grados_cerrados=floor($grados);
	$minutos=($grados-$grados_cerrados)*60;
	$nueva=$grados_cerrados*100 + $minutos;
	$int=str_pad(floor($nueva),4,"0",STR_PAD_LEFT);

	$nueva_formateada=$int . "." . round((($nueva-floor($nueva)) * pow(10,4))) . "," . $card;
	print_r($nueva_formateada . "\n");
}

function interpretar_coordenadas_smartcar($hexInput){

	$binInput=hex2bin($hexInput);

	$signo_lat_bin=substr($binInput,0,1);
	$grados_lat=str_pad(sacar_de_bin_to_dec($binInput,1,7),2,"0",STR_PAD_LEFT);
	$minutos_lat=str_pad(sacar_de_bin_to_dec($binInput,8,6),2,"0",STR_PAD_LEFT);
	$xx_lat=str_pad(sacar_de_bin_to_dec($binInput,14,7),2,"0",STR_PAD_LEFT);
	$yy_lat=str_pad(sacar_de_bin_to_dec($binInput,21,7),2,"0",STR_PAD_LEFT);
	$minutos=$minutos_lat . "." . $xx_lat . $yy_lat;
	$lat=$grados_lat+($minutos/60);
	if($signo_lat_bin=="1") $lat=$lat*-1;
	$arreglo["latitud"]=$lat;
	
	$gg_bin=substr($binInput,28,2);
	switch($gg_bin){
		case "00":
			$gg="Sin antena";
			break;
		case "01":
			$gg="Sin pos";
			break;
		case "10":
			$gg="2D";
			break;
		case "11":
			$gg="3D";
			break;
	}
	$arreglo["GG"]=$gg;
	$C_bin=substr($binInput,30,1);
	if($C_bin=="1") $C="Carro prendido";
	else $C="Carro apagado";
	$arreglo["C"]=$C;

	$signo_long_bin=substr($binInput,31,1);
	$grados_long=str_pad(sacar_de_bin_to_dec($binInput,32,8),2,"0",STR_PAD_LEFT);
	$minutos_long=str_pad(sacar_de_bin_to_dec($binInput,40,6),2,"0",STR_PAD_LEFT);
	$xx_long=str_pad(sacar_de_bin_to_dec($binInput,46,7),2,"0",STR_PAD_LEFT);
	$yy_long=str_pad(sacar_de_bin_to_dec($binInput,53,7),2,"0",STR_PAD_LEFT);
	$minutos=$minutos_long . "." . $xx_long . $yy_long;
	$long=$grados_long+($minutos/60);
	if($signo_long_bin=="1") $long=$long*-1;
	$arreglo["longitud"]=$long;
	return($arreglo);
}

function sacar_de_bin_to_dec($input,$from,$width){
	$var_bin=substr($input,$from,$width);
	$var_dec=bindec($var_bin);
	return($var_dec);
}


// por cambio a version php 5.6
if (!function_exists('hex2bin')) 
{
    function hex2bin($hex) {
    if (strlen($hex) % 2)
        $hex = "0".$hex;
    $bin = '';
    for ($i = 0; $i < strlen($hex); $i += 2) { 
        $bin .= chr(hexdec(substr($hex, $i, 2))); 
    }

       return $bin; 
    } 
}

function interpretar_trama($origen){
	if(bin2hex($origen{0})=="ee") return interpretar_trama_smartcar($origen);
	if(preg_match("/^\\\$[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+,[^,]+\$/",$origen)) return interpretar_trama_gnss($origen);
	if(preg_match("/^.*[$]?GPRMC,.*,.*,.*,.*,.*,.*,.*,.*,.*,.*,.*$/m",$origen)) return(interpretar_trama_gprmc($origen));
	if(strlen($origen)!=54 && strlen($origen)!=52) return (false);
	$arreglo=array();
	$arreglo["rev"]=substr($origen,0,4);
	$arreglo["evento"]=substr($origen,4,2);
	$arreglo["tiempo"]=calcular_tiempo(substr($origen,6,10));
	$arreglo["latitud"]=substr($origen,16,8)/100000;
	$arreglo["longitud"]=substr($origen,24,9)/100000;
	$arreglo["velocidad"]=round(substr($origen,33,3)*1.609344);
	$arreglo["azimut"]=substr($origen,36,3);
	$arreglo["data_source"]=substr($origen,39,1);
	$arreglo["age_of_data"]=substr($origen,40,1);
	if(preg_match("/[a-z][a-z]/i",substr($origen,45,2))) $arreglo["id_vehiculo"]=trim(substr($origen,47,4));
	else $arreglo["id_vehiculo"]=trim(substr($origen,45,4));
	return($arreglo);
}

function interpretar_trama_sp4603($origen,$imei){

  $trama = explode(',',$origen);
  $SYS = explode(';',$trama[2]);
  $GPS = explode(';',$trama[3]);
	$arreglo=array();
	$arreglo["rev"]="REV";
	$arreglo["evento"] = ($trama[1] == '') ? 0 : $trama[1];
	$arreglo["tiempo"]=calcular_tiempo_sp(substr($trama[0],6,12),substr($trama[0],0,6));
	$arreglo["satelites"]=$GPS[1];
	$arreglo["latitud"]= (strstr($GPS[2],'N') === FALSE) ? str_replace('S','-',$GPS[2]) : str_replace('N','',$GPS[2]);
	$arreglo["longitud"]= (strstr($GPS[3],'E') === FALSE)? str_replace('W','-',$GPS[3]) : str_replace('E','',$GPS[3]);
	$arreglo["velocidad"]= $GPS[4];
	$arreglo["azimut"]= $GPS[5];
	$arreglo["imei"]= $imei;
	$arreglo["data_source"]= '';
	$arreglo["age_of_data"]= '';
	$arreglo["id_vehiculo"]=(substr($SYS[0],4,5));


	return($arreglo);
}

function interpretar_trama_spF8($origen,$imei,$id_vehi){
 
  $trama = explode(';',$origen);
  $division=array();
  $porcion0 = explode('=', $trama[0]);
  $porcion1 = explode('=', $trama[1]);
  $porcion2 = explode('=', $trama[2]);
  $porcion3 = explode('=', $trama[3]);
  $porcion4 = explode(',', $trama[6]);
  
  $division[0]=$porcion0[1];
 // substr($array[2],6,15);
  $division[1]=substr($porcion1[1],0,4);
  $division[2]=$porcion2[1];
  $division[3]=substr($porcion3[1],0,4);
  $division[4]=explode('%', $porcion4[0]);
  $division[5]=$porcion4[1];
  $division[6]=$porcion4[2];
 
	$arreglo=array(); 	
	$arreglo["id_vehi"]= $id_vehi;	
	$arreglo["temperatura_sonda1"]= hexdec($division[0]);
	$arreglo["nivel_combustible_sonda1"] = hexdec($division[1]);
	$arreglo["temperatura_sonda2"] = hexdec($division[2]);
	$arreglo["nivel_combustible_sonda2"] = hexdec($division[3]);
	$arreglo["sensor_peso"] = $division[4][1];	
	$arreglo["milivoltiossensor"] = $division[5];
	$arreglo["toneladas_equivalente"] = $division[6];
	$arreglo["imei"]= $imei;

	return($arreglo);
}



function interpretar_trama_comb_IMEI($origen,$imei,$id_vehi){  
 
 
 
 
  $trama = explode(' ',$origen); 
  
  $division=array();
  $porcion0 = explode('=', $trama[0]);
  $porcion1 = explode('=', $trama[1]);
  $porcion2 = explode('=', $trama[2]);
  

  $division[0]=$porcion1[1];
  $division[1]=$porcion2[1];  
 
	$arreglo=array(); 
	$arreglo["temperatura_sonda1"]= ($division[0]);
	$arreglo["nivel_combustible_sonda1"] = ($division[1]);	
	$arreglo["imei"]= $imei;
	$arreglo["id_vehi"]= $id_vehi;

	return($arreglo);
}


function insertar_registro_gps_comb_IMEI($arreglo){
	  echo "-----------ARREGLO COMBUSTIBLE -----------";
	  print_r($arreglo);
	GLOBAL $CFG, $db;	
	
	$validar=$arreglo['id_vehi'];
	$posicion_coincidencia = strpos($validar, '???');
	if ($posicion_coincidencia === false) {
		$id_vehi=$arreglo['id_vehi'];	
	}else{
		$id_vehi=0;	
	}
	
		
			$nivel_galon=explode('.',$arreglo['nivel_combustible_sonda1']); 
			$galones=((0.0159)*hexdec($nivel_galon[0])) - (5.7596);
	
		
	$temperatura_sonda2=0;
	$nivel_combustible_sonda2=0;
	$sensor_peso=0;
	$milivoltiossensor=0;
	$kilos=0;
	$toneladas_equivalente=0;
	
			$fecha_proceso= date("Y-m-d H:i:s");
			$sql= "INSERT INTO sensores (id_vehi, temperatura_sonda1, temperatura_sonda2, nivel_combustible_sonda1, nivel_combustible_sonda2, sensor_peso,imei,tiempo,milivoltiossensor,galones,pesokilos,toneladas_equivalente,combustible,ton_equivalente,peso,id_ciudad)
			VALUES('".$id_vehi."','".$arreglo['temperatura_sonda1']."','".$temperatura_sonda2."','".$arreglo['nivel_combustible_sonda1']."','".$nivel_combustible_sonda2."','".$sensor_peso."','".$arreglo['imei']."','".$fecha_proceso."','".$milivoltiossensor."','".$galones."','".$kilos."','".$toneladas_equivalente."','".$galones."','".$toneladas_equivalente."','".$kilos."',2)";
			$qid=$db->sql_query($sql);		
	
}







function insertar_registro_gps_peso_IMEI($arreglo){
	
	GLOBAL $CFG, $db;	
	$id_vehi=$arreglo[4];	


	$milivoltiossensor=$arreglo[1];
	$temperatura_sonda1=0;
	$temperatura_sonda2=0;
	$sensor_peso=0;
		
	
		
		$kilos=   (242522.5685*$milivoltiossensor)+ (-50125.2568);
			
	
			$fecha_proceso= date("Y-m-d H:i:s");
			$sql= "INSERT INTO sensorpeso (id_vehi, temperatura_sonda1, temperatura_sonda2, sensor_peso,imei,tiempo,milivoltiossensor,pesokilos,id_ciudad)
			VALUES('".$id_vehi."','".$temperatura_sonda1."','".$temperatura_sonda2."','".$sensor_peso."','".$arreglo[0]."','".$fecha_proceso."','".$milivoltiossensor."','".$kilos."',2)";
			$qid=$db->sql_query($sql);	
		
	
}

function interpretar_trama_peso_IMEI($origen,$imei){  
 
  $trama = explode(' ',$origen); 
  
  $division=array();
  $porcion0 = explode('=', $trama[0]);
  $porcion1 = explode('=', $trama[1]);
  $porcion2 = explode('=', $trama[2]);
  

  $division[0]=$porcion1[1];
  $division[1]=$porcion2[1];  
 
	$arreglo=array(); 
	$arreglo["temperatura_sonda1"]= ($division[0]);
	$arreglo["nivel_combustible_sonda1"] = ($division[1]);	
	$arreglo["imei"]= $imei;

	return($arreglo);
}

function Hex2String($hex){
    $string='';
    for ($i=0; $i < strlen($hex)-1; $i+=2){
        $string .= chr(hexdec($hex[$i].$hex[$i+1]));
    }
    return $string;
}

function bchexdec($hex)
{
    $dec = 0;
    $len = strlen($hex);
    for ($i = 1; $i <= $len; $i++) {
        $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
    }
    return $dec;
}

function interpretar_trama_sp4600($origen,$imei){

  $trama = explode(',',$origen);
  $SYS = explode(';',$trama[2]);
  $GPS = explode(';',$trama[3]);

	$arreglo=array();
	$arreglo["rev"]="REV";
	$arreglo["evento"] = ($trama[1] == '') ? 0 : $trama[1];
	$arreglo["tiempo"]=calcular_tiempo_sp(substr($trama[0],6,12),substr($trama[0],0,6));
	$arreglo["satelites"]=$GPS[1];
	$arreglo["latitud"]= (strstr($GPS[2],'N') === FALSE) ? str_replace('S','-',$GPS[2]) : str_replace('N','',$GPS[2]);
	$arreglo["longitud"]= (strstr($GPS[3],'E') === FALSE)? str_replace('W','-',$GPS[3]) : str_replace('E','',$GPS[3]);
	$arreglo["velocidad"]= $GPS[4];
	$arreglo["azimut"]= $GPS[5];
	$arreglo["imei"]= $imei;
	$arreglo["data_source"]= '';
	$arreglo["age_of_data"]= '';
	$arreglo["id_vehiculo"]=(substr($SYS[0],4,6));
	return($arreglo);
}

function interpretar_trama_peso($origen,$imei){

  $trama = explode(',',$origen);
  $SYS = explode(';',$trama[2]);
  $GPS = explode(';',$trama[3]);
	$arreglo=array();
	$arreglo["rev"]="REV";
	$arreglo["evento"] = ($trama[1] == '') ? 0 : $trama[1];
	$arreglo["tiempo"]=calcular_tiempo_sp(substr($trama[0],6,12),substr($trama[0],0,6));
	$arreglo["satelites"]=$GPS[1];
	$arreglo["latitud"]= (strstr($GPS[2],'N') === FALSE) ? str_replace('S','-',$GPS[2]) : str_replace('N','',$GPS[2]);
	$arreglo["longitud"]= (strstr($GPS[3],'E') === FALSE)? str_replace('W','-',$GPS[3]) : str_replace('E','',$GPS[3]);
	$arreglo["velocidad"]= $GPS[4];
	$arreglo["azimut"]= $GPS[5];
	$arreglo["imei"]= $imei;
	$arreglo["data_source"]= '';
	$arreglo["age_of_data"]= '';
	$arreglo["id_vehiculo"]=(substr($SYS[0],4,6));
	return($arreglo);
}



function calcular_tiempo_sp($fecha,$hora){
	if(!preg_match("/^([0-9]{2})([0-9]{2})([0-9]{2})\$/",$fecha,$matches)) return(false);
	$time="20" . $matches[3] . "-" . $matches[2] . "-" . $matches[1];
	if(!preg_match("/^([0-9]{2})([0-9]{2})([0-9]{2})\$/",$hora,$matches)) return(false);
	$time.=" " . $matches[1] . ":" . $matches[2] . ":" . round($matches[3]);
	return($time);
}

function interpretar_trama_ack($origen){

	$arreglo=array();
	return($arreglo);

}

function interpretar_trama_udp($origen){
	
	if(preg_match("/^.*\\\$GPRMC,.*,.*,.*,.*,.*,.*,.*,.*,.*,.*,.*$/m",$origen)) return(interpretar_trama_gprmc($origen));
	elseif(strlen($origen)==43) return(interpretar_trama_taip($origen));
	elseif(strlen($origen)==37) return(interpretar_trama_ack($origen));
	elseif(preg_match("/^[0-9]* *[A-Z]{2}[0-9]+$/",$origen)) return(interpretarTramaSinGPS($origen));
	else return(false);
}

function interpretarTramaSinGPS($origen){
	GLOBAL $CFG;

	if(!preg_match("/^([0-9]*) *[A-Z]{2}([0-9]*)$/",$origen,$matches)) return(FALSE);
	$arreglo=array();
	$arreglo["rev"]="REV>";
	$arreglo["evento"]="2";

	if($matches[1]=="") $arreglo["C"]="Carro apagado";
	else $arreglo["evento"]=$matches[1];

	$arreglo["id_vehiculo"]=$matches[2];

	$arreglo["status"]="V";
	$myDateGmt=mktime(date("H")+$CFG->gmtoffset,date("i"),date("s"),date("m"),date("d"),date("Y"));
	$myDate=date("Y-m-d H:i:s",$myDateGmt);
	$arreglo["tiempo"]=$myDate;
	$arreglo["latitud"]="";
	$arreglo["longitud"]="";
	$arreglo["azimut"]=0;
	$arreglo["velocidad"]=0;
	return($arreglo);
}

function interpretar_trama_taip($origen){
	$arreglo=array();
	$arreglo["evento"]="1";
	$arreglo["tiempo"]=calcular_tiempo_corto(substr($origen,8,5));
	$arreglo["latitud"]=substr($origen,13,8)/100000;
	$arreglo["longitud"]=substr($origen,21,9)/100000;
	$arreglo["status"]=substr($origen,37,1);
	if($arreglo["status"]==0) $arreglo["status"]="V";
	$arreglo["velocidad"]=substr($origen,30,3);
	$arreglo["azimut"]=substr($origen,33,3);
	$arreglo["data_source"]=substr($origen,36,1);
	$arreglo["age_of_data"]=substr($origen,37,1);
	$arreglo["id_vehiculo"]=1113;

	return($arreglo);
}

function interpretar_trama_gprmc($origen){
	GLOBAL $CFG;
	if(!preg_match("/^(.*)\\\$GPRMC,(.*),(.*),(.*),(.*),(.*),(.*),(.*),(.*),(.*),(.*),(.*),(.*)$/m",$origen,$matches))
		if(!preg_match("/^(.*)\\\$GPRMC,(.*),(.*),(.*),(.*),(.*),(.*),(.*),(.*),(.*),(.*),(.*)$/m",$origen,$matches)) return(false);

	$arreglo=array();
	$arreglo["rev"]="REV>";
	if(preg_match("/^ *([0-9]+) *([^ ]+) *$/",$matches[1],$resultados)){
		$arreglo["evento"]=$resultados[1];
		$matches[1]=$resultados[2];
	}
	else{
		$arreglo["evento"]="99";
		$arreglo["C"]="Carro prendido";
	}
	$arreglo["id_vehiculo"]=trim(preg_replace("/([A-Z]+)/","",$matches[1]));
	if(!is_numeric($arreglo["id_vehiculo"])){
		error_log("id_vehiculo:" . $arreglo["id_vehiculo"]);
		echo "id_vehiculo:" . $arreglo["id_vehiculo"] . "\n";
		$placa=trim($matches[1]);

		$qid=db_query("SELECT * FROM vehiculos WHERE placa = '" . $placa . "'");
		if($equipo=db_fetch_array($qid)){
			$arreglo["id_vehiculo"]=$equipo["idgps"];
		}
		else{
			$arreglo["id_vehiculo"]=0;
		}
	}
	$hora=str_pad(round($matches[2]),6,"0",STR_PAD_LEFT);
	$arreglo["status"]=$matches[3];
	$latitud=$matches[4];
	$norte_sur=$matches[5];
	$longitud=$matches[6];
	$este_oeste=$matches[7];
	$arreglo["velocidad"]=round($matches[8]*1.85185);
	$arreglo["azimut"]=round($matches[9]);
	$fecha=$matches[10];
	$year="20" . substr($fecha,4,2);
	$month=substr($fecha,2,2);
	$day=substr($fecha,0,2);
	$hours=substr($hora,0,2);
	$minutes=substr($hora,2,2);
	$seconds=substr($hora,4,2);
	if(
		!is_numeric($year) ||
		!is_numeric($month) ||
		!is_numeric($day) ||
		!is_numeric($hours) ||
		!is_numeric($minutes) ||
		!is_numeric($seconds)
	){
		$arreglo["status"]="V";
		if(!is_numeric($hours) || !is_numeric($minutes) || !is_numeric($seconds)) $arreglo["tiempo"]="";
		else{
			$myDateGmt=mktime(date("H")+$CFG->gmtoffset,date("i"),date("s"),date("m"),date("d"),date("Y"));
			$myDate=date("Y-m-d",$myDateGmt);
			$arreglo["tiempo"]=$myDate . " $hours:$minutes:$seconds";
		}
		$arreglo["latitud"]="";
		$arreglo["longitud"]="";
		$arreglo["velocidad"]="";
	
		return($arreglo);
	}
	$arreglo["tiempo"]="$year-$month-$day $hours:$minutes:$seconds";
	
	$grados=substr($latitud,0,2);
	$minutos=substr($latitud,2,7);
	$arreglo["latitud"]=$grados+$minutos/60;
	if($norte_sur=="S") $arreglo["latitud"]=$arreglo["latitud"]*(-1);
	
	$grados=substr($longitud,0,3);
	$minutos=substr($longitud,3,7);
	$arreglo["longitud"]=$grados+$minutos/60;
	if($este_oeste=="W") $arreglo["longitud"]=$arreglo["longitud"]*(-1);
	return($arreglo);
}

function calcular_tiempo_corto($tiempo){
GLOBAL $CFG;

	$ahora_gmt=mktime(date("H")+$CFG->gmtoffset,date("i"),date("s"),date("m"),date("d"),date("Y"));
	$dia_gmt=date("d",$ahora_gmt);
	$mes_gmt=date("m",$ahora_gmt);
	$ano_gmt=date("Y",$ahora_gmt);
	$fecha=date("Y-m-d H:i:s",mktime(0,0,$tiempo,$mes_gmt,$dia_gmt,$ano_gmt));
	return($fecha);
}

function calcular_tiempo($tiempo){
	$semana=substr($tiempo,0,4);
	$dia=substr($tiempo,4,1);
	$segundos=substr($tiempo,5,5);
	$hora_local=0;

	$time=mktime(0+$hora_local,0,0+$segundos,1,6+($semana*7)+$dia,1980);
	$fecha_corregida=date("Y-m-d H:i:s",$time);
	return($fecha_corregida);
}

function insertar_mensaje($idgps,$mensaje){
	$mensaje=trim($mensaje);
	$fecha=date("Y-m-d H:i:s",mktime(date("H")+5,date("i"),date("s"),date("m"),date("d"),date("Y")));
	$qid=db_query("INSERT INTO eventos(id_gps,fecha,mensaje) VALUES ('$idgps','$fecha','$mensaje')");
	$qid=db_query("UPDATE vehiculos SET estado='$mensaje' WHERE idgps = '$idgps'");
}

function geocode($longitud,$latitud){
	GLOBAL $CFG;

	$db_geo=new sql_db_postgres($CFG->dbhost_geo_postgres,$CFG->dbuser_geo_postgres,$CFG->dbpass_geo_postgres,$CFG->dbname_geo_postgres);
	if(trim($longitud)=="" || trim($latitud)=="") return("??/--");
	$offset=0.0025;
	$xmin=$longitud-$offset;
	$ymin=$latitud-$offset;
	$xmax=$longitud+$offset;
	$ymax=$latitud+$offset;
	$geom="GeometryFromText('POINT($longitud $latitud)',4326)";
	$geomCiudad="GeometryFromText('POINT($longitud $latitud)',1)";

	$qlocalizacion=$db_geo->sql_query("
		SELECT distance($geom,the_geom),tipo_via,nomvial,g_nv,g_ln, g_card FROM sig_gr_entidades
		WHERE the_geom && SetSRID('BOX3D($xmin $ymin, $xmax $ymax)'::box3d,4326)
		ORDER BY distance($geom,the_geom) LIMIT 1
	");
	if($localizacion=$db_geo->sql_fetchrow($qlocalizacion)){
		if(in_array($localizacion["tipo_via"],array(1,3,5)) && $localizacion["g_card"]==1) $localizacion["g_card"]=" E"; //Si es una calle
		elseif(in_array($localizacion["tipo_via"],array(2,4,6)) && $localizacion["g_card"]==1) $localizacion["g_card"]=" S"; //Si es una carrera
		else $localizacion["g_card"]="";
		$posicion=$localizacion["nomvial"] . " - " . $localizacion["g_nv"] . $localizacion["g_ln"] . $localizacion["g_card"];
	}
	else $posicion="--";
//AVERIGUAR LA CIUDAD:

	$ciudad="";
	$db_geo->sql_close();
	$posicion=$ciudad . "/" . $posicion;
	return($posicion);

}

function insertar_registro_gps_4603($arreglo){
	GLOBAL $CFG, $db;
   $arreglo['id_vehi'] = 0;
	$qid=$db->sql_query("SELECT v.*,(SELECT id FROM mtto.equipos WHERE id_vehiculo=v.id LIMIT 1) as id_equipo FROM vehiculos v WHERE v.idgps = '" . $arreglo["id_vehiculo"] . "'");
	if(!$equipo=$db->sql_fetchrow($qid)) error_log("No tiene equipo asociado :: " . $arreglo["id_vehiculo"]);
  else{
    $arreglo['id_vehi'] = $equipo["id"];
  }
	
	$entrarInsertar = horarios_laborables($arreglo);
	if($entrarInsertar){
		$geometry="ST_GeomFromText('POINT($arreglo[longitud] $arreglo[latitud])',4326)";
		$geometry_vehi="ST_GeomFromText('POINT($arreglo[longitud] $arreglo[latitud])',4326)";
		if($arreglo["velocidad"]>150 || $arreglo["azimut"]>360){
			error_log("Registro invádo: " . print_r("Tiempo->".$arreglo['tiempo']." vehiculo->".$arreglo['id_vehiculo']." longitud->".$arreglo['longitud']." latitud->".$arreglo['latitud']." \n",true));
			return;
		}
		if((nvl($arreglo["status"])=="V" || nvl($arreglo["GG"])=="Sin antena" || nvl($arreglo["GG"])=="Sin pos")){
		//Si no es una casa y viene sin posicióe GPS
			$geometry="NULL";
			$geometry_vehi="NULL";
			$velocidad="NULL";
		}
		else{
			$velocidad="'" . $arreglo["velocidad"] . "'";
		}
		
		//Verificar validez del registro..
		if((nvl($arreglo["status"])!="V" && nvl($arreglo["GG"])!="Sin antena" && nvl($arreglo["GG"])!="Sin pos")){
			$diferencia_horas=abs(strtotime($arreglo["tiempo"])-time())/60/60;
			if($diferencia_horas>24){
			error_log("Registro invádo: " . print_r("Tiempo->".$arreglo['tiempo']." vehiculo->".$arreglo['id_vehiculo']." longitud->".$arreglo['longitud']." latitud->".$arreglo['latitud']." \n",true));
				return;
			}
		}
		if((nvl($arreglo["status"])=="V" || nvl($arreglo["GG"])=="Sin antena" || nvl($arreglo["GG"])=="Sin pos")){
		//Si viene sin posicióe GPS
			$pos="CASE WHEN hrposition ~ 'NO GPS' THEN hrposition ELSE hrposition || ' (NO GPS)' END";
			$pos_gps_vehi="NULL";
			$geometry="the_geom";
			$geometry_vehi="NULL";
			$km_virtual="km_virtual";
			$diff_km="0";
		}
		else{
			$pos="'" . reverse_geocode($arreglo["longitud"],$arreglo["latitud"]) . "'";
			$pos_gps_vehi=$pos;
			$posicion_anterior=$equipo["the_geom"];
			$posicion_nueva=$geometry_vehi;
			if($posicion_anterior==""){
				$km_virtual="0";
				$diff_km="0";
			}   
			else{
					$km_virtual="km_virtual + (SELECT ST_Distance('$posicion_anterior' , $posicion_nueva ,FALSE))/1000";
					$diff_km="(SELECT ST_Distance('$posicion_anterior' , $posicion_nueva ,FALSE))/1000";
			}
			//error_log($km_virtual);
		}
		if($arreglo["evento"]==14 || $arreglo["evento"]==15) $id_estado_motor="'$arreglo[evento]'";//Prendido o apagado
		else $id_estado_motor="id_estado_motor";

		if($equipo["id_estado_motor"]==14){//Si estárendido
			$diff=(time()-strtotime($equipo["tiempo"]))/3600;
			$horometro_virtual="horometro_virtual+'$diff'";
		}
		else{
			$diff="0";
			$horometro_virtual="horometro_virtual";
		}
	
		//echo var_dump($arreglo);
		$sql = "INSERT INTO gps_vehi (id_vehi,tiempo,rumbo,velocidad,evento,gps_geom,hrposition,imei)
			VALUES('$arreglo[id_vehi]', timestamp '$arreglo[tiempo]' -interval '5 hour',$arreglo[azimut],$velocidad,'$arreglo[evento]',$geometry_vehi,$pos_gps_vehi,'$arreglo[imei]')";
		
		$qid=$db->sql_query($sql);
		if($arreglo["velocidad"]=="") $velocidad="NULL";
		else $velocidad="'" . $arreglo["velocidad"] . "'";
		$strSQL="
			UPDATE vehiculos SET
				tiempo='" . date("Y-m-d H:i:s") . "',
				hrposition=$pos,
				velocidad=$velocidad,
				the_geom=$geometry,
				km_virtual=($km_virtual),
				kilometraje=($km_virtual),
				id_estado_motor=$id_estado_motor,
				horometro_virtual=$horometro_virtual,
				horometro=$horometro_virtual
			WHERE idgps = '" . $arreglo["id_vehiculo"] . "'
		";
		$strSQL="
			UPDATE vehiculos SET
				tiempo = timestamp '$arreglo[tiempo]' -interval '5 hour',
				hrposition = $pos,
				velocidad = $velocidad,
				the_geom = $geometry,
				km_virtual = ($km_virtual),
				id_estado_motor = $id_estado_motor,
				horometro_virtual = $horometro_virtual
			WHERE idgps = '" . $arreglo["id_vehiculo"] . "'
		";
		$qupdate=$db->sql_query($strSQL);

		//HISTÓICO RECORRIDOS:
		if($equipo["id_equipo"] == ""){
			error_log("No tiene equipo asociado :: " . $arreglo["id_vehiculo"]);
			//file_put_contents('../error.log',"No tiene equipo asociado :: " . $arreglo["id_vehiculo"] . "\n",FILE_APPEND);
		}
		elseif($result=$db->sql_row("SELECT * FROM historico_recorrido WHERE id_equipo='$equipo[id_equipo]' AND fecha='" . date("Y-m-d") . "'")){
			//Ya existe, hay que actualizarlo
			$strSQL="
				UPDATE historico_recorrido SET 
					km=km+$diff_km,
					horas=horas+'$diff'
				WHERE id='$result[id]'
			";
			$qid=$db->sql_query($strSQL);
		}
		else{
			//No existe, toca crearlo
			$qid=$db->sql_query("
				INSERT INTO historico_recorrido(id_equipo,fecha,km,horas)
				VALUES('$equipo[id_equipo]','" . date("Y-m-d") . "',$diff_km,$diff)
			");
		}
			
		
		
	}
	else{
		preguntarLogMtto($arreglo);
	}
	return $qid;
}

function insertar_registro_gps_F8($arreglo){
	
	GLOBAL $CFG, $db;
	$validar=$arreglo['id_vehi'];
	$posicion_coincidencia = strpos($validar, '???');

		$galones=((0.1056653608175478)*pow($arreglo['nivel_combustible_sonda1'],0))+			
		((0.013749817995208184)*pow($arreglo['nivel_combustible_sonda1'],1))+			
		((0.00021172799407004775)*pow($arreglo['nivel_combustible_sonda1'],2))+	
		((-0.00000044811976410226907)*pow($arreglo['nivel_combustible_sonda1'],3))+ 
		((0.0000000004802217000070812)*pow($arreglo['nivel_combustible_sonda1'],4))+			
		((-0.00000000000019884521784009749)*pow($arreglo['nivel_combustible_sonda1'],5));
		
		//Calculo lineal;		
				
		$kilos=((-28463916.867465526)*pow($arreglo['milivoltiossensor'],0))+			
		((122663.02100018767)*pow($arreglo['milivoltiossensor'],1))+			
		((-210.07844988149563)*pow($arreglo['milivoltiossensor'],2))+	
		((0.1854299868746669)*pow($arreglo['milivoltiossensor'],3))+ 
		((-0.00008964021966763734)*pow($arreglo['milivoltiossensor'],4))+		
		((0.00000002260504385699597)*pow($arreglo['milivoltiossensor'],5))+				
		((-0.0000000000023294346392084656)*pow($arreglo['milivoltiossensor'],6));
		
		
			
		if ($posicion_coincidencia === false) {	
			$fecha_proceso= date("Y-m-d H:i:s");
			$sql= "INSERT INTO sensores (id_vehi, temperatura_sonda1, temperatura_sonda2, nivel_combustible_sonda1, nivel_combustible_sonda2, sensor_peso,imei,tiempo,milivoltiossensor,galones,pesokilos,toneladas_equivalente, combustible, ton_equivalente, peso)
			VALUES('".$arreglo['id_vehi']."','".$arreglo['temperatura_sonda1']."','".$arreglo['temperatura_sonda2']."','".$arreglo['nivel_combustible_sonda1']."','".$arreglo['nivel_combustible_sonda2']."','".$arreglo['sensor_peso']."','".$arreglo['imei']."','".$fecha_proceso."','".$arreglo["milivoltiossensor"]."','".$galones."','".$kilos."','".$arreglo["toneladas_equivalente"]."','".$galones."','".$arreglo["toneladas_equivalente"]."','".$kilos."')";
			$qid=$db->sql_query($sql);
		}else{
			$fecha_proceso= date("Y-m-d H:i:s");
			$sql= "INSERT INTO sensores (id_vehi, temperatura_sonda1, temperatura_sonda2, nivel_combustible_sonda1, nivel_combustible_sonda2, sensor_peso,imei,tiempo,milivoltiossensor,galones,pesokilos,toneladas_equivalente,combustible,ton_equivalente,peso)
			VALUES('0','".$arreglo['temperatura_sonda1']."','".$arreglo['temperatura_sonda2']."','".$arreglo['nivel_combustible_sonda1']."','".$arreglo['nivel_combustible_sonda2']."','".$arreglo['sensor_peso']."','".$arreglo['imei']."','".$fecha_proceso."','".$arreglo["milivoltiossensor"]."','".$galones."','".$kilos."','".$arreglo["toneladas_equivalente"]."','".$galones."','".$arreglo["toneladas_equivalente"]."','".$kilos."')";
			$qid=$db->sql_query($sql);
		}
	
	//echo "==================================================================";
	//	echo "\n==================================================================FIN \n\n"	;
}



function insertar_registro_gps_peso($arreglo){
	
	GLOBAL $CFG, $db;	
	echo "IDVEHI2".$arreglo['id_vehi']."\n";	
	$validar=$arreglo['id_vehi'];
	$posicion_coincidencia = strpos($validar, '???');

		$galones=((0.1056653608175478)*pow($arreglo['nivel_combustible_sonda1'],0))+			
		((0.013749817995208184)*pow($arreglo['nivel_combustible_sonda1'],1))+			
		((0.00021172799407004775)*pow($arreglo['nivel_combustible_sonda1'],2))+	
		((-0.00000044811976410226907)*pow($arreglo['nivel_combustible_sonda1'],3))+ 
		((0.0000000004802217000070812)*pow($arreglo['nivel_combustible_sonda1'],4))+			
		((-0.00000000000019884521784009749)*pow($arreglo['nivel_combustible_sonda1'],5));
		
		//echo "calculo lineal para GALONES".		
				
		$kilos=((-28463916.867465526)*pow($arreglo['milivoltiossensor'],0))+			
		((122663.02100018767)*pow($arreglo['milivoltiossensor'],1))+			
		((-210.07844988149563)*pow($arreglo['milivoltiossensor'],2))+	
		((0.1854299868746669)*pow($arreglo['milivoltiossensor'],3))+ 
		((-0.00008964021966763734)*pow($arreglo['milivoltiossensor'],4))+		
		((0.00000002260504385699597)*pow($arreglo['milivoltiossensor'],5))+				
		((-0.0000000000023294346392084656)*pow($arreglo['milivoltiossensor'],6));
		
		// echo " calculo lineal para KILOS";
			
		if ($posicion_coincidencia === false) {	
			$fecha_proceso= date("Y-m-d H:i:s");
			$sql= "INSERT INTO sensores (id_vehi, temperatura_sonda1, temperatura_sonda2, nivel_combustible_sonda1, nivel_combustible_sonda2, sensor_peso,imei,tiempo,milivoltiossensor,galones,pesokilos,toneladas_equivalente, combustible, ton_equivalente, peso)
			VALUES('".$arreglo['id_vehi']."','".$arreglo['temperatura_sonda1']."','".$arreglo['temperatura_sonda2']."','".$arreglo['nivel_combustible_sonda1']."','".$arreglo['nivel_combustible_sonda2']."','".$arreglo['sensor_peso']."','".$arreglo['imei']."','".$fecha_proceso."','".$arreglo["milivoltiossensor"]."','".$galones."','".$kilos."','".$arreglo["toneladas_equivalente"]."','".$galones."','".$arreglo["toneladas_equivalente"]."','".$kilos."')";
			$qid=$db->sql_query($sql);
		}else{
			$fecha_proceso= date("Y-m-d H:i:s");
			$sql= "INSERT INTO sensores (id_vehi, temperatura_sonda1, temperatura_sonda2, nivel_combustible_sonda1, nivel_combustible_sonda2, sensor_peso,imei,tiempo,milivoltiossensor,galones,pesokilos,toneladas_equivalente,combustible,ton_equivalente,peso)
			VALUES('0','".$arreglo['temperatura_sonda1']."','".$arreglo['temperatura_sonda2']."','".$arreglo['nivel_combustible_sonda1']."','".$arreglo['nivel_combustible_sonda2']."','".$arreglo['sensor_peso']."','".$arreglo['imei']."','".$fecha_proceso."','".$arreglo["milivoltiossensor"]."','".$galones."','".$kilos."','".$arreglo["toneladas_equivalente"]."','".$galones."','".$arreglo["toneladas_equivalente"]."','".$kilos."')";
			$qid=$db->sql_query($sql);
		}
		
	
	echo "==================================================================";
		echo "\n==================================================================FIN \n\n"	;
}




function insertar_registro_gps($arreglo){
	GLOBAL $CFG, $db;	
	$arreglo['id_vehi'] = 0;  
   	$qid=$db->sql_query("SELECT v.*,(SELECT id FROM mtto.equipos WHERE id_vehiculo=v.id LIMIT 1) as id_equipo FROM vehiculos v WHERE v.idgps = '" . $arreglo["id_vehiculo"] . "'");
	if(!$equipo=$db->sql_fetchrow($qid)) error_log("No tiene equipo asociado 1 :: " . $arreglo["id_vehiculo"]);
	else{
    $arreglo['id_vehi'] = $equipo["id"];
  }
  echo "SELECT v.*,(SELECT id FROM mtto.equipos WHERE id_vehiculo=v.id LIMIT 1) as id_equipo FROM vehiculos v WHERE v.idgps = '" . $arreglo["id_vehiculo"] . "'";
  
	
	$entrarInsertar = horarios_laborables($arreglo);
	if($entrarInsertar){
		$geometry="ST_GeomFromText('POINT($arreglo[longitud] $arreglo[latitud])',4326)";
		$geometry_vehi="ST_GeomFromText('POINT($arreglo[longitud] $arreglo[latitud])',4326)";
		if($arreglo["velocidad"]>150 || $arreglo["azimut"]>360){
			error_log("Registro inválido: " . print_r("Tiempo->".$arreglo['tiempo']." vehiculo->".$arreglo['id_vehiculo']." longitud->".$arreglo['longitud']." latitud->".$arreglo['latitud']." \n",true));
			return;
		}
		if((nvl($arreglo["status"])=="V" || nvl($arreglo["GG"])=="Sin antena" || nvl($arreglo["GG"])=="Sin pos")){
		//Si no es una casa y viene sin posición de GPS
			$geometry="NULL";
			$geometry_vehi="NULL";
			$velocidad="NULL";
		}
		else{
			$velocidad="'" . $arreglo["velocidad"] . "'";
		}
		
	//Verificar validez del registro..
		if((nvl($arreglo["status"])!="V" && nvl($arreglo["GG"])!="Sin antena" && nvl($arreglo["GG"])!="Sin pos")){

			$diferencia_horas=abs(strtotime($arreglo["tiempo"])-time())/60/60;
			if($diferencia_horas>24){
			error_log("Registro inválido: " . print_r("Tiempo->".$arreglo['tiempo']." vehiculo->".$arreglo['id_vehiculo']." longitud->".$arreglo['longitud']." latitud->".$arreglo['latitud']." \n",true));
				return;
			}
		}
		if((nvl($arreglo["status"])=="V" || nvl($arreglo["GG"])=="Sin antena" || nvl($arreglo["GG"])=="Sin pos")){
		//Si viene sin posición de GPS
			$pos="CASE WHEN hrposition ~ 'NO GPS' THEN hrposition ELSE hrposition || ' (NO GPS)' END";
			$pos_gps_vehi="NULL";
			$geometry="the_geom";
			$geometry_vehi="NULL";
			$km_virtual="km_virtual";
			$diff_km="0";
		}
		else{
			$pos="'" . reverse_geocode($arreglo["longitud"],$arreglo["latitud"]) . "'";
			$pos_gps_vehi=$pos;
			$posicion_anterior=$equipo["the_geom"];
			$posicion_nueva=$geometry_vehi;
			if($posicion_anterior==""){
				$km_virtual="0";
				$diff_km="0";
			}   
			else{
					$km_virtual="km_virtual + (SELECT ST_Distance('$posicion_anterior' , $posicion_nueva ,FALSE))/1000";
					$diff_km="(SELECT ST_Distance('$posicion_anterior' , $posicion_nueva ,FALSE))/1000";
			}
		}
		if($arreglo["evento"]==14 || $arreglo["evento"]==15) $id_estado_motor="'$arreglo[evento]'";//Prendido o apagado
		else $id_estado_motor="id_estado_motor";

		if($equipo["id_estado_motor"]==14){//Si está prendido
			$diff=(time()-strtotime($equipo["tiempo"]))/3600;
			$horometro_virtual="horometro_virtual+'$diff'";
		}
		else{
			$diff="0";
			$horometro_virtual="horometro_virtual";
		}



//*****************BUSCAR EL ID DEL VEHICULO A PARTIR DE LA PLACA *******************//

 $qidvehi=$db->sql_query("SELECT id as id_vehi FROM public.vehiculos WHERE placa = '" . $arreglo['id_vehiculo'] . "'");	
					
					if(!$equipov=$db->sql_fetchrow($qidvehi)){
				
					$arreglo['id_vehi']=0;
						error_log("No tiene vehiculo asociado :: " . $array[0]);
					}else{ 
					$arreglo['id_vehi'] = $equipov["id_vehi"];
					}
 
echo "NOEL".$arreglo['id_vehi'] ;




		$sql = "INSERT INTO gps_vehi (id_vehi,tiempo,rumbo,velocidad,evento,gps_geom,hrposition,imei)
			VALUES($arreglo[id_vehi], timestamp '$arreglo[tiempo]' -interval '5 hour',$arreglo[azimut],$velocidad,'$arreglo[evento]',$geometry_vehi,$pos_gps_vehi,'$arreglo[imei]')";
		
		
		$qid=$db->sql_query($sql);
		if($arreglo["velocidad"]=="") $velocidad="NULL";
		else $velocidad="'" . $arreglo["velocidad"] . "'";
		$strSQL="
			UPDATE vehiculos SET
				tiempo='" . date("Y-m-d H:i:s") . "',
				hrposition=$pos,
				velocidad=$velocidad,
				the_geom=$geometry,
				km_virtual=($km_virtual),
				kilometraje=($km_virtual),
				id_estado_motor=$id_estado_motor,
				horometro_virtual=$horometro_virtual,
				horometro=$horometro_virtual
			WHERE idgps = '" . $arreglo["id_vehiculo"] . "'
		";
		$strSQL="
			UPDATE vehiculos SET
				tiempo = timestamp '$arreglo[tiempo]' -interval '5 hour',
				hrposition = $pos,
				velocidad = $velocidad,
				the_geom = $geometry,
				km_virtual = ($km_virtual),
				id_estado_motor = $id_estado_motor,
				horometro_virtual = $horometro_virtual
			WHERE idgps = '" . $arreglo["id_vehiculo"] . "'
		";
		$qupdate=$db->sql_query($strSQL);

	//HISTÓRICO RECORRIDOS:

		if($equipo["id_equipo"] == ""){
			error_log("No tiene equipo asociado 2:: " . $arreglo["id_vehiculo"]);
	//		file_put_contents('../error.log',"No tiene equipo asociado 3 :: " . $arreglo["id_vehiculo"] . "\n",FILE_APPEND);
		}
		elseif($result=$db->sql_row("SELECT * FROM historico_recorrido WHERE id_equipo='$equipo[id_equipo]' AND fecha='" . date("Y-m-d") . "'")){
			//Ya existe, hay que actualizarlo
			$strSQL="
				UPDATE historico_recorrido SET 
					km=km+$diff_km,
					horas=horas+'$diff'
				WHERE id='$result[id]'
			";
			//error_log($strSQL);
			$qid=$db->sql_query($strSQL);
		}
		else{
			//No existe, toca crearlo
			$qid=$db->sql_query("
				INSERT INTO historico_recorrido(id_equipo,fecha,km,horas)
				VALUES('$equipo[id_equipo]','" . date("Y-m-d") . "',$diff_km,$diff)
			");
		}
	}
	else{
		preguntarLogMtto($arreglo);
	}
	return $qid;
}


function horarios_laborables($arreglo)
{ 
  global $db, $CFG;

  $dia = strftime("%u",strtotime($arreglo["tiempo"]));
  $hora = strftime("%H:%M:%S",strtotime($arreglo["tiempo"]));
  
  $horarios = array();
  $qidH = $db->sql_query("SELECT h.* FROM vehiculos_horarios h LEFT JOIN vehiculos v ON v.id=h.id_vehiculo WHERE v.idgps='".$arreglo["id_vehiculo"]."'");
  if($db->sql_numrows($qidH) == 0)
    return true;
  else
  {
    while($h = $db->sql_fetchrow($qidH))
    {
     if($h["dia"] == $dia && $h["hora_inicio"]<=$hora && $h["hora_final"] >= $hora)
        return true;
    }
    
    return false;
  } 
} 

function insertar_registro_sin_gps($arreglo){
	GLOBAL $CFG,$db;
	$qid=db_query("
		INSERT INTO gps_vehi (id_vehi,tiempo,rumbo,velocidad,evento,gps_geom)
		VALUES('$arreglo[id_vehiculo]','$arreglo[tiempo]','$arreglo[azimut]','$arreglo[velocidad]','$arreglo[evento]',NULL)
	");

	return $qid;
}

function forward_registro($servidor,$id_remoto,$arreglo){
	$dir=dirname(__FILE__);
	$command=translate_to_trama_CL($arreglo);
	$linea_comando="/usr/bin/php " . $dir . "/udp_plain_client.php $servidor \"$command\"";
	echo "Enviando al servidor $servidor ...\n";
	echo $linea_comando . "\n";
	exec($linea_comando);
}
 
function translate_to_trama_CL($arreglo){
	GLOBAL $CFG;
	$string="";
	$string.=str_pad(substr($arreglo["id_vehiculo"],0,10),10,"0",STR_PAD_LEFT);
	$arreglo["latitud"]=round($arreglo["latitud"]*1000000);
	if(abs($arreglo["latitud"])!=$arreglo["latitud"]) $signo="-";
	else $signo="+";
	$string.=$signo;
	$arreglo["latitud"]=abs($arreglo["latitud"]);
	$string.=str_pad($arreglo["latitud"],8,"0",STR_PAD_LEFT);
	$arreglo["longitud"]=round($arreglo["longitud"]*1000000);
	if(abs($arreglo["longitud"])!=$arreglo["longitud"]) $signo="-";
	else $signo="+";
	$string.=$signo;
	$arreglo["longitud"]=abs($arreglo["longitud"]);
	$string.=str_pad($arreglo["longitud"],9,"0",STR_PAD_LEFT);
//	En nuevas versiones de PHP saca error si ni se le pone esto:
//	date_default_timezone_set('America/Bogota');
	$tiempoSegundos=strtotime($arreglo["tiempo"])-($CFG->gmtoffset * 3600);
	$arreglo["tiempo"]=date("YmdHis",$tiempoSegundos);
	
	$string.=$arreglo["tiempo"];
	$string.=str_pad($arreglo["velocidad"],3,"0",STR_PAD_LEFT);
	$string.=str_pad($arreglo["azimut"],3,"0",STR_PAD_LEFT);
	//OJO: HAY QUE REVISAR LO DEL ESTADO DEL VEHÍCULO.  EN TODO CASO EN CL NO SE USA.
	$string.="·";

	return($string);
}

function procesar_evento($arreglo){
	GLOBAL $CFG;
	$qvehiculo=db_query("SELECT * FROM vehiculos WHERE idgps = '" . $arreglo["id_vehiculo"] . "'");
	$vehi=db_fetch_array($qvehiculo);
	if(!$vehi) return(0);
	$qeventos=db_query("
		SELECT te.*, eu.var1, eu.var2
		FROM eventos_unidades eu LEFT JOIN tipos_eventos te ON eu.id_evento=te.id
		WHERE eu.id_equipo='" . $vehi["id"] . "' AND eu.evento='" . $arreglo["evento"] . "'
	");
	$dir_comandos=dirname(__FILE__) . "/comandos";
	while($event=db_fetch_array($qeventos)){
		$linea_comando="/usr/bin/php " . $dir_comandos . "/" . $event["comando"] . " " . $vehi["idgps"];
		if($event["variables"]==1 || $event["variables"]==2) $linea_comando .= " \"" . str_replace("\"","\\\"",$event["var1"]) . "\"";
		if($event["variables"]==2) $linea_comando .= " \"" . str_replace("\"","\\\"",$event["var2"]) . "\"";
		echo $linea_comando . "\n";
		exec($linea_comando);
	}
}

function find_location($punto){

	$offset=0.0025;
	$xmin=$punto["longitud"]-$offset;
	$ymin=$punto["latitud"]-$offset;
	$xmax=$punto["longitud"]+$offset;
	$ymax=$punto["latitud"]+$offset;

	$texto=number_format($punto["longitud"],3) . "/" . number_format($punto["latitud"],3);
	$qlocalizacion=db_query("
			SELECT distance('$punto[gps_geom]',setsrid(the_geom,1)),nomvial,g_nv, g_ln, g_card FROM sig_gr_entidades
			WHERE setsrid(the_geom,1) && 'BOX3D($xmin $ymin, $xmax $ymax)'::box3d
			ORDER BY distance('$punto[gps_geom]',setsrid(the_geom,1)) LIMIT 1
			");
	if($localizacion=db_fetch_array($qlocalizacion)){
		if($localizacion["g_card"]==4) $localizacion["g_card"]=" S";
		elseif($localizacion["g_card"]==2) $localizacion["g_card"]=" E";
		else $localizacion["g_card"]="";
		$titulo=$localizacion["nomvial"] . " - " . $localizacion["g_nv"] . $localizacion["g_ln"] . $localizacion["g_card"];
	}
	else $titulo=$texto;

	return($titulo);
	
}

function reverse_geocode($longitud,$latitud){
	GLOBAL $CFG;

	$db_osm=new sql_db_postgres($CFG->dbhost_osm,$CFG->dbuser_osm,$CFG->dbpass_osm,$CFG->dbname_osm);
	if(trim($longitud)=="" || trim($latitud)=="") return("??/--");
	$offset=0.0003;
	$geom="ST_GeomFromText('POINT($longitud $latitud)',4326)";
	if($loc=$db_osm->sql_row("
		SELECT osm_id,name,st_astext(way) as geom
		FROM osm_line l
		WHERE (l.way && expand($geom,$offset)) AND l.highway IS NOT NULL
		ORDER BY ST_Distance($geom,l.way) LIMIT 1
	")){
		$posicion=utf8_decode($loc["name"]) . " - ";
		$geomLine="ST_GeomFromText('$loc[geom]',4326)";
		if($loc2=$db_osm->sql_row("
			SELECT l.osm_id,l.name
			FROM osm_line l
			WHERE l.way && expand($geomLine,$offset) AND
			 ST_INTERSECTS($geomLine,l.way) AND l.osm_id!='$loc[osm_id]' AND l.name!='" . $db_osm->sql_escape($loc["name"]) . "' AND l.highway IS NOT NULL
			ORDER BY ST_Distance($geom,l.way) LIMIT 1
		")){
			$posicion.=utf8_decode($loc2["name"]);
		}
		$posicion = substr($posicion,0,127);
	}
	else $posicion="--";
	return($posicion);
}

function singleTramaTeltonika($str){
	GLOBAL $CFG;
	
	$CFG->gmtoffset=5;

	$arreglo["evento"]="8";
	//Hay que sumarle las horas de gmtOffset, porque viene en hora local
	$arreglo["tiempo"]=date("Y-m-d H:i:s",(hexdec(substr($str,0*2,16))/1000) + $CFG->gmtoffset*3600);

	$arreglo["longitud"]=hexdec(substr($str,9*2,8))/10000000;

	if($arreglo["longitud"]>180){
		$arreglo["longitud"] = (hexdec(substr($str,9*2,8)) - 4294967296)/10000000;
	}
	
	$arreglo["latitud"]=hexdec(substr($str,13*2,8))/10000000;
	if($arreglo["latitud"]>180){
		$arreglo["latitud"] = (hexdec(substr($str,13*2,8)) - 4294967296)/10000000;
	}
	$arreglo["altitud"]=hexdec(substr($str,17*2,4));
	$arreglo["azimut"]=hexdec(substr($str,19*2,4));
	$arreglo["satelites"]=hexdec(substr($str,21*2,2));
	$arreglo["velocidad"]=hexdec(substr($str,22*2,4));
	return($arreglo);
}

?>