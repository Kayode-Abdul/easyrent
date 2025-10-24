<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;

class PaymentsExport implements FromCollection, WithHeadings
{
    protected $payments;

    public function __construct($payments)
    {
        $this->payments = $payments;
    }

    public function collection()
    {
        return $this->payments->map(function($payment) {
            return [
                'Transaction ID' => $payment->transaction_id,
                'Amount' => $payment->amount,
                'Status' => $payment->status,
                'Payment Method' => $payment->payment_method,
                'Date' => $payment->created_at->format('Y-m-d H:i:s'),
                'Tenant' => $payment->tenant->name,
                'Property' => $payment->apartment->name,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Transaction ID',
            'Amount',
            'Status',
            'Payment Method',
            'Date',
            'Tenant',
            'Property',
        ];
    }
}
