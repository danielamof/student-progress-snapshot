          <?

          $numInteractions = num_of_course_interactions();
          $numInteractionsSocials = num_of_course_interactions('socials');
          $numInteractionsAssignments = num_of_course_interactions('assignments');
          $numInteractionsResources = num_of_course_interactions('resources');
          $numInteractionsReports = num_of_course_interactions('reports');
          ?>
          <div>
            <div style="float:left">
              <b>Total Users</b>&nbsp;<?= num_of_course_users(); ?>&nbsp;&nbsp;|&nbsp;&nbsp;
            </div>
            <div style="float:left">
              <b>Total Interactions</b>&nbsp;<?= number_from_locale($numInteractions[0]+$numInteractions[1]); ?>&nbsp;&nbsp;|&nbsp;&nbsp;
            </div>
            <div style="float:left">
              <b>Total Forum Interactions</b>&nbsp;<?= number_from_locale($numInteractionsSocials[0]+$numInteractionsSocials[1]); ?>&nbsp;&nbsp;|&nbsp;&nbsp;
            </div>
            <div style="float:left">
              <b>Total Assignments Interactions</b>&nbsp;<?= number_from_locale($numInteractionsAssignments[0]+$numInteractionsAssignments[1]); ?>&nbsp;&nbsp;
            </div>
          </div>
          <br />
          <div style="clear:both" id="charts">

          </div>