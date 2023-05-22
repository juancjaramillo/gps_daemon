#!/usr/bin/php -q
<?
// Create a TCP Stream socket


/*	----------------------------------------------	*/
/*						MAIN						*/
/*	----------------------------------------------	*/

// Set time limit to indefinite execution
/********************************************************/
/***$CFG->common_libdir ::: es la ruta a la carpeta dir***/
/******** Se inicializa en el archivo application*********/
/*********************************************************/

include(dirname(__FILE__) . "/application.php");
require($CFG->common_libdir . "/funciones_gps.php");
//include("funciones.php");
$argumentos=$_SERVER["argv"];
$CFG->verbose=0;
for($i=1;$i<sizeof($argumentos);$i++){
	if($argumentos[$i]=="-p" && isset($argumentos[$i+1]) && is_numeric($argumentos[$i+1])){
		$port=$argumentos[$i+1];
		$i++;
	}
	if($argumentos[$i]=="-v") $CFG->verbose=1;
}

$msg="[" . date("Y-m-d H:i:s") . "] Starting...\n";
if($CFG->verbose) echo $msg;

set_time_limit (0);

// Set the ip and port we will listen on

$address = 'xxx.xxx.xxx.xxx';
if(!isset($port)) $port = 7777;

$max_clients = 300;

// Array that will hold client information
$client = Array();

// Create a TCP Stream socket
$sock = socket_create(AF_INET, SOCK_STREAM, 0);
if (!socket_setopt($sock,SOL_SOCKET,SO_REUSEADDR,1)) {
	$msg=date("[Y-m-d H:i:s]") . "socket_setopt() failed: reason: ".socket_strerror(socket_last_error($sock))."\n";
	if($CFG->verbose) echo $msg;
	exit;
}
// Bind the socket to an address/port
socket_bind($sock, $address, $port) or die('Could not bind to address');
// Start listening for connections
socket_listen($sock);


$activo=FALSE;
$inicio=time();
$arreglo_pendientes=array();
// Loop continuously
while (true) {
		// Setup clients listen socket for reading
	$read[0] = $sock;
	for ($i = 0; $i < $max_clients; $i++)
	{
		if (isset($client[$i]['sock']) && $client[$i]['sock']	!= null)
			$read[$i + 1] = $client[$i]['sock'] ;
	}
	// Set up a blocking call to socket_select()
	$ready = socket_select($read,$write = null,$except = null,null);
	/* if a new connection is being made add it to the client array */
	if (in_array($sock, $read)) {
		for ($i = 0; $i < $max_clients; $i++)
		{
			if (!isset($client[$i]['sock']) || $client[$i]['sock'] == null) {
				$client[$i]['sock'] = socket_accept($sock);
				break;
			}
			elseif ($i == $max_clients - 1){
				print ("too many clients");
				die();
			}
		}
		if (--$ready <= 0)
			continue;
	} // end if in_array

	// If a client is trying to write - handle it now
	for ($i = 0; $i < $max_clients; $i++) // for each client
	{
		if (isset($client[$i]['sock']) && in_array($client[$i]['sock'] , $read))
		{
			if(!$input = socket_read($client[$i]['sock'] , 4096)){
				echo "[" . date("Y-m-d H:i:s") . "] Socket $i (" . nvl($client[$i]['id_gps']). ") DESCONECTADO\n";
				echo "Error en la conexion.  Reseteando...\n";
				socket_close($client[$i]['sock']);
				unset($client[$i]);
			}
			if ($input) {
/*	--------------------------------------------------------	*/
				$DBConnected=false;
				$msg=date("[Y-m-d H:i:s]") . "\n";
				if($CFG->verbose) echo $msg;

				$hex=bin2hex($input);

				if(ord($input{0})==0 && ord($input{1})==15){
					if($CFG->verbose) echo "Unidad teltonika...\n";
					if(!isset($vehiculos)) $vehiculos=array();
					$vehiculos[$i]=substr($input,2,14);

					//Coger la mitad de digitos:
					//$vehiculos[$i]=(int) substr($input,9,7);
					socket_write($client[$i]['sock'], chr(1));
				}
				elseif(strlen($input)==256){
					echo "Trama incompleta, esperando el resto...\n";
					if(isset($inputAnterior[$i])) $inputAnterior[$i].=$input;
					else $inputAnterior[$i]=$input;
				}
				else{
					if(isset($inputAnterior[$i]) && $inputAnterior[$i]!=""){
						echo "Completando trama incompleta...\n";
						$input=$inputAnterior[$i].$input;
						$hex=bin2hex($input);

					}
					else{
					 	echo "Trama completa.\n";
					}
					$inputAnterior[$i]="";
					$start=10;
					//$length=63;
					$length=67;
					$numTramas=round(strlen($input)/$length);

					echo "TRAMAS: " . $numTramas . "\n";

					echo "\n======\nHEX:\n" . $hex . "\n======\n";
					$prefix="0000015f";
					$arrTramas=explode($prefix,$hex);
#print_r($arrTramas);
					for($k=1;$k<sizeof($arrTramas);$k++){
						$strTrama=$prefix . $arrTramas[$k];
						$arreglo=singleTramaTeltonika($strTrama);
						$arreglo["id_vehiculo"]=$vehiculos[$i];
						insertar_registro_gps($arreglo);
#print_r($arreglo);
echo "Registro: Tiempo->".$arreglo['tiempo']." vehiculo->".$arreglo['id_vehiculo']." longitud->".$arreglo['longitud']." latitud->".$arreglo['latitud']." \n";
					}

					for($j=0;$j<$numTramas;$j++){
						$arreglo=singleTramaTeltonika(substr($hex,($start+$j*$length)*2,$length*2));
						$arreglo["id_vehiculo"]=$vehiculos[$i];
						insertar_registro_gps($arreglo);
#print_r($arreglo);
echo "Registro: Tiempo->".$arreglo['tiempo']." vehiculo->".$arreglo['id_vehiculo']." longitud->".$arreglo['longitud']." latitud->".$arreglo['latitud']." \n";
					}
					socket_write($client[$i]['sock'], pack('N',$numTramas));
				}

/*	--------------------------------------------------------	*/
			}
		}
	}
} // end while
// Close the master sockets
socket_close($sock);
?>
