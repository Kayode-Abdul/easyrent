#!/bin/bash

# Super Marketer System Deployment Script
# This script handles the deployment of the Super Marketer System to production

set -e  # Exit on any error

echo "=== Super Marketer System Deployment Script ==="
echo "Starting deployment process..."

# Configuration
BACKUP_DIR="/var/backups/easyrent"
LOG_FILE="/var/log/easyrent/deployment.log"
MAINTENANCE_FILE="storage/framework/maintenance.php"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
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

# Check if running as correct user
check_user() {
    if [[ $EUID -eq 0 ]]; then
        error_exit "This script should not be run as root"
    fi
    success "Running as non-root user"
}

# Check prerequisites
check_prerequisites() {
    log "Checking prerequisites..."
    
    # Check PHP version
    PHP_VERSION=$(php -r "echo PHP_VERSION;")
    if [[ $(echo "$PHP_VERSION" | cut -d. -f1) -lt 8 ]]; then
        error_exit "PHP 8.0 or higher is required. Current version: $PHP_VERSION"
    fi
    success "PHP version check passed: $PHP_VERSION"
    
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
    
    # Check required directories
    mkdir -p "$BACKUP_DIR"
    mkdir -p "$(dirname "$LOG_FILE")"
    success "Required directories created"
}

# Create backup
create_backup() {
    log "Creating backup..."
    
    TIMESTAMP=$(date +%Y%m%d_%H%M%S)
    BACKUP_PATH="$BACKUP_DIR/backup_$TIMESTAMP"
    
    mkdir -p "$BACKUP_PATH"
    
    # Backup database
    DB_NAME=$(php artisan tinker --execute="echo config('database.connections.mysql.database');")
    DB_USER=$(php artisan tinker --execute="echo config('database.connections.mysql.username');")
    DB_PASS=$(php artisan tinker --execute="echo config('database.connections.mysql.password');")
    
    mysqldump -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" > "$BACKUP_PATH/database.sql"
    success "Database backup created"
    
    # Backup application files
    tar -czf "$BACKUP_PATH/application.tar.gz" \
        --exclude=node_modules \
        --exclude=vendor \
        --exclude=storage/logs \
        --exclude=storage/framework/cache \
        --exclude=storage/framework/sessions \
        --exclude=storage/framework/views \
        .
    success "Application backup created"
    
    echo "Backup created at: $BACKUP_PATH"
}

# Put application in maintenance mode
enable_maintenance() {
    log "Enabling maintenance mode..."
    php artisan down --message="System upgrade in progress" --retry=60
    success "Maintenance mode enabled"
}

# Disable maintenance mode
disable_maintenance() {
    log "Disabling maintenance mode..."
    php artisan up
    success "Maintenance mode disabled"
}

# Update dependencies
update_dependencies() {
    log "Updating dependencies..."
    
    # Update Composer dependencies
    composer install --no-dev --optimize-autoloader
    success "Composer dependencies updated"
    
    # Update NPM dependencies if needed
    if [ -f "package.json" ]; then
        npm ci --production
        npm run production
        success "NPM dependencies updated and assets compiled"
    fi
}

# Run database migrations
run_migrations() {
    log "Running database migrations..."
    
    # Check for pending migrations
    if php artisan migrate:status | grep -q "N"; then
        php artisan migrate --force
        success "Database migrations completed"
    else
        success "No pending migrations"
    fi
    
    # Seed Super Marketer system data if needed
    php artisan db:seed --class=SuperMarketerSystemSeeder --force
    success "Super Marketer system data seeded"
}

# Clear and optimize caches
optimize_application() {
    log "Optimizing application..."
    
    # Clear all caches
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    
    # Optimize for production
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    
    success "Application optimized"
}

# Validate deployment
validate_deployment() {
    log "Validating deployment..."
    
    # Run system validation
    if php artisan system:validate; then
        success "System validation passed"
    else
        error_exit "System validation failed"
    fi
    
    # Check critical services
    if php artisan tinker --execute="app(\App\Services\Commission\MultiTierCommissionCalculator::class);" &> /dev/null; then
        success "Core services are accessible"
    else
        error_exit "Core services validation failed"
    fi
    
    # Test database connectivity
    if php artisan tinker --execute="DB::table('users')->count();" &> /dev/null; then
        success "Database connectivity verified"
    else
        error_exit "Database connectivity test failed"
    fi
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
}

# Main deployment function
deploy() {
    log "Starting Super Marketer System deployment"
    
    check_user
    check_prerequisites
    create_backup
    enable_maintenance
    
    # Deployment steps
    update_dependencies
    run_migrations
    optimize_application
    set_permissions
    validate_deployment
    
    disable_maintenance
    
    success "Super Marketer System deployment completed successfully!"
    log "Deployment completed at $(date)"
}

# Rollback function
rollback() {
    log "Starting rollback process..."
    
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
    
    mysql -u"$DB_USER" -p"$DB_PASS" "$DB_NAME" < "$BACKUP_PATH/database.sql"
    success "Database restored"
    
    # Restore application files
    tar -xzf "$BACKUP_PATH/application.tar.gz"
    success "Application files restored"
    
    optimize_application
    disable_maintenance
    
    success "Rollback completed successfully!"
}

# Script usage
usage() {
    echo "Usage: $0 [deploy|rollback <timestamp>|validate]"
    echo ""
    echo "Commands:"
    echo "  deploy              - Deploy the Super Marketer System"
    echo "  rollback <timestamp> - Rollback to a specific backup"
    echo "  validate            - Validate current deployment"
    echo ""
    echo "Examples:"
    echo "  $0 deploy"
    echo "  $0 rollback 20231209_143022"
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
    validate)
        validate_deployment
        ;;
    *)
        usage
        exit 1
        ;;
esac