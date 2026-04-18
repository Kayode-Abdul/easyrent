<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\Log;

class PaymentReceiptController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Generate and download a professional PDF receipt for a payment.
     */
    public function download(Request $request, $transactionId)
    {
        try {
            $payment = Payment::with(['tenant', 'landlord', 'apartment.property', 'currency'])
                ->where('transaction_id', $transactionId)
                ->firstOrFail();

            // Authorization check: Only tenant, landlord, or admin can download
            $user = auth()->user();
            if ($user->user_id !== $payment->tenant_id && 
                $user->user_id !== $payment->landlord_id && 
                !$user->isAdmin()) {
                return back()->with('error', 'Unauthorized access to receipt.');
            }

            // Generate QR Code for verification
            $verificationUrl = url('/verify-payment/' . $payment->transaction_id);
            $qrCode = base64_encode(QrCode::format('png')->size(100)->generate($verificationUrl));

            $data = [
                'payment' => $payment,
                'qrCode' => $qrCode,
                'appName' => config('app.name', 'Easy Rent'),
                'date' => now()->format('F j, Y, g:i a'),
            ];

            $pdf = Pdf::loadView('receipts.payment', $data);
            
            return $pdf->download('Receipt-' . $payment->transaction_id . '.pdf');

        } catch (\Exception $e) {
            Log::error('Failed to generate receipt: ' . $e->getMessage());
            return back()->with('error', 'Could not generate receipt. Please try again.');
        }
    }
}
