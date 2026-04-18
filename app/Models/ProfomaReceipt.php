<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfomaReceipt extends Model
{
    use HasFactory;

    protected $table = 'profoma_receipt';

    protected $fillable = [
        'user_id',
        'tenant_id',
        'status',
        'transaction_id',
        'apartment_id',
        'amount',
        'duration',
        'security_deposit',
        'water',
        'internet',
        'generator',
        'other_charges_desc',
        'other_charges_amount',
        'total',
        'calculation_method',
        'calculation_log',
        'currency_id',
    ];

    protected $casts = [
        'calculation_log' => 'array',
    ];

    // Status constants
    const STATUS_REJECTED = 0;
    const STATUS_CONFIRMED = 1;
    const STATUS_NEW = 2;
    const STATUS_PAID = 4;

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function apartment()
    {
        // The apartment_id field in proforma_receipt refers to apartments.apartment_id field
        // not apartments.id (primary key)
        return $this->belongsTo(Apartment::class, 'apartment_id', 'apartment_id');
    }

    public function tenant()
    {
        return $this->belongsTo(User::class, 'tenant_id', 'user_id');
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    // Human-readable status label, including legacy value 3
    public function getStatusLabelAttribute(): string
    {
        switch ((int) $this->status) {
            case self::STATUS_REJECTED:
                return 'Rejected';
            case self::STATUS_CONFIRMED:
                return 'Confirmed';
            case self::STATUS_NEW:
                return 'New';
            case 3:
                return 'Draft'; // legacy/new-unsent by landlord
            case self::STATUS_PAID:
                return 'Paid';
            default:
                return (string) $this->status;
        }
    }

    /**
     * Get benefactor payment invitations for this proforma
     */
    public function benefactorInvitations()
    {
        return $this->hasMany(PaymentInvitation::class, 'proforma_id');
    }

    /**
     * Get benefactor payments for this proforma
     */
    public function benefactorPayments()
    {
        return $this->hasMany(BenefactorPayment::class, 'proforma_id');
    }

    /**
     * Check if proforma has been paid by benefactor
     */
    public function isPaidByBenefactor(): bool
    {
        return $this->benefactorPayments()
            ->where('status', 'completed')
            ->exists();
    }

    /**
     * Check if this proforma has been paid successfully
     */
    public function hasSuccessfulPayment(): bool
    {
        return Payment::where('transaction_id', $this->transaction_id)
            ->whereIn('status', [Payment::STATUS_SUCCESS, Payment::STATUS_COMPLETED])
            ->exists();
    }

    /**
     * Get the successful payment for this proforma
     */
    public function getSuccessfulPayment()
    {
        return Payment::where('transaction_id', $this->transaction_id)
            ->whereIn('status', [Payment::STATUS_SUCCESS, Payment::STATUS_COMPLETED])
            ->first();
    }

    /**
     * Get any payment attempts for this proforma
     */
    public function payments()
    {
        return Payment::where('transaction_id', $this->transaction_id)->get();
    }

    /**
     * Store calculation details for audit trail
     */
    public function storeCalculationDetails(string $method, array $steps, array $metadata = []): void
    {
        $this->calculation_method = $method;
        $this->calculation_log = [
            'method' => $method,
            'steps' => $steps,
            'metadata' => $metadata,
            'timestamp' => now()->toISOString(),
            'version' => '1.0'
        ];
        $this->save();
    }

    /**
     * Retrieve calculation details
     */
    public function getCalculationDetails(): ?array
    {
        return $this->calculation_log;
    }

    /**
     * Get calculation method used
     */
    public function getCalculationMethod(): ?string
    {
        return $this->calculation_method;
    }

    /**
     * Get calculation steps for display
     */
    public function getCalculationSteps(): array
    {
        if (!$this->calculation_log || !isset($this->calculation_log['steps'])) {
            return [];
        }
        
        return $this->calculation_log['steps'];
    }

    /**
     * Get calculation metadata
     */
    public function getCalculationMetadata(): array
    {
        if (!$this->calculation_log || !isset($this->calculation_log['metadata'])) {
            return [];
        }
        
        return $this->calculation_log['metadata'];
    }

    /**
     * Check if calculation details are available
     */
    public function hasCalculationDetails(): bool
    {
        return !empty($this->calculation_log) && !empty($this->calculation_method);
    }

    /**
     * Get formatted calculation breakdown for display
     */
    public function getCalculationBreakdownAttribute(): array
    {
        if (!$this->hasCalculationDetails()) {
            return [
                'method' => 'Legacy calculation',
                'steps' => [],
                'total' => $this->total,
                'timestamp' => null
            ];
        }

        $steps = $this->getCalculationSteps();
        $metadata = $this->getCalculationMetadata();
        
        return [
            'method' => $this->calculation_method,
            'steps' => $steps,
            'total' => $this->total,
            'timestamp' => $this->calculation_log['timestamp'] ?? null,
            'metadata' => $metadata,
            'version' => $this->calculation_log['version'] ?? '1.0'
        ];
    }

    /**
     * Get human-readable calculation summary
     */
    public function getCalculationSummaryAttribute(): string
    {
        $currency = $this->currency ?? ($this->apartment->currency ?? null);
        if (!$this->hasCalculationDetails()) {
            return "Legacy calculation - Total: " . format_money($this->total, $currency);
        }

        $method = $this->calculation_method;
        $timestamp = $this->calculation_log['timestamp'] ?? 'Unknown';
        
        return "Method: {$method} - Total: " . format_money($this->total, $currency) . " (Calculated: {$timestamp})";
    }

    /**
     * Relationship to audit logs for this proforma calculation
     */
    public function auditLogs()
    {
        return $this->hasMany(AuditLog::class, 'model_id')
            ->where('model_type', self::class)
            ->where('action', 'calculation')
            ->orderBy('created_at', 'desc');
    }

    /**
     * Create audit log entry for calculation
     */
    public function logCalculationAudit(array $calculationData, ?User $user = null): void
    {
        $auditData = [
            'action' => 'calculation',
            'model_type' => self::class,
            'model_id' => $this->id,
            'description' => "Payment calculation performed using method: {$this->calculation_method}",
            'old_values' => [],
            'new_values' => $calculationData,
            'user_id' => $user ? $user->user_id : (auth()->check() ? auth()->user()->user_id : null),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'performed_at' => now()
        ];

        AuditLog::create($auditData);
    }

    /**
     * Get calculation history from audit logs
     */
    public function getCalculationHistory(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->auditLogs()
            ->with('user')
            ->get();
    }

    /**
     * Check if calculation has been modified since creation
     */
    public function hasCalculationModifications(): bool
    {
        return $this->auditLogs()->count() > 1;
    }

    /**
     * Get the original calculation details
     */
    public function getOriginalCalculation(): ?AuditLog
    {
        return $this->auditLogs()
            ->orderBy('created_at', 'asc')
            ->first();
    }

    /**
     * Get the latest calculation modification
     */
    public function getLatestCalculationModification(): ?AuditLog
    {
        return $this->auditLogs()
            ->orderBy('created_at', 'desc')
            ->first();
    }
}
