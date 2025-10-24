<?php

namespace App\Services\Commission;

use App\Models\CommissionRate;
use App\Models\User;
use App\Models\Property;
use App\Models\ReferralChain;

class PropertyManagementCommissionService
{
    /**
     * Calculate commission for a rent payment based on property management status
     */
    public function calculateRentCommission(
        float $rentAmount,
        Property $property,
        User $referringMarketer = null
    ): array {
        // Determine property management status
        $propertyManagementStatus = $this->getPropertyManagementStatus($property);
        
        // Determine hierarchy status
        $hierarchyStatus = $this->getHierarchyStatus($referringMarketer);
        
        // Get property region
        $region = $property->state ?? 'default';
        
        // Get commission rate
        $commissionRate = CommissionRate::getRateForScenario(
            $region,
            $propertyManagementStatus,
            $hierarchyStatus
        );
        
        if (!$commissionRate) {
            throw new \Exception("No commission rate found for scenario: {$region}, {$propertyManagementStatus}, {$hierarchyStatus}");
        }
        
        // Calculate breakdown
        $breakdown = $commissionRate->calculateCommissionBreakdown($rentAmount);
        
        // Add additional context
        $breakdown['scenario'] = [
            'region' => $region,
            'property_management_status' => $propertyManagementStatus,
            'hierarchy_status' => $hierarchyStatus,
            'property_id' => $property->id,
            'referring_marketer_id' => $referringMarketer?->user_id
        ];
        
        return $breakdown;
    }
    
    /**
     * Determine if property is managed or unmanaged
     */
    private function getPropertyManagementStatus(Property $property): string
    {
        // Check if property has a project manager/agent assigned
        // This would depend on your property management structure
        // For now, assuming there's a field or relationship to check
        
        if ($property->project_manager_id || $property->agent_id || $property->is_managed) {
            return 'managed';
        }
        
        return 'unmanaged';
    }
    
    /**
     * Determine hierarchy status based on referring marketer
     */
    private function getHierarchyStatus(?User $referringMarketer): string
    {
        if (!$referringMarketer) {
            return 'without_super_marketer';
        }
        
        // Check if the referring marketer has a Super Marketer above them
        $superMarketer = $referringMarketer->referringSuperMarketer();
        
        return $superMarketer ? 'with_super_marketer' : 'without_super_marketer';
    }
    
    /**
     * Get commission distribution for a rent payment
     */
    public function getCommissionDistribution(
        float $rentAmount,
        Property $property,
        User $referringMarketer = null
    ): array {
        $breakdown = $this->calculateRentCommission($rentAmount, $property, $referringMarketer);
        
        $distribution = [];
        
        // Super Marketer commission
        if ($breakdown['super_marketer_commission'] > 0 && $referringMarketer) {
            $superMarketer = $referringMarketer->referringSuperMarketer();
            if ($superMarketer) {
                $distribution[] = [
                    'user_id' => $superMarketer->user_id,
                    'role' => 'super_marketer',
                    'amount' => $breakdown['super_marketer_commission'],
                    'rate' => $breakdown['rates']['super_marketer_rate'],
                    'description' => 'Super Marketer Commission'
                ];
            }
        }
        
        // Marketer commission
        if ($breakdown['marketer_commission'] > 0 && $referringMarketer) {
            $distribution[] = [
                'user_id' => $referringMarketer->user_id,
                'role' => 'marketer',
                'amount' => $breakdown['marketer_commission'],
                'rate' => $breakdown['rates']['marketer_rate'],
                'description' => 'Marketer Commission'
            ];
        }
        
        // Regional Manager commission
        if ($breakdown['regional_manager_commission'] > 0) {
            $regionalManager = $this->getRegionalManager($property);
            if ($regionalManager) {
                $distribution[] = [
                    'user_id' => $regionalManager->user_id,
                    'role' => 'regional_manager',
                    'amount' => $breakdown['regional_manager_commission'],
                    'rate' => $breakdown['rates']['regional_manager_rate'],
                    'description' => 'Regional Manager Commission'
                ];
            }
        }
        
        // Company commission
        $distribution[] = [
            'user_id' => null,
            'role' => 'company',
            'amount' => $breakdown['company_commission'],
            'rate' => $breakdown['rates']['company_rate'],
            'description' => 'Company Commission'
        ];
        
        return [
            'total_commission' => $breakdown['total_commission'],
            'distribution' => $distribution,
            'scenario' => $breakdown['scenario']
        ];
    }
    
    /**
     * Get regional manager for a property
     */
    private function getRegionalManager(Property $property): ?User
    {
        // This would depend on your regional management structure
        // You might have a regional_scopes table or similar
        $region = $property->state ?? 'default';
        
        return User::whereHas('roles', function($q) {
                $q->where('name', 'regional_manager');
            })
            ->where('state', $region)
            ->first();
    }
    
    /**
     * Get all available commission scenarios
     */
    public function getAvailableScenarios(): array
    {
        return [
            'property_management_statuses' => ['managed', 'unmanaged'],
            'hierarchy_statuses' => ['with_super_marketer', 'without_super_marketer'],
            'regions' => CommissionRate::getAvailableRegions()
        ];
    }
    
    /**
     * Preview commission for different scenarios
     */
    public function previewCommissionScenarios(float $rentAmount, string $region = 'default'): array
    {
        $scenarios = [];
        
        $propertyStatuses = ['managed', 'unmanaged'];
        $hierarchyStatuses = ['with_super_marketer', 'without_super_marketer'];
        
        foreach ($propertyStatuses as $propertyStatus) {
            foreach ($hierarchyStatuses as $hierarchyStatus) {
                $rate = CommissionRate::getRateForScenario($region, $propertyStatus, $hierarchyStatus);
                
                if ($rate) {
                    $breakdown = $rate->calculateCommissionBreakdown($rentAmount);
                    
                    $scenarios[] = [
                        'property_management_status' => $propertyStatus,
                        'hierarchy_status' => $hierarchyStatus,
                        'total_rate' => $rate->total_commission_rate,
                        'breakdown' => $breakdown
                    ];
                }
            }
        }
        
        return $scenarios;
    }
}