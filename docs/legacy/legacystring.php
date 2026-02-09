<?php

/**
 * REFERENCIA ÚNICAMENTE — No forma parte de la aplicación.
 *
 * Código legacy del cotizador (app anterior). La lógica actual está en
 * App\Services\QuoteGeneratorService y en las plantillas/reglas de la base de datos.
 *
 * Este archivo se conserva solo como documentación del flujo y textos originales.
 * No debe ser incluido ni ejecutado en Laravel.
 */

function cotizarString()
{
    $tarifa = 0; // precio estandar
    $distanciaBase = 30000; // se incluyen 3 kms en la tarifa base, a partir de ahi sale en 10 pesos por kilometro adicional
    $precioKM = 30;
    // punto de origen para calculo de compras sencillas
    $origen = "25.694905,-100.3520877"; // domicilio de LÁPIDAS MONTERREY

    $location = $this->input->get('l1');
    $producto = Item::find_by_id($this->input->get('item_id'));
    $periodo = $this->input->get('pago_periodo');


    // Get cURL resource
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, array(
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => 'https://maps.googleapis.com/maps/api/distancematrix/json?origins='.$origen.'&destinations='.$location."&key=AIzaSyD3-tMlAEuZmHA0BK9_nmDjFdUt8531Q9c",
        CURLOPT_USERAGENT => 'Codular Sample cURL Request'
    ));
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    // Close request to clear up some resources
    curl_close($curl);

    $resp = json_decode($resp);

    $distancia = @$resp->rows[0]->elements[0]->distance->value;

    $tarifaEnvio = 0;
    if(isset($distancia)){
        if($distancia > $distanciaBase){
            $tarifaEnvio = (ceil($distancia / 1000) * $precioKM) * $producto->viajes_instalacion ;
        }
    }

    $pago3meses = ($producto->value + $tarifaEnvio) / 3;
    $abono = $pago12meses = (($producto->precio12 * 12) + $tarifaEnvio) / 12;
    $pagos_totales = 12;

    if($periodo == 'semana'){
        $enganche = $abono;
        $pagos_totales = ceil(11 * 4.3333);
        $abono = ($abono * 11) / $pagos_totales;
        $string_pago_incial = " con un único pago inicial de $".number_format($enganche,2,".",",");
    }

    $per = [
        'mes'=>['singular'=>'mes', 'plural'=>'meses', 'periodo_singular'=>'mensual','periodo_plural'=>'mensuales'],
        'semana'=>['singular'=>'semana', 'plural'=>'semanas', 'periodo_singular'=>'semanal','periodo_plural'=>'semanales'],
    ];

    if($producto->speech == 1){
        $cotizacionString = "¡Buen día! :), El monumento que le gustó esta
        hermoso, es el número ". $producto->name . " de nuestro catálogo,
        es de ". $producto->material . ", y aplica para el financiamiento a
        ".$pagos_totales." ".$per[$periodo]['plural']." de tan sólo $".
        number_format($abono,2,".",",").$string_pago_incial.", o también se lo podemos manejar con descuento especial
        en 3 pagos mensuales de $". number_format($pago3meses,2,".",",") . ".
        El precio ya incluye: ". $producto->incluye . ". También ya se
        incluye en el precio la correcta y profesional instalación del monumento
        en su terreno en su panteón. Además, es muy importante que usted sepa
        que la primera mensualidad es el enganche y ya solo con eso le
        fabricamos su monumento y ¡¡se lo instalamos!! :). El segundo pago
        no sería hasta que ya le hayamos instalado el monumento y usted este
        completamente satisfecho con el y le haya encantado, y los pagos
        restantes serían ".$per[$periodo]['singular']." con ".$per[$periodo]['singular'].",
        ¡¡está increible!!. ¿Le gustaría que le ayude a elaborar su contrato?";
    }


    $pc = explode(',',$producto->precio_compuesto);
    $pagos_totales = $pc[0];
    $pagos_enganche = $pc[1];
    $mensualidad_enganche = $pc[2];
    $precio_real = $producto->value + $tarifaEnvio;
    // si las mensualidades totales son mayores a las del enganche
    $restante = "";
    if($pagos_enganche <= $pagos_totales){
        $pagos_restantes = $pagos_totales - $pagos_enganche;
        $monto_restante = ceil($producto->value - ($pagos_enganche * $mensualidad_enganche));
        $mensualidad = $monto_restante / $pagos_restantes;

        if($pc[3]){ $mensualidad = $pc[3]; }
        // consideramos el envío para una nuevo enganche y una mensualidad
        if($tarifaEnvio > 0){
            $mensualidad_envio = $tarifaEnvio / $pagos_totales;
            $mensualidad_enganche += $mensualidad_envio;
            $mensualidad += $mensualidad_envio;
        }
    }

    $abono_enganche = $primer_pago = ceil($mensualidad_enganche);
    $abono = ceil($mensualidad);

    $string_pago_incial = "";
    if($periodo == 'semana'){
        $monto_enganche = ($pagos_enganche - 1) * $abono_enganche;
        $monto_restante = $pagos_restantes * $abono;

        $pagos_enganche = ceil(($pagos_enganche - 1) * 4.3333);
        $pagos_restantes = ceil($pagos_restantes * 4.3333);
        $pagos_totales = $pagos_enganche + $pagos_restantes;

        $abono_enganche = ceil($monto_enganche / $pagos_enganche);
        $abono = ceil($monto_restante / $pagos_restantes);

        $string_pago_incial = " con un único pago inicial de $".number_format($primer_pago,2,".",",");
    }

    if($producto->speech == 2){
        $precio_lista = ceil(($producto->value + $tarifaEnvio) / .9);

        if($pagos_enganche < $pagos_totales){
            $restante_string = " y los ".$pagos_restantes." restantes de  $".
                                number_format($abono,2,".",",").$string_pago_incial;
        }

        $cotizacionString = "Buen día!  :) El monumento que le  gustó está hermoso,
        es el numero ".$producto->name." de nuestro catalogo, es de "
        .$producto->material.", tiene un costo de $".number_format($precio_lista,2,".",",")."
        pero por el momento contamos con el 10% de descuento en ese modelo,
        quedaría en tan solo $".number_format($precio_real,2,".",",").", pero
        no se preocupe ya que éste precio se dividiría en ".$pagos_totales."
        pagos :), se lo podemos financiar en ".$pagos_totales." pequeñísimos
        pagos ".$per[$periodo]['periodo_plural'].",  los primeros ".$pagos_enganche."
        serían de tan solo $".number_format($abono_enganche,2,".",",").$restante_string.".
        El precio incluye: ".$producto->incluye.". También ya se incluye en
        el precio la correcta y profesional instalación del monumento en su
        terreno en su panteón. Además es muy importante que usted sepa que
        el primer pago es el enganche y ya solo con eso le fabricamos
        su monumento y se lo instalamos!! :). El segundo pago no sería hasta
        que ya le hayamos instalado el monumento y usted este completamente
        satisfecho con el y le haya encantado, y los pagos restantes serían
        ".$per[$periodo]['singular']." con ".$per[$periodo]['singular']."!
        está increible!!. ¿Le gustaría que le ayude a elaborar su contrato?";
    }

    if($producto->speech == 3){

        $restante_string = " y ".$pagos_restantes." pagos aún más bajos de $".
                            number_format($abono,2,".",",").$string_pago_incial;

        $cotizacionString ="Buen día!  :) El monumento que le  gustó está hermoso,
        es el número ".$producto->name." de nuestro catálogo, es de ".$producto->material.",
        y aplica para el financiemiento a ".$pagos_totales." ".$per[$periodo]['plural']." entregandole
        el monumento desde el primer pago!, ahorita esta en promocion y tiene
        un costo de $".number_format($precio_real,2,".",",")." pago de contado
        o tambien podria ser con el plan de pagos a ".$pagos_totales." "
        .$per[$periodo]['plural']." que consiste en ".$pagos_enganche."
        pequeños pagos ".$per[$periodo]['periodo_plurarl']." de tan solo
        $".number_format($abono_enganche,2,".",",")." ".$restante_string.".
        El precio incluye: ".$producto->incluye.". También ya se incluye en
        el precio la correcta y profesional instalación del monumento en su
        terreno en su panteón. Además es muy importante que usted sepa que
        el primer pago es el enganche y ya solo con eso le fabricamos
        su monumento y se lo instalamos!! :). El segundo pago no sería hasta
        que ya le hayamos instalado el monumento y usted este completamente satisfecho
        con el y le haya encantado, y los pagos restantes serían ".
        $per[$periodo]['singular']." con ".$per[$periodo]['singular']."!
        está increible!!. ¿Le gustaría que le ayude a elaborar su contrato?";
    }



    $resp = ['type'=>'success', 'cotizacion'=>$cotizacionString];

    echo json_encode($resp);die;
}
