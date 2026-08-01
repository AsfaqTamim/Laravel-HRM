<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payslip - {{ $payslip->employee->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #4f46e5; padding-bottom: 10px; }
        .header h1 { color: #4f46e5; margin: 0; font-size: 22px; }
        .header p { margin: 2px 0; color: #666; }
        .section { margin-bottom: 15px; }
        .section h3 { background: #f3f4f6; padding: 6px 10px; margin: 0 0 8px 0; font-size: 13px; border-left: 3px solid #4f46e5; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 5px 8px; border-bottom: 1px solid #e5e7eb; }
        th { text-align: left; font-weight: 600; background: #f9fafb; }
        .text-right { text-align: right; }
        .text-bold { font-weight: bold; }
        .text-success { color: #059669; }
        .text-danger { color: #dc2626; }
        .footer { margin-top: 20px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 10px; color: #999; text-align: center; }
        .employee-info { margin-bottom: 15px; }
        .employee-info td { padding: 3px 8px; border: none; }
        .net-pay { font-size: 18px; font-weight: bold; color: #059669; text-align: center; padding: 10px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 4px; margin-top: 10px; }
        .info-grid { width: 100%; }
        .info-grid td { width: 50%; vertical-align: top; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 3px; font-size: 10px; }
        .badge-finalized { background: #dbeafe; color: #1d4ed8; }
        .badge-computed { background: #fef3c7; color: #d97706; }
    </style>
</head>
<body>
    <div class="header">
        <h1>miniHRM HRM</h1>
        <p>Payslip for {{ $payslip->payrollRun->label }}</p>
        <p><span class="badge badge-{{ $payslip->status }}">{{ ucfirst($payslip->status) }}</span></p>
    </div>

    <table class="employee-info">
        <tr>
            <td><strong>Employee:</strong> {{ $payslip->employee->name }}</td>
            <td><strong>Department:</strong> {{ $payslip->employee->department->name ?? '-' }}</td>
        </tr>
        <tr>
            <td><strong>Employee Code:</strong> {{ $payslip->employee->employee_code ?? '-' }}</td>
            <td><strong>Designation:</strong> {{ $payslip->employee->designation->name ?? '-' }}</td>
        </tr>
    </table>

    <div class="section">
        <h3>Earnings</h3>
        <table>
            <tr>
                <td>Basic Salary</td>
                <td class="text-right">৳ {{ number_format($payslip->basic_salary, 2) }}</td>
            </tr>
            <tr>
                <td>House Rent Allowance</td>
                <td class="text-right">৳ {{ number_format($payslip->house_rent_allowance, 2) }}</td>
            </tr>
            <tr>
                <td>Medical Allowance</td>
                <td class="text-right">৳ {{ number_format($payslip->medical_allowance, 2) }}</td>
            </tr>
            <tr>
                <td>Conveyance Allowance</td>
                <td class="text-right">৳ {{ number_format($payslip->conveyance_allowance, 2) }}</td>
            </tr>
            @if($payslip->other_allowance > 0)
            <tr>
                <td>Other Allowance</td>
                <td class="text-right">৳ {{ number_format($payslip->other_allowance, 2) }}</td>
            </tr>
            @endif
            <tr class="text-bold">
                <td>Gross Pay</td>
                <td class="text-right text-success">৳ {{ number_format($payslip->gross_pay, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="section">
        <h3>Deductions</h3>
        <table>
            @if($payslip->unpaid_leave_days > 0)
            <tr>
                <td>Unpaid Leave ({{ $payslip->unpaid_leave_days }} days)</td>
                <td class="text-right">৳ {{ number_format($payslip->unpaid_leave_deduction, 2) }}</td>
            </tr>
            @endif
            @if($payslip->provident_fund > 0)
            <tr>
                <td>Provident Fund</td>
                <td class="text-right">৳ {{ number_format($payslip->provident_fund, 2) }}</td>
            </tr>
            @endif
            @if($payslip->tax > 0)
            <tr>
                <td>Tax</td>
                <td class="text-right">৳ {{ number_format($payslip->tax, 2) }}</td>
            </tr>
            @endif
            @if($payslip->other_deduction > 0)
            <tr>
                <td>Other Deduction</td>
                <td class="text-right">৳ {{ number_format($payslip->other_deduction, 2) }}</td>
            </tr>
            @endif
            <tr class="text-bold">
                <td>Total Deductions</td>
                <td class="text-right text-danger">৳ {{ number_format($payslip->total_deductions, 2) }}</td>
            </tr>
        </table>
    </div>

    <div class="net-pay">
        Net Pay: ৳ {{ number_format($payslip->net_pay, 2) }}
    </div>

    <div class="footer">
        <p>Generated on {{ now()->format('d M Y, h:i A') }} | miniHRM HRM Payroll System</p>
        <p>This is a computer-generated document.</p>
    </div>
</body>
</html>
