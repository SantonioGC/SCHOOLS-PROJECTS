function construirTd(dato, clase) {
    var td = document.createElement("td");
    td.classList.add(clase);
    td.textContent = dato;
    return td;
}

function construirTr(paciente) {
    var pacienteTr = document.createElement("tr");
    pacienteTr.classList.add("paciente");

    pacienteTr.appendChild(construirTd(paciente.nombre, "info-nombre"));
    pacienteTr.appendChild(construirTd(paciente.peso, "info-peso"));
    pacienteTr.appendChild(construirTd(paciente.altura, "info-altura"));
    pacienteTr.appendChild(construirTd(paciente.gordura, "info-gordura"));
    pacienteTr.appendChild(construirTd(paciente.imc, "info-imc"));

    return pacienteTr;
}

function adicionarPacienteEnLaTabla(paciente) {
    var tabla = document.querySelector("#tabla-pacientes");
    tabla.appendChild(construirTr(paciente));
}

var errorAjax = document.querySelector("#error-ajax");
var botonBuscar = document.querySelector("#buscar-paciente");

botonBuscar.addEventListener("click", function() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "https://raw.githubusercontent.com/SantonioGC/SCHOOLS-PROJECTS/refs/heads/main/lista_pacientes.json", true);

    xhr.addEventListener("load", function() {
        if (xhr.status == 200) {
            var respuesta = xhr.responseText;
            var pacientes = JSON.parse(respuesta);

            pacientes.forEach(function(paciente) {
                adicionarPacienteEnLaTabla(paciente);
            });

        } else {
            errorAjax.style.visibility = "visible";
        }
    });

    xhr.send();
});