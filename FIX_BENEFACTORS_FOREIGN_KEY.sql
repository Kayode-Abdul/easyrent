-- SQL Script to Fix Benefactors Foreign Key Constraint
-- Run this directly on your live database

-- Step 1: Check current structure
SHOW CREATE TABLE users;
SHOW CREATE TABLE benefactors;

-- Step 2: Ensure user_id in users table is indexed (required for foreign key)
-- This will fail silently if index already exists
ALTER TABLE `users` ADD INDEX `users_user_id_index` (`user_id`);

-- Step 3: Check if there are any invalid user_id values in benefactors table
-- This query shows benefactors with user_id that don't exist in users table
SELECT b.id, b.user_id, b.email 
FROM benefactors b 
LEFT JOIN users u ON b.user_id = u.user_id 
WHERE b.user_id IS NOT NULL AND u.user_id IS NULL;

-- Step 4: Fix invalid data (if any found in Step 3)
-- Option A: Set invalid user_id to NULL (makes them guest benefactors)
-- UPDATE benefactors SET user_id = NULL WHERE user_id NOT IN (SELECT user_id FROM users);

-- Step 5: Add foreign key constraint to benefactors table
ALTER TABLE `benefactors` 
ADD CONSTRAINT `benefactors_user_id_foreign` 
FOREIGN KEY (`user_id`) 
REFERENCES `users` (`user_id`) 
ON DELETE CASCADE;

-- Step 6: Verify the constraint was added
SHOW CREATE TABLE benefactors;
