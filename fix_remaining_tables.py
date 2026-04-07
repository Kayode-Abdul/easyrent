#!/usr/bin/env python3
import re

def fix_remaining_tables():
    with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent_all_tables_fixed.sql', 'r') as f:
        content = f.read()
    
    # List of remaining tables to fix
    remaining_tables = [
        'blog', 'commission_payments', 'commission_rates', 'complaints', 
        'complaint_attachments', 'complaint_categories', 'complaint_updates',
        'database_maintenance_logs', 'durations', 'failed_jobs', 'invitation_analytics_cache',
        'invitation_conversion_funnel', 'invitation_performance_metrics', 'invitation_security_monitoring',
        'landlord_invitation_dashboard', 'marketer_profiles', 'messages', 'migrations',
        'password_resets', 'payment_invitations', 'payment_tracking', 'performance_logs',
        'personal_access_tokens', 'profoma_receipt', 'property_attributes', 'referrals',
        'referral_campaigns', 'referral_chains', 'referral_rewards', 'regional_scopes',
        'reviews', 'roles', 'role_assignment_audits', 'role_change_notifications',
        'role_user', 'session_cleanup_history'
    ]
    
    # Fix each table
    for table_name in remaining_tables:
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
            
            # Add PRIMARY KEY before ENGINE
            if '`id`' in table_def and 'AUTO_INCREMENT' in table_def:
                table_def = re.sub(
                    r'(\) ENGINE=)',
                    r',\n  PRIMARY KEY (`id`)\n\1',
                    table_def
                )
            
            return table_def
        
        content = re.sub(pattern, fix_table, content, flags=re.DOTALL)
    
    # Write the fixed content
    with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent_all_tables_fixed.sql', 'w') as f:
        f.write(content)
    
    print("Fixed all remaining tables")

if __name__ == "__main__":
    fix_remaining_tables()
