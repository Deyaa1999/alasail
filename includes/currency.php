<?php
/**
 * Al Asail Equine Veterinary Supplies
 * includes/currency.php — JOD as default currency
 */
const CURRENCIES = [
    'JOD' => ['symbol'=>'JOD ', 'name'=>'Jordanian Dinar',    'rate'=>1.0,     'stripe'=>'jod', 'zero_decimal'=>false],
    'USD' => ['symbol'=>'$ ',  'name'=>'US Dollar',           'rate'=>1.4104,  'stripe'=>'usd', 'zero_decimal'=>false],
    'EUR' => ['symbol'=>'€',   'name'=>'Euro',                'rate'=>1.2960,  'stripe'=>'eur', 'zero_decimal'=>false],
    'GBP' => ['symbol'=>'£',   'name'=>'British Pound',       'rate'=>1.1120,  'stripe'=>'gbp', 'zero_decimal'=>false],
    'SAR' => ['symbol'=>'﷼',   'name'=>'Saudi Riyal',         'rate'=>5.2860,  'stripe'=>'sar', 'zero_decimal'=>false],
    'AED' => ['symbol'=>'د.إ', 'name'=>'UAE Dirham',          'rate'=>5.1790,  'stripe'=>'aed', 'zero_decimal'=>false],
    'BDT' => ['symbol'=>'৳',   'name'=>'Bangladeshi Taka',    'rate'=>164.0,   'stripe'=>'bdt', 'zero_decimal'=>false],
];

function getActiveCurrency(): array {
    global $conn;
    if (!empty($_SESSION['active_currency_data']) && isset($_SESSION['active_currency_data']['code'])) {
        $code = $_SESSION['active_currency_data']['code'];
        if (isset(CURRENCIES[$code])) {
            $cur = CURRENCIES[$code];
            $cur['code'] = $code;
            $_SESSION['active_currency_data'] = $cur;
            return $cur;
        }
    }
    $res = $conn->query("SELECT setting_value FROM settings WHERE setting_key='active_currency'");
    $code = ($res && $res->num_rows) ? $res->fetch_assoc()['setting_value'] : 'JOD';
    $cur = CURRENCIES[$code] ?? CURRENCIES['JOD'];
    $cur['code'] = $code;
    $_SESSION['active_currency_data'] = $cur;
    return $cur;
}

function convertAmount(float $amount): float {
    $cur = getActiveCurrency();
    return $amount * $cur['rate'];
}

function formatPrice(float $amount): string {
    $cur = getActiveCurrency();
    $converted = $amount * $cur['rate'];
    if ($cur['zero_decimal']) {
        return $cur['symbol'] . number_format($converted, 0);
    }
    return $cur['symbol'] . number_format($converted, 2);
}

function getStripeAmount(float $amount): int {
    $cur = getActiveCurrency();
    $converted = $amount * $cur['rate'];
    return $cur['zero_decimal'] ? (int)round($converted) : (int)round($converted * 100);
}

function getStripeCurrencyCode(): string {
    return getActiveCurrency()['stripe'];
}
