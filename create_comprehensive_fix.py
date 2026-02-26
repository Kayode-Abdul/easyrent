#!/usr/bin/env python3
import re

def create_comprehensive_fixed_sql():
    # Read the original SQL dump
    with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent (3).sql', 'r') as f:
        content = f.read()
    
    # List of all tables that need PRIMARY KEY and AUTO_INCREMENT fixes
    tables_to_fix = [
        'activity_logs', 'agent_ratings', 'amenities', 'apartments', 
        'apartment_invitations', 'apartment_types', 'audit_logs',
        'benefactors', 'benefactor_payments', 'blog', 'commission_payments',
        'commission_rates', 'complaints', 'complaint_attachments',
        'complaint_categories', 'complaint_updates', 'database_maintenance_logs',
        'durations', 'failed_jobs', 'invitation_analytics', 'invitation_analytics_cache',
        'invitation_conversion_funnel', 'invitation_performance_metrics',
        'invitation_security_monitoring', 'landlord_invitation_dashboard',
        'marketer_profiles', 'messages', 'migrations', 'password_resets',
        'payments', 'payment_invitations', 'payment_tracking', 'performance_logs',
        'personal_access_tokens', 'profoma_receipt', 'properties', 'property_attributes',
        'property_types', 'referrals', 'referral_campaigns', 'referral_chains',
        'referral_rewards', 'regional_scopes', 'reviews', 'roles',
        'role_assignment_audits', 'role_change_notifications', 'role_user',
        'session_cleanup_history', 'users'
    ]
    
    # Fix each table's CREATE TABLE statement
    for table_name in tables_to_fix:
        # Pattern to match CREATE TABLE for this specific table
        pattern = rf'(CREATE TABLE `{table_name}` \([^)]+?\) ENGINE=InnoDB[^;]*;)'
        
        def fix_table(match):
            table_def = match.group(1)
            
            # Skip if already has PRIMARY KEY
            if 'PRIMARY KEY' in table_def:
                return table_def
            
            # Fix id column to AUTO_INCREMENT
            table_def = re.sub(
                r'`id` bigint\(20\) UNSIGNED NOT NULL,',
                '`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,',
                table_def
            )
            
            table_def = re.sub(
                r'`id` bigint\(20\) NOT NULL,',
                '`id` bigint(20) NOT NULL AUTO_INCREMENT,',
                table_def
            )
            
            # Fix apartment_invitations specific case
            if table_name == 'apartment_invitations':
                table_def = re.sub(
                    r'`id` int\(20\) NOT NULL,',
                    '`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,',
                    table_def
                )
            
            # Add PRIMARY KEY before ENGINE
            if '`id`' in table_def and 'AUTO_INCREMENT' in table_def:
                table_def = re.sub(
                    r'(\) ENGINE=)',
                    r',\n  PRIMARY KEY (`id`)\n\1',
                    table_def
                )
            
            return table_def
        
        content = re.sub(pattern, fix_table, content, flags=re.DOTALL)
    
    # Remove duplicate PRIMARY KEY from ALTER TABLE statements
    content = re.sub(
        r'ALTER TABLE `[^`]+`\s+ADD PRIMARY KEY \(`id`\),?\s*\n',
        '',
        content
    )
    
    # Fix duplicate entries
    # Fix apartment_types duplicate
    content = re.sub(
        r'INSERT INTO `apartment_types` \([^)]+VALUES\s*\([^)]+\),\s*\([^)]+\);',
        'INSERT INTO `apartment_types` (`id`, `name`, `category`, `description`, `is_active`, `created_at`, `updated_at`) VALUES\n(1, \'Studio\', \'residential\', \'Single room apartment\', 1, \'2025-12-16 13:14:05\', \'2025-12-16 13:14:05\');',
        content
    )
    
    # Fix property_types duplicate
    content = re.sub(
        r'INSERT INTO `property_types` \([^)]+VALUES\s*\([^)]+\),\s*\([^)]+\);',
        'INSERT INTO `property_types` (`id`, `name`, `category`, `description`, `is_active`, `created_at`, `updated_at`) VALUES\n(1, \'Mansion\', \'residential\', \'Large residential property\', 1, \'2025-12-16 13:14:05\', \'2025-12-16 13:14:05\');',
        content
    )
    
    # Write the fixed content
    with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent_all_tables_fixed.sql', 'w') as f:
        f.write(content)
    
    print("Comprehensive SQL dump created: easyrent_all_tables_fixed.sql")

if __name__ == "__main__":
    create_comprehensive_fixed_sql()
