#!/usr/bin/env python3
import re

def final_batch_fix():
    with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent_all_tables_fixed.sql', 'r') as f:
        content = f.read()
    
    # Simple and direct approach: fix all CREATE TABLE statements
    lines = content.split('\n')
    new_lines = []
    i = 0
    
    while i < len(lines):
        line = lines[i]
        new_lines.append(line)
        
        # Check if this is a CREATE TABLE line
        if 'CREATE TABLE' in line and '(' in line:
            # Collect all lines until the closing parenthesis and ENGINE
            table_lines = [line]
            i += 1
            
            # Find the end of the CREATE TABLE statement
            while i < len(lines) and 'ENGINE=' not in lines[i]:
                table_lines.append(lines[i])
                i += 1
            
            # Add the ENGINE line and the closing semicolon
            if i < len(lines):
                table_lines.append(lines[i])
                i += 1
            
            # Join the table definition
            table_def = '\n'.join(table_lines)
            
            # Check if it needs fixing (has id column but no PRIMARY KEY)
            if '`id`' in table_def and 'PRIMARY KEY' not in table_def:
                # Add AUTO_INCREMENT to id column
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
                
                # Add PRIMARY KEY before ENGINE
                if 'AUTO_INCREMENT' in table_def:
                    table_def = re.sub(
                        r'(\) ENGINE=)',
                        r',\n  PRIMARY KEY (`id`)\n\1',
                        table_def
                    )
            
            # Replace the table lines in new_lines
            new_lines = new_lines[:-1] + table_def.split('\n')
            continue
        
        i += 1
    
    # Write the fixed content
    with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent_all_tables_fixed.sql', 'w') as f:
        f.write('\n'.join(new_lines))
    
    print("Final batch fix completed")

if __name__ == "__main__":
    final_batch_fix()
