<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
    <script type="text/javascript" src="js/canvasjs/canvasjs.js"></script>
    <script type="text/javascript" src="js/canvasjs/excanvas.js"></script>
    <script type="text/javascript" src="js/canvasjs/jquery.canvasjs.js"></script>
    <script type="text/javascript">

    function drawLineChart(config, divId, data, dataTitle, data2, maximumValue, legendData, legendData2) {
      var backgroundColorChart = "#FFFFFF";
      if (data.length == 0) {
        data = new Array();
        backgroundColorChart = "#CC0000";
      }

      var chart = new CanvasJS.Chart(divId,
      {
        culture:"es",
        backgroundColor: backgroundColorChart,
        legend: {
         horizontalAlign: "center", // "center" , "right"
         verticalAlign: "bottom",  // "top" , "bottom"
         fontSize: 10,
         maxWidth: 750
       },
        title:{
        text: dataTitle
        },
        axisX: {
          gridColor:"black",
          valueFormatString: "MMM",
          interval:config["axisX.interval"],
          intervalType: "month"
        },
        axisY:{
          includeZero: false,
          maximum: maximumValue + 50

        },
        data: [
        {
          type: "line",
          lineColor: config["data.lineColor"],
          color: config["data.lineColor"],
          markerType: config["markerType"],
          showInLegend: legendData[0],
          legendText: legendData[1],
          dataPoints: data
        },
        {
          type: "line",
          lineColor:'#E8A317',
          color:'#E8A317',
          showInLegend: legendData2[0],
          legendText: legendData2[1],
          dataPoints: data2
        }
        ]
      });

      chart.render();
    }

    function drawPieChart(divId, data, dataTitle) {
      var chart = new CanvasJS.Chart(divId,
      {
        title:{
          text: dataTitle
        },
        legend: {
          maxWidth: 200,
          itemWidth: 120
        },
        data: [
        {
          type: "pie",
          showInLegend: true,
          legendText: "{indexLabel}",
          dataPoints: [
            { y: data[0], indexLabel: "Lecturas "+Math.round((data[0]/(data[0]+data[1]))*100)+"%"},
            { y: data[1], indexLabel: "Envíos "+Math.round((data[1]/(data[0]+data[1]))*100)+"%"}
          ]
        }
        ]
      });
      chart.render();
    }

    function addUserCharts(userId, userName, data, maxDataValue) {
        drawLineChart({markerType:'circle', 'axisX.interval':1, "data.lineColor": "#4863A0"},userId+'chartInteracions', data[0], "Interacciones totales del alumno <?= $userName; ?> a lo largo del curso", data[1], maxDataValue[0], [true, "Interacciones del alumno <?=$userName?>"], [true, "Media de interacciones de todos los alumnos"]);
        drawLineChart({markerType:'circle', 'axisX.interval':1, 'data.lineColor':'lightgray'},userId+'chartTendency', data[2], "Tendencia de las interacciones del alumno <?= $userName; ?> a lo largo del curso", [], maxDataValue[0], [false, ""], [false, ""]);
        drawPieChart(userId+'chartSocialInteractions', data[7], "% Interacciones sociales del alumno "+userId);
        drawLineChart({markerType:'circle', 'axisX.interval':1, 'data.lineColor':'#4863A0'},userId+'lineSocialInteractions', data[3], "Interacciones sociales del alumno <?= $userName; ?> a lo largo del curso", [], maxDataValue[0], [false, ""], [false, ""]);
        drawPieChart(userId+'chartAssignmentInteractions', data[8], "% Interacciones en actividades del alumno "+userId);
        drawLineChart({markerType:'circle', 'axisX.interval':1, 'data.lineColor':'#4863A0'},userId+'lineAssignmentInteractions', data[4], "Interacciones en actividades del alumno <?= $userName; ?> a lo largo del curso", [], maxDataValue[0], [false, ""], [false, ""]);
        drawPieChart(userId+'chartResourceInteractions', data[9], "% Interacciones en recursos del alumno "+userId);
        drawLineChart({markerType:'circle', 'axisX.interval':1, 'data.lineColor':'#4863A0'},userId+'lineResourceInteractions', data[5], "Interacciones en recursos del alumno <?= $userName; ?> a lo largo del curso", [], maxDataValue[0], [false, ""], [false, ""]);
        drawPieChart(userId+'chartReportInteractions', data[10], "% Interacciones en informes del alumno "+userId);
        drawLineChart({markerType:'circle', 'axisX.interval':1, 'data.lineColor':'#4863A0'},userId+'lineReportInteractions', data[6], "Interacciones en informes del alumno <?= $userName; ?> a lo largo del curso", [], maxDataValue[0], [false, ""], [false, ""]);
    }

    window.onload = function () {


      CanvasJS.addCultureInfo("es",
      {
          decimalSeparator: ".",
          digitGroupSeparator: ",",
          months: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
          shortMonths: ["Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"],
     });


      <?php
        function calculateTendency($recordset) {
          $X = array();
          $Y = array();
          foreach ($recordset as $key=>$value) {
            $X[] = $value[0];
            $Y[] = $value[1];
          }

          // Now convert to log-scale for X
          $logX = array_map('log', $X);

          // Now estimate $a and $b using equations from Math World
          $n = count($X);
          $square = create_function('$x', 'return pow($x,2);');
          $x_squared = array_sum(array_map($square, $logX));
          $xy = array_sum(array_map(create_function('$x,$y', 'return $x*$y;'), $logX, $Y));

          $bFit = ($n * $xy - array_sum($Y) * array_sum($logX)) /
                  ($n * $x_squared - pow(array_sum($logX), 2));

          $aFit = (array_sum($Y) - $bFit * array_sum($logX)) / $n;

          $Yfit = array();
          foreach($X as $x) {
            $Yfit[] = round($aFit + $bFit * log($x));
          }
          $result = array();
          for ($i=0;$i<$n;$i+=1) {
            $result[] = array($X[$i], $Yfit[$i]);
          }

          return $result;
        }

        function getMonth($month, $from) {
            foreach ($from as $key=>$value) {
              if ($value[0] == $month)
                return $value[1];
            }
            return 0;
        }

        function checkMonths($months) {
          global $recordset_hits_by_month, $recordset_median_hits_by_month;

          $porEncima = 0;
          $porDebajo = 0;
          $sumInteractions = 0; // Para contrastar con meses anteriores
          foreach ($months as $key=>$value) {

            $monthValue = getMonth($value, $recordset_hits_by_month);
            $sumInteractions+=$monthValue;
            if ($monthValue < getMonth($value, $recordset_median_hits_by_month))
              $porDebajo++;
            else
              $porEncima++;
          }
          return array($porEncima, $porDebajo, $sumInteractions);
        }

        function subidaYBajada($months) {
          $trim_1 = checkMonths(array($months[0]));
          $trim_2 = checkMonths(array($months[1]));
          $trim_3 = checkMonths(array($months[2]));
          $subidaEn = "";
          $bajadaEn = "";
          if ($trim_1[2]>$trim_2[2]) {
            $subidaEn = "primer";
            $bajadaEn = "segundo";
            if ($trim_3[2]>$trim_1[2])
              $subidaEn = "tercer";
            if ($trim_2[2]>$trim_3[2])
              $bajadaEn = "tercer";
          }
          elseif ($trim_1[2]>$trim_3[2]) {
            $subidaEn = "tercero";
            $bajadaEn = "primer";
            if ($trim_2[2]>$trim_3[2])
              $subidaEn = "segundo";
          } else {
            $bajadaEn = "primer";
            if ($trim_2[2]>$trim_3[2])
              $subidaEn = "segundo";
            else
              $subidaEn = "tercer";
          }
          return array($subidaEn, $bajadaEn);
        }

        function elMes($mes) {
          switch($mes) {
            case 1:
              return "Enero";
              break;
            case 2:
              return "Febrero";
              break;
            case 3:
              return "Marzo";
              break;
            case 4:
              return "Abril";
              break;
            case 5:
              return "Mayo";
              break;
            case 9:
              return "Septiembre";
              break;
            case 10:
              return "Octubre";
              break;
            case 11:
              return "Noviembre";
              break;
            case 12:
              return "Diciembre";
              break;
          }
        }

        function chartsToText() {
          global $userName, $conclusiones;
          global $recordset_hits_by_month, $recordset_median_hits_by_month, $recordset_tendency_hits_by_month;

          $desc = "Este gráfico muestra en azul todas las interacciones del alumno ".$userName." en relación a la media de las interacciones de todos los alumnos indicada en naranja, desde inicio de curso hasta la fecha actual.";
          
          $conclusiones .= "<br/><br/>Considerando las interacciones del alumno globalmente y por trimestre, se aprecia un primer trimestre en general ";
          $trim1 = checkMonths(array(9,10,11));
          if ($trim1[0]>$trim1[1])
            $conclusiones .= "por encima de la media";
          else
            $conclusiones .= "por debajo de la media";

          $trim2 = checkMonths(array(12,1,2));
          if ($trim2[0]>$trim2[1])
            $conclusiones .= ", un segundo trimestre ".( ($trim1[0]>$trim1[1])?"también":"" )." por encima de la media ".(($trim1[2]>$trim2[2])?"e inferior al anterior trimestre":"e superior al anterior trimestre");
          else
            $conclusiones .= ", un segundo trimestre ".( ($trim1[0]<=$trim1[1])?"también":"" )." por debajo de la media ".(($trim1[2]>$trim2[2])?"e inferior al anterior trimestre":"e superior al anterior trimestre");

          $trim3 = checkMonths(array(3,4,5));
          if ($trim3[0]>$trim3[1])
            $conclusiones .= " y un tercer trimestre ".( (($trim1[0]>$trim1[1]) || ($trim2[0]>$trim2[1]))?"también":"" )." por encima de la media, ".(($trim1[0]>$trim1[1])?"como en el primero":"al contrario que al primero")." y ".(($trim2[0]>$trim2[1])?"como en el segundo trimestre":"al contrario que al segundo trimestre").".";
          else
            $conclusiones .= " y un tercer trimestre ".( (($trim1[0]<=$trim1[1]) || ($trim2[0]<=$trim2[1]))?"también":"" )." por debajo de la media, ".(($trim1[0]<=$trim1[1])?"como en el primero":"al contrario que al primero")." y ".(($trim2[0]<=$trim2[1])?"como en el segundo trimestre":"al contrario que al segundo trimestre").".";

// el gráfico de tendencias confirma las aseveraciones anteriores...
// El 80% de las interacciones sociales se encuentran en la franja baja del 25% de interacciones globales. 

          $subebaja1 = subidaYBajada(array(9,10,11));
          $conclusiones.="<br/><br/>En el primer trimestre se aprecia un ".$subebaja1[0]." mes con más interacciones y un ".$subebaja1[1]." con menos.";
          
          $subebaja2 = subidaYBajada(array(12,1,2));
          $conclusiones.=" En el segundo trimestre el alumno realiza más intervenciones en el ".$subebaja2[0]." mes y menos en el ".$subebaja2[1].".";

          $subebaja3 = subidaYBajada(array(3,4,5));
          $conclusiones.=" En el tercer trimestre el alumno realiza más intervenciones en el ".$subebaja3[0]." mes y menos en el ".$subebaja3[1]."".( ($subebaja3[0]==$subebaja1[0] && $subebaja3[1]==$subebaja1[1])?", igual que en el primer trimestre":"" ).".";

          // Conseguir interacciones de cada trimestre y comparar
          $sumTrimestres = array(0, 0, 0);
          foreach ($recordset_hits_by_month as $key=>$value) {
            if (in_array($value[0], array(9,10,11)))
              $sumTrimestres[0] += $value[1];
            if (in_array($value[0], array(12,1,2)))
              $sumTrimestres[2] += $value[1];
            if (in_array($value[0], array(3,4,5)))
              $sumTrimestres[3] += $value[1];
          }

          $descEsfuerzo = "";
          if ($sumTrimestres[0]>$sumTrimestres[1] && $sumTrimestres[0]>$sumTrimestres[2])
            $descEsfuerzo .= " en el primer trimestre";
          if ($sumTrimestres[1]>$sumTrimestres[0] && $sumTrimestres[1]>$sumTrimestres[2])
            $descEsfuerzo .= " en el segundo trimestre";
          if ($sumTrimestres[3]>$sumTrimestres[0] && $sumTrimestres[3]>$sumTrimestres[1])
            $descEsfuerzo .= " en el segundo trimestre";
          
          $descRelajacion = "";
          if ($sumTrimestres[0]<$sumTrimestres[1] && $sumTrimestres[0]<$sumTrimestres[2])
            $descRelajacion .= " en el primero";
          if ($sumTrimestres[1]<$sumTrimestres[0] && $sumTrimestres[1]<$sumTrimestres[2])
            $descRelajacion .= " en el segundo";
          if ($sumTrimestres[3]<$sumTrimestres[0] && $sumTrimestres[3]<$sumTrimestres[1])
            $descRelajacion .= " en el segundo ";
          
          $conclusiones .= "<b> En cómputo global, ";
          if ($descEsfuerzo != "")
            $conclusiones .= "se realiza más esfuerzo".$descEsfuerzo;

          if (($descEsfuerzo != "")&&($descRelajacion != ""))
            $conclusiones .= " y ";  

          if ($descRelajacion != "")
            $conclusiones .= "se cae en una cierta relajación".$descRelajacion;  

            $conclusiones .= ".</b>";        
          /*
          En general el alumno 5741f40a99007 se ha mantenido constante en relación a la media de
          interacciones aunque en el primer y segundo trimestre ha aflojado en el último momento.
          No obstante, en el tercer trimestre ha realizado un esfuerzo adicional.
          */
          echo "$('#".$userName."chartInteracionsExplain').html(\"<p align='justify'>".$desc.$conclusiones."</p>\");";
          
          $desc = "Este gráfico muestra la tendencia de las interacciones del alumno ".$userName.". ".
          "En gris se muestra la línea de la tendencia. ".
          "Si la línea marca un ascenso indica que en ese tramo temporal el alumno ha realizado un esfuerzo globalmente positivo. Se indicará con un círculo verde. ".
          "En cambio, si la línea marca un descenso signifca que en ese tramo temporal el alumno ha realizado un esfuerzo globalmente menor al tramo anterior. Se indicará con un círculo rojo.";
          
          $numPositivos = array();
          $numNegativos = array();
          for ($x=0;$x<count($recordset_tendency_hits_by_month); $x++) {
            if (isset($recordset_tendency_hits_by_month[$x-1])) {
              if ($recordset_tendency_hits_by_month[$x][1]-$recordset_tendency_hits_by_month[$x-1][1] > 0)
                $numPositivos[] = $recordset_tendency_hits_by_month[$x][0];
              else
                $numNegativos[] = $recordset_tendency_hits_by_month[$x][0];
            }
          }

          $conclusionesTendency = "";
          if (count($numPositivos)>count($numNegativos))
            $conclusionesTendency .= "<br /><br /><b>La tendencia del alumno a lo largo del curso muestra un aumento de la dedicación con pequeños descensos en el esfuerzo, como en ".elMes($numNegativos[0]).".</b>";
          if (count($numPositivos)<count($numNegativos))
            $conclusionesTendency .= "<br /><br /><b>La tendencia del alumno a lo largo del curso muestra un descenso de la dedicación con pequeños ascensos en el esfuerzo, como en ".elMes($numPositivos[0]).".</b>";
          if (count($numPositivos)==count($numNegativos))
            $conclusionesTendency .= "<br /><br /><b>La tendencia del alumno a lo largo del curso muestra un equilibrio entre descensos y ascensos.</b>";

          $conclusiones .= $conclusionesTendency;

          echo "$('#".$userName."chartTendencyExplain').html(\"<p align='justify'>".$desc.$conclusionesTendency."</p>\");";
          echo "$('#".$userName."Conclusions').html(\"<p align='justify'>".$conclusiones."</p>\");";
        } 

        $usr_data = array(4);
        $usr_data[0] = '';
        $usr_data[1] = '';
        $usr_data[2] = '';
        $usr_data[3] = '';


        $masAltoInteractions = 0;

        // Interacciones alumno
        $recordset_hits_by_month = hits_by_month();
        foreach ($recordset_hits_by_month as $key=>$value) {
          // From September to July
          $yearDate = 2016;
          if (in_array($value[0], array(9,10,11,12)))
            $yearDate = 2015;
          
          $usr_data[0] .= "{x: new Date(".$yearDate.",".$value[0]."-1, 1),y:".$value[1]."},";
          if ($masAltoInteractions < $value[1])
            $masAltoInteractions = $value[1];
        }

        // Media de interacciones totales
        $recordset_median_hits_by_month = median_hits_by_month();
        foreach ($recordset_median_hits_by_month as $key=>$value) {
          // From September to July
          $yearDate = 2016;
          if (in_array($value[0], array(9,10,11,12)))
            $yearDate = 2015;

          $markTrimestre = "";
          if (in_array($value[0], array(11,2,5)))
            $markTrimestre = ', indexLabel: "fin de trimestre",markerColor: "red", markerType: "triangle"';

          $usr_data[1] .= "{x: new Date(".$yearDate.",".$value[0]."-1, 1),y:".$value[1].$markTrimestre."},";
          if ($masAltoInteractions < $value[1])
            $masAltoInteractions = $value[1];
        }


        // Tendencia de interacciones alumno
        $masAltoTendencia = 0;
        $recordset_tendency_hits_by_month = calculateTendency($recordset_hits_by_month);
        for ($x=0;$x<count($recordset_tendency_hits_by_month); $x++) {
          // From September to July
          $yearDate = 2016;
          if (in_array($recordset_tendency_hits_by_month[$x][0], array(9,10,11,12)))
            $yearDate = 2015;

          // Colores: verde (aumento), rojo (disminución)
          if (isset($recordset_tendency_hits_by_month[$x-1])) {
            $markerType = "";
            if ($recordset_tendency_hits_by_month[$x][1]-$recordset_tendency_hits_by_month[$x-1][1] > 0)
              $lineColor = ",lineColor:'green',color:'green'";
            else
              $lineColor = ",lineColor:'red',color:'red'";
          } else {$markerType = "markerType:'none',";$lineColor = "";}


          $usr_data[2] .= "{".$markerType."x: new Date(".$yearDate.",".$recordset_tendency_hits_by_month[$x][0]."-1, 1),y:".$recordset_tendency_hits_by_month[$x][1].$lineColor."},";
          if ($masAltoTendencia < $recordset_tendency_hits_by_month[$x][1])
            $masAltoTendencia = $recordset_tendency_hits_by_month[$x][1];
        }

        // Interacciones sociales
        foreach (hits_by_month('socials') as $key=>$value) {
          // From September to July
          $yearDate = 2016;
          if (in_array($value[0], array(9,10,11,12)))
            $yearDate = 2015;

          $usr_data[3] .= "{x: new Date(".$yearDate.",".$value[0]."-1, 1),y:".$value[1]."},";
        }

        // Interacciones actividades
        foreach (hits_by_month('assignments') as $key=>$value) {
          // From September to July
          $yearDate = 2016;
          if (in_array($value[0], array(9,10,11,12)))
            $yearDate = 2015;

          $usr_data[4] .= "{x: new Date(".$yearDate.",".$value[0]."-1, 1),y:".$value[1]."},";
        }

        // Interacciones recursos
        foreach (hits_by_month('resources') as $key=>$value) {
          // From September to July
          $yearDate = 2016;
          if (in_array($value[0], array(9,10,11,12)))
            $yearDate = 2015;

          $usr_data[5] .= "{x: new Date(".$yearDate.",".$value[0]."-1, 1),y:".$value[1]."},";
        }

        // Interacciones informes
        foreach (hits_by_month('reports') as $key=>$value) {
          // From September to July
          $yearDate = 2016;
          if (in_array($value[0], array(9,10,11,12)))
            $yearDate = 2015;

          $usr_data[6] .= "{x: new Date(".$yearDate.",".$value[0]."-1, 1),y:".$value[1]."},";
        }

        echo "addUserCharts('".$userName."', '".$userName."', [[".$usr_data[0]."], [".$usr_data[1]."], [".$usr_data[2]."], [".$usr_data[3]."], [".$usr_data[4]."], [".$usr_data[5]."], [".$usr_data[6]."], [".$numInteractionsSocials[0].",".$numInteractionsSocials[1]."], [".$numInteractionsAssignments[0].",".$numInteractionsAssignments[1]."], [".$numInteractionsResources[0].",".$numInteractionsResources[1]."], [".$numInteractionsReports[0].",".$numInteractionsReports[1]."]], [".$masAltoInteractions.", ".$masAltoTendencia."]);\n";

        chartsToText($recordset_hits_by_month, $recordset_median_hits_by_month);
      ?>
    }
  </script>