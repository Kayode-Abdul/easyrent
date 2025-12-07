# Commission Rates Quick Reference

## Quick Commands

### Check Migration Status
```bash
php artisan migrate:status | grep commission
```

### Run Migration
```bash
php artisan migrate
```

### Seed Commission Rates
```bash
php artisan db:seed --class=CommissionRatesSeeder
```

### Test the Fix
```bash
php test_commission_rates_fix.php
```

### Clear Caches
```bash
php artisan optimize:clear
```

## Quick Code Examples

### Get Rate for Scenario
```php
use App\Models\CommissionRate;

$rate = CommissionRate::getRateForScenario(
    region: 'lagos',
    propertyManagementStatus: 'unmanaged',
    hierarchyStatus: 'with_super_marketer'
);
```

### Calculate Commission
```php
$rentAmount = 100000; // ₦100,000
$breakdown = $rate->calculateCommissionBreakdown($rentAmount);

echo "Total: ₦" . number_format($breakdown['total_commission'], 2);
echo "Marketer: ₦" . number_format($breakdown['marketer_commission'], 2);
```

### Query Active Rates
```php
$rates = CommissionRate::active()
    ->forRegion('lagos')
    ->forPropertyManagement('managed')
    ->get();
```

### Get All Regions
```php
$regions = CommissionRate::getAvailableRegions();
```

## Rate Structure Quick Reference

| Scenario | Total | Super Marketer | Marketer | Regional Mgr | Company |
|----------|-------|----------------|----------|--------------|---------|
| Unmanaged w/o SM | 5.0% | - | 1.5% | 0.25% | 3.25% |
| Unmanaged w/ SM | 5.0% | 0.5% | 1.0% | 0.25% | 3.25% |
| Managed w/o SM | 2.5% | - | 0.75% | 0.1% | 1.65% |
| Managed w/ SM | 2.5% | 0.25% | 0.5% | 0.1% | 1.65% |

## Available Regions
- default
- lagos
- abuja
- kano
- port_harcourt
- ibadan

## Troubleshooting

### Error: Column not found
```bash
# Run migration
php artisan migrate

# If already run, check status
php artisan migrate:status
```

### No Data Found
```bash
# Seed the database
php artisan db:seed --class=CommissionRatesSeeder
```

### Rates Don't Sum Correctly
```php
// Check rate validation
$rate = CommissionRate::find($id);
$isValid = $rate->validateRatesSum();
```

## Important Notes

⚠️ **Never use these commands in production**:
- `php artisan migrate:fresh` (drops all tables)
- `php artisan migrate:refresh` (drops all tables)
- `php artisan migrate:reset` (rolls back all migrations)

✅ **Always use**:
- `php artisan migrate` (safe, preserves data)

## File Locations

- **Migration**: `database/migrations/2025_12_06_225053_add_missing_columns_to_commission_rates_table.php`
- **Seeder**: `database/seeders/CommissionRatesSeeder.php`
- **Model**: `app/Models/CommissionRate.php`
- **Controllers**: 
  - `app/Http/Controllers/Admin/CommissionManagementController.php`
  - `app/Http/Controllers/Admin/RegionalCommissionController.php`
- **Views**: `resources/views/admin/commission-management/`

## API Endpoints (if needed)

```php
// In routes/api.php
Route::get('/commission-rates', [CommissionManagementController::class, 'index']);
Route::get('/commission-rates/{region}', [CommissionManagementController::class, 'showRegion']);
Route::post('/commission-breakdown', [CommissionManagementController::class, 'getCommissionBreakdown']);
```

## Database Schema

```sql
-- Key columns
property_management_status ENUM('managed', 'unmanaged')
hierarchy_status ENUM('with_super_marketer', 'without_super_marketer')
super_marketer_rate DECIMAL(5,3)
marketer_rate DECIMAL(5,3)
regional_manager_rate DECIMAL(5,3)
company_rate DECIMAL(5,3)
total_commission_rate DECIMAL(5,3)
```

## Testing Checklist

- [ ] Migration runs without errors
- [ ] All columns exist in database
- [ ] Seeder populates data correctly
- [ ] Original query works (ORDER BY property_management_status)
- [ ] Model methods return expected results
- [ ] Commission calculations are accurate
- [ ] Rate validation works
- [ ] Controllers can query rates
- [ ] Views display rates correctly

## Support

For detailed documentation, see:
- `COMMISSION_RATES_FIX_COMPLETE.md` - Full technical details
- `SESSION_SUMMARY_DECEMBER_7_2025.md` - Implementation summary
