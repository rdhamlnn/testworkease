<?php

namespace App\Traits;

use Carbon\Carbon;

trait ReportHelper
{
    /**
     * Calculate week date range for real calendar weeks (Monday to Sunday) using Indonesia timezone
     */
    protected function calculateWeekRange($tahun, $bulan, $weekNumber)
    {
        // Get the first day of the month using Indonesia timezone
        $firstDayOfMonth = new \DateTime($tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01', new \DateTimeZone('Asia/Makassar'));
        
        // Get the last day of the month
        $lastDayOfMonth = clone $firstDayOfMonth;
        $lastDayOfMonth->modify('last day of this month');
        $lastDay = (int)$lastDayOfMonth->format('d');
        
        $startDay = (($weekNumber - 1) * 7) + 1;
        $endDay = min($startDay + 6, $lastDay);
        
        // If week number is beyond the month, return null
        if ($startDay > $lastDay) {
            return null;
        }
        
        // Create start and end dates
        $weekStartDate = new \DateTime($tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($startDay, 2, '0', STR_PAD_LEFT), new \DateTimeZone('Asia/Makassar'));
        $weekEndDate = new \DateTime($tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-' . str_pad($endDay, 2, '0', STR_PAD_LEFT), new \DateTimeZone('Asia/Makassar'));
        
        return [
            'start' => $weekStartDate->format('Y-m-d'),
            'end' => $weekEndDate->format('Y-m-d')
        ];
    }
}
