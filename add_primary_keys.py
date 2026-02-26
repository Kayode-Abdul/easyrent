#!/usr/bin/env python3
import re

def add_primary_keys():
    with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent_comprehensive_fixed.sql', 'r') as f:
        content = f.read()
    
    # Find all CREATE TABLE statements and add PRIMARY KEY
    lines = content.split('\n')
    new_lines = []
    i = 0
    
    while i < len(lines):
        line = lines[i]
        new_lines.append(line)
        
        # Check if this is a CREATE TABLE line
        if 'CREATE TABLE' in line and '(' in line:
            # Find the closing parenthesis and ENGINE clause
            table_lines = []
            while i < len(lines) and ')' not in lines[i]:
                i += 1
                table_lines.append(lines[i])
            
            # Add the closing line
            if i < len(lines):
                table_lines.append(lines[i])
            
            # Check if PRIMARY KEY already exists
            table_content = '\n'.join(table_lines)
            if 'PRIMARY KEY' not in table_content and '`id`' in table_content and 'AUTO_INCREMENT' in table_content:
                # Find the line with ENGINE= and insert PRIMARY KEY before it
                for j, table_line in enumerate(table_lines):
                    if 'ENGINE=' in table_line:
                        # Insert PRIMARY KEY before this line
                        indent = len(table_line) - len(table_line.lstrip())
                        spaces = ' ' * indent
                        table_lines.insert(j, f',{spaces}PRIMARY KEY (`id`)')
                        break
                
                # Replace the table lines
                new_lines = new_lines[:-1] + table_lines
        
        i += 1
    
    # Write the fixed content
    with open('/Applications/XAMPP/xamppfiles/htdocs/easyrent/easyrent_comprehensive_fixed.sql', 'w') as f:
        f.write('\n'.join(new_lines))
    
    print('Added PRIMARY KEY to all CREATE TABLE statements')

if __name__ == "__main__":
    add_primary_keys()
