<!DOCTYPE html>
<html lang="en">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Perfil Analítico de Alumnos</title>
  </head>

  <body style="font-family:courier">
  	<?php
	set_time_limit (0);

  	// No cookies
  	ini_set('session.use_cookies', '0');

  	if (!isset($_FILES['logmoodle'])) {
  	?>
  	<h1>Investigación en Perfiles Analíticos de Alumnos</h1>
  	<p>
  <b>En primer lugar darte las gracias por participar en esta investigación.</b>
  <br /><br />
  Mi dedicación inquisitiva en el mundo educativo pretende mejorar los procesos de enseñanza-aprendizaje dentro y fuera del aula.
  Es por esto que con esta iniciativa se pretende validar si la conjunción de gráficos (barras, sectores...) y texto (descripción, etiquetas...) mostrado de forma independiente para cada alumno facilita y mejora la tutoría y evaluación de los mismos.
  <br /><br />
  El proceso de investigación consta de distintos pasos. El primero de todos consiste en despersonalizar los datos.
  Es decir, impedir que el Inform de Registros de Moodle que envies puedan asociarse a un alumno de tu curso.
  Solamante tú como profesor o tutor debes saber la identidad de tus alumnos en este proceso de investigación.
  <br /><br />
  Una vez efectuada esta depersonalización, empezarás a recibir visualizaciones junto a una serie de preguntas. Las respuesta a estas preguntas las usaré para validar las hipótesis. 
  <br /><br />
  <b>Cómo enviar el Informe de Registros</b>
  <br /><br />
  <ol>
  <li>Descarga el Informe de Registros de tu curso de Moodle. Es necesario que descarges el inform con el formato "Archivo de texto con valores separados por comas".</li>
  <li>Utiliza el formulario de más abajo para enviar el Informe.</li>
  <li>Una vez enviado el Informe recibirás por correo electrónico un listado de alumnos con sus correspondientes identificadores. En las visualizaciones se mostrarán los identificadores.</li>
  </ol>
  <br /><br />
  <b>Envía el Informe de Registros desde el siguiente formulario</b>
  	</p>
  	<form action="depersonalizacion.php" method="POST" enctype="multipart/form-data">
  	Nombre:&nbsp;<input name="nombre" value="" placeholder="Escribe aquí tu nombre" style="font-size:10pt;width:200px"/><br /><br />
  	Correo electrónico:&nbsp;<input name="email" value="" placeholder="Escribe aquí tu correo electrónico" style="font-size:10pt;width:200px"/><br /><br />
  	<input type="file" name="logmoodle" style="font-size:10pt"  /><br /><br />
  	<input type="submit" name="enviar" style="font-size:12pt" value="Enviar para despersonalizar" />
  	<br /><b>(Recuerda, recibirás un correo electrónico con los identificadores de los alumnos para poder saber de quién son las visualizaciones, puesto que no podemos guardar los nombres de los alumnos)</b>
  	<br /><br />Puedes acceder al código fuente de este script PHP desde <a href="despersonalizacion.txt">aquí</a>.
  	</form>
  	<?php
  	}
  	else {

  		// Usuarios depersonalizados
		$depersonalizados = array();

  		// Diseccionar en filas
  		$fileMove = uniqid().".csv";
  		if (move_uploaded_file($_FILES['logmoodle']['tmp_name'], dirname(__FILE__)."/tmp/".$fileMove)) {
	  		$Data = str_getcsv(file_get_contents(dirname(__FILE__)."/tmp/".$fileMove), "\n");
			foreach($Data as &$Row) {
				// Diseccionar ítems en array
				$Row = str_getcsv($Row, ",");
				// Encontrar id de usuarios para depersonalizar
				$start = "user with id '";
				$end = "'";
	    		$regex = "/$start([a-zA-Z0-9_]*)$end/";
				$numMatches = preg_match_all($regex, $Row[7], $users, PREG_SET_ORDER);
				// Cambiar el nombre del usuario por el identificador para depersonalizar
				if ($numMatches >= 1) {
					if (!isset($depersonalizados[$Row[2]]))
						$depersonalizados[$Row[2]] = array($users[0][1], uniqid());
					$Row[2] = $depersonalizados[$Row[2]][1];
					$Row[7] = str_replace($start.$users[0][1].$end, $start.$Row[2].$end, $Row[7]);
				}
				if ($numMatches > 1) {
					if (!isset($depersonalizados[$Row[3]]))
						$depersonalizados[$Row[3]] = array($users[1][1], uniqid());
					$Row[3] = $depersonalizados[$Row[3]][1];
					$Row[7] = str_replace($start.$users[1][1].$end, $start.$Row[3].$end, $Row[7]);
				}
				// Limpiar nombre de usuario
				if ($Row[3] != "-")
					$Row[3] = $Row[2];
				// Eliminar referencias IP
				$Row[9] = "";
			}

			$fileName = uniqid();
			file_put_contents(dirname(__FILE__)."/tmp/".$fileName.".txt", $_POST["nombre"]."\n".$_POST["email"]);

			$fp = fopen(dirname(__FILE__)."/tmp/".$fileName.".csv", 'w');
			foreach ($Data as $campos) {
			    fputcsv($fp, $campos);
			}
			fclose($fp);
			
			// Mostrar depersonalización
			$csvFile = "";
			foreach($depersonalizados as $key=>$value) {
				$csvFile .= '"'.$key . '";"' . $value[0] .  '";"' . $value[1] . '"'."\n";
			}

			file_put_contents(dirname(__FILE__)."/tmp/"."alumnos-codigos.csv", $csvFile);

	        require 'PHPMailer/PHPMailerAutoload.php';
	        $mail = new PHPMailer;
	        $mail->setFrom('hola@eduliticas.com', 'eduliticas.com');
	        $mail->addAddress($_POST["email"], $_POST["nombre"]);
	        $mail->Subject = 'Codigos de alumnos para la Investigacion Perfiles Analiticos de Alumnos';
	        $mail->msgHTML("<h2>Hola ".$_POST["nombre"]."</h2><p>Gracias por participar en la investigación.<br />Adjunto encontrarás un listado de tus alumnos junto al código que utilizaremos para generar sus visualizaciones. Puedes importar el archivo en un excel para más comodidad. No pierdas el archivo, lo necesitarás para responder a las distintas preguntas que te llegarán en breve :)<br /> Saludos! <br /> Dani</p> ");
	        // Attach the uploaded file
	        $mail->addAttachment(dirname(__FILE__)."/tmp/"."alumnos-codigos.csv", 'eduliticas-alumnos-codigos.csv');
	        if (!$mail->send()) {
	            $msg .= "Ocurrió un error al enviar los códigos. Por favor, contacta en hola@eduliticas.com. " . $mail->ErrorInfo;
	        } else {
	            $msg .= "Códigos enviados a tu correo electrónico.";
	        }


	        echo $msg;

			unlink(dirname(__FILE__)."/tmp/".$fileMove);
			unlink(dirname(__FILE__)."/tmp/"."alumnos-codigos.csv");
		}
  	}
  	?>
  </body>
</html>