# gps_daemon
Captura de GPS e inserción a base de datos
Captura por el puerto 7777 de un servidor especifico, las tramas de GPS
Se ctiva el shell, que recibe las tramas, se los pasa al archivo php, este convierte la trama en hexadecimal, separa los campos
y se guardan en la tabla de la base de datos.

El resultado de cada proceso y la trama se registra en un archivo log.
