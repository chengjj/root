<?php

/**
 * @params:
 *   $activities
 */
?>
<ul>
  <?php foreach ($activities as $activity) { ?>
  <li><?php print theme('activity_item', array('activity' => $activity)); ?></li>
  <?php } ?>
</ul>
