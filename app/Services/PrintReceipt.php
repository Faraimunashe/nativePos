<?php

namespace App\Services;

use App\Models\CartItem;
use App\Context\SharedCart;
use Exception;

class PrintReceipt
{
    private $cashierName;
    private $saleDateTime;
    private $saleRef;
    private $amountPaid;
    private $change;
    private $transactionSource;
    private $totalAmount;
    private $currency;

    public function __construct(
        $cashierName,
        $saleDateTime,
        $saleRef,
        $amountPaid,
        $change,
        $transactionSource,
        $totalAmount,
        $currency
    ) {
        $this->cashierName = $cashierName;
        $this->saleDateTime = $saleDateTime;
        $this->saleRef = $saleRef;
        $this->amountPaid = $amountPaid;
        $this->change = $change;
        $this->transactionSource = $transactionSource;
        $this->totalAmount = $totalAmount;
        $this->currency = $currency;
    }

    public function printReceipt()
    {

    }

    private function showAlert($title, $message)
    {

    }

    private function generateQRCode($data)
    {

    }
}
