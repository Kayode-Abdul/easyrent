#!/usr/bin/env python3
import re
import sys

def fix_sql_dump(input_file, output_file):
    with open(input_file, 'r') as f:
        content = f.read()
    
    # Fix AUTO_INCREMENT for id columns
    content = re.sub(
        r'`id` bigint\(20\) UNSIGNED NOT NULL,',
        '`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,',
        content
    )
    
    content = re.sub(
        r'`id` bigint\(20\) NOT NULL,',
        '`id` bigint(20) NOT NULL AUTO_INCREMENT,',
        content
    )
    
    # Fix apartment_invitations id field
    content = re.sub(
        r'`id` int\(20\) NOT NULL,',
        '`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,',
        content
    )
    
    # Add PRIMARY KEY to CREATE TABLE statements that don't have it
    def add_primary_key(match):
        table_def = match.group(0)
        if 'PRIMARY KEY' not in table_def:
            # Add PRIMARY KEY before ENGINE
            table_def = re.sub(
                r'(\) ENGINE=)',
                r')\n  PRIMARY KEY (`id`)\n\1',
                table_def
            )
        return table_def
    
    content = re.sub(
        r'CREATE TABLE `[^`]+` \([^)]+\) ENGINE=InnoDB[^;]*;',
        add_primary_key,
        content,
        flags=re.DOTALL
    )
    
    # Remove duplicate PRIMARY KEY from ALTER TABLE statements
    content = re.sub(
        r'ALTER TABLE `[^`]+`\s+ADD PRIMARY KEY \(`id`\),?\n',
        '',
        content
    )
    
    # Fix duplicate entries in apartment_types and property_types
    content = re.sub(
        r'INSERT INTO `apartment_types`[^;]*VALUES\s*\([^)]*\),\s*\([^)]*\);',
        'INSERT INTO `apartment_types` (`id`, `name`, `category`, `description`, `is_active`, `created_at`, `updated_at`) VALUES\n(1, \'Studio\', \'residential\', \'Single room apartment\', 1, \'2025-12-16 13:14:05\', \'2025-12-16 13:14:05\');',
        content
    )
    
    content = re.sub(
        r'INSERT INTO `property_types`[^;]*VALUES\s*\([^)]*\),\s*\([^)]*\);',
        'INSERT INTO `property_types` (`id`, `name`, `category`, `description`, `is_active`, `created_at`, `updated_at`) VALUES\n(1, \'Mansion\', \'residential\', \'Large residential property\', 1, \'2025-12-16 13:14:05\', \'2025-12-16 13:14:05\');',
        content
    )
    
    with open(output_file, 'w') as f:
        f.write(content)
    
    print(f"Fixed SQL dump saved to: {output_file}")

if __name__ == "__main__":
    input_file = "/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent (3).sql"
    output_file = "/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent_comprehensive_fixed.sql"
    fix_sql_dump(input_file, output_file)
