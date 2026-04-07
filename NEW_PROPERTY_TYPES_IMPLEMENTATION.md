# New Property Types Implementation Plan

## Overview
Adding support for commercial and agricultural property types:
- **Warehouse** - Storage facilities
- **Land/Farm** - Agricultural land with size attributes
- **Store/Shop** - Retail/commercial spaces

## Current Property Types
Based on your existing system, you likely have:
- Residential (apartments, houses, etc.)

## New Property Types to Add

### 1. Warehouse
**Attributes:**
- Size (square meters/feet)
- Height clearance
- Loading docks
- Storage type (cold storage, dry storage, etc.)
- Security features
- Access type (24/7, business hours)

### 2. Land/Farm
**Attributes:**
- Size (acres, hectares, or square meters)
- Land type (agricultural, residential, commercial, mixed-use)
- Soil type (for farms)
- Water access
- Topography (flat, hilly, etc.)
- Zoning classification
- Current use (farmland, vacant, etc.)

### 3. Store/Shop
**Attributes:**
- Size (square meters/feet)
- Frontage width
- Store type (retail, restaurant, office, etc.)
- Foot traffic level
- Parking availability
- Display windows

## Implementation Steps

### Step 1: Database Changes

#### A. Update Properties Table
Add new property types to the enum or property_type column

#### B. Create Property Attributes Table
Store flexible attributes for different property types

#### C. Update Apartments Table
Rename or extend to handle non-residential units

### Step 2: Model Updates
- Update Property model
- Update Apartment model (or create Unit model)
- Add validation for property-specific attributes

### Step 3: Form Updates
- Update property creation forms
- Add conditional fields based on property type
- Update property listing forms

### Step 4: View Updates
- Update property display pages
- Add property-type-specific details
- Update search/filter functionality

## Database Schema

### Option 1: Flexible Attributes (Recommended)
```sql
-- Add to properties table
ALTER TABLE properties 
ADD COLUMN property_type ENUM('residential', 'warehouse', 'land', 'farm', 'store', 'shop') DEFAULT 'residential';

-- Create property_attributes table
CREATE TABLE property_attributes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id BIGINT UNSIGNED NOT NULL,
    attribute_key VARCHAR(100) NOT NULL,
    attribute_value TEXT,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (property_id) REFERENCES properties(prop_id) ON DELETE CASCADE,
    INDEX idx_property_attribute (property_id, attribute_key)
);
```

### Option 2: Dedicated Columns
```sql
-- Add specific columns to properties table
ALTER TABLE properties
ADD COLUMN property_type VARCHAR(50) DEFAULT 'residential',
ADD COLUMN size_value DECIMAL(10,2) NULL COMMENT 'Size in square meters or acres',
ADD COLUMN size_unit VARCHAR(20) NULL COMMENT 'sqm, sqft, acres, hectares',
ADD COLUMN land_type VARCHAR(50) NULL,
ADD COLUMN storage_type VARCHAR(50) NULL,
ADD COLUMN has_loading_dock BOOLEAN DEFAULT FALSE,
ADD COLUMN height_clearance DECIMAL(5,2) NULL,
ADD COLUMN frontage_width DECIMAL(5,2) NULL,
ADD COLUMN soil_type VARCHAR(50) NULL,
ADD COLUMN water_access BOOLEAN DEFAULT FALSE,
ADD COLUMN zoning VARCHAR(50) NULL;
```

## Attribute Definitions

### Warehouse Attributes
```json
{
  "size": "1000 sqm",
  "size_unit": "sqm",
  "height_clearance": "8 meters",
  "loading_docks": 3,
  "storage_type": "dry_storage",
  "security_features": ["CCTV", "24/7 Security", "Alarm System"],
  "access_hours": "24/7",
  "power_backup": true,
  "fire_safety": true
}
```

### Land/Farm Attributes
```json
{
  "size": "5 acres",
  "size_unit": "acres",
  "land_type": "agricultural",
  "soil_type": "loamy",
  "water_access": true,
  "water_source": "borehole",
  "topography": "flat",
  "zoning": "agricultural",
  "current_use": "farmland",
  "crops_grown": ["maize", "cassava"],
  "fenced": true,
  "road_access": "tarred"
}
```

### Store/Shop Attributes
```json
{
  "size": "50 sqm",
  "size_unit": "sqm",
  "frontage_width": "6 meters",
  "store_type": "retail",
  "foot_traffic": "high",
  "parking_spaces": 5,
  "display_windows": 2,
  "floor_level": "ground",
  "restroom": true,
  "air_conditioning": true
}
```

## Form Field Examples

### Warehouse Form Fields
- Size (number + unit dropdown)
- Height Clearance
- Number of Loading Docks
- Storage Type (dropdown: dry, cold, hazmat, general)
- Security Features (checkboxes)
- Access Hours (24/7 or business hours)

### Land/Farm Form Fields
- Size (number + unit: acres/hectares/sqm)
- Land Type (dropdown: agricultural, residential, commercial, mixed)
- Soil Type (for agricultural)
- Water Access (yes/no + source)
- Topography (dropdown: flat, hilly, sloped)
- Zoning
- Current Use
- Fencing Status

### Store/Shop Form Fields
- Size (number + unit)
- Frontage Width
- Store Type (dropdown: retail, restaurant, office, salon, etc.)
- Foot Traffic Level (low/medium/high)
- Parking Availability
- Number of Display Windows
- Floor Level

## Migration Files Needed

1. `add_property_types_to_properties_table.php`
2. `create_property_attributes_table.php`
3. `add_size_fields_to_properties_table.php`

## Benefits of This Approach

### Flexible Attributes Table (Recommended)
✅ Easy to add new attributes without schema changes
✅ Different property types can have unique attributes
✅ No null columns for unused attributes
✅ Easy to extend in the future

### Dedicated Columns
✅ Faster queries (no joins needed)
✅ Easier to enforce data types
✅ Better for reporting/analytics
❌ Many null columns
❌ Schema changes needed for new attributes

## Recommendation

Use **Option 1 (Flexible Attributes)** because:
1. Your property types have very different attributes
2. You may add more property types in the future
3. Landlords may want custom attributes
4. Cleaner database schema

## Next Steps

1. Review and approve this plan
2. Create database migrations
3. Update Property and Apartment models
4. Update property creation forms
5. Update property listing/search
6. Test with sample data
7. Deploy to production

## Estimated Timeline

- Database migrations: 1 hour
- Model updates: 2 hours
- Form updates: 3 hours
- View updates: 3 hours
- Testing: 2 hours
- **Total: ~11 hours**

## Questions to Consider

1. Should we rename "Apartment" to "Unit" to be more generic?
2. Do you want to allow mixed-use properties (e.g., shop with apartment above)?
3. Should land be rentable or only for sale?
4. Do you need different pricing models (per sqm, per acre, flat rate)?
5. Should warehouses have multiple units or always be single units?
