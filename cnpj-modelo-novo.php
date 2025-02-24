<?php
class ValidacaoNovoCnpj {
    // Regex para validar o formato do CNPJ alfanumérico
    private static $pattern = "/^[0-9A-Z]{2}\.[0-9A-Z]{3}\.[0-9A-Z]{3}\/[0-9A-Z]{4}-[0-9]{2}$/";

    // Multiplicadores para o cálculo dos DVs
    private static $multiplicadores = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

    /**
     * Remove a máscara do CNPJ e normaliza para maiúsculas.
     */
    private static function normalizarCnpj($cnpj) {
        return strtoupper(preg_replace('/[^0-9A-Z]/', '', $cnpj));
    }

    /**
     * Converte caracteres alfanuméricos em valores numéricos (A=10, B=11, ..., Z=35).
     */
    private static function converterCaracter($caracter) {
        if (is_numeric($caracter)) {
            return intval($caracter);
        }
        if (ctype_alpha($caracter)) {
            return ord($caracter) - ord('A') + 10; // 'A' = 10, 'B' = 11, ..., 'Z' = 35
        }
        throw new InvalidArgumentException("Caractere inválido: $caracter");
    }

    /**
     * Calcula a soma dos dígitos multiplicados pelos seus respectivos pesos.
     */
    private static function multiplicarSomarDividir($cnpj, $length, $shift) {
        $soma = 0;
        for ($i = 0; $i < $length; $i++) {
            $valor = self::converterCaracter($cnpj[$i]);
            $multiplicador = self::$multiplicadores[$i + $shift];
            $soma += $valor * $multiplicador;
        }
        return $soma % 11;
    }

    /**
     * Valida o CNPJ (formato e dígitos verificadores).
     */
    public static function isCnpjValido($cnpj) {
        if (!$cnpj) {
            return false;
        }

        $cnpjNormalizado = self::normalizarCnpj($cnpj);

        if (strlen($cnpjNormalizado) != 14) {
            echo "Tamanho inválido: " . strlen($cnpjNormalizado) . "<br>";
            return false;
        }

        $baseCnpj = substr($cnpjNormalizado, 0, 12);

        $soma1 = self::multiplicarSomarDividir($baseCnpj, 12, 1);
        $dv1 = $soma1 < 2 ? 0 : 11 - $soma1;

        $soma2 = self::multiplicarSomarDividir($baseCnpj . $dv1, 13, 0);
        $dv2 = $soma2 < 2 ? 0 : 11 - $soma2;

        $dvOriginal1 = self::converterCaracter($cnpjNormalizado[12]);
        $dvOriginal2 = self::converterCaracter($cnpjNormalizado[13]);

        return $dv1 == $dvOriginal1 && $dv2 == $dvOriginal2;
    }
}

// Testes
$cnpj1 = "19.JA2.KO8/Z001-51"; // CNPJ INVÁLIDO DV INVÁLIDO
$cnpj2 = "19JA2KO8Z00199";     // CNPJ sem máscara, formato inválido
$cnpj3 = "3A.JU2.KX8/Z001-81"; // CNPJ com DV inválido
$cnpj4 = "19.JA2.KO8/Z001-44"; // CNPJ VALIDO

echo "CNPJ $cnpj1 é válido? " . (ValidacaoNovoCnpj::isCnpjValido($cnpj1) ? "Sim" : "Não") . "<br>";
echo "CNPJ $cnpj2 é válido? " . (ValidacaoNovoCnpj::isCnpjValido($cnpj2) ? "Sim" : "Não") . "<br>";
echo "CNPJ $cnpj3 é válido? " . (ValidacaoNovoCnpj::isCnpjValido($cnpj3) ? "Sim" : "Não") . "<br>";
echo "CNPJ $cnpj4 é válido? " . (ValidacaoNovoCnpj::isCnpjValido($cnpj4) ? "Sim" : "Não") . "<br>";
