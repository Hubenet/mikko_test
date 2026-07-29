<?php

namespace App\Service;

use DateTimeImmutable;

class PayrollService
{
    /**
     * Returns an array of payment rows for each month from the given date's month
     * through December of the same year. Each row: ['month' => 'January', 'salary' => 'YYYY-MM-DD', 'bonus' => 'YYYY-MM-DD']
     */
    public function getPaymentDatesForRemainderOfYear(DateTimeImmutable $from = null): array
    {
        $from = $from ?? new DateTimeImmutable('now');
        $year = (int) $from->format('Y');
        $startMonth = (int) $from->format('n');

        $results = [];

        for ($m = $startMonth; $m <= 12; $m++) {
            $firstOfMonth = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $m));

            // Salary: last day of the month, move earlier if weekend to previous weekday
            $salary = $firstOfMonth->modify('last day of this month');
            while ((int) $salary->format('N') >= 6) { // 6 = Saturday, 7 = Sunday
                $salary = $salary->modify('-1 day');
            }

            // Bonus: normally on the 15th of the month. If 15th is weekend, pay the first Wednesday after the 15th.
            $bonus = new DateTimeImmutable(sprintf('%04d-%02d-15', $year, $m));
            if ((int) $bonus->format('N') >= 6) {
                // find first Wednesday (N === 3) after the 15th
                while ((int) $bonus->format('N') !== 3) {
                    $bonus = $bonus->modify('+1 day');
                }
            }

            $results[] = [
                'month' => $firstOfMonth->format('F'),
                'salary' => $salary->format('Y-m-d'),
                'bonus' => $bonus->format('Y-m-d'),
            ];
        }

        return $results;
    }
}
