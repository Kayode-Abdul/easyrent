#!/bin/bash

# Payment Calculation Service Deployment Script
# This script handles the deployment of the Payment Calculation Service fix

set -e  # Exit on any error

echo "=== Payment Calculation Service Deployment Script ==="
echo "Starting deployment process..."

# Configuration
BACKUP_DIR="/var/backups/easyrent/payment_calculation"
LOG_FILE="/var/log/easyrent/payment_calculation_deployment.log"
MAINTENANCE_FILE="storage/framework/maintenance.php"
SERVICE_NAME="Payment Calculation Service"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    echo "[$(date '+%Y-%m-%d %H:%M:%S')] $1" | tee -a "$LOG_FILE"
}

# Error handling
error_exit() {
    echo -e "${RED}ERROR: $1${NC}" >&2
    log "ERROR: $1"
    exit 1
}

# Success message
success() {
    echo -e "${GREEN}✓ $1${NC}"
    log "SUCCESS: $1"
}

# Warning message
warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
    log "WARNING: $1"
}

# Info message
info() {
    echo -e "${BLUE}ℹ $1${NC}"
    log "INFO: $1"
}

# Check if running as correct user
check_user() {
    if [[ $EUID -eq 0 ]]; then
        error_exit "This script should not be run as root"
    fi
    success "Running as non-root user"
}

# Check prerequisites specific to payment calculation service
check_prerequisites() {
    log "Checking prerequisites for $SERVICE_NAME..."
    
    # Check PHP version
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    if [[ $(echo "$PHP_VERSION" | cut -d. -f1) -lt 8 ]]; then
        error_exit "PHP 8.0 or higher is required. Current version: $PHP_VERSION"
    fi
    success "PHP version check passed: $PHP_VERSION"
    
    # Check required PHP extensions for calculations
    REQUIRED_EXTENSIONS=("bcmath" "json" "pdo" "mysql")
    for ext in "${REQUIRED_EXTENSIONS[@]}"; do
        if ! php -m | grep -q "$ext"; then
            error_exit "Required PHP extension '$ext' is not installed"
        fi
    done
    success "Required PHP extensions are available"
    
    # Check Composer
    if ! command -v composer &> /dev/null; then
        error_exit "Composer is not installed"
    fi
    success "Composer is available"
    
    # Check database connection
    if ! php artisan tinker --execute="DB::connection()->getPdo();" &> /dev/null; then
        error_exit "Database connection failed"
    fi
    success "Database connection verified"
    
    # Check if payment calculation tables exist
    if ! php artisan tinker --execute="DB::table('apartments')->exists();" &> /dev/null; then
        error_exit "Required database tables are missing"
    fi
    success "Required database tables exist"
    
    # Check required directories
    mkdir -p "$BACKUP_DIR"
    mkdir -p "$(dirname "$LOG_FILE")"
    success "Required directories created"
}

# Validate payment calculation service files
validate_service_files() {
    log "Validating payment calculation service files..."
    
    REQUIRED_FILES=(
        "app/Services/Payment/PaymentCalculationServiceInterface.php"
        "app/Services/Payment/PaymentCalculationService.php"
        "app/Services/Payment/PaymentCalculationResult.php"
        "config/payment_calculation.php"
        "app/Providers/PaymentCalculationServiceProvider.php"
    )
    
    for file in "${REQUIRED_FILES[@]}"; do
        if [ ! -f "$file" ]; then
            error_exit "Required service file missing: $file"
        fi
    done
    success "All required service files are present"
    
    # Validate service interface
    if ! php -l app/Services/Payment/PaymentCalculationServiceInterface.php > /dev/null; then
        error_exit "PaymentCalculationServiceInterface has syntax errors"
    fi
    
    # Validate service implementation
    if ! php -l app/Services/Payment/PaymentCalculationService.php > /dev/null; then
        error_exit "PaymentCalculationService has syntax errors"
    fi
    
    success "Service files syntax validation passed"
}

