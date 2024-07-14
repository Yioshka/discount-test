<?php

declare(strict_types=1);

namespace App\Service;

use Carbon\Carbon;

final class CalculatePriceService
{
    public function calculateWithDiscount(float $price, Carbon $birthdate, Carbon $startDate, ?Carbon $paymentDate): float
    {
        $discount = $this->calculateChildDiscount($price, $birthdate);
        $price = $price - $discount;
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
            return $price * 0.8;
        }

        if ($years < 12) {
            $discount = $price * 0.3;

            return min($discount, 4500);
        }

        return $price * 0.1;
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
        $dateInterval = [
            'start' => Carbon::create(year: $startDate->year, month: 4),
            'end' => Carbon::create(year: $startDate->year, month: 9, day: 30),
        ];

        if ($startDate->isBetween($dateInterval['start'], $dateInterval['end'])) {
            if (11 === $paymentDate->month && 0 === $years || $years > 1) {
                return min($price * 0.07, 1500);
            }

            if (12 === $paymentDate->month && 1 === $years) {
                return min($price * 0.05, 1500);
            }

            if (1 === $paymentDate->month && 0 === $years) {
                return min($price * 0.03, 1500);
            }

            return 0.0;
        }

        unset($dateInterval);

        $dateIntervalCurrentYear = [
            'start' => Carbon::create(year: $startDate->year, month: 10),
            'end' => Carbon::create(year: $startDate->year, month: 10, day: 31),
        ];

        $dateIntervalNextYear = [
            'start' => Carbon::create(year: $startDate->year),
            'end' => Carbon::create(year: $startDate->year, day: 14),
        ];

        if (
            $startDate->isBetween($dateIntervalCurrentYear['start'], $dateIntervalCurrentYear['end'])
            || $startDate->isBetween($dateIntervalNextYear['start'], $dateIntervalNextYear['end'])
        ) {
            if (3 === $paymentDate->month && 0 === $years || $years > 0) {
                return min($price * 0.07, 1500);
            }

            if (04 === $paymentDate->month && 0 === $years) {
                return min($price * 0.05, 1500);
            }

            if (05 === $paymentDate->month && 0 === $years) {
                return min($price * 0.03, 1500);
            }

            return 0.0;
        }

        unset($dateIntervalCurrentYear, $dateIntervalNextYear);

        if ($startDate->isAfter(Carbon::create($startDate->year, 1, 14)->endOfDay())) {
            if (8 === $paymentDate->month && 1 === $years || $years > 1) {
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
