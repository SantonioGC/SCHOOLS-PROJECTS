  $(document).ready(function(){
            $("#anadirCompra").click(function(){
                var nuevoTexto = $("#compra").val().trim();
                
                if(nuevoTexto !== ""){
                    var nuevoli = $("<li></li>").text(nuevoTexto);
                    
                    // Asignar eventos al nodo creado
                    nuevoli.on("click", function(){
                        $(this).css({
                            "text-decoration": "line-through", 
                            "font-style": "italic"
                        });
                    });

                    nuevoli.on("dblclick", function(){
                        $(this).remove();
                    });

                    // Añadir elemento a la lista
                    $("#listaCompra").append(nuevoli);

                    // Resetear el campo input
                    $("#compra").val("");
                }
            });

            $("#reset").click(function(){
                $("#listaCompra").empty();
            });
        });