function calculo(operacion){
let alojamiento;
let alimentacion;
let entretenimiento;
let total;
       
alojamiento = parseFloat(document.calculadora.alojamiento.value);
alimentacion = parseFloat(document.calculadora.alimentacion.value);
entretenimiento = parseFloat(document.calculadora.entretenimiento.value);
        
if(operacion == 1) total = alojamiento + alimentacion + entretenimiento;
document.calculadora.resultado.value = total;
}