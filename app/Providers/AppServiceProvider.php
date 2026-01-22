<?php

// Em app/Providers/AppServiceProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidCNPJ;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Registre quaisquer serviços de aplicativo.
     *
     * @return void
     */
    public function boot()
    {
        Validator::extend('cnpj', function($attribute, $value, $parameters, $validator) {
            return $this->validCNPJ($value);
        });
    }

    /**
     * Função para validar um CNPJ.
     *
     * @param $cnpj
     * @return bool
     */
    private function validCNPJ($cnpj)
    {
        // Limpa o CNPJ
        $cnpj = preg_replace('/[^0-9]/', '', $cnpj);

        // Verifica se o CNPJ possui 14 caracteres
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Verifica se todos os caracteres são iguais, o que invalida o CNPJ
        if (preg_match('/(\d)\1{13}/', $cnpj)) {
            return false;
        }

        // Calcula o primeiro dígito verificador
        $firstDigit = $this->calculateFirstCNPJVerifier($cnpj);
        
        // Calcula o segundo dígito verificador
        $secondDigit = $this->calculateSecondCNPJVerifier($cnpj);

        // Verifica se o CNPJ informado é válido
        return $cnpj[12] == $firstDigit && $cnpj[13] == $secondDigit;
    }

    /**
     * Cálculo do primeiro dígito verificador do CNPJ
     */
    private function calculateFirstCNPJVerifier($cnpj)
    {
        $weights = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        return $this->calculateCNPJVerifier($cnpj, $weights);
    }

    /**
     * Cálculo do segundo dígito verificador do CNPJ
     */
    private function calculateSecondCNPJVerifier($cnpj)
    {
        $weights = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        return $this->calculateCNPJVerifier($cnpj, $weights);
    }

    /**
     * Cálculo do dígito verificador do CNPJ
     */
    private function calculateCNPJVerifier($cnpj, $weights)
    {
        $sum = 0;

        foreach ($weights as $index => $weight) {
            $sum += $cnpj[$index] * $weight;
        }

        $remainder = $sum % 11;
        if ($remainder < 2) {
            return 0;
        }

        return 11 - $remainder;
    }
}
