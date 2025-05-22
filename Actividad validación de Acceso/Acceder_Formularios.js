function acceder()
	{
			var n=document.form1.nombre.value;
			var p=document.form1.pass.value;
			var contador_mayusculas=0;
			var contador_simbolos=0;
			if(n.length>8 && n.length<15){
				document.write("El nombre de usuario es: "+n);
				document.write("<br>");
				for(var i=0; i<p.length; i++)
				{
					if(p[i]!="(" && p[i]!="^")
					{
						var valor = p.charCodeAt(i);
						if(valor>34 && valor<91)
						{
							contador_mayusculas++;
						}
					}else
					{
						contador_simbolos++;
						document.write("La contraseña es incorrecta.");
						document.write("<br>");
					}
					if(p.length>=6 && i == 4 && contador_mayusculas!=0 && contador_simbolos==0)
					{
						document.write("La contraseña es correcta. Usuario Validado.");
						document.write("<br>");
					}
				}
			}else{
				document.write("Nombre de usuario incorrecto. Debe ser mayor que 8 y menor que 15.");
				document.write("<br>");
			}
	}