#!/usr/bin/env python3
import re

def create_final_comprehensive_fix():
    with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent_all_tables_fixed.sql', 'r') as f:
        content = f.read()
    
    # Fix all tables that still need AUTO_INCREMENT and PRIMARY KEY
    # This is a comprehensive pattern that will fix all remaining tables
    
    def fix_all_tables(match):
        table_def = match.group(0)
        
        # Skip if already has PRIMARY KEY
        if 'PRIMARY KEY' in table_def:
            return table_def
        
        # Fix id column to AUTO_INCREMENT for all variations
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
        
        table_def = re.sub(
            r'`id` int\(20\) NOT NULL,',
            '`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,',
            table_def
        )
        
        # Add PRIMARY KEY before ENGINE if id column exists and has AUTO_INCREMENT
        if '`id`' in table_def and 'AUTO_INCREMENT' in table_def:
            table_def = re.sub(
                r'(\) ENGINE=)',
                r',\n  PRIMARY KEY (`id`)\n\1',
                table_def
            )
        
        return table_def
    
    # Apply to all CREATE TABLE statements
    content = re.sub(
        r'CREATE TABLE `[^`]+` \([^)]+?\) ENGINE=InnoDB[^;]*;',
        fix_all_tables,
        content,
        flags=re.DOTALL
    )
    
    # Write the fixed content
    with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent_all_tables_fixed.sql', 'w') as f:
        f.write(content)
    
    print("Final comprehensive fix completed")

if __name__ == "__main__":
    create_final_comprehensive_fix()