# Create backup specific to payment calculation components
create_backup() {
    log "Creating backup for $SERVICE_NAME..."
    
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="$BACKUP_DIR/backup_$TIMESTAMP"
    
    mkdir -p "$BACKUP_PATH"
    
    # Backup database with focus on payment calculation tables
    DB_NAME=$(php artisan tinker --execute="echo config('database.connections.mysql.database');")
    DB_USER=$(php artisan tinker --execute="echo config('database.connections.mysql.username');")
    DB_PASS=$(php artisan tinker --execute="echo config('database.connections.mysql.password');")
    
    # Full database backup
    mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_PATH/database_full.sql"
    
    # Specific table backups for payment calculation
    PAYMENT_TABLES=("apartments" "profoma_receipts" "payments" "apartment_invitations")
    for table in "${PAYMENT_TABLES[@]}"; do
        mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" "$table" > "$BACKUP_PATH/${table}_backup.sql"
    done
    success "Database backup created"
    
    # Backup payment calculation service files
    mkdir -p "$BACKUP_PATH/service_files"
    cp -r app/Services/Payment "$BACKUP_PATH/service_files/"
    cp config/payment_calculation.php "$BACKUP_PATH/service_files/" 2>/dev/null || true
    cp app/Providers/PaymentCalculationServiceProvider.php "$BACKUP_PATH/service_files/" 2>/dev/null || true
    success "Service files backup created"
    
    # Backup related controllers
    mkdir -p "$BACKUP_PATH/controllers"
    cp app/Http/Controllers/ProfomaController.php "$BACKUP_PATH/controllers/" 2>/dev/null || true
    cp app/Http/Controllers/ApartmentInvitationController.php "$BACKUP_PATH/controllers/" 2>/dev/null || true
    cp app/Http/Controllers/PaymentController.php "$BACKUP_PATH/controllers/" 2>/dev/null || true
    success "Controller files backup created"
    
    echo "Backup created at: $BACKUP_PATH"
}

# Put application in maintenance mode
enable_maintenance() {
    log "Enabling maintenance mode for $SERVICE_NAME deployment..."
    php artisan down --message="Payment calculation system upgrade in progress" --retry=60
    success "Maintenance mode enabled"
}

# Disable maintenance mode
disable_maintenance() {
    log "Disabling maintenance mode..."
    php artisan up
    success "Maintenance mode disabled"
}

# Run payment calculation specific migrations
run_payment_migrations() {
    log "Running payment calculation migrations..."
    
    # Check for pending migrations related to payment calculation
    PAYMENT_MIGRATIONS=(
        "2025_12_15_055139_add_pricing_configuration_to_apartments_table"
        "2025_12_15_061759_add_calculation_fields_to_profoma_receipts_table"
        "2025_12_15_070000_migrate_existing_payment_calculation_data"
    )
    
    for migration in "${PAYMENT_MIGRATIONS[@]}"; do
        if php artisan migrate:status | grep -q "$migration.*Pending"; then
            info "Running migration: $migration"
            php artisan migrate --path=database/migrations/${migration}.php --force
        else
            info "Migration already applied: $migration"
        fi
    done
    
    success "Payment calculation migrations completed"
}

# Validate payment calculation service registration
validate_service_registration() {
    log "Validating payment calculation service registration..."
    
    # Check if service is registered in container
    if ! php artisan tinker --execute="app()->bound('App\Services\Payment\PaymentCalculationServiceInterface');" &> /dev/null; then
        error_exit "PaymentCalculationService is not registered in service container"
    fi
    success "Service is registered in container"
    
    # Test service instantiation
    if ! php artisan tinker --execute="app('App\Services\Payment\PaymentCalculationServiceInterface');" &> /dev/null; then
        error_exit "PaymentCalculationService cannot be instantiated"
    fi
    success "Service can be instantiated"
    
    # Test basic calculation functionality
    if ! php artisan tinker --execute="
        \$service = app('App\Services\Payment\PaymentCalculationServiceInterface');
        \$result = \$service->calculatePaymentTotal(100000, 12, 'total');
        if (!\$result->isValid || \$result->totalAmount !== 100000.0) {
            throw new Exception('Basic calculation test failed');
        }
    " &> /dev/null; then
        error_exit "Basic calculation functionality test failed"
    fi
    success "Basic calculation functionality verified"
}

# Test payment calculation accuracy
test_calculation_accuracy() {
    log "Testing payment calculation accuracy..."
    
    # Test total pricing
    info "Testing total pricing calculations..."
    php artisan tinker --execute="
        \$service = app('App\Services\Payment\PaymentCalculationServiceInterface');
        
        // Test total pricing
        \$result = \$service->calculatePaymentTotal(500000, 12, 'total');
        if (!\$result->isValid || \$result->totalAmount !== 500000.0) {
            throw new Exception('Total pricing test failed: Expected 500000, got ' . \$result->totalAmount);
        }
        echo 'Total pricing test passed: 500000 for 12 months = ' . \$result->totalAmount . PHP_EOL;
        
        // Test monthly pricing
        \$result = \$service->calculatePaymentTotal(50000, 12, 'monthly');
        if (!\$result->isValid || \$result->totalAmount !== 600000.0) {
            throw new Exception('Monthly pricing test failed: Expected 600000, got ' . \$result->totalAmount);
        }
        echo 'Monthly pricing test passed: 50000 * 12 months = ' . \$result->totalAmount . PHP_EOL;
        
        // Test edge cases
        \$result = \$service->calculatePaymentTotal(0, 1, 'total');
        if (!\$result->isValid || \$result->totalAmount !== 0.0) {
            throw new Exception('Zero price test failed');
        }
        echo 'Zero price test passed' . PHP_EOL;
    "
    success "Payment calculation accuracy tests passed"
}

