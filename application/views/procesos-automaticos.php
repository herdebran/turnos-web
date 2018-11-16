<?php
$tipexaValue = isset($_POST['tipexa']) ? $_POST['tipexa'] : $viewData[0]['tipexa'];
$fechaValue = isset($_POST['fecha']) ? $_POST['fecha'] : $viewData[0]['fecha'];
?>
 <style>
input { text-transform: uppercase; }
.small, small { font-size: 82% !important;  }
</style>
<?php if (count($validationErrors) > 0) { ?>
    <div class="alert alert-danger" role="alert">
        <?php foreach ($validationErrors as $error) { ?>
            <p><?php echo $error; ?>
            <?php } ?>
    </div>
<?php } ?>
<?php
// arreglo de anios (del actual hasta 10 antes)
$anios = array();
for ($i = 0; $i < 20; $i++) {
    $anios[] = date('Y') - $i;
}
?>
<fieldset>
  <h3>Procesos automáticos</h3>
    <div class="panel panel-default">
      <div class="panel-body">
        <table class="table table-striped table-hover table-condensed" id="table-resultados">
          <thead>
            <tr>
              
              <th>Proceso</th>
              <th>Descripción</th>
              <th>Acción</th>
            </tr>
          </thead>
          <tbody>
			<tr>
           
            <td>#P1</td>
            <td><b>PROCESO A EJECUTARSE EN MARZO UNA VEZ TERMINADOS LOS FINALES Y ANTES DE LA MATRICULACION</b><br />
	1) SE RECORRE ALUMNOMATERIAS TODAS AQUELLAS MATERIAS CUYO AÑO DE CURSADA SEA MENOR AL ACTUAL<br />
	SI TIENEN ESTADO LIBRE, SE PONE DESAPROBADA.<br />
	2) SE RECORRE ALUMNOMATERIAS TODAS AQUELLAS MATERIAS CUYO AÑO DE CURSADA SEA HACE 5 AÑOS<br />
	SI TIENE ESTADO LIBRE O CURSADA APROBADA, SE PONE DESAPROBADA.
            </td>
            <td><input type="button" id="btnProceso1" class="btn btn-info" value="Ejecutar" /><br><br>
            <input type="button" id="btnProceso1Simular" class="btn btn-info" value="Simular" />
            </td>
            </tr>
 			<tr>
            
            <td>#P2</td>
            <td><b>CREACIÓN DE EXÁMENES AUTOMÁTICOS</b><br />
            ESTA FUNCIONALIDAD SIRVE PARA EXAMENES CUATRIMESTRALES Y PROMOCIONES. CREARÁ AUTOMATICAMENTE TODOS LOS EXÁMENES
            DE TODAS LAS MATERIAS PARA LA FECHA INDICADA TOMANDO TODAS LAS COMISIONES DEL AÑO DE CURSADA SELECCIONADO.
            <br /><br />
            <form id="daForm" class="form-horizontal" action="/proceso2/" method="POST" accept-charset="utf-8"> 

            <div class="form-group col-xs-12 required">
            <label for="tipexa" class="col-xs-1 control-label">Tipo Exámen</label>
            <div class="col-xs-3">
                <select class="form-control" id="tipexa" name="tipexa" autofocus="on">
                    <option value="0">SELECCIONAR</option>
                    <?php foreach ($viewDataTipoExamen as $tipexa) { ?>
                        <option value="<?php echo $tipexa['id']; ?>" <?php if ($tipexaValue == $tipexa['id']) echo "selected"; ?>><?php echo $tipexa['descripcion']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <label for="fecha" class="col-xs-1 control-label">Fecha</label>
            <div class="col-xs-2">
                <input type="text" class="form-control" maxlength="10" id="fecha" name="fecha" placeholder="dd/mm/aaaa" value="<?php echo $fechaValue; ?>" autocomplete="off" >
            </div>
            <label for="anio-cursada"class="col-xs-1 control-label">Año cursada:</label>
             <div class="col-xs-2">
                <select class="form-control" id="anio" name="anio">
                    <?php foreach ($anios as $anio): ?>
                        <option value="<?php echo $anio; ?>"><?php echo $anio; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-xs-2">
            <input type="checkbox" id="crearfaltantes" name="crearfaltantes" value=""> Contemplar Existentes
         	</div>
        </div>
        </form>
            </td>
            <td><input type="button" id="btnProceso2" class="btn btn-info" value="Ejecutar" /></td>
            </tr>
            
            <tr>
            
            <td>#P3</td>
            <td><b>PROCESO PARA EJECUTAR EN MARZO UNA VEZ FINALIZADAS TODAS LAS CURSADAS.</b><br />
	1) SE RECORRE ALUMNOCARRERA TODAS LAS CARRERAS EN CURSO<br />
	POR CADA UNA SE VERIFICA SI TODAS LAS MATERIAS ESTAN APROBADAS, EN ESE CASO ESTADO FINALIZADA.<br />
	2) SI NO TODAS ESTAN FINALIZADAS SE VERIFICA SI TIENE LAS MATERIAS NECESARIAS PARA PRE FINALIZAR<br />
            </td>
            <td><input type="button" id="btnProceso3" class="btn btn-info" value="Ejecutar" /><br><br>
            <input type="button" id="btnProceso3Simular" class="btn btn-info" value="Simular" /></td>
            </tr>
            
            <tr>
           
            <td>#P4</td>
            <td><b>PROCESO PARA ACTUALIZAR CONDICIONALIDADES.</b><br />
	1) Obtengo los registros a AlumnoMateria que tengan el tilde de CONDICIONAL<br />
	2) Se vuelve a corroborar las correlatividades de cada materia para verificar si sigue en el mismo estado.<br />
	3) Si ahora cumple con la regla, se le quita el tilde de condicional. <br />
        4) En caso de tildar en DESAPROBAR CONDICIONALES se le pondrá como DESAPROBADA la cursada a todos los que no cumplan con las reglas de condicionalidades. <br />
        
            <input type="checkbox" id="desaprobar" name="desaprobar" value=""> Desaprobar Condicionales
         	
            </td>
            <td><input type="button" id="btnProceso4" class="btn btn-info" value="Ejecutar" /><br><br>
            <input type="button" id="btnProceso4Simular" class="btn btn-info" value="Simular" /></td>
            </tr>
            <tr>
           
            <td>#P5</td>
            <td><b>PROCESO PARA ACTUALIZAR CONDICIONALIDADES SIMULTANEAS.</b><br />
	1) Obtengo las correlativas para verificar nuevamente si cumple<br />
	2) Si cumple la regla debo quitar tilde de condicional simultaneo<br />
	3) Si sigue sin cumplir con la regla de condicionalidad simultanea<br />
        4) Si el estado actual es LIBRE, DESAPROBADA, CANCELADA, APROBADA, EQUIV, CURSADA APROBADA quito el tilde ya que no es necesario.    <br/>
        5) Si Continua condicionalidad simultanea y tengo el tilde de desaprobar<BR/>
        6) Verifico si la materia esta entre las que deberia desaprobar por no cumplir la regla.<BR/>
            [1ER AÑO ] PRÁCTICA DE CONJUNTO VOCAL E INSTRUMENTAL<BR/>
            [1ER AÑO ] ELEMENTOS TÉCNICOS DE LA MÚSICA<BR/>
            [1ER AÑO ] ENTRENAMIENTO AUDITIVO I<BR/>
            [2DO AÑO ] PRÁCTICA GRUPAL DEL GÉNERO I<BR/>
            [3ER AÑO ] PRÁCTICA GRUPAL DEL GÉNERO II<BR/>
            [4TO AÑO ] PRÁCTICA GRUPAL DEL GÉNERO III<BR/>
        7) Si es una de esas materias, le cambio el estado a desaprobado y se le quita el tilde de condicional simultanea.<br/>
        	<input type="checkbox" id="desaprobarp5" name="desaprobarp5" value=""> Desaprobar Condicionales
            </td>
            <td><input type="button" id="btnProceso5" class="btn btn-info" value="Ejecutar" /><br><br>
            <input type="button" id="btnProceso5Simular" class="btn btn-info" value="Simular" /></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
