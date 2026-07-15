<div id="headerInicio" >
    <div class="row">
        <div class="col-lg-12">
            <div class="panel panel-default sombraPanel">
                <div class="panel-heading colorAxioma">
                    <h2 class="text-center blanco">DASHBOARD</h2>                    
                </div>
                <div class="panel-body paddingBottom text-center">
                    <div class="row">
<div id="loading-message" style="display: none;"><h3>Cargando dashboard...</h3></div>
<iframe
  id="dashboard-iframe"
  src="./inicio/dashboard.php?idUserRev=<?php echo $usuarioSession->getIdUsuario();?>"
  width="100%"
  height="3000"
  style="border: none; overflow: hidden;"
  onload="hideLoadingMessage()"
></iframe>                    </div>
    
                </div>
            </div>
        </div>
    </div>

</div>
<style>
    
</style>
<script>

  function showLoadingMessage() {
    document.getElementById('loading-message').style.display = 'block';
  }

  function hideLoadingMessage() {
    document.getElementById('loading-message').style.display = 'none';
  }

  // Mostrar el mensaje de carga cuando se carga la página
  showLoadingMessage();
</script>
