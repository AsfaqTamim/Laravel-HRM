<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Income
            ['name' => 'Client Payment',     'code' => 'INC-01', 'type' => 'income'],
            ['name' => 'Service Fee',         'code' => 'INC-02', 'type' => 'income'],
            ['name' => 'Product Sale',        'code' => 'INC-03', 'type' => 'income'],
            ['name' => 'Subscription',        'code' => 'INC-04', 'type' => 'income'],
            ['name' => 'Refund Received',     'code' => 'INC-05', 'type' => 'income'],
            ['name' => 'Interest Income',     'code' => 'INC-06', 'type' => 'income'],
            ['name' => 'Other Income',        'code' => 'INC-07', 'type' => 'income'],

            // Expense
            ['name' => 'Office Rent',         'code' => 'EXP-01', 'type' => 'expense'],
            ['name' => 'Utilities',           'code' => 'EXP-02', 'type' => 'expense'],
            ['name' => 'Office Supplies',     'code' => 'EXP-03', 'type' => 'expense'],
            ['name' => 'Equipment',           'code' => 'EXP-04', 'type' => 'expense'],
            ['name' => 'Software / Licenses', 'code' => 'EXP-05', 'type' => 'expense'],
            ['name' => 'Travel',              'code' => 'EXP-06', 'type' => 'expense'],
            ['name' => 'Meals & Entertainment','code' => 'EXP-07', 'type' => 'expense'],
            ['name' => 'Transport',           'code' => 'EXP-08', 'type' => 'expense'],
            ['name' => 'Salary / Payroll',    'code' => 'EXP-09', 'type' => 'expense'],
            ['name' => 'Marketing & Ads',     'code' => 'EXP-10', 'type' => 'expense'],
            ['name' => 'Maintenance',         'code' => 'EXP-11', 'type' => 'expense'],
            ['name' => 'Tax & Fees',          'code' => 'EXP-12', 'type' => 'expense'],
            ['name' => 'Other Expense',       'code' => 'EXP-13', 'type' => 'expense'],
        ];

        foreach ($accounts as $data) {
            Account::create($data);
        }
    }
}
