#!/bin/bash

###############################################################################
# EasyRent Shared Hosting Deployment Preparation Script
# 
# This script prepares the application for deployment to shared hosting by:
# - Removing test and debug files
# - Optimizing composer dependencies
# - Cleaning up unnecessary files
# - Preparing environment configuration
#
# Usage: bash prepare-deployment.sh
###############################################################################

set -e

echo "=========================================="
echo "EasyRent Deployment Preparation"
echo "=========================================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# ============================================================================
# STEP 1: Backup Current State
# ============================================================================
echo -e "${YELLOW}[1/7]${NC} Creating backup..."
BACKUP_DIR="backups/deployment-$(date +%Y%m%d-%H%M%S)"
mkdir -p "$BACKUP_DIR"
cp -r . "$BACKUP_DIR" 2>/dev/null || true
echo -e "${GREEN}✓${NC} Backup created at $BACKUP_DIR"
echo ""

# ============================================================================
# STEP 2: Remove Test Files
# ============================================================================
echo -e "${YELLOW}[2/7]${NC} Removing test files..."

TEST_FILES_REMOVED=0

# Remove test_*.php files
for file in test_*.php; do
    if [ -f "$file" ]; then
        rm "$file"
        ((TEST_FILES_REMOVED++))
    fi
done

# Remove debug_*.php files
for file in debug_*.php; do
    if [ -f "$file" ]; then
        rm "$file"
        ((TEST_FILES_REMOVED++))
    fi
done

# Remove diagnose_*.php files
for file in diagnose_*.php; do
    if [ -f "$file" ]; then
        rm "$file"
        ((TEST_FILES_REMOVED++))
    fi
done

# Remove other test files
rm -f complete_test_payments.php 2>/dev/null || true
rm -f fix_*.php 2>/dev/null || true
rm -f check_*.php 2>/dev/null || true
rm -f assign_role.php 2>/dev/null || true
rm -f create_*.php 2>/dev/null || true
rm -f role_debug.php 2>/dev/null || true
rm -f setup_*.php 2>/dev/null || true
rm -f validate_*.php 2>/dev/null || true
rm -f update_apartment_pricing_type.php 2>/dev/null || true
rm -f webhook_logger.php 2>/dev/null || true

echo -e "${GREEN}✓${NC} Removed $TEST_FILES_REMOVED test/debug files"
echo ""

# ============================================================================
# STEP 3: Remove Development Directories
# ============================================================================
echo -e "${YELLOW}[3/7]${NC} Removing development directories..."

DIRS_REMOVED=0

# Remove tests directory
if [ -d "tests" ]; then
    rm -rf tests
    ((DIRS_REMOVED++))
    echo -e "${GREEN}✓${NC} Removed tests/"
fi

# Remove .kiro directory
if [ -d ".kiro" ]; then
    rm -rf .kiro
    ((DIRS_REMOVED++))
    echo -e "${GREEN}✓${NC} Removed .kiro/"
fi

# Remove .git directory
if [ -d ".git" ]; then
    rm -rf .git
    ((DIRS_REMOVED++))
    echo -e "${GREEN}✓${NC} Removed .git/"
fi

# Remove .vscode directory
if [ -d ".vscode" ]; then
    rm -rf .vscode
    ((DIRS_REMOVED++))
    echo -e "${GREEN}✓${NC} Removed .vscode/"
fi

echo ""

# ============================================================================
# STEP 4: Remove Unnecessary Files
# ============================================================================
echo -e "${YELLOW}[4/7]${NC} Removing unnecessary files..."

FILES_REMOVED=0

# Remove SQL dump files
for file in *.sql; do
    if [ -f "$file" ]; then
        rm "$file"
        ((FILES_REMOVED++))
    fi
done

# Remove Python scripts
for file in *.py; do
    if [ -f "$file" ]; then
        rm "$file"
        ((FILES_REMOVED++))
    fi
done

# Remove HTML test files
rm -f *.html 2>/dev/null || true
((FILES_REMOVED++))

# Remove shell scripts (except prepare-deployment.sh)
for file in *.sh; do
    if [ "$file" != "prepare-deployment.sh" ]; then
        rm "$file" 2>/dev/null || true
        ((FILES_REMOVED++))
    fi
done

# Remove documentation files (optional - comment out if you want to keep them)
# for file in *.md; do
#     if [ "$file" != "README.md" ]; then
#         rm "$file" 2>/dev/null || true
#         ((FILES_REMOVED++))
#     fi
# done

