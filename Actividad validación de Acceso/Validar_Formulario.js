function validarFormulario() {
            let nombre = document.getElementById('nombre').value;
            let pass = document.getElementById('pass').value;
            let errorNombre = document.getElementById('errorNombre');
            let errorPass = document.getElementById('errorPass');

            errorNombre.innerHTML = "";
            errorPass.innerHTML = "";

            // Validación de nombre de usuario
            if (nombre.length < 8 || nombre.length > 15) {
                errorNombre.innerHTML = "El nombre debe tener entre 8 y 15 caracteres.";
                return;
            }

            // Validación de contraseña con expresiones regulares
            let regexPass = /^(?!.*[\(\^]).*(?=.*[A-Z]).{6,}$/;
            if (!regexPass.test(pass)) {
                errorPass.innerHTML = "La contraseña debe tener al menos 6 caracteres, contener al menos una mayúscula y no incluir '(' o '^'.";
                return;
            }

            alert("Usuario validado correctamente.");
        }