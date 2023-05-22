function abrirWHora(campo,formulario){
  ruta='/public_html/calendario/calendarAndHora.php?formulario='+formulario+'&nomcampo=' + campo + '&mode=editar';
  ventana = 'v_calendar';
  window.open(ruta,ventana,'scrollbars=yes,width=250,height=400,screenX=100,screenY=100');
}

function abrirSoloHora(campo,formulario){
  ruta='/public_html/calendario/soloHora.php?formulario='+formulario+'&nomcampo=' + campo;
  ventana = 'v_calendar';
  window.open(ruta,ventana,'scrollbars=yes,width=250,height=400,screenX=100,screenY=100');
}


function abrir(campo,formulario){
  ruta='/public_html/calendario/calendar.php?formulario='+formulario+'&nomcampo=' + campo;
  ventana = 'v_calendar';
  window.open(ruta,ventana,'scrollbars=yes,width=250,height=350,screenX=100,screenY=100');
}

function abrirVentanaNueva(name,width,height){
  izq=(screen.width-width)/2;
  arriba=(screen.height-height)/2;
  return window.open('',name,'scrollbars=yes,width=' + width +',height=' + height +',resizable=yes,left='+izq+',top='+arriba);
}

function abrirVentanaJavaScript(name,width,height,url){
  vent=abrirVentanaNueva(name,width,height);
  vent.location.href=url;
  if(vent.focus) vent.focus();
}

function abrirCalendarioConModo(archivo,modo,esquema){
	ruta='/public_html/calendario/calendarWithMode.php?archivo=' + archivo + '&modo=' + modo + '&esquema=' + esquema;
	ventana = 'v_calendar';
	window.open(ruta,ventana,'scrollbars=yes,width=250,height=350,screenX=100,screenY=100');
}

function abrirCalendarioConModoModule(archivo,module){
	ruta='/public_html/calendario/calendarWithModeModule.php?archivo=' + archivo + '&module=' + module;
	ventana = 'v_calendar';
	window.open(ruta,ventana,'scrollbars=yes,width=250,height=350,screenX=100,screenY=100');
}

function verificarFechaCompleta(fecha)
{
	fechaE = fecha.split(" ");
	parteUno = fechaE[0].split("-");
	parteDos = fechaE[1].split(":");

	if(parteUno[1] > 12 || parteUno[1] < 1)
		return false;
	if(parteUno[2] > 31 || parteUno[2] < 1)
		return false;

	if(parteDos[0] > 23)
		return false;

	if(parteDos[1] > 59)
		return false;

	if(parteDos[2] > 59)
		return false;

	return true;
}


function showCalendarHora(casilla, boton)
{
	var cal = Calendar.setup({
		onSelect: function(cal) { cal.hide() },
		showTime: true,
		minuteStep:1,
		weekNumbers:true,
		inputField: casilla,
		trigger: boton,
		dateFormat:  "%Y-%m-%d %H:%M:%S",
		opacity:0
	});
}

function showCalendarSencillo(casilla, boton)
{
	var cal = Calendar.setup({
		onSelect: function(cal) { cal.hide() },
		weekNumbers:true,
		inputField: casilla,
		trigger: boton,
		dateFormat:  "%Y-%m-%d",
		opacity:0
	});
}

