<?php

namespace App\Services\Commission;

use App\Models\CommissionRate;
use App\Models\Role;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Exception;

class RegionalRateManager
{
    /**
     * Maximum allowed total commission percentage (2.5%)
     */
    const MAX_TOTAL_COMMISSION = 2.5000;

    /**
     * Set commission rate for a specific region and role
     *
     * @param string $region
     * @param int $roleId
     * @param float $rate
     * @param int $createdBy
     * @param Carbon|null $effectiveFrom
     * @return bool
     * @throws Exception
     */
    public function setRegionalRate(
        string $region, 
        int $roleId, 
        float $rate, 
        int $createdBy,
        ?Carbon $effectiveFrom = null
    ): bool {
        try {
            DB::beginTransaction();

            // Validate the rate
            $this->validateRate($rate);
            
            // Validate that total rates don't exceed maximum
            $this->validateTotalRates($region, $roleId, $rate);

            $effectiveFrom = $effectiveFrom ?? now();

            // Deactivate current rate if exists
            $this->deactivateCurrentRate($region, $roleId, $effectiveFrom);

            // Create new rate
            $commissionRate = CommissionRate::create([
                'region' => $region,
                'role_id' => $roleId,
                'commission_percentage' => $rate,
                'effective_from' => $effectiveFrom,
                'created_by' => $createdBy,
                'is_active' => true
            ]);

            DB::commit();

            Log::info('Regional commission rate updated', [
                'region' => $region,
                'role_id' => $roleId,
                'rate' => $rate,
                'created_by' => $createdBy,
                'commission_rate_id' => $commissionRate->id
            ]);

            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Failed to set regional rate', [
                'region' => $region,
                'role_id' => $roleId,
                'rate' => $rate,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Get active commission rate for a region and role
     *
     * @param string $region
     * @param int $roleId
     * @return float
     */
    public function getActiveRate(string $region, int $roleId): float
    {
        $rate = CommissionRate::active()
            ->forRegion($region)
            ->forRole($roleId)
            ->orderBy('effective_from', 'desc')
            ->first();

        return $rate ? (float) $rate->commission_percentage : 0.0;
    }

    /**
     * Validate rate configuration for a region
     *
     * @param array $rates Array of ['role_id' => rate] pairs
     * @param string $region
     * @return array Validation results
     */
    public function validateRateConfiguration(array $rates, string $region): array
    {
        $errors = [];
        $totalRate = 0;

        foreach ($rates as $roleId => $rate) {
            // Validate individual rate
            if ($rate < 0 || $rate > self::MAX_TOTAL_COMMISSION) {
                $errors[] = "Rate for role {$roleId} must be between 0 and " . self::MAX_TOTAL_COMMISSION . "%";
            }

            // Validate role exists
            if (!Role::find($roleId)) {
                $errors[] = "Role {$roleId} does not exist";
            }

            $totalRate += $rate;
        }

        // Validate total doesn't exceed maximum
        if ($totalRate > self::MAX_TOTAL_COMMISSION) {
            $errors[] = "Total commission rate ({$totalRate}%) exceeds maximum allowed (" . self::MAX_TOTAL_COMMISSION . "%)";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'total_rate' => $totalRate
        ];
    }

    /**
     * Get historical rates for a region and role
     *
     * @param string $region
     * @param int $roleId
     * @param int $limit
     * @return Collection
     */
    public function getHistoricalRates(string $region, int $roleId, int $limit = 50): Collection
    {
        return CommissionRate::forRegion($region)
            ->forRole($roleId)
            ->with(['role', 'creator'])
            ->orderBy('effective_from', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Bulk update rates for multiple regions and roles
     *
     * @param array $regionRates Array of region => [role_id => rate] mappings
     * @param int $createdBy
     * @return bool
     * @throws Exception
     */
    public function bulkUpdateRates(array $regionRates, int $createdBy): bool
    {
        try {
            DB::beginTransaction();

            $effectiveFrom = now();
            $updatedCount = 0;

            foreach ($regionRates as $region => $rates) {
                // Validate rates for this region
                $validation = $this->validateRateConfiguration($rates, $region);
                
                if (!$validation['valid']) {
                    throw new Exception("Validation failed for region {$region}: " . implode(', ', $validation['errors']));
                }

                // Update each rate
                foreach ($rates as $roleId => $rate) {
                    $this->setRegionalRate($region, $roleId, $rate, $createdBy, $effectiveFrom);
                    $updatedCount++;
                }
            }

            DB::commit();

            Log::info('Bulk rate update completed', [
                'regions_updated' => count($regionRates),
                'rates_updated' => $updatedCount,
                'created_by' => $createdBy
            ]);

            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Bulk rate update failed', [
                'error' => $e->getMessage(),
                'created_by' => $createdBy
            ]);
            throw $e;
        }
    }

    /**
     * Get all active rates for a region
     *
     * @param string $region
     * @return Collection
     */
    public function getRegionRates(string $region): Collection
    {
        return CommissionRate::active()
            ->forRegion($region)
            ->with('role')
            ->orderBy('role_id')
            ->get();
    }

    /**
     * Get total commission rate for a region
     *
     * @param string $region
     * @return float
     */
    public function getTotalRegionRate(string $region): float
    {
        return CommissionRate::active()
            ->forRegion($region)
            ->sum('commission_percentage');
    }

    /**
     * Check if a region has any commission rates configured
     *
     * @param string $region
     * @return bool
     */
    public function hasRegionConfiguration(string $region): bool
    {
        return CommissionRate::active()
            ->forRegion($region)
            ->exists();
    }

    /**
     * Get all configured regions
     *
     * @return Collection
     */
    public function getConfiguredRegions(): Collection
    {
        return CommissionRate::active()
            ->select('region')
            ->distinct()
            ->orderBy('region')
            ->pluck('region');
    }

    /**
     * Validate individual rate value
     *
     * @param float $rate
     * @throws Exception
     */
    private function validateRate(float $rate): void
    {
        if ($rate < 0) {
            throw new Exception('Commission rate cannot be negative');
        }

        if ($rate > self::MAX_TOTAL_COMMISSION) {
            throw new Exception("Commission rate cannot exceed " . self::MAX_TOTAL_COMMISSION . "%");
        }
    }

    /**
     * Validate that total rates for region don't exceed maximum
     *
     * @param string $region
     * @param int $excludeRoleId
     * @param float $newRate
     * @throws Exception
     */
    private function validateTotalRates(string $region, int $excludeRoleId, float $newRate): void
    {
        $currentTotal = CommissionRate::active()
            ->forRegion($region)
            ->where('role_id', '!=', $excludeRoleId)
            ->sum('commission_percentage');

        $projectedTotal = $currentTotal + $newRate;

        if ($projectedTotal > self::MAX_TOTAL_COMMISSION) {
            throw new Exception(
                "Total commission rate would be {$projectedTotal}%, exceeding maximum of " . 
                self::MAX_TOTAL_COMMISSION . "%"
            );
        }
    }

    /**
     * Deactivate current rate for region and role
     *
     * @param string $region
     * @param int $roleId
     * @param Carbon $effectiveFrom
     */
    private function deactivateCurrentRate(string $region, int $roleId, Carbon $effectiveFrom): void
    {
        CommissionRate::active()
            ->forRegion($region)
            ->forRole($roleId)
            ->update([
                'effective_until' => $effectiveFrom,
                'is_active' => false
            ]);
    }
}