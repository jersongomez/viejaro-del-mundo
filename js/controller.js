$(document).ready(function () {
    $('.carousel').carousel({
        interval: 5000 //changes the speed
    });
    $("#botonFlecha").on("click", function () {
        console.log('Yes!');
        $('html,body').animate({
            scrollTop: $("#contenido").offset().top - 20
        }, 1000);
    });
    $.validator.addMethod("lettersonly", function (value, element) {
        return this.optional(element) || /^[a-zA-ZÃ±ÃÃ¡Ã©Ã­Ã³ÃºÃÃÃÃÃÃ Ã¡Ã£Ã¢Ã§ÃªÃ©Ã­ÃµÃ´Ã³ÃÃÃÃÃÃÃÃÃÃÃÃÃ¦Ã¨Ã«Ã®Ã¯Ã´Â½Ã»Ã¹Ã¼Â«Â»ÃÃÃÃÃÃÂ¼ÃÃ\s]+$/i.test(value);
    }, "Formato  invalido!");
    $('#frmDatos').validate({
        rules: {
            nombre: "required lettersonly",
            apellido: "required lettersonly",
            documento: "required number",
            telefono: "required number",
            email: "required email",
            condiciones: "required",
            mayoredad: "required"
        },
        messages: {
            nombre: {required: "Nombre", lettersonly: "No debe ingresar caracteres especiales en el nombre"},
            apellido: {required: "Apellido", lettersonly: "No debe ingresar caracteres especiales en el apellido"},
            documento: {required: "Cédula", number: "De ingresar solo números en la cédula"},
            telefono: {required: "Teléfono domicilio", number: "De ingresar solo números en la teléfono"},
            movil: {required: "Celular", number: "De ingresar solo números en la celular"},
            email: {required: "Correo electrónico", email: "Formato email incorrecto"},
            condiciones: {required: "Acepta términos y condiciones"},
            mayoredad: {required: "Acepta ser mayor de edad"}

        },
        showErrors: function (errorMap, errorList) {
            $("#errors").addClass("hide");
            var brk = false;
            $.each(errorList, function (i, o) {
                if (brk) {
                    return;
                }
                brk = true;
                $("#sperror").html(this.message);
                $("#errors").removeClass("hide");
            });
        },
        success: function (label, element) {

        },
        submitHandler: function (form) {
            var data = $('#formInscripcion').serializeArray();
            data.push({name: 'action', value: 'callMDM'});
            $.ajax({
                url: "index.php",
                data: data,
                contentType: "application/x-www-form-urlencoded",
                dataType: "json", //xml,html,script,json
                async: false,
                error: function () {
                },
                ifModified: false,
                processData: true,
                success: function (respuesta) {
                    if (respuesta.codigo == 200) {
                        $("#errors").children("div").removeClass("alert-danger alert-danger-viajeros").addClass("btn-enviar sussess-confirmation").html("Registro Exitoso!");
                    } else {
                        $("#errors").children("div").removeClass("btn-enviar sussess-confirmation").addClass("alert-danger alert-danger-viajeros").html("Error en registro!");
                    }
                    $("#errors").removeClass("hide");
                },
                type: "POST"
            });
        }
    });
});