# Update apartment pricing configurations
update_apartment_configurations() {
    log "Updating apartment pricing configurations..."
    
    # Set default pricing type for existing apartments
    php artisan tinker --execute="
        \$count = DB::table('apartments')
            ->whereNull('pricing_type')
            ->update(['pricing_type' => 'total']);
        echo 'Updated ' . \$count . ' apartments with default pricing type' . PHP_EOL;
    "
    
    success "Apartment pricing configurations updated"
}

# Clear and optimize caches
optimize_application() {
    log "Optimizing application for payment calculation service..."
    
    # Clear all caches
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    # Optimize for production
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    # Clear payment calculation specific caches if they exist
    if php artisan tinker --execute="Cache::tags(['payment_calculations'])->flush();" &> /dev/null; then
        info "Payment calculation caches cleared"
    fi
    
    success "Application optimized"
}

# Validate deployment
validate_deployment() {
    log "Validating $SERVICE_NAME deployment..."
    
    # Test service availability
    validate_service_registration
    
    # Test calculation accuracy
    test_calculation_accuracy
    
    # Test controller integration
    info "Testing controller integration..."
    if ! php artisan tinker --execute="
        \$controller = new App\Http\Controllers\ProfomaController();
        echo 'ProfomaController can be instantiated' . PHP_EOL;
    " &> /dev/null; then
        warning "ProfomaController integration test failed"
    else
        success "Controller integration verified"
    fi
    
    # Check database connectivity for payment tables
    if ! php artisan tinker --execute="
        DB::table('apartments')->count();
        DB::table('profoma_receipts')->count();
        echo 'Payment calculation tables accessible' . PHP_EOL;
    " &> /dev/null; then
        error_exit "Payment calculation tables not accessible"
    fi
    success "Database connectivity for payment tables verified"
    
    # Test configuration loading
    if ! php artisan tinker --execute="
        \$config = config('payment_calculation');
        if (empty(\$config)) {
            throw new Exception('Payment calculation configuration not loaded');
        }
        echo 'Payment calculation configuration loaded successfully' . PHP_EOL;
    " &> /dev/null; then
        error_exit "Payment calculation configuration not loaded"
    fi
    success "Configuration loading verified"
}

# Set proper permissions
set_permissions() {
    log "Setting proper permissions..."
    
    # Set storage and cache permissions
    chmod -R 775 storage bootstrap/cache
    
    # Set ownership if running with web server user
    if groups | grep -q www-data; then
        chgrp -R www-data storage bootstrap/cache
        success "Permissions set for www-data group"
    else
        success "Permissions set (no www-data group)"
    fi
    
    # Ensure log files are writable
    touch "$LOG_FILE"
    chmod 664 "$LOG_FILE"
    success "Log file permissions set"
}

# Performance optimization for payment calculations
optimize_payment_calculations() {
    log "Optimizing payment calculation performance..."
    
    # Pre-calculate common scenarios if feature is enabled
    if php artisan tinker --execute="
        \$config = config('payment_calculation.features.enable_pre_calculation', false);
        echo \$config ? 'true' : 'false';
    " | grep -q "true"; then
        info "Pre-calculating common payment scenarios..."
        php artisan tinker --execute="
            \$service = app('App\Services\Payment\PaymentCalculationServiceInterface');
            if (method_exists(\$service, 'preCalculateCommonScenarios')) {
                \$service->preCalculateCommonScenarios();
                echo 'Common scenarios pre-calculated' . PHP_EOL;
            }
        "
    fi
    
    # Warm up caches if caching is enabled
    if php artisan tinker --execute="
        \$config = config('payment_calculation.caching.enable_result_caching', false);
        echo \$config ? 'true' : 'false';
    " | grep -q "true"; then
        info "Warming up payment calculation caches..."
        # Perform some common calculations to warm cache
        php artisan tinker --execute="
            \$service = app('App\Services\Payment\PaymentCalculationServiceInterface');
            \$commonAmounts = [50000, 100000, 200000, 500000];
            \$commonDurations = [6, 12, 24];
            \$types = ['total', 'monthly'];
            
            foreach (\$commonAmounts as \$amount) {
                foreach (\$commonDurations as \$duration) {
                    foreach (\$types as \$type) {
                        \$service->calculatePaymentTotal(\$amount, \$duration, \$type);
                    }
                }
            }
            echo 'Cache warmed with common calculations' . PHP_EOL;
        "
    fi
    
    success "Payment calculation performance optimized"
}

