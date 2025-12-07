# New Property Types - Usage Guide

## ✅ Successfully Implemented

The following new property types have been added to your EasyRent platform:
- **Warehouse** (Type 5)
- **Land** (Type 6)
- **Farm** (Type 7)
- **Store** (Type 8)
- **Shop** (Type 9)

## Database Changes

### 1. New Tables
- `property_attributes` - Stores flexible attributes for properties

### 2. New Columns in `properties` table
- `size_value` - Numeric size value
- `size_unit` - Unit of measurement (sqm, sqft, acres, hectares)

## Using the New Property Types

### Creating a Warehouse
```php
$property = Property::create([
    'user_id' => $userId,
    'prop_id' => $propId,
    'prop_type' => Property::TYPE_WAREHOUSE,
    'address' => '123 Industrial Road',
    'state' => 'Lagos',
    'lga' => 'Ikeja',
    'size_value' => 1000,
    'size_unit' => 'sqm',
]);

// Add warehouse-specific attributes
$property->setAttribute('height_clearance', '8 meters');
$property->setAttribute('loading_docks', 3);
$property->setAttribute('storage_type', 'dry_storage');
$property->setAttribute('security_features', json_encode(['CCTV', '24/7 Security']));
```

### Creating Land/Farm
```php
$property = Property::create([
    'user_id' => $userId,
    'prop_id' => $propId,
    'prop_type' => Property::TYPE_FARM,
    'address' => 'Plot 45, Epe Road',
    'state' => 'Lagos',
    'lga' => 'Epe',
    'size_value' => 5,
    'size_unit' => 'acres',
]);

// Add farm-specific attributes
$property->setAttribute('land_type', 'agricultural');
$property->setAttribute('soil_type', 'loamy');
$property->setAttribute('water_access', true);
$property->setAttribute('water_source', 'borehole');
$property->setAttribute('topography', 'flat');
$property->setAttribute('fenced', true);
```

### Creating a Store/Shop
```php
$property = Property::create([
    'user_id' => $userId,
    'prop_id' => $propId,
    'prop_type' => Property::TYPE_STORE,
    'address' => '45 Allen Avenue',
    'state' => 'Lagos',
    'lga' => 'Ikeja',
    'size_value' => 50,
    'size_unit' => 'sqm',
]);

// Add store-specific attributes
$property->setAttribute('frontage_width', '6 meters');
$property->setAttribute('store_type', 'retail');
$property->setAttribute('foot_traffic', 'high');
$property->setAttribute('parking_spaces', 5);
$property->setAttribute('display_windows', 2);
```

## Model Methods

### Check Property Type
```php
// Check if commercial
if ($property->isCommercial()) {
    // Warehouse, Store, or Shop
}

// Check if land
if ($property->isLand()) {
    // Land or Farm
}

// Check if residential
if ($property->isResidential()) {
    // Mansion, Duplex, Flat, or Terrace
}
```

### Get Property Type Name
```php
echo $property->getPropertyTypeName(); // "Warehouse", "Land", etc.
```

### Get All Property Types
```php
$types = Property::getPropertyTypes();
// Returns: [1 => 'Mansion', 2 => 'Duplex', ..., 5 => 'Warehouse', ...]
```

### Working with Attributes
```php
// Set an attribute
$property->setAttribute('parking_spaces', 10);

// Get an attribute
$parkingSpaces = $property->getAttribute('parking_spaces', 0); // 0 is default

// Get formatted size
echo $property->getFormattedSize(); // "1,000.00 sqm"
```

## Form Dropdown Example

### In Your Blade View
```blade
<select name="prop_type" class="form-control" required>
    <option value="">-- Select Property Type --</option>
    @foreach(\App\Models\Property::getPropertyTypes() as $typeId => $typeName)
        <option value="{{ $typeId }}">{{ $typeName }}</option>
    @endforeach
</select>
```