</fieldset>

<script type="text/javascript">
 $("#tipexa").change(function() {
	 sessionStorage.setItem("procesos-tipoexamen",$(this).val());
	 });
 $("#anio").change(function() {
	 sessionStorage.setItem("procesos-anio",$(this).val());
	 });

	 
$('#btnProceso1').click(function() {
   var texto = "¿Esta seguro de querer ejecutar el proceso automatico?";
   if (confirm(texto)) 	  window.location.href = '/proceso1/0';
 });
$('#btnProceso1Simular').click(function() {
   var texto = "¿Esta seguro de querer ejecutar el proceso automatico?";
   if (confirm(texto)) 	  window.location.href = '/proceso1/1';
 });
 
 $('#btnProceso2').click(function() {
	  sessionStorage.setItem("procesos-fecha",$('#fecha').val());
  	  sessionStorage.setItem("procesos-anio",$('#anio').val());
	  
   var texto = "¿Esta seguro de querer crear automaticamente los examenes indicados?";
   if (confirm(texto)) 	  $("#daForm").submit();
 });
 
 $('#btnProceso3').click(function() {
   var texto = "¿Esta seguro de querer ejecutar el proceso automatico?";
   if (confirm(texto)) 	  window.location.href = '/proceso3/0';
 });
 $('#btnProceso3Simular').click(function() {
   var texto = "¿Esta seguro de querer ejecutar el proceso automatico?";
   if (confirm(texto)) 	  window.location.href = '/proceso3/1';
 });
 
 $('#btnProceso4').click(function() {
     var checkBox = document.getElementById("desaprobar");
     var desaprobar=0;
     if(checkBox.checked==true){
         desaprobar=1;
     }else{ desaprobar=0;}
   var texto = "¿Esta seguro de querer ejecutar el proceso automatico?. Si tildo DESAPROBAR, se desaprobarán todas aquellas materias que tengan el tilde de condicional en los alumnos.";
   if (confirm(texto)) 	  window.location.href = '/proceso4/0/'+desaprobar;
 });
 
 $('#btnProceso4Simular').click(function() {
     var checkBox = document.getElementById("desaprobar");
     var desaprobar=0;
     if(checkBox.checked==true){
         desaprobar=1;
     }else{ desaprobar=0;}
   var texto = "¿Esta seguro de querer ejecutar el proceso automatico?";
   if (confirm(texto)) 	  window.location.href = '/proceso4/1/'+desaprobar;
 });
 
 $('#btnProceso5').click(function() {
     var checkBox = document.getElementById("desaprobarp5");
     var desaprobar=0;
     if(checkBox.checked==true){
         desaprobar=1;
     }else{ desaprobar=0;}
   var texto = "¿Esta seguro de querer ejecutar el proceso automatico?. Si tildo DESAPROBAR, se desaprobarán todas aquellas materias que tengan el tilde de condicional en los alumnos.";
   if (confirm(texto)) 	  window.location.href = '/proceso5/0/'+desaprobar;
 });
 
 $('#btnProceso5Simular').click(function() {
     var checkBox = document.getElementById("desaprobarp5");
     var desaprobar=0;
     if(checkBox.checked==true){
         desaprobar=1;
     }else{ desaprobar=0;}
   var texto = "¿Esta seguro de querer ejecutar el proceso automatico?";
   if (confirm(texto)) 	  window.location.href = '/proceso5/1/'+desaprobar;
 });
 
if(sessionStorage.getItem("procesos-tipoexamen")){
			$('#tipexa').val(sessionStorage.getItem("procesos-tipoexamen"));
}	 

if(sessionStorage.getItem("procesos-fecha")){
			$('#fecha').val(sessionStorage.getItem("procesos-fecha"));
}
if(sessionStorage.getItem("procesos-anio")){
			$('#anio').val(sessionStorage.getItem("procesos-anio"));
}		 
</script>