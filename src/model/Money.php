<?php

namespace nl\rabobank\gict\payments_savings\omnikassa_sdk\model;

use nl\rabobank\gict\payments_savings\omnikassa_sdk\model\signing\SignatureDataProvider;

class Money implements \JsonSerializable, SignatureDataProvider
{
    /** @var string */
    private $currency;
    /** @var int */
    private $amount;

    /**
     * Construct a Money object with the given currency and the amount in cents.
     *
     * @param string    $currency
     * @param int|float $amount   in cents
     *
     * @return Money
     */
    public static function fromCents($currency, $amount)
    {
        $money = new self();
        $money->setCurrency($currency);
        $money->setAmount(intval($amount));

        return $money;
    }

    /**
     * Construct a Money object with the given currency and the amount in decimals.
     *
     * @param string $currency
     * @param float  $amount
     *
     * @return Money
     */
    public static function fromDecimal($currency, $amount)
    {
        $amountStr = number_format($amount, 3, '.', '');
        $decimalPos = strpos($amountStr, '.');

        $wholePart = (int)substr($amountStr, 0, $decimalPos);
        $decimalPart = substr($amountStr, $decimalPos + 1);

        // Pad or truncate to exactly 3 decimal places
        $decimalPart = str_pad($decimalPart, 3, '0');
        $decimalPart = substr($decimalPart, 0, 3);

        // Manual rounding: if third decimal >= 5, round up
        $firstTwoDecimals = (int)substr($decimalPart, 0, 2);
        $thirdDecimal = (int)substr($decimalPart, 2, 1);

        if (5 <= $thirdDecimal) {
            $firstTwoDecimals += 1;
            // Handle overflow (99 + 1 = 100)
            if (100 === $firstTwoDecimals) {
                $wholePart = $wholePart + 1;
                $firstTwoDecimals = 0;
            }
        }

        $roundedAmountInCents = $wholePart * 100 + $firstTwoDecimals;


        return self::fromCents($currency, $roundedAmountInCents);
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    }

    public function getSignatureData()
    {
        return [$this->currency, $this->amount];
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return ['currency' => $this->currency, 'amount' => $this->amount];
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return ($this->amount / 100).' '.$this->currency;
    }
}