### With Grouping
```blade
<select name="prop_type" class="form-control" required>
    <option value="">-- Select Property Type --</option>
    
    <optgroup label="Residential">
        <option value="{{ \App\Models\Property::TYPE_MANSION }}">Mansion</option>
        <option value="{{ \App\Models\Property::TYPE_DUPLEX }}">Duplex</option>
        <option value="{{ \App\Models\Property::TYPE_FLAT }}">Flat</option>
        <option value="{{ \App\Models\Property::TYPE_TERRACE }}">Terrace</option>
    </optgroup>
    
    <optgroup label="Commercial">
        <option value="{{ \App\Models\Property::TYPE_WAREHOUSE }}">Warehouse</option>
        <option value="{{ \App\Models\Property::TYPE_STORE }}">Store</option>
        <option value="{{ \App\Models\Property::TYPE_SHOP }}">Shop</option>
    </optgroup>
    
    <optgroup label="Land/Agricultural">
        <option value="{{ \App\Models\Property::TYPE_LAND }}">Land</option>
        <option value="{{ \App\Models\Property::TYPE_FARM }}">Farm</option>
    </optgroup>
</select>
```

## Conditional Form Fields Example

```blade
<div class="form-group">
    <label>Property Type *</label>
    <select name="prop_type" id="prop_type" class="form-control" required>
        <!-- options here -->
    </select>
</div>

<!-- Size fields (for all types) -->
<div class="form-group">
    <label>Size *</label>
    <div class="row">
        <div class="col-md-6">
            <input type="number" name="size_value" class="form-control" step="0.01" required>
        </div>
        <div class="col-md-6">
            <select name="size_unit" class="form-control" required>
                <option value="sqm">Square Meters (sqm)</option>
                <option value="sqft">Square Feet (sqft)</option>
                <option value="acres">Acres</option>
                <option value="hectares">Hectares</option>
            </select>
        </div>
    </div>
</div>

<!-- Warehouse-specific fields -->
<div id="warehouse-fields" style="display: none;">
    <div class="form-group">
        <label>Height Clearance (meters)</label>
        <input type="number" name="height_clearance" class="form-control" step="0.1">
    </div>
    <div class="form-group">
        <label>Number of Loading Docks</label>
        <input type="number" name="loading_docks" class="form-control">
    </div>
    <div class="form-group">
        <label>Storage Type</label>
        <select name="storage_type" class="form-control">
            <option value="dry_storage">Dry Storage</option>
            <option value="cold_storage">Cold Storage</option>
            <option value="hazmat">Hazardous Materials</option>
            <option value="general">General Storage</option>
        </select>
    </div>
</div>

<!-- Land/Farm-specific fields -->
<div id="land-fields" style="display: none;">
    <div class="form-group">
        <label>Land Type</label>
        <select name="land_type" class="form-control">
            <option value="agricultural">Agricultural</option>
            <option value="residential">Residential</option>
            <option value="commercial">Commercial</option>
            <option value="mixed">Mixed Use</option>
        </select>
    </div>
    <div class="form-group">
        <label>Soil Type</label>
        <input type="text" name="soil_type" class="form-control" placeholder="e.g., loamy, sandy, clay">
    </div>
    <div class="form-group">
        <label>Water Access</label>
        <select name="water_access" class="form-control">
            <option value="1">Yes</option>
            <option value="0">No</option>
        </select>
    </div>
</div>

<!-- Store/Shop-specific fields -->
<div id="store-fields" style="display: none;">
    <div class="form-group">
        <label>Frontage Width (meters)</label>
        <input type="number" name="frontage_width" class="form-control" step="0.1">
    </div>
    <div class="form-group">
        <label>Store Type</label>
        <select name="store_type" class="form-control">
            <option value="retail">Retail</option>
            <option value="restaurant">Restaurant</option>
            <option value="office">Office</option>
            <option value="salon">Salon/Spa</option>
            <option value="other">Other</option>
        </select>
    </div>
    <div class="form-group">
        <label>Foot Traffic Level</label>
        <select name="foot_traffic" class="form-control">
            <option value="low">Low</option>
            <option value="medium">Medium</option>
            <option value="high">High</option>
        </select>
    </div>
</div>

<script>
document.getElementById('prop_type').addEventListener('change', function() {
    const propType = parseInt(this.value);
    
    // Hide all conditional fields
    document.getElementById('warehouse-fields').style.display = 'none';
    document.getElementById('land-fields').style.display = 'none';
    document.getElementById('store-fields').style.display = 'none';
    
    // Show relevant fields based on property type
    if (propType === 5) { // Warehouse
        document.getElementById('warehouse-fields').style.display = 'block';
    } else if (propType === 6 || propType === 7) { // Land or Farm
        document.getElementById('land-fields').style.display = 'block';
    } else if (propType === 8 || propType === 9) { // Store or Shop
        document.getElementById('store-fields').style.display = 'block';
    }
});
</script>
```

