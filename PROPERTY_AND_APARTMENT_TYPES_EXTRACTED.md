# Property and Apartment Types - Extracted from Forms

## Property Types (from property/edit.blade.php)

### Residential
1. **Mansion** (ID: 1)
2. **Duplex** (ID: 2)
3. **Flat** (ID: 3)
4. **Terrace** (ID: 4)

### Commercial
5. **Warehouse** (ID: 5)
8. **Store** (ID: 8)
9. **Shop** (ID: 9)

### Land/Agricultural
6. **Land** (ID: 6)
7. **Farm** (ID: 7)

---

## Apartment/Unit Types (from myProperty.blade.php)

### Residential Units
- **Studio**
- **1 Bedroom**
- **2 Bedroom**
- **3 Bedroom**
- **4 Bedroom**
- **Penthouse**
- **Duplex Unit**

### Commercial Units
- **Shop Unit**
- **Store Unit**
- **Office Unit**
- **Restaurant Unit**
- **Warehouse Unit**
- **Showroom**

### Other
- **Storage Unit**
- **Parking Space**
- **Other**

---

## Current Database Schema Issues

### Properties Table
- `prop_id` column is `unsignedBigInteger` but should be auto-incrementing integer
- `prop_type` column is `unsignedTinyInteger` (1-9) which works with current IDs

### Apartments Table
- `apartment_type` column is likely `string` or `varchar` storing text values
- Should be normalized to use integer IDs with foreign key to apartment_types table

---

## Recommended Schema Changes

### 1. Create `property_types` Table
```sql
CREATE TABLE property_types (
    id TINYINT UNSIGNED PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    category ENUM('residential', 'commercial', 'land_agricultural') NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 2. Create `apartment_types` Table
```sql
CREATE TABLE apartment_types (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    category ENUM('residential', 'commercial', 'other') NOT NULL,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 3. Update Properties Table
- Change `prop_id` to auto-increment (or keep as is if it's intentionally a reference)
- Add foreign key constraint: `prop_type` REFERENCES `property_types(id)`

### 4. Update Apartments Table
- Add new column `apartment_type_id` INT UNSIGNED
- Migrate existing `apartment_type` text values to IDs
- Add foreign key constraint: `apartment_type_id` REFERENCES `apartment_types(id)`
- Eventually drop old `apartment_type` column after migration

---

## Migration Strategy

1. Create lookup tables with seeders
2. Add new foreign key columns
3. Migrate existing data
4. Update application code to use IDs
5. Add foreign key constraints
6. Remove old columns (after verification)
