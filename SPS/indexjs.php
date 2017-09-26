<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.2.3/jquery.min.js"></script>
    <script type="text/javascript" src="js/canvasjs/canvasjs.js"></script>
    <script type="text/javascript" src="js/canvasjs/excanvas.js"></script>
    <script type="text/javascript" src="js/canvasjs/jquery.canvasjs.js"></script>
    <script type="text/javascript">

    function drawLineChart(divId, data, dataTitle) {
      var backgroundColorChart = "#FFFFFF";
      if (data.length == 0) {
        data = new Array();
        backgroundColorChart = "#CC0000";
      }
      var chart = new CanvasJS.Chart(divId,
      {
        culture:"es",
        backgroundColor: backgroundColorChart,
        title:{
        text: dataTitle
        },
        axisX: {
          valueFormatString: "MMM",
          interval:1,
          intervalType: "month"
        },
        axisY:{
          includeZero: false,
          maximum: 1000 

        },
        data: [
        {
          type: "line",

          dataPoints: data
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
          showInLegend: false,
          legendText: "{indexLabel}",
          dataPoints: [
            { y: <?= $numInteractionsSocials; ?>, indexLabel: "All users" },
            { y: data, indexLabel: "This user" }
          ]
        }
        ]
      });
      chart.render();
    }

    function addUserCharts(userId, userName, data, totalForumsInteractions) {
        var height = "100px";
        var width = "150px";
        var htmlData = '<div style="float:left;border: 1px #000000 solid;display:inline-block;margin:10px;padding:10px">';
        htmlData += '<div style="float:left;width:200px;word-break: keep-all; word-wrap: normal;"><h2><a href="student.php?username='+userName+'">'+userName+'</a></h2></div>';
        htmlData += '<div id="'+userId+'chartInteracions" style="float:left;height: '+height+'; width: '+width+';"></div>';
        htmlData += '<div id="'+userId+'chartForumsInteractionsPerMonth" style="float:left;height: '+height+'; width: '+width+';"></div>';
        htmlData += '<div id="'+userId+'chartAssignmentsInteractionsPerMonth" style="float:left;height: '+height+'; width: '+width+';"></div>';
        htmlData += '<div id="'+userId+'chartAPieInteractions" style="float:left;height: '+height+'; width: '+width+';"></div>';
        htmlData += '</div>';
        $("#charts").append(htmlData);
        drawLineChart(userId+'chartInteracions', data[0], "Total Interactions");
        drawLineChart(userId+'chartForumsInteractionsPerMonth', data[1], "Forums Interactions");
        drawLineChart(userId+'chartAssignmentsInteractionsPerMonth', data[2], "Assignments Interactions");
        drawPieChart(userId+'chartAPieInteractions', totalForumsInteractions, "% Forum Interactions");
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
      //exit;
        $users = course_users_list();
        foreach ($users as $keyUser=>$userName) {
            $usr_data = array(3);
            $usr_data[0] = '';
            $usr_data[1] = '';
            $usr_data[2] = '';

            foreach (hits_by_month('', $userName) as $key=>$value) {
              // From September to July
              $yearDate = 2016;
              if (in_array($value[0], array(9,10,11,12)))
                $yearDate = 2015;

              $usr_data[0] .= "{x: new Date(".$yearDate.",".$value[0]."-1, 1),y:".$value[1]."},";
            }
            $totalForumsInteractions = 0;
            foreach (hits_by_month('socials', $userName) as $key=>$value) {
              // From September to July
              $yearDate = 2016;
              if (in_array($value[0], array(9,10,11,12)))
                $yearDate = 2015;

              $usr_data[1] .= "{x: new Date(".$yearDate.",".$value[0]."-1, 1),y:".$value[1]."},";
              $totalForumsInteractions += $value[1];
            }
            foreach (hits_by_month('assignments', $userName) as $key=>$value) {
              // From September to July
              $yearDate = 2016;
              if (in_array($value[0], array(9,10,11,12)))
                $yearDate = 2015;
              
              $usr_data[2] .= "{x: new Date(".$yearDate.",".$value[0]."-1, 1),y:".$value[1]."},";
            }

            echo "addUserCharts('".$userName."', '".$userName."', [[".$usr_data[0]."], [".$usr_data[1]."], [".$usr_data[2]."]], ".$totalForumsInteractions.");\n";


        }

      ?>
    }
  </script>