# Remove other unnecessary files
rm -f Null Type cookies.txt 2>/dev/null || true
rm -f .phpunit.result.cache 2>/dev/null || true
rm -f .DS_Store 2>/dev/null || true

echo -e "${GREEN}✓${NC} Removed $FILES_REMOVED unnecessary files"
echo ""

# ============================================================================
# STEP 5: Optimize Composer Dependencies
# ============================================================================
echo -e "${YELLOW}[5/7]${NC} Optimizing composer dependencies..."

if command -v composer &> /dev/null; then
    composer install --no-dev --optimize-autoloader --classmap-authoritative
    echo -e "${GREEN}✓${NC} Composer dependencies optimized"
else
    echo -e "${YELLOW}⚠${NC} Composer not found. Run this manually:"
    echo "   composer install --no-dev --optimize-autoloader --classmap-authoritative"
fi
echo ""

# ============================================================================
# STEP 6: Prepare Environment File
# ============================================================================
echo -e "${YELLOW}[6/7]${NC} Preparing environment configuration..."

if [ ! -f ".env" ]; then
    if [ -f ".env.shared-hosting" ]; then
        cp .env.shared-hosting .env
        echo -e "${GREEN}✓${NC} Created .env from .env.shared-hosting"
    elif [ -f ".env.example" ]; then
        cp .env.example .env
        echo -e "${GREEN}✓${NC} Created .env from .env.example"
    fi
else
    echo -e "${YELLOW}⚠${NC} .env already exists, skipping"
fi

# Set proper permissions
chmod 644 .env 2>/dev/null || true
chmod 755 artisan 2>/dev/null || true

echo ""

# ============================================================================
# STEP 7: Create Deployment Summary
# ============================================================================
echo -e "${YELLOW}[7/7]${NC} Creating deployment summary..."

cat > DEPLOYMENT_SUMMARY.txt << 'EOF'
EasyRent Deployment Preparation Summary
========================================

Preparation completed on: $(date)

Files Removed:
- Test files (test_*.php, debug_*.php, diagnose_*.php)
- Development directories (.git, .kiro, tests, .vscode)
- SQL dump files
- Python scripts
- HTML test files
- Shell scripts (except prepare-deployment.sh)
- Unnecessary files (Null, Type, cookies.txt, etc.)

Directories Cleaned:
- Removed development-only directories
- Kept production-essential directories

Composer Optimized:
- Removed dev dependencies
- Optimized autoloader
- Generated classmap

Environment Prepared:
- Created .env from .env.shared-hosting
- Set proper file permissions

Next Steps:
1. Update .env with your production credentials:
   - Database credentials
   - Paystack keys
   - Email configuration
   - Social auth credentials

2. Build frontend assets:
   npm run build

3. Upload to shared hosting:
   - Via FTP/SFTP
   - Via cPanel File Manager
   - Via SSH (if available)

4. Run setup on shared host:
   - Visit: https://yourdomain.com/setup.php?key=YOUR_SECRET_KEY&cmd=migrate
   - Visit: https://yourdomain.com/setup.php?key=YOUR_SECRET_KEY&cmd=seed
   - Visit: https://yourdomain.com/setup.php?key=YOUR_SECRET_KEY&cmd=config-cache

5. Delete setup.php after deployment

6. Follow DEPLOYMENT_CHECKLIST.md for complete deployment guide

For more information, see:
- SHARED_HOSTING_DEPLOYMENT_GUIDE.md
- DEPLOYMENT_CHECKLIST.md
EOF

echo -e "${GREEN}✓${NC} Deployment summary created"
echo ""

# ============================================================================
# FINAL SUMMARY
# ============================================================================
echo "=========================================="
echo -e "${GREEN}Preparation Complete!${NC}"
echo "=========================================="
echo ""
echo "Summary:"
echo "  - Test files removed: $TEST_FILES_REMOVED"
echo "  - Development directories removed: $DIRS_REMOVED"
echo "  - Unnecessary files removed: $FILES_REMOVED"
echo ""
echo "Next steps:"
echo "  1. Update .env with production credentials"
echo "  2. Run: npm run build"
echo "  3. Upload to shared hosting"
echo "  4. Follow DEPLOYMENT_CHECKLIST.md"
echo ""
echo "Backup location: $BACKUP_DIR"
echo ""
