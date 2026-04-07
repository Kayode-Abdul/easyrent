<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\BenefactorPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentWebhookController extends Controller
{
    /**
     * Handle Paystack webhook events
     */
    public function handlePaystackWebhook(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('x-paystack-signature');
        $body = $request->getContent();
        $expectedSignature = hash_hmac('sha512', $body, config('paystack.secretKey'));
        
        if (!hash_equals($expectedSignature, $signature)) {
            Log::warning('Invalid Paystack webhook signature', [
                'received_signature' => $signature,
                'expected_signature' => $expectedSignature
            ]);
            return response()->json(['error' => 'Invalid signature'], 400);
        }
        
        $event = $request->json()->all();
        
        Log::info('Paystack webhook received', [
            'event' => $event['event'] ?? 'unknown',
            'reference' => $event['data']['reference'] ?? 'unknown'
        ]);
        
        try {
            switch ($event['event']) {
                case 'charge.success':
                    return $this->handleChargeSuccess($event['data']);
                    
                case 'charge.failed':
                    return $this->handleChargeFailed($event['data']);
                    
                case 'transfer.success':
                    return $this->handleTransferSuccess($event['data']);
                    
                case 'transfer.failed':
                    return $this->handleTransferFailed($event['data']);
                    
                default:
                    Log::info('Unhandled webhook event', ['event' => $event['event']]);
                    return response()->json(['message' => 'Event not handled'], 200);
            }
        } catch (\Exception $e) {
            Log::error('Webhook processing error', [
                'error' => $e->getMessage(),
                'event' => $event['event'] ?? 'unknown',
                'reference' => $event['data']['reference'] ?? 'unknown'
            ]);
            
            return response()->json(['error' => 'Processing failed'], 500);
        }
    }
    
    /**
     * Handle successful charge
     */
    private function handleChargeSuccess($data)
    {
        $reference = $data['reference'];
        
        Log::info('Processing charge success', ['reference' => $reference]);
        
        DB::beginTransaction();
        
        try {
            // Find payment by reference
            $payment = Payment::where('transaction_id', $reference)
                ->orWhere('payment_reference', $reference)
                ->first();
            
            if (!$payment) {
                // Check benefactor payments
                $benefactorPayment = BenefactorPayment::where('transaction_id', $reference)
                    ->orWhere('payment_reference', $reference)
                    ->first();
                
                if ($benefactorPayment) {
                    return $this->updateBenefactorPaymentStatus($benefactorPayment, 'completed', $data);
                }
                
                Log::warning('Payment not found for successful charge', ['reference' => $reference]);
                return response()->json(['message' => 'Payment not found'], 404);
            }
            
            // Update payment status if not already completed
            if ($payment->status !== 'completed' && $payment->status !== 'success') {
                $payment->update([
                    'status' => 'completed',
                    'paid_at' => now(),
                    'payment_method' => $data['channel'] ?? $payment->payment_method,
                    'payment_meta' => json_encode(array_merge(
                        json_decode($payment->payment_meta ?? '{}', true),
                        [
                            'webhook_data' => $data,
                            'updated_via_webhook' => true,
                            'webhook_timestamp' => now()->toISOString()
                        ]
                    ))
                ]);
                
                Log::info('Payment status updated to completed via webhook', [
                    'payment_id' => $payment->id,
                    'reference' => $reference
                ]);
                
                // Trigger post-payment processing
                $this->triggerPostPaymentProcessing($payment);
            }
            
            DB::commit();
            
            return response()->json(['message' => 'Payment updated successfully'], 200);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            Log::error('Error updating payment status', [
                'reference' => $reference,
                'error' => $e->getMessage()
            ]);
            
            return response()->json(['error' => 'Update failed'], 500);
        }
    }
    
    /**
     * Handle failed charge
     */
    private function handleChargeFailed($data)
    {
        $reference = $data['reference'];
        
        Log::info('Processing charge failure', ['reference' => $reference]);
        
        // Find payment by reference
        $payment = Payment::where('transaction_id', $reference)
            ->orWhere('payment_reference', $reference)
            ->first();
        
        if (!$payment) {
            // Check benefactor payments
            $benefactorPayment = BenefactorPayment::where('transaction_id', $reference)
                ->orWhere('payment_reference', $reference)
                ->first();
            
            if ($benefactorPayment) {
                return $this->updateBenefactorPaymentStatus($benefactorPayment, 'failed', $data);
            }
            
            Log::warning('Payment not found for failed charge', ['reference' => $reference]);
            return response()->json(['message' => 'Payment not found'], 404);
        }
        
        // Update payment status to failed
        $payment->update([
            'status' => 'failed',
            'payment_meta' => json_encode(array_merge(
                json_decode($payment->payment_meta ?? '{}', true),
                [
                    'failure_reason' => $data['gateway_response'] ?? 'Payment failed',
                    'webhook_data' => $data,
                    'failed_via_webhook' => true,
                    'webhook_timestamp' => now()->toISOString()
                ]
            ))
        ]);
        
        Log::info('Payment status updated to failed via webhook', [
            'payment_id' => $payment->id,
            'reference' => $reference
        ]);
        
        return response()->json(['message' => 'Payment failure recorded'], 200);
    }
    
    /**
     * Update benefactor payment status
     */
    private function updateBenefactorPaymentStatus($benefactorPayment, $status, $data)
    {
        $benefactorPayment->update([
            'status' => $status,
            'paid_at' => $status === 'completed' ? now() : null,
            'payment_method' => $data['channel'] ?? $benefactorPayment->payment_method,
            'payment_meta' => json_encode(array_merge(
                json_decode($benefactorPayment->payment_meta ?? '{}', true),
                [
                    'webhook_data' => $data,
                    'updated_via_webhook' => true,
                    'webhook_timestamp' => now()->toISOString()
                ]
            ))
        ]);
        
        Log::info('Benefactor payment status updated via webhook', [
            'benefactor_payment_id' => $benefactorPayment->id,
            'status' => $status,
            'reference' => $data['reference']
        ]);
        
        return response()->json(['message' => 'Benefactor payment updated successfully'], 200);
    }
    
    /**
     * Trigger post-payment processing
     */
    private function triggerPostPaymentProcessing($payment)
    {
        try {
            // Send notifications
            if ($payment->tenant && $payment->tenant->email) {
                \Mail::to($payment->tenant->email)->send(new \App\Mail\PaymentReceiptMail($payment));
            }
            
            if ($payment->landlord && $payment->landlord->email) {
                \Mail::to($payment->landlord->email)->send(new \App\Mail\LandlordPaymentNotification($payment));
            }
            
            // Update apartment status if needed
            if ($payment->apartment) {
                $payment->apartment->update([
                    'tenant_id' => $payment->tenant_id,
                    'occupied' => true,
                    'range_start' => now(),
                    'range_end' => now()->addMonths($payment->duration ?? 12)
                ]);
            }
            
            Log::info('Post-payment processing completed', ['payment_id' => $payment->id]);
            
        } catch (\Exception $e) {
            Log::error('Post-payment processing failed', [
                'payment_id' => $payment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle transfer success (for landlord payouts)
     */
    private function handleTransferSuccess($data)
    {
        Log::info('Transfer success received', ['reference' => $data['reference']]);
        
        // Handle landlord payout success
        // This would be implemented based on your payout system
        
        return response()->json(['message' => 'Transfer success recorded'], 200);
    }
    
    /**
     * Handle transfer failure (for landlord payouts)
     */
    private function handleTransferFailed($data)
    {
        Log::info('Transfer failure received', ['reference' => $data['reference']]);
        
        // Handle landlord payout failure
        // This would be implemented based on your payout system
        
        return response()->json(['message' => 'Transfer failure recorded'], 200);
    }
}