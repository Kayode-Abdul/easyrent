<?php
require 'vendor/autoload.php';

try {
    if (function_exists('format_money')) {
        echo "Function exists!\n";
        $result = format_money(123.45);
        echo "Format for 123.45: " . $result . "\n";
        echo "Symbol: " . $result->getSymbol() . "\n";
    } else {
        echo "FUNCTION DOES NOT EXIST!\n";
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
