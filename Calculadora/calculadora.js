function calculo(opera){
    let num1;
    let num2;
    let resu;
   
    num1 = parseInt(document.calculadora.valor1.value);
    num2 = parseInt(document.calculadora.valor2.value);
    

    if(opera == 1) resu = num1 + num2;
    if(opera == 2) resu = num1 - num2;
    if(opera == 3) resu = num1 * num2;
    if(opera == 4) resu = num1 / num2;

    document.calculadora.resultado.value = resu;
}