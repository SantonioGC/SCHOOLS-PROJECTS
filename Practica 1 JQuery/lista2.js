$(document).ready(function() {
    function asignarEventos(elemento) {
        $(elemento).on("click", function() {
            $(this).css({
                "text-decoration": "line-through",
                "font-style": "italic"
            });
        });

        $(elemento).on("dblclick", function() {
            $(this).remove();
        });
    }

    // Asignamos eventos a los elementos iniciales
    $("#listaCompra li").each(function() {
        asignarEventos(this);
    });

    // Evento para añadir nuevos elementos
    $("#anadirCompra").click(function() {
        var nuevoTexto = $("#compra").val();
        if (nuevoTexto.trim() !== "") {
            var nuevoLi = $("<li></li>").text(nuevoTexto);
            asignarEventos(nuevoLi);
            $("#listaCompra").append(nuevoLi);
            $("#compra").val("");
        }
    });

    // Evento para eliminar todos los elementos de la lista
    $("#reset").click(function() {
        $("#listaCompra").empty();
    });
});