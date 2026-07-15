$(document).ready(function() {
    // Event listener for the "Enviar" button
    $('#btnAddUsuario').click(function() {
        agregarUsuario();
    });
/*
    Toastify({
        text: "Hello there",
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "blue"
    }).showToast(); */
});
//agregar document ready

//agregar event listener para el id del boton enviar #botonEnviar
//agregar validaciones para que la contraseña de #contrasena sea igual a #reContrasena
//agregar validacion de que todos los campos estén llenos y los selects seleccionados
//agregar validacion de que el correo sea formato correo

function agregarUsuario() {    
    const nombre = $('#nombre').val();
    const apellidoP = $('#apellidoP').val();
    const apellidoM = $('#apellidoM').val();
    const correo = $('#correo').val();
    const nombreUsuario = $('#nombreUsuario').val();
    const contrasena = $('#contrasena').val();
    const reContrasena = $('#reContrasena').val();
    const area = $('#idArea').val();
    const contrato = $('#idContrato').val();
    const subcontrato = $('#idSubcontrato2').val();
    const perfil = $('#idPerfil2').val();

    

    // Validations with Toastify
    if (!nombre || !apellidoP || !apellidoM || !correo || !nombreUsuario || !contrasena || !area || !contrato || !subcontrato || !perfil) {
        Toastify({
            text: "Por favor, complete todos los campos.",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "red"
        }).showToast();
        return;
    }

    if (contrasena !== reContrasena) {
        Toastify({
            text: "Las contraseñas no coinciden.",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "red"
        }).showToast();
        return;
    }

    if (!validateEmail(correo)) {
        Toastify({
            text: "Por favor, ingrese un correo electrónico válido.",
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: "red"
        }).showToast();
        return;
    }
    
    $.ajax({
        data: { accion: 'agegarUsuarioEncargado', id_contrato: contrato, nombre: nombre, apellido_p: apellidoP, apellido_m: apellidoM, correo: correo, nombre_usuario: nombreUsuario, contrasena: contrasena, id_perfil: perfil, id_contrato: contrato, id_estado_usuario: 1, area: area, subcontrato: JSON.stringify(subcontrato)},
        url: './gg/Views/UsuariosCrud.php',
        type: 'post',
        dataType: 'json',
        success: function (data) {
            console.log(data);
            // Check if the query was successful
            if (data.estado == 1) {
                // Query was successful
                console.log('Usuario ingresado correctamente.');
                Toastify({
                    text: data.mensaje,
                    duration: 3000, // Duration in milliseconds
                    gravity: "bottom", // Toast position
                    backgroundColor: "green", // Background color
                }).showToast();
            } else {
                // Query failed
                console.error('Error ingresando el usaurio: ' + data.mensaje);
                Toastify({
                    text: "Error ingresando el usuario: " + data.mensaje,
                    duration: 3000,
                    gravity: "bottom",
                    backgroundColor: "red",
                }).showToast();
            }
        },
        error: function (xhr, status, error) {
            console.error('AJAX Error: ' + status + ' - ' + error);
            Toastify({
                text: "AJAX Error: " + status + " - " + error,
                duration: 3000,
                gravity: "bottom",
                backgroundColor: "red",
            }).showToast();
        }
    });

}

function validateEmail(email) {
    const re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(String(email).toLowerCase());
}