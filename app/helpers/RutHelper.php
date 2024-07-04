<?php

function validarRut($rut)
{
    if (!preg_match("/^[0-9]+-[0-9kK]{1}$/", $rut)) {
        return false; // Verify the RUT format (numbers and hyphen with check digit)
    }

    list($numero, $dv) = explode('-', $rut);

    $suma = 0;
    $factor = 2;
    for ($i = strlen($numero) - 1; $i >= 0; $i--) {
        $suma += $numero[$i] * $factor;
        $factor = ($factor == 7) ? 2 : $factor + 1;
    }

    $dvEsperado = 11 - ($suma % 11);

    if ($dvEsperado == 11) {
        $dvEsperado = 0;
    } elseif ($dvEsperado == 10) {
        $dvEsperado = 'K';
    }

    return strtoupper($dv) == strtoupper($dvEsperado);
}
