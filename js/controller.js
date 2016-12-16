window.cityList = [];

$(document).ready(function () {

    $.getJSON("data/cities.json", function (cities) {
        window.cityList = cities;
        $("#ciudad").autocomplete({
            source: cities,
            minLength: 2
        });
    });

    loadCustomValidatorMethods();
    init();
});


function init() {

    var divClasses = ".loader, .loader-msg";
    $('.carousel').carousel({
        interval: 5000 //changes the speed
    });

    $("#botonFlecha").on("click", function () {
        $('html,body').animate({
            scrollTop: $("#contenido").offset().top - 20
        }, 1000);
    });


    $("button[type=submit]").click(function () {

        if ($("#frmDatos").valid()) {
            $("button[type=submit]")
                    .text("Enviando ...")
                    .removeClass("btn-enviar")
                    .addClass("btn-enviando")
                    .attr("disabled", "disabled");
            
            $(divClasses).show();
            
            setTimeout(function () {
                $("#frmDatos").submit();
            }, 500);
        }
    });

    var maxPhoneLenght = 7;
    var maxMobileLenght = 10;
    var minMobileLenght = 10;

    $('#frmDatos').validate({
        rules: {
            nombre: "required lettersonly",
            apellido: "required lettersonly",
            documento: "required number",
            email: "required email",
            condiciones: "required",
            mayoredad: "required",
            telefono: {
                required: true,
                minlength: maxPhoneLenght,
                number: true
            },
            mobile: {
                required: true,
                minlength: maxMobileLenght,
                maxlength: minMobileLenght,
                number: true
            },
            ciudad: "required validcity"
        },
        messages: {
            nombre: {
                required: "Escribe tu nombre",
                lettersonly: "No debes ingresar caracteres especiales en el nombre"
            },
            apellido: {
                required: "Escribe tu apellido",
                lettersonly: "No debes ingresar caracteres especiales en el apellido"
            },
            documento: {
                required: "Escribe tu C&eacute;dula",
                number: "Debes ingresar solo n&uacute;meros en la c&eacute;dula"
            },
            telefono: {
                required: "Escribe tu tel&eacute;fono de domicilio",
                number: "Debes ingresar solo n&uacute;meros en el tel&eacute;fono",
                minlength: "Escribe los " + maxPhoneLenght + " d&iacute;gitos de tu tel&eacute;fono"
            },
            mobile: {
                required: "Escribe tu celular",
                number: "De ingresar solo n&uacute;meros en la celular",
                minlength: "Escribe los " + minMobileLenght + " d&iacute;gitos de tu celular",
                maxlength: "Escribe los " + maxMobileLenght + " d&iacute;gitos de tu celular"
            },
            email: {
                required: "Escribe tu correo electr&oacute;nico",
                email: "Formato email incorrecto"
            },
            condiciones: {
                required: "Debes aceptar los t&eacute;rminos y condiciones"
            },
            mayoredad: {
                required: "Debes ser mayor de edad"
            },
            ciudad: {
                required: "Escribe tu ciudad de domicilio"
            }

        },
        showErrors: function (errorMap, errorList) {

            var brk = false;
            $("#errors").addClass("hide");

            $.each(errorList, function (i, o) {
                if (brk) {
                    return;
                }
                brk = true;
                $("#sperror").html(this.message);
                $("#errors").removeClass("hide");
            });
        },
        submitHandler: function (form) {

            var data = $('#frmDatos').serializeArray();

            data.forEach(function (item, index) {
                $('#' + item.name).attr('value', item.value);
            });

            data.push({name: 'action', value: 'callMDM'});
            data.push({name: 'form', value: document.documentElement.innerHTML});
            $.ajax({
                url: "index.php",
                data: data,
                contentType: "application/x-www-form-urlencoded",
                dataType: "json", //xml,html,script,json
                async: false,
                error: function () {
                    //$(divClasses).hide();
                },
                ifModified: false,
                processData: true,
                success: function (respuesta) {

                    //$(divClasses).hide();

                    if (respuesta.codigo == 200) {
                        location.href = 'thank-you.html';
                    } else {
                        $("#errors").children("div").removeClass("btn-enviar sussess-confirmation").addClass("alert-danger alert-danger-viajeros").html("Error en registro!");
                    }
                },
                type: "POST"
            });
        }
    });
}

function loadCustomValidatorMethods() {

    $.validator.addMethod("lettersonly", function (value, element) {
        return this.optional(element) || /^[a-zA-ZÃ±ÃÃ¡Ã©Ã­Ã³ÃºÃÃÃÃÃÃ Ã¡Ã£Ã¢Ã§ÃªÃ©Ã­ÃµÃ´Ã³ÃÃÃÃÃÃÃÃÃÃÃÃÃ¦Ã¨Ã«Ã®Ã¯Ã´Â½Ã»Ã¹Ã¼Â«Â»ÃÃÃÃÃÃÂ¼ÃÃ\s]+$/i.test(value);
    }, "Formato  invalido!");

    $.validator.addMethod("validcity", function (value, element) {
        return this.optional(element) || $.inArray(value, window.cityList) !== -1;
    }, "Ciudad invalida!");
}