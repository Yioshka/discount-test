<?php

declare(strict_types=1);

namespace App\Service;

use Carbon\Carbon;

final class CalculatePriceService
{
    private const int CHILD_MAX_DISCOUNT = 4500;

    private const int PAYMENT_MAX_DISCOUNT = 1500;

    public function calculateWithDiscount(float $price, Carbon $birthDate, Carbon $startDate, ?Carbon $paymentDate): float
    {
        $discount = $this->calculateChildDiscount($price, $birthDate);
        $price -= $discount;
        $paymentDiscount = $this->calculatePaymentDiscount($price, $startDate, $paymentDate);

        return $price - $paymentDiscount;
    }

    private function calculateChildDiscount(float $price, Carbon $birthdate): float
    {
        $years = (int) $birthdate->diffInYears(Carbon::now()->startOfDay());

        if ($years < 3 || $years >= 18) {
            return 0.0;
        }

        if ($years < 6) {
            return min(self::CHILD_MAX_DISCOUNT, $price * 0.8);
        }

        if ($years < 12) {
            return min(self::CHILD_MAX_DISCOUNT, $price * 0.3);
        }

        return min(self::CHILD_MAX_DISCOUNT, $price * 0.1);
    }

    private function calculatePaymentDiscount(float $price, Carbon $startDate, ?Carbon $paymentDate): float
    {
        if (
            null === $paymentDate
            || Carbon::now()->startOfDay()->equalTo($startDate)
            || $paymentDate->equalTo($startDate)
            || $paymentDate->isAfter($startDate)
        ) {
            return 0.0;
        }

        $years = $startDate->year - $paymentDate->year;
        /** @var array{start: Carbon, end: Carbon} $dateInterval */
        $dateInterval = [
            'start' => Carbon::create(year: $startDate->year, month: 4),
            'end' => Carbon::create(year: $startDate->year, month: 9, day: 30),
        ];

        if ($startDate->isBetween($dateInterval['start'], $dateInterval['end'])) {
            if (11 === $paymentDate->month || $years > 1) {
                return min($price * 0.07, self::PAYMENT_MAX_DISCOUNT);
            }

            if (12 === $paymentDate->month) {
                return min($price * 0.05, self::PAYMENT_MAX_DISCOUNT);
            }

            if (1 === $paymentDate->month) {
                return min($price * 0.03, self::PAYMENT_MAX_DISCOUNT);
            }

            return 0.0;
        }

        unset($dateInterval);

        /** @var array{start: Carbon, end: Carbon} $dateIntervalCurrentYear */
        $dateIntervalCurrentYear = [
            'start' => Carbon::create(year: $startDate->year, month: 10),
            'end' => Carbon::create(year: $startDate->year, month: 10, day: 31),
        ];
        /** @var array{start: Carbon, end: Carbon} $dateIntervalNextYear */
        $dateIntervalNextYear = [
            'start' => Carbon::create(year: $startDate->year),
            'end' => Carbon::create(year: $startDate->year, day: 14),
        ];

        if (
            $startDate->isBetween($dateIntervalCurrentYear['start'], $dateIntervalCurrentYear['end'])
            || $startDate->isBetween($dateIntervalNextYear['start'], $dateIntervalNextYear['end'])
        ) {
            if (3 === $paymentDate->month || $years > 2) {
                return min($price * 0.07, self::PAYMENT_MAX_DISCOUNT);
            }

            if (4 === $paymentDate->month) {
                return min($price * 0.05, self::PAYMENT_MAX_DISCOUNT);
            }

            if (5 === $paymentDate->month) {
                return min($price * 0.03, self::PAYMENT_MAX_DISCOUNT);
            }

            return 0.0;
        }

        unset($dateIntervalCurrentYear, $dateIntervalNextYear);

        /** @var Carbon $date */
        $date = Carbon::create($startDate->year, 9, 30);

        if ($startDate->isAfter($date->endOfDay())) {
            if (8 === $paymentDate->month || $years > 1) {
                return min($price * 0.07, 1500);
            }

            if (9 === $paymentDate->month && 1 === $years) {
                return min($price * 0.05, 1500);
            }

            if (10 === $paymentDate->month && 1 === $years) {
                return min($price * 0.03, 1500);
            }

            return 0.0;
        }

        return 0.0;
    }
}
