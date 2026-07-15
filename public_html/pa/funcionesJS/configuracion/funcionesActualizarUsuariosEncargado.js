$(document).ready(function () {

  var userId; // Variable to store the user ID

  // Event listener for opening the modal and storing the user ID
  $('.btn-info').click(function () {
    userId = $(this).attr('id').split('_')[1]; // Extract user ID from button's ID
    $('#subcontratosModal').modal('show');
  });

  // Event listener for the 'Guardar cambios' button
  $('#guardarSubcontrato').click(function () {
    var subcontratoId = $('#subcontratosSelect').val(); // Get selected subcontrato ID
    var dataToSend = {
      accion: 'actualizarSubcontrato',
      idSubcontrato: subcontratoId,
      idUsuario: userId
    };

    $.ajax({
      data: dataToSend,
      url: './gg/Views/UsuariosCrud.php', 
      type: 'POST',
      dataType: "json",
      success: function(response) {
        if(response.estado == 1) {
            Toastify({
                text: response.mensaje,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "#4fbe87", // Green for success
            }).showToast();
        } else {
            Toastify({
                text: response.mensaje,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                backgroundColor: "#e74c3c", // Red for error
            }).showToast();
        }
    },    
      error: function (xhr, status, error) {
        // Handle errors
        console.error('Error:', error);
      }
    });
  });


  // Attach event listeners to all 'Actualizar' buttons
  $(".btn-success").each(function () {
    $(this).click(function () {
      var userId = this.id.split("_")[1];
      $.ajax({
        data: {
          accion: "actualizarUsuarioEncargado",
          id_usuario: userId,
          nombre: $("#nombre_" + userId).val(),
          apellido_p: $("#apellido_p_" + userId).val(),
          apellido_m: $("#apellido_m_" + userId).val(),
          correo: $("#correo_" + userId).val(),
          perfil: $("#perfil_" + userId).val(),
          contrasena: $("#contrasena_" + userId).val(),
          estado: $("#estado_" + userId).val(),
          idPerfilUser: $("#idPerfilUser").val(),
        },
        url: "./gg/Views/UsuariosCrud.php",
        type: "POST",
        dataType: "json",
        success: function (response) {
          console.log(response);
          if (response.estado === 1) {
            Toastify({
              text: response.mensaje,
              gravity: "bottom",
              backgroundColor: "green",
            }).showToast();
          } else {
            Toastify({
              text: response.mensaje,
              gravity: "bottom",
              backgroundColor: "red",
            }).showToast();
          }
        },
        error: function (xhr) {
          Toastify({
            text: "Server error occurred: " + xhr.status,
            gravity: "bottom",
            backgroundColor: "red",
          }).showToast();
        },
      });
    });
  });
});
