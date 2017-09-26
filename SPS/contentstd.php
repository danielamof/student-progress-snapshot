      <?php
        
        // Resumen de los textos
        $conclusiones = "";

        function formatInteractions($interactions, $detail = false) {
          if (!$detail)
            echo number_from_locale($interactions[0]+$interactions[1]);
          else
            echo "&nbsp;(".number_from_locale($interactions[0])." lecturas / ".number_from_locale($interactions[1])." envíos)";
        }

        function formatDedication($time) {
          if ($time < 1) {
              return "0m";
          }
          $hours = floor($time / 60);
          $minutes = ($time % 60);
          if ($hours == 0)
            return sprintf(($minutes<10)?"%01dm":"%02dm", $minutes);
          else
            return sprintf("%02dh %02dm", $hours, $minutes);
        }

        function analyticsLight($max, $num) {
          if ($num <= ($max)*25/100)
            return "tdRedLight ".$max;
          elseif ($num <= ($max)*50/100)
            return "tdOrangeLight ".$max;
          elseif ($num <= ($max)*75/100)
            return "tdGreenLight ".$max;
          else
            return "tdWhiteLight ".$max;
        }

        function leyendaDeColores() {
        ?>
                <br />
                <b>Leyenda de colores:</b>
                <table>
                  <tr>
                    <td class="tdRedLight">Está entre el 0-25% de las interacciones de todos los alumnos.</td>
                    <td class="tdOrangeLight">Está entre el 26-50% de las interacciones de todos los alumnos.</td>
                    <td class="tdGreenLight">Está entre el 51-75% de las interacciones de todos los alumnos.</td>
                    <td class="tdWhiteLight">Está entre el 76-100% de las interacciones de todos los alumnos.</td>
                  </tr>
                </table>
        <?
        }

        // Info del curso
        $numUsers = num_of_course_users();

        // Info del alumno
        $numInteractions = num_of_course_interactions();
        $numInteractionsSocials = num_of_course_interactions('socials');
        $numInteractionsAssignments = num_of_course_interactions('assignments');
        $numInteractionsResources = num_of_course_interactions('resources');
        $numInteractionsReports = num_of_course_interactions('reports');

      ?>
      <div style="margin:auto;width:750px">
          <div style="float:left;padding:10px;margin-bottom:10px;border:1px #000000 solid;background:lightgrey;display:inline-block;width:720px;text-align:center">
            <h1>Perfil analítico del alumno <?= $userName; ?></h1>
          </div>
          <div><h1>1.- Resumen de interacciones</h1></div>
          <div style="clear:both;padding:10px;margin-top:10px;margin-right:10px;width:250px;height:240px;border:1px #000000 solid;background:lightgrey;float:left;text-align:left">
            Se entiende por interacciones toda acción llevada a cabo por el alumno relacionadas con aspectos Sociales (mensajes enviados a foros...), de Actividades (tareas enviadas...), de Recursos (páginas vistas...) o de Informes (perfiles de usuario vistos...).
          </div>
          <div style="padding:10px;margin-top:10px;height:240px;border:1px #000000 solid;background:lightgrey;display:inline-block;width:440px">
            <div>
              <table border="0" style="border:0px;">
                <tr>
                  <td><b>Dedicación total:</b></td>
                  <td style="text-align:right"><?= $dedicacionTotalAlumno = formatDedication(time_dedication_total()); ?></td>
                </tr>
                <tr>
                  <td><b>Interacciones Totales:</b></td>
                  <td style="text-align:right"><?=formatInteractions($numInteractions);?></td>
                  <td><?=formatInteractions($numInteractions, true);?></td>
                </tr>
                <tr>
                  <td><b>Interacciones Sociales:</b></td>
                  <td style="text-align:right"><? formatInteractions($numInteractionsSocials);?></td>
                  <td><?=formatInteractions($numInteractionsSocials, true);?></td>
                </tr>
                <tr>
                  <td><b>Interacciones en Actividades</b>:</td>
                  <td style="text-align:right"><?=formatInteractions($numInteractionsAssignments);?></td>
                  <td><?=formatInteractions($numInteractionsAssignments, true);?></td>
                </tr>
                <tr>
                  <td><b>Interacciones en Recursos</b>:</td>
                  <td style="text-align:right"><?=formatInteractions($numInteractionsResources);?></td>
                  <td><?=formatInteractions($numInteractionsResources, true);?></td>
                </tr>
                <tr>
                  <td><b>Interacciones en Informes</b>:</td>
                  <td style="text-align:right"><?=formatInteractions($numInteractionsReports);?></td>
                  <td><?=formatInteractions($numInteractionsReports, true);?></td>
                </tr>
                <tr>
                  <td colspan="3" style="background-color:white">
                    <?
                      $conclusiones = "A lo largo de las ".$dedicacionTotalAlumno." dedicadas al curso el alumno ".$userName." ha realizado ".number_from_locale($numInteractions[0]+$numInteractions[1])." interacciones de las ".
                    "cuales un ".number_from_locale(round((($numInteractionsSocials[0]+$numInteractionsSocials[1])*100)/($numInteractions[0]+$numInteractions[1]),2))."% son Sociales, ".
                     "un ".number_from_locale(round((($numInteractionsAssignments[0]+$numInteractionsAssignments[1])*100)/($numInteractions[0]+$numInteractions[1]),2))."% son en Actividades, ".
                     "un ".number_from_locale(round((($numInteractionsResources[0]+$numInteractionsResources[1])*100)/($numInteractions[0]+$numInteractions[1]),2))."% son en Recursos y ".
                     "un ".number_from_locale(round((($numInteractionsReports[0]+$numInteractionsReports[1])*100)/($numInteractions[0]+$numInteractions[1]),2))."% son en Informes de datos.";
                      echo $conclusiones;
                    ?>
                   </td>
                </tr>
              </table>
            </div>
          </div>
          <div><h1>2.- Visualizaciones de las interacciones y tendencia a lo largo del curso</h1></div>
          <div style="clear:both;float:left;margin-top:10px;margin-bottom:10px;">
            <div style="width:720px;padding:10px;border:1px #000000 solid;background:lightgrey">
              <div style="height:150px" id="<?= $userName; ?>chartInteracions">

              </div>     
              <div id="<?= $userName; ?>chartInteracionsExplain"></div>         
            </div>
            <div style="margin-top:10px;width:720px;padding:10px;border:1px #000000 solid;background:lightgrey">
              <div style="height:150px" id="<?= $userName; ?>chartTendency">

              </div>     
              <div id="<?= $userName; ?>chartTendencyExplain"></div>         
            </div>
            <div><h1>3.- Visualizaciones detalladas de las interacciones Sociales, en Actividades, en Recursos y en Informes </h1></div>
            <!-- SOCIALES -->
            <div style="margin-top:10px;width:720px;padding:10px;border:1px #000000 solid;background:lightgrey">
              <div id="<?= $userName; ?>tableSocialInteractions">
                <h2 align="center">Detalles de las interacciones Sociales</h2>
                <div style="margin:auto;width:700px;height:170px">
                  <div>
                    <div style="height:200px;width:300px;float:left" id="<?= $userName; ?>chartSocialInteractions">
                    </div>
                  </div>
                  <div>
                    <div style="height:200px;width:400px;float:left" id="<?= $userName; ?>lineSocialInteractions">
                    </div>
                  </div>
                </div>
                <table>
                  <tr class="trHeader">
                    <td><b>Actividad</b></td>
                    <td><b>Lecturas</b></td>
                    <td><b>Envíos</b></td>
                    <td><b>Dedicación</b></td>
                  </tr>
                <?
                  $recordset = list_course_interactions('socials');
                  foreach ($recordset as $key=>$value) {
                    $maxTotalInteractionsSocials = max_total_course_interactions($value["EVENT_CONTEXT"], 'socials');
                ?>
                  <tr>
                    <td title="hey"><?=$value["EVENT_CONTEXT"];?></td>
                    <td class="tdViewRead <?=analyticsLight($maxTotalInteractionsSocials[0], $value["EVENT_CONTEXT_READ"]);?>"><?=$value["EVENT_CONTEXT_READ"];?></td>
                    <td class="tdViewRead <?=analyticsLight($maxTotalInteractionsSocials[2], $value["EVENT_CONTEXT_WRITE"]);?>"><?=$value["EVENT_CONTEXT_WRITE"];?></td>
                    <td class="tdViewRead"><?=formatDedication(time_dedication_total(false, $value["EVENT_CONTEXT"]));?></td>
                  </tr>
                <?
                  }
                ?>
                </table>
                <?=leyendaDeColores();?>
              </div>     
              <div id="<?= $userName; ?>tableSocialInteractionsExplain"></div>         
            </div>
            <!-- ACTIVIDADES -->
            <div style="margin-top:10px;width:720px;padding:10px;border:1px #000000 solid;background:lightgrey">
              <div id="<?= $userName; ?>tableAssignmentsInteractions">
                <h2 align="center">Detalles de las interacciones en Actividades</h2>
                <div style="margin:auto;width:700px;height:170px">
                  <div>
                    <div style="height:200px;width:300px;float:left" id="<?= $userName; ?>chartAssignmentInteractions">
                    </div>
                  </div>
                  <div>
                    <div style="height:200px;width:400px;float:left" id="<?= $userName; ?>lineAssignmentInteractions">
                    </div>
                  </div>
                </div>
                <table>
                  <tr class="trHeader">
                    <td><b>Actividad</b></td>
                    <td><b>Lecturas</b></td>
                    <td><b>Envíos</b></td>
                    <td><b>Dedicación</b></td>
                  </tr>
                <?
                  $recordset = list_course_interactions('assignments');
                  foreach ($recordset as $key=>$value) {
                    $maxTotalInteractionsAssignments = max_total_course_interactions($value["EVENT_CONTEXT"], 'assignments');
                ?>
                  <tr>
                    <td><?=$value["EVENT_CONTEXT"];?></td>
                    <td class="tdViewRead <?=analyticsLight($maxTotalInteractionsAssignments[0], $value["EVENT_CONTEXT_READ"]);?>"><?=$value["EVENT_CONTEXT_READ"];?></td>
                    <td class="tdViewRead <?=analyticsLight($maxTotalInteractionsAssignments[2], $value["EVENT_CONTEXT_WRITE"]);?>"><?=$value["EVENT_CONTEXT_WRITE"];?></td>
                    <td class="tdViewRead"><?=formatDedication(time_dedication_total(false, $value["EVENT_CONTEXT"]));?></td>
                  </tr>
                <?
                  }
                ?>
                </table>
                <?=leyendaDeColores();?>
              </div>     
              <div id="<?= $userName; ?>tableAssignmentsInteractionsExplain"></div>         
            </div>
            <!-- RECURSOS -->
            <div style="margin-top:10px;width:720px;padding:10px;border:1px #000000 solid;background:lightgrey">
              <div id="<?= $userName; ?>tableAssignmentsInteractions">
                <h2 align="center">Detalles de las interacciones en Recursos</h2>
                <div style="margin:auto;width:700px;height:170px">
                  <div>
                    <div style="height:200px;width:300px;float:left" id="<?= $userName; ?>chartResourceInteractions">
                    </div>
                  </div>
                  <div>
                    <div style="height:200px;width:400px;float:left" id="<?= $userName; ?>lineResourceInteractions">
                    </div>
                  </div>
                </div>
                <table>
                  <tr class="trHeader">
                    <td><b>Recurso</b></td>
                    <td><b>Lecturas</b></td>
                    <td><b>Dedicación</b></td>
                  </tr>
                <?
                  $recordset = list_course_interactions('resources');
                  foreach ($recordset as $key=>$value) {
                    $maxTotalInteractionsResources = max_total_course_interactions($value["EVENT_CONTEXT"], 'resources');
                ?>
                  <tr>
                    <td><?=$value["EVENT_CONTEXT"];?></td>
                    <td class="tdViewRead <?=analyticsLight($maxTotalInteractionsResources[0], $value["EVENT_CONTEXT_READ"]);?>"><?=$value["EVENT_CONTEXT_READ"];?></td>
                    <td class="tdViewRead"><?=formatDedication(time_dedication_total(false, $value["EVENT_CONTEXT"]));?></td>
                  </tr>
                <?
                  }
                ?>
                </table>
                <?=leyendaDeColores();?>
              </div>     
              <div id="<?= $userName; ?>tableResourcesInteractionsExplain"></div>         
            </div>
            <!-- INFORMES -->
            <div style="margin-top:10px;width:720px;padding:10px;border:1px #000000 solid;background:lightgrey">
              <div id="<?= $userName; ?>tableReportsInteractions">
                <h2 align="center">Detalles de las interacciones en Informes</h2>
                <div style="margin:auto;width:700px;height:170px">
                  <div>
                    <div style="height:200px;width:300px;float:left" id="<?= $userName; ?>chartReportInteractions">
                    </div>
                  </div>
                  <div>
                    <div style="height:200px;width:400px;float:left" id="<?= $userName; ?>lineReportInteractions">
                    </div>
                  </div>
                </div>
                <table>
                  <tr class="trHeader">
                    <td><b>Informe</b></td>
                    <td><b>Lecturas</b></td>
                    <td><b>Dedicación</b></td>
                  </tr>
                <?
                  $recordset = list_course_interactions('reports');
                  foreach ($recordset as $key=>$value) {
                    $maxTotalInteractionsReports = max_total_course_interactions($value["EVENT_CONTEXT"], 'reports');
                    if ($value["EVENT_CONTEXT_READ"] > 0 ) {
                ?>
                  <tr>
                    <td><?=$value["EVENT_CONTEXT"];?></td>
                    <td class="tdViewRead <?=analyticsLight($maxTotalInteractionsReports[0], $value["EVENT_CONTEXT_READ"]);?>"><?=$value["EVENT_CONTEXT_READ"];?></td>
                    <td class="tdViewRead"><?=formatDedication(time_dedication_total(false, $value["EVENT_CONTEXT"]));?></td>
                  </tr>
                <?
                    }
                  }
                ?>
                </table>
                <?=leyendaDeColores();?>
              </div>     
              <div id="<?= $userName; ?>tableReportsInteractionsExplain"></div>         
            </div>
            <h1>4.- Conclusiones</h1>
            <!-- CONCLUSIONES -->
            <div style="margin-top:10px;width:720px;padding:10px;border:1px #000000 solid;background:lightgrey">
              <div id="<?= $userName; ?>Conclusions"></div>         
            </div>
          </div>
        </div>