# Main deployment function
deploy() {
    log "Starting $SERVICE_NAME deployment"
    
    check_user
    check_prerequisites
    validate_service_files
    create_backup
    enable_maintenance
    
    # Deployment steps
    run_payment_migrations
    update_apartment_configurations
    optimize_application
    set_permissions
    validate_deployment
    optimize_payment_calculations
    
    disable_maintenance
    
    success "$SERVICE_NAME deployment completed successfully!"
    log "Deployment completed at $(date)"
    
    # Display post-deployment information
    echo ""
    echo -e "${BLUE}=== Post-Deployment Information ===${NC}"
    echo "✓ Payment Calculation Service is now active"
    echo "✓ All apartment pricing configurations updated"
    echo "✓ Calculation accuracy verified"
    echo "✓ Performance optimizations applied"
    echo ""
    echo "Next steps:"
    echo "1. Monitor payment calculations for accuracy"
    echo "2. Review proforma generation with new service"
    echo "3. Test EasyRent invitation calculations"
    echo "4. Check system logs for any calculation errors"
    echo ""
    echo "Documentation available at:"
    echo "- docs/PAYMENT_CALCULATION_SERVICE_DOCUMENTATION.md"
    echo "- docs/PRICING_CONFIGURATION_USER_GUIDE.md"
}

# Rollback function
rollback() {
    log "Starting $SERVICE_NAME rollback process..."
    
    if [ -z "$1" ]; then
        error_exit "Please specify backup timestamp for rollback"
    fi
    
    BACKUP_PATH="$BACKUP_DIR/backup_$1"
    
    if [ ! -d "$BACKUP_PATH" ]; then
        error_exit "Backup not found: $BACKUP_PATH"
    fi
    
    enable_maintenance
    
    # Restore database
    DB_NAME=$(php artisan tinker --execute="echo config('database.connections.mysql.database');")
    DB_USER=$(php artisan tinker --execute="echo config('database.connections.mysql.username');")
    DB_PASS=$(php artisan tinker --execute="echo config('database.connections.mysql.password');")
    
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_PATH/database_full.sql"
    success "Database restored"
    
    # Restore service files
    if [ -d "$BACKUP_PATH/service_files" ]; then
        cp -r "$BACKUP_PATH/service_files/Payment" app/Services/ 2>/dev/null || true
        cp "$BACKUP_PATH/service_files/payment_calculation.php" config/ 2>/dev/null || true
        cp "$BACKUP_PATH/service_files/PaymentCalculationServiceProvider.php" app/Providers/ 2>/dev/null || true
        success "Service files restored"
    fi
    
    # Restore controllers
    if [ -d "$BACKUP_PATH/controllers" ]; then
        cp "$BACKUP_PATH/controllers/"*.php app/Http/Controllers/ 2>/dev/null || true
        success "Controller files restored"
    fi
    
    optimize_application
    disable_maintenance
    
    success "$SERVICE_NAME rollback completed successfully!"
}

# Health check function
health_check() {
    log "Performing $SERVICE_NAME health check..."
    
    # Check service availability
    if php artisan tinker --execute="app('App\Services\Payment\PaymentCalculationServiceInterface');" &> /dev/null; then
        success "Payment Calculation Service is available"
    else
        error_exit "Payment Calculation Service is not available"
    fi
    
    # Check calculation accuracy
    if php artisan tinker --execute="
        \$service = app('App\Services\Payment\PaymentCalculationServiceInterface');
        \$result = \$service->calculatePaymentTotal(100000, 12, 'total');
        if (!\$result->isValid || \$result->totalAmount !== 100000.0) {
            throw new Exception('Health check calculation failed');
        }
    " &> /dev/null; then
        success "Calculation functionality is working"
    else
        error_exit "Calculation functionality is not working"
    fi
    
    # Check database connectivity
    if php artisan tinker --execute="DB::table('apartments')->count();" &> /dev/null; then
        success "Database connectivity is working"
    else
        error_exit "Database connectivity is not working"
    fi
    
    success "$SERVICE_NAME health check passed"
}

# Script usage
usage() {
    echo "Usage: $0 [deploy|rollback <timestamp>|health-check|validate]"
    echo ""
    echo "Commands:"
    echo "  deploy              - Deploy the Payment Calculation Service"
    echo "  rollback <timestamp> - Rollback to a specific backup"
    echo "  health-check        - Perform health check on the service"
    echo "  validate            - Validate current deployment"
    echo ""
    echo "Examples:"
    echo "  $0 deploy"
    echo "  $0 rollback 20241216_143022"
    echo "  $0 health-check"
    echo "  $0 validate"
}

# Main script logic
case "$1" in
    deploy)
        deploy
        ;;
    rollback)
        rollback "$2"
        ;;
    health-check)
        health_check
        ;;
    validate)
        validate_deployment
        ;;
    *)
        usage
        exit 1
        ;;
esac