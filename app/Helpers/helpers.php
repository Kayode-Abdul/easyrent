<?php

use App\Models\Currency;
use App\Helpers\MoneyAmount;

if (!function_exists('format_money')) {
    /**
     * Format an amount with a currency symbol.
     *
     * @param float|int|null $amount
     * @param mixed $currency Currency model, ID, code, or null for default
     * @param bool $includeSymbol Whether to include the currency symbol
     * @return MoneyAmount|string
     */
    function format_money($amount, $currency = null, $includeSymbol = true)
    {
        $amount = (float)($amount ?? 0);
        
        $currencyModel = null;
        
        if ($currency instanceof Currency) {
            $currencyModel = $currency;
        } elseif (is_numeric($currency)) {
            $currencyModel = Currency::find($currency);
        } elseif (is_string($currency) && strlen($currency) === 3) {
            $currencyModel = Currency::where('code', $currency)->first();
        }
        
        // Fallback to default currency if not found
        if (!$currencyModel) {
            try {
                $currencyModel = Currency::where('is_default', true)->first() ?? Currency::first();
            } catch (\Exception $e) {
                // Fallback for cases where DB is not ready
                $currencyModel = null;
            }
        }
        
        $symbol = $currencyModel ? $currencyModel->symbol : '₦';
        
        if ($includeSymbol) {
            return new MoneyAmount($amount, $symbol);
        }
        
        return number_format($amount, 2);
    }
}
