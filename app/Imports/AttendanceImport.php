<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class AttendanceImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        $headerSkipped = false;

        foreach ($rows as $row) {

            if (!$headerSkipped) {
                $headerSkipped = true;
                continue;
            }

            $empCode = trim($row[0]);
            if (empty($empCode)) {
                continue;
            }

            /** -----------------------
             *       DATE
             * ------------------------*/
            if (!empty($row[1])) {
                if (is_numeric($row[1])) {
                    $date = ExcelDate::excelToDateTimeObject($row[1])->format('Y-m-d');
                } else {
                    $date = date('Y-m-d', strtotime(str_replace('/', '-', $row[1])));
                }
            } else {
                continue;
            }

            /** -----------------------
             *       IN TIME
             * ------------------------*/
            $inTime = null;
            if (!empty($row[2])) {
                if (is_numeric($row[2])) {
                    $inTime = ExcelDate::excelToDateTimeObject($row[2])->format('H:i:s');
                } else {
                    $inTime = date('H:i:s', strtotime($row[2]));
                }
            }

            /** -----------------------
             *       OUT TIME
             * ------------------------*/
            $outTime = null;
            if (!empty($row[3])) {
                if (is_numeric($row[3])) {
                    $outTime = ExcelDate::excelToDateTimeObject($row[3])->format('H:i:s');
                } else {
                    $outTime = date('H:i:s', strtotime($row[3]));
                }
            }

            /** -----------------------
             *       STATUS
             * ------------------------*/
            $status = !empty($row[4]) ? strtoupper(trim($row[4])) : 'A';

            /** -----------------------
             *    FIND USER BY emp_code
             * ------------------------*/
            $user = User::where('emp_code', $empCode)->first();
            if (!$user) {
                continue;
            }

            /** -----------------------
             *    CALCULATE HOURS
             * ------------------------*/

            // default values
            $shiftHours = "09:00:00";
            $workHours = null;
            $otHours = null;

            if ($inTime && $outTime && $status == 'P') {

                // Convert times into seconds
                $inSec = strtotime($date . ' ' . $inTime);
                $outSec = strtotime($date . ' ' . $outTime);

                // Work hours in seconds
                $workSec = $outSec - $inSec;
                if ($workSec < 0) {
                    $workSec = 0; // safety
                }

                // Convert sec → H:i:s
                $workHours = gmdate("H:i:s", $workSec);

                // Shift hours (9 hours = 32400 sec)
                $shiftSec = 9 * 3600;

                // OT = work - shift
                $otSec = $workSec - $shiftSec;
                if ($otSec < 0) {
                    $otSec = 0;
                }

                $otHours = gmdate("H:i:s", $otSec);
            }

            /** -----------------------
             *     SAVE IN DATABASE
             * ------------------------*/
            Attendance::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'date' => $date,
                ],
                [
                    'in_time'    => $inTime,
                    'out_time'   => $outTime,
                    'shift_hours' => $shiftHours,
                    'work_hours'  => $workHours,
                    'ot_hours'    => $otHours,
                    'status'      => $status
                ]
            );
        }
    }
}