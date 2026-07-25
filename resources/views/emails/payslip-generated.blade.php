<x-mail::message>
# Payslip Generated

Hello **{{ $payslip->employee->name }}**,

Your payslip for **{{ $payslip->period }}** has been generated and is now available.

| Detail | Info |
|---|---|
| **Period** | {{ $payslip->period }} |
| **Basic Salary** | {{ number_format($payslip->basic_salary, 2) }} |
| **Total Allowances** | {{ number_format($payslip->total_allowances, 2) }} |
| **Total Deductions** | {{ number_format($payslip->total_deductions, 2) }} |
| **Net Pay** | **{{ number_format($payslip->net_pay, 2) }}** |
| **Status** | {{ ucfirst($payslip->status) }} |

<x-mail::button :url="config('app.url')">
View My Payslips
</x-mail::button>

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
