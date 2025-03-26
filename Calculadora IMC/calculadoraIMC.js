function calculoIMC(operacion){
    let altura;
    let peso;
    let imc;
    let msj;
   
    altura = parseFloat(document.calculadora.altura.value);
    peso = parseFloat(document.calculadora.peso.value);
    
    if(operacion == 1) imc = peso / (altura * altura);
    if(imc<18.5){
        msj = 'bajo peso'
    } else if(imc>=18.5 && imc<25){
        msj = 'peso normal'
    } else if(imc>=25 && imc<30){
        msj = 'sobrepeso'
    } else {
        msj = 'obesidad'
    }

    document.calculadora.resultado.value = imc.toFixed(2);
    document.calculadora.resultadoMSJ.value = msj;
}