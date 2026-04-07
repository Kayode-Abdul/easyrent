-- Alternative Fix: Don't Add Foreign Key Constraint
-- Since benefactors table can have multiple records per user_id,
-- we can't add a foreign key constraint (which requires uniqueness)

-- Instead, we'll just ensure the index exists for performance
-- and handle data integrity in the application code

-- Step 1: Ensure user_id is indexed in benefactors table (for performance)
ALTER TABLE `benefactors` ADD INDEX `benefactors_user_id_index` (`user_id`);

-- Step 2: Clean up any invalid user_id values (optional but recommended)
-- This sets user_id to NULL for any benefactors referencing non-existent users
UPDATE benefactors 
SET user_id = NULL 
WHERE user_id IS NOT NULL 
AND user_id NOT IN (SELECT user_id FROM users);

-- Step 3: Verify the structure
SHOW CREATE TABLE benefactors;

-- That's it! The application code will handle the relationship.
