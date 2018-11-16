<?php
// url prueba: http://localhost:2891/semillas?familia=vaAliaceas,Apiaceae,Asparagaceae&arqueo=nn&especie=coa&otro=otra&fotos=be1,4
if (isset($filtroDef)) { 
  $parsedFilters = array();
?>
<div class="modal fade modal-filters" tabindex="-1" role="dialog" aria-hidden="true" style="display: none;">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">

      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
        <h4 class="modal-title" id="myModalLabel2">Title</h4>
      </div>
      <div class="modal-body">
        <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">

<?php
  $iCount = 0;
  foreach ($filtroDef as $fName=>$filtro) { 
    $iCount++;
    $values = "";
    $ope = "";
    if (array_key_exists($fName, $_GET)) {
      $ope = strtolower(substr($_GET[$fName], 0, 2));
      $values = substr(strtolower($_GET[$fName]), 2);
      $parsedFilters[] = strtolower($filtro["descripcion"]) . " " . $filtro["parsed"];
    }
?>
          <div class="panel panel-default">
            <div class="panel-heading" role="tab" id="heading<?php echo $fName; ?>">
              <h4 class="panel-title">
              <a <?php if ($iCount > 1) echo "class=\"collapsed\" "; ?>role="button" data-toggle="collapse" data-parent="#accordion" href="#collapse<?php echo $fName; ?>" aria-expanded="true" aria-controls="collapse<?php echo $fName; ?>">
                <?php echo $filtro['descripcion'];?> <small><?php echo $filtro["parsed"]; ?></small>
              </a>
              </h4>
            </div>
            <div id="collapse<?php echo $fName; ?>" class="panel-collapse collapse<?php if ($iCount == 1) echo " in"; ?>" role="tabpanel" aria-labelledby="heading<?php echo $fName; ?>">
              <div class="panel-body">
                <select id="accion-<?php echo $fName; ?>" name="accion-<?php echo $fName; ?>" data-tipo="<?php echo $filtro["tipo"]; ?>">
                  <option value="" <?php if ($ope == "") echo "selected"; ?>>Sin filtrar</option>
                  <?php foreach ($filtro["acciones"] as $k=>$v) { ?>
                  <option value="<?php echo $k;?>" <?php if ($k == $ope) echo "selected"; ?>><?php echo $v; ?></option>
                  <?php } ?>
                </select>

                <?php
              switch (strtolower($filtro["tipo"])) {
                case "list": 
                  if ($ope == "va") $values=explode(",", $values); else $values=array(); ?>
                <select multiple id="accion-<?php echo $fName; ?>-list">
                  <?php foreach ($filtro["values"] as $row) { ?>
                  <option value="<?php echo $row['name'];?>" <?php if (in_array(strtolower($row['name']), $values)) echo "selected"; ?>><?php echo $row['name']; ?></option>
                  <?php } ?>
                </select>
    
              <?php   break;
                case "number": 
                  if ($ope == "be") $values=explode(",", $values); ?>
                <input type="number" id="accion-<?php echo $fName; ?>-number" value="<?php if (is_array($values)) echo $values[0]; else if ($values=="") echo "0"; else echo $values; ?>" />
                <input type="number" id="accion-<?php echo $fName; ?>-number-2" value="<?php if (is_array($values)) echo $values[1]; else echo "0"; ?>" />
                  
              <?php   break;
                case "date": 
                  if ($ope == "be") $values=array(substr($values,0,8), substr($values,8,8)); ?>
                <input type="date" id="accion-<?php echo $fName; ?>-date" value="<?php if (is_array($values)) echo substr($values[0],0,4) . "-" . substr($values[0],4,2) . "-" . substr($values[0],6,2); else if ($values=="") echo date("Y-m-d"); else echo substr($values,0,4) . "-" . substr($values,4,2) . "-" . substr($values,6,2); ?>"/>
                <input type="date" id="accion-<?php echo $fName; ?>-date-2" value="<?php if (is_array($values)) echo substr($values[1],0,4) . "-" . substr($values[1],4,2) . "-" . substr($values[1],6,2); else echo date("Y-m-d"); ?>" />
              
              <?php   break;
                case "text":  ?>
                <input type="text" id="accion-<?php echo $fName; ?>-text" value="<?php echo $values; ?>" />

              <?php   break;
              } ?>

              </div>
            </div>
          </div>

<script type="text/javascript">
  $('#accion-<?php echo $fName; ?>').on('input', function (event) {
<?php 
switch (strtolower($filtro["tipo"])) {
  case "list":  /********************************************************************************************************************************/ ?>
    switch ($(this).val()) {
      case "":
      case "nn":
      case "nu":
        $("#" + $(this).attr('id') + "-list").hide();
        break;
      default:
        $("#" + $(this).attr('id') + "-list").show();
        break;
    }
<?php   break;
  case "number": /********************************************************************************************************************************/  ?>
    switch ($(this).val()) {
      case "be":
        $("#" + $(this).attr('id') + "-number").show();
        $("#" + $(this).attr('id') + "-number-2").show();
        break;
      case "":
      case "nn":
      case "nu":
        $("#" + $(this).attr('id') + "-number").hide();
        $("#" + $(this).attr('id') + "-number-2").hide();
        break;
      default:
        $("#" + $(this).attr('id') + "-number").show();
        $("#" + $(this).attr('id') + "-number-2").hide();
        break;
    }
<?php   break;
  case "date": /********************************************************************************************************************************/ ?>
    switch ($(this).val()) {
      case "be":
        $("#" + $(this).attr('id') + "-date").show();
        $("#" + $(this).attr('id') + "-date-2").show();
        break;
      case "":
      case "nn":
      case "nu":
        $("#" + $(this).attr('id') + "-date").hide();
        $("#" + $(this).attr('id') + "-date-2").hide();
        break;
      default:
        $("#" + $(this).attr('id') + "-date-2").hide();
        $("#" + $(this).attr('id') + "-date").show();
        break;
    }
<?php   break;
  case "text": /********************************************************************************************************************************/ ?>
    switch ($(this).val()) {
      case "":
      case "nn":
      case "nu":
        $("#" + $(this).attr('id') + "-text").hide();
        break;
      default:
        $("#" + $(this).attr('id') + "-text").show();
        break;
    }
<?php   break;
  }
?>
  });

  var fnUpdateFilterTitle = function (event) {
    var newText = $('#accion-' + event.data[0] + ' option:selected').text();
    var operation = $('#accion-' + event.data[0]).val().toLowerCase();
    switch (event.data[1].toLowerCase()) {
      case "list":
        if (operation=="va") {
          var selectedValues = $('#accion-' + event.data[0] + '-list').val();
          if (selectedValues) { 
          	newText += " ('" + selectedValues.join("','").toLowerCase() + "')";
          } else {
          	newText = $('#accion-' + event.data[0] + ' option:first-child').text();
          }
        }
        break;

      case "date":
        switch (operation) {
          case "be": 
            var date1 = $('#accion-' + event.data[0] + '-date').val();
            var date2 = $('#accion-' + event.data[0] + '-date-2').val();
            newText += ' ' + date1.substring(8,10) + '-' + date1.substring(5,7) + '-' + date1.substring(0,4);
            newText += ' y ' + date2.substring(8,10) + '-' + date2.substring(5,7) + '-' + date2.substring(0,4);
            break;

          case "eq": 
          case "ne": 
          case "gt": 
          case "lt": 
            var date1 = $('#accion-' + event.data[0] + '-date').val();
            newText += ' ' + date1.substring(8,10) + '-' + date1.substring(5,7) + '-' + date1.substring(0,4);
            break;
          }
          break;

      case "text":
        if (operation=="eq" || operation=="co") {
          var selectedValues = $('#accion-' + event.data[0] + '-text').val();
          newText += " '" + selectedValues;
        }
        break;

      case "number":

    }
    $('#heading' + event.data[0] + ' h4.panel-title small').text(newText);
  };

  $('#accion-<?php echo $fName; ?>').trigger('input');

  $('#accion-<?php echo $fName; ?>').on('input'         , null, ['<?php echo $fName; ?>','<?php echo strtolower($filtro["tipo"]); ?>'], fnUpdateFilterTitle);
  $('#accion-<?php echo $fName; ?>-list').on('input'    , null, ['<?php echo $fName; ?>','<?php echo strtolower($filtro["tipo"]); ?>'], fnUpdateFilterTitle);
  $('#accion-<?php echo $fName; ?>-number').on('input'  , null, ['<?php echo $fName; ?>','<?php echo strtolower($filtro["tipo"]); ?>'], fnUpdateFilterTitle);
  $('#accion-<?php echo $fName; ?>-number-2').on('input', null, ['<?php echo $fName; ?>','<?php echo strtolower($filtro["tipo"]); ?>'], fnUpdateFilterTitle);
  $('#accion-<?php echo $fName; ?>-date').on('input'    , null, ['<?php echo $fName; ?>','<?php echo strtolower($filtro["tipo"]); ?>'], fnUpdateFilterTitle);
  $('#accion-<?php echo $fName; ?>-date-2').on('input'  , null, ['<?php echo $fName; ?>','<?php echo strtolower($filtro["tipo"]); ?>'], fnUpdateFilterTitle);
  $('#accion-<?php echo $fName; ?>-text').on('input'    , null, ['<?php echo $fName; ?>','<?php echo strtolower($filtro["tipo"]); ?>'], fnUpdateFilterTitle);

</script>

<?php
  } // closes -> foreach 
?>
        </div>
      </div>
      <div class="modal-footer"> 
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="submit-filters">Apply filters</button>
      </div>

    </div>
  </div> 
</div>            

<?php 
  $newQS = "";
  foreach ($_GET as $k=>$v) if (! array_key_exists($k, $filtroDef)) $newQS .= "$k=$v&";
  if (strlen($newQS)>0) $newQS = substr($newQS, 0, -1);
?>

<script type="text/javascript">
  $('#submit-filters').on('click', function(event) {
    var newQueryString = "<?php echo $newQS; ?>";
    var newQueryStringTemp = "";

<?php
    //loopear por cada filtro con GET
    //por cada uno analizar el tipo y concatenar el parametro en newQueryStringTemp con un & al final
    foreach ($filtroDef as $fName=>$filtro) {  ?>
      var current<?php echo $fName; ?>Operation = $('#accion-<?php echo $fName; ?>').val().toLowerCase();
<?php switch (strtolower($filtro["tipo"])) {
        case "list":  /********************************************************************************************************************************/
?>        
      switch (current<?php echo $fName; ?>Operation) {
        case "va": 
          if ($('#accion-<?php echo $fName; ?>-list').val()) {
          	newQueryStringTemp += '<?php echo $fName; ?>=va' +  $('#accion-<?php echo $fName; ?>-list').val() + '&';
          }
          break;
        case "nn": 
        case "nu": 
          newQueryStringTemp += '<?php echo $fName; ?>=' + current<?php echo $fName; ?>Operation + '&';
          break;
      }
<?php
          break;
        case "number": /*******************************************************************************************************************************/
?>        
      switch (current<?php echo $fName; ?>Operation) {
        case "be": 
          newQueryStringTemp += '<?php echo $fName; ?>=be' + $('#accion-<?php echo $fName; ?>-number').val() + ',' + $('#accion-<?php echo $fName; ?>-number-2').val() + '&';
          break;
        case "eq": 
        case "gt": 
        case "lt": 
          newQueryStringTemp += '<?php echo $fName; ?>=' + current<?php echo $fName; ?>Operation + $('#accion-<?php echo $fName; ?>-number').val() + '&';
          break;
        case "nn": 
        case "nu": 
          newQueryStringTemp += '<?php echo $fName; ?>=' + current<?php echo $fName; ?>Operation + '&';
          break;
      }
<?php
          break;
        case "date":  /********************************************************************************************************************************/
?>        
      switch (current<?php echo $fName; ?>Operation) {
        case "be": 
          newQueryStringTemp += '<?php echo $fName; ?>=be' + $('#accion-<?php echo $fName; ?>-date').val().replace("-","").replace("-","") + $('#accion-<?php echo $fName; ?>-date-2').val().replace("-","").replace("-","") + '&';
          break;
        case "eq": 
        case "ne": 
        case "gt": 
        case "lt": 
          newQueryStringTemp += '<?php echo $fName; ?>=' + current<?php echo $fName; ?>Operation + $('#accion-<?php echo $fName; ?>-date').val().replace("-","").replace("-","") + '&';
          break;
        case "nn": 
        case "nu": 
          newQueryStringTemp += '<?php echo $fName; ?>=' + current<?php echo $fName; ?>Operation + '&';
          break;
      }
<?php
          break;
        case "text":  /********************************************************************************************************************************/
?>        
      switch (current<?php echo $fName; ?>Operation) {
        case "eq": 
        case "co": 
          newQueryStringTemp += '<?php echo $fName; ?>=' + current<?php echo $fName; ?>Operation + $('#accion-<?php echo $fName; ?>-text').val() + '&';
          break;
        case "nn": 
        case "nu": 
          newQueryStringTemp += '<?php echo $fName; ?>=' + current<?php echo $fName; ?>Operation + '&';
          break;
      }
<?php
          break;
      } // closes -> switch (strtolower($filtro["tipo"]))
    } // closes -> foreach ($filtroDef as $fName=>$filtro)
?>  
      if (newQueryStringTemp != '') newQueryStringTemp = newQueryStringTemp.substring(0, newQueryStringTemp.length-1);
      if (newQueryString != '') {
        if (newQueryStringTemp != '') newQueryString += '&' + newQueryStringTemp;
      } else {
        if (newQueryStringTemp != '') newQueryString += newQueryStringTemp;
      }

      if (newQueryString != '') {
        window.location.assign(window.location.pathname+"?"+newQueryString);
      } else {
        window.location.assign(window.location.pathname);
      }
  }); <?php /* closes --> $('#submit-filters').on('click' */ ?>

</script>


<?php
  //armo el codigo para un boton de filtros (para usarse en la vista especifica)
  $filtersButton = "<button type=\"button\" class=\"btn btn-default\" data-toggle=\"modal\" data-target=\".modal-filters\">";
  $filtersButton.= "<span class=\"glyphicon glyphicon-filter\" title=\"";
  if (count($parsedFilters) > 0) {
    $filtersButton.= implode(",", $parsedFilters);
  } else {
    $filtersButton.= "Filtro";
  }
  $filtersButton.= "\"></span>";
  if (count($parsedFilters > 0)) $filtersButton.= "<span class=\"badge\">" . count($parsedFilters) . "</span>";
  $filtersButton.= "</button>";
} // closes -> if (isset($filtroDef)) 
?>