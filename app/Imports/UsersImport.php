<?php
namespace App\Imports;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
      return new User([
            'name' => $row['name'],
            'emp_code' => $row['emp_code'],
            'email' => $row['email'],
            'designation' => $row['designation'],
            'joining_date' => $row['joining_date'],
            'aadhar_number' => $row['aadhar_number'],
            'pan_number' => $row['pan_number'],
            'father_name' => $row['father_name'],
            'mother_name' => $row['mother_name'],
            'years_of_experience' => $row['years_of_experience'],
            'training_experience' => $row['training_experience'],
            'previous_company_name' => $row['previous_company_name'],
            'previous_designation' => $row['previous_designation'],
            'previous_company_duration' => $row['previous_company_duration'],
            'role' => $row['role'],
            'password' => Hash::make($row['password'] ?? 'password123')
        ]);
    }
}