## Controller Example

```php
public function store(Request $request)
{
    $validated = $request->validate([
        'prop_type' => 'required|integer|in:1,2,3,4,5,6,7,8,9',
        'address' => 'required|string',
        'state' => 'required|string',
        'lga' => 'required|string',
        'size_value' => 'nullable|numeric',
        'size_unit' => 'nullable|string|in:sqm,sqft,acres,hectares',
        // ... other fields
    ]);

    $property = Property::create([
        'user_id' => auth()->user()->user_id,
        'prop_id' => $this->generatePropId(),
        'prop_type' => $validated['prop_type'],
        'address' => $validated['address'],
        'state' => $validated['state'],
        'lga' => $validated['lga'],
        'size_value' => $validated['size_value'] ?? null,
        'size_unit' => $validated['size_unit'] ?? null,
    ]);

    // Save property-specific attributes
    if ($property->prop_type == Property::TYPE_WAREHOUSE) {
        if ($request->has('height_clearance')) {
            $property->setAttribute('height_clearance', $request->height_clearance);
        }
        if ($request->has('loading_docks')) {
            $property->setAttribute('loading_docks', $request->loading_docks);
        }
        if ($request->has('storage_type')) {
            $property->setAttribute('storage_type', $request->storage_type);
        }
    } elseif ($property->isLand()) {
        if ($request->has('land_type')) {
            $property->setAttribute('land_type', $request->land_type);
        }
        if ($request->has('soil_type')) {
            $property->setAttribute('soil_type', $request->soil_type);
        }
        if ($request->has('water_access')) {
            $property->setAttribute('water_access', $request->water_access);
        }
    } elseif ($property->isCommercial()) {
        if ($request->has('frontage_width')) {
            $property->setAttribute('frontage_width', $request->frontage_width);
        }
        if ($request->has('store_type')) {
            $property->setAttribute('store_type', $request->store_type);
        }
        if ($request->has('foot_traffic')) {
            $property->setAttribute('foot_traffic', $request->foot_traffic);
        }
    }

    return redirect()->route('properties.show', $property->prop_id)
        ->with('success', 'Property created successfully!');
}
```

## Display Example

```blade
<div class="property-details">
    <h3>{{ $property->getPropertyTypeName() }}</h3>
    <p><strong>Address:</strong> {{ $property->getFullAddress() }}</p>
    
    @if($property->size_value)
        <p><strong>Size:</strong> {{ $property->getFormattedSize() }}</p>
    @endif
    
    @if($property->isCommercial())
        <h4>Commercial Details</h4>
        @if($property->getAttribute('frontage_width'))
            <p><strong>Frontage:</strong> {{ $property->getAttribute('frontage_width') }} meters</p>
        @endif
        @if($property->getAttribute('store_type'))
            <p><strong>Type:</strong> {{ ucfirst($property->getAttribute('store_type')) }}</p>
        @endif
    @endif
    
    @if($property->isLand())
        <h4>Land Details</h4>
        @if($property->getAttribute('land_type'))
            <p><strong>Land Type:</strong> {{ ucfirst($property->getAttribute('land_type')) }}</p>
        @endif
        @if($property->getAttribute('soil_type'))
            <p><strong>Soil Type:</strong> {{ ucfirst($property->getAttribute('soil_type')) }}</p>
        @endif
    @endif
    
    @if($property->prop_type == \App\Models\Property::TYPE_WAREHOUSE)
        <h4>Warehouse Details</h4>
        @if($property->getAttribute('height_clearance'))
            <p><strong>Height Clearance:</strong> {{ $property->getAttribute('height_clearance') }}</p>
        @endif
        @if($property->getAttribute('loading_docks'))
            <p><strong>Loading Docks:</strong> {{ $property->getAttribute('loading_docks') }}</p>
        @endif
    @endif
</div>
```

## Next Steps

1. Update your property creation forms to include the new property types
2. Add conditional fields for property-specific attributes
3. Update property listing/search to filter by new types
4. Update property display pages to show type-specific details
5. Test creating properties of each new type

## Notes

- The `property_attributes` table uses a flexible key-value structure
- You can add new attributes without database migrations
- Attributes can store JSON for complex data
- The application handles referential integrity (no foreign key constraint)
