<table class='table table-striped'>
<thead>
  <tr>
    <?php foreach($ccols as $ccol){ ?>
    <th><?php echo $clabels[$ccol];?></th>
    <?php } ?>
  </tr>
</thead>
<tbody>
  <?php foreach($crows as $crow){ ?>
  <tr>
    <?php foreach($ccols as $ccol){ ?>
    <td>
      <?php
        if($ccol == 'result'){
          echo CHtml::link($crow[$ccol],array($crow['link']));
        } else {
          echo $crow[$ccol];
        }
      ?>
    </td>
    <?php } ?>
  </tr>
  <?php } ?>
</tbody>
</table>    


