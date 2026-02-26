<!-- Header area start -->
@include('header')
<!-- Header area end -->

<meta name="csrf-token" content="{{ csrf_token() }}">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>


<!-- Hero Section -->
<section class="relative py-24 bg-slate-900 overflow-hidden">
  {{-- Background Image with Overlay --}}
  <div class="absolute inset-0 z-0">
    <div class="absolute inset-0 bg-cover bg-center opacity-30"
      style="background-image: url('assets/images/bg_1.jpg');"></div>
    <div class="absolute inset-0 bg-gradient-to-b from-slate-900/60 via-slate-900/80 to-slate-900"></div>
  </div>

  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
    <div class="text-center">
      <nav class="flex justify-center mb-8" aria-label="Breadcrumb">
        <ol class="flex items-center space-x-4 text-sm font-medium">
          <li>
            <a href="/" class="text-gray-400 hover:text-white transition-colors">Home</a>
          </li>
          <li class="flex items-center">
            <i class="bi bi-chevron-right text-gray-500 text-[10px] mx-2"></i>
            <span class="text-primary">Add Property</span>
          </li>
        </ol>
      </nav>
      <h1 class="text-4xl md:text-5xl font-extrabold text-white tracking-tight">Property Listing</h1>
      <p class="mt-4 text-lg text-gray-400 max-w-2xl mx-auto">
        List your properties and apartments on EasyRent to begin automated management.
      </p>
    </div>
  </div>
</section>


<!-- Property Form Section -->
<section class="py-20 bg-slate-50">
  <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="bg-white rounded-[2.5rem] shadow-xl shadow-slate-200/50 overflow-hidden border border-slate-100">
      <div class="p-8 md:p-12 border-b border-slate-100 bg-slate-50/50">
        <h2 class="text-2xl font-bold text-slate-900 flex items-center gap-3">
          <div class="w-10 h-10 bg-primary/10 rounded-xl flex items-center justify-center text-primary">
            <i class="bi bi-building"></i>
          </div>
          Add New Property
        </h2>
        <p class="mt-2 text-slate-500">Provide the essential details to create your property entry.</p>
      </div>

      <div id="message" class="mx-8 mt-8"></div>

      <form method="post" class="p-8 md:p-12 space-y-10" id="propertyForm">
        @csrf

        <!-- Property Classification -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="space-y-2">
            <label for="property-type" class="block text-sm font-bold text-slate-700">Property Type *</label>
            <select name="propertyType" id="property-type"
              class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4 text-slate-900 focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none"
              required>
              <option value="" disabled="disabled" selected>-- Select Property Type --</option>
              <optgroup label="Residential" class="font-bold text-xs uppercase tracking-widest text-slate-400 bg-white">
                <option value="1">Mansion</option>
                <option value="2">Duplex</option>
                <option value="3">Flat</option>
                <option value="4">Terrace</option>
              </optgroup>
              <optgroup label="Commercial"
                class="font-bold text-xs uppercase tracking-widest text-slate-400 bg-white mt-2">
                <option value="5">Warehouse</option>
                <option value="8">Store</option>
                <option value="9">Shop</option>
              </optgroup>
              <optgroup label="Land/Agricultural"
                class="font-bold text-xs uppercase tracking-widest text-slate-400 bg-white mt-2">
                <option value="6">Land</option>
                <option value="7">Farm</option>
              </optgroup>
            </select>
          </div>

          <!-- Size Fields (Conditional) -->
          <div class="space-y-2" id="size-fields" style="display: none;">
            <label for="size_value" class="block text-sm font-bold text-slate-700">Property Size *</label>
            <div class="flex gap-2">
              <input type="number" name="size_value" id="size_value"
                class="flex-1 h-12 bg-slate-50 border-slate-200 rounded-xl px-4 text-slate-900 focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none"
                placeholder="Size" step="0.01" min="0">
              <select name="size_unit" id="size_unit"
                class="w-32 h-12 bg-slate-50 border-slate-200 rounded-xl px-3 text-slate-900 focus:ring-2 focus:ring-primary focus:border-primary transition-all outline-none">
                <option value="sqm">sqm</option>
                <option value="sqft">sqft</option>
                <option value="acres">Acres</option>
                <option value="hectares">Hectares</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Dynamic Details Sections (Hidden by default) -->
        <div id="dynamic-details" class="space-y-10 border-t border-slate-100 pt-10" style="display: none;">

          <!-- Warehouse Details -->
          <div id="warehouse-fields" style="display: none;" class="space-y-6">
            <h3 class="text-lg font-bold text-slate-900 border-l-4 border-blue-500 pl-4">Warehouse Specifications</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label for="height_clearance" class="block text-sm font-medium text-slate-600">Height Clearance
                  (m)</label>
                <input type="number" name="height_clearance" id="height_clearance"
                  class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4" placeholder="8.5" step="0.1">
              </div>
              <div class="space-y-2">
                <label for="loading_docks" class="block text-sm font-medium text-slate-600">Loading Docks</label>
                <input type="number" name="loading_docks" id="loading_docks"
                  class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4" placeholder="3" min="0">
              </div>
              <div class="space-y-2 md:col-span-2">
                <label for="storage_type" class="block text-sm font-medium text-slate-600">Storage Type</label>
                <select name="storage_type" id="storage_type"
                  class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4">
                  <option value="">-- Select --</option>
                  <option value="dry_storage">Dry Storage</option>
                  <option value="cold_storage">Cold Storage</option>
                  <option value="hazmat">Hazardous Materials</option>
                  <option value="general">General Storage</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Land/Farm Details -->
          <div id="land-fields" style="display: none;" class="space-y-6">
            <h3 class="text-lg font-bold text-slate-900 border-l-4 border-emerald-500 pl-4">Land & Agricultural Details
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label for="land_type" class="block text-sm font-medium text-slate-600">Land Type</label>
                <select name="land_type" id="land_type"
                  class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4">
                  <option value="">-- Select --</option>
                  <option value="agricultural">Agricultural</option>
                  <option value="residential">Residential</option>
                  <option value="commercial">Commercial</option>
                  <option value="mixed">Mixed Use</option>
                </select>
              </div>
              <div class="space-y-2">
                <label for="soil_type" class="block text-sm font-medium text-slate-600">Soil Type</label>
                <input type="text" name="soil_type" id="soil_type"
                  class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4" placeholder="Loamy, Sandy...">
              </div>
              <div class="space-y-2">
                <label for="water_access" class="block text-sm font-medium text-slate-600">Water Access</label>
                <select name="water_access" id="water_access"
                  class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4">
                  <option value="">-- Select --</option>
                  <option value="1">Yes</option>
                  <option value="0">No</option>
                </select>
              </div>
              <div class="space-y-2">
                <label for="topography" class="block text-sm font-medium text-slate-600">Topography</label>
                <select name="topography" id="topography"
                  class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4">
                  <option value="">-- Select --</option>
                  <option value="flat">Flat</option>
                  <option value="hilly">Hilly</option>
                  <option value="sloped">Sloped</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Store/Shop Details -->
          <div id="store-fields" style="display: none;" class="space-y-6">
            <h3 class="text-lg font-bold text-slate-900 border-l-4 border-amber-500 pl-4">Store & Shop Specifications
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-2">
                <label for="frontage_width" class="block text-sm font-medium text-slate-600">Frontage Width (m)</label>
                <input type="number" name="frontage_width" id="frontage_width"
                  class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4" placeholder="6.0" step="0.1">
              </div>
              <div class="space-y-2">
                <label for="store_type" class="block text-sm font-medium text-slate-600">Store Type</label>
                <select name="store_type" id="store_type"
                  class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4">
                  <option value="">-- Select --</option>
                  <option value="retail">Retail</option>
                  <option value="restaurant">Restaurant</option>
                  <option value="office">Office</option>
                  <option value="salon">Salon/Spa</option>
                </select>
              </div>
              <div class="space-y-2">
                <label for="foot_traffic" class="block text-sm font-medium text-slate-600">Foot Traffic Level</label>
                <select name="foot_traffic" id="foot_traffic"
                  class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4">
                  <option value="">-- Select --</option>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                </select>
              </div>
              <div class="space-y-2">
                <label for="parking_spaces" class="block text-sm font-medium text-slate-600">Parking Spaces</label>
                <input type="number" name="parking_spaces" id="parking_spaces"
                  class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4" placeholder="0" min="0">
              </div>
            </div>
          </div>
        </div>

        <!-- Location Details -->
        <div class="space-y-6 border-t border-slate-100 pt-10">
          <h3 class="text-lg font-bold text-slate-900">Location Information</h3>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label for="states" class="block text-sm font-bold text-slate-700">State *</label>
              <select name="state" id="states"
                class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4 outline-none focus:ring-2 focus:ring-primary transition-all"
                onchange="getCities()" required>
                <option value="" disabled="disabled" selected>-- Select State --</option>
                <?php 
                                      foreach ($location as $key => $item) {   
                                    ?>
                <option value="<?=$item['name'] ?>">
                  <?=$item['name'] ?>
                </option>
                <?php
} 
                                    ?>
              </select>
            </div>
            <div class="space-y-2">
              <label for="cities" class="block text-sm font-bold text-slate-700">L.G.A *</label>
              <select name="city" id="cities"
                class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4 outline-none focus:ring-2 focus:ring-primary transition-all"
                required>
                <option value="" disabled="disabled" selected>-- Select L.G.A --</option>
              </select>
            </div>
            <div class="space-y-2 md:col-span-2">
              <label for="propertyAdd" class="block text-sm font-bold text-slate-700">Property Address *</label>
              <textarea
                class="w-full py-4 bg-slate-50 border-slate-200 rounded-2xl px-4 outline-none focus:ring-2 focus:ring-primary transition-all"
                name="address" id="propertyAdd" placeholder="Enter full property address..." rows="3"
                required></textarea>
            </div>
          </div>
        </div>

        <!-- Residential Units (Conditional) -->
        <div class="space-y-4 border-t border-slate-100 pt-10" id="apartments-field" style="display: none;">
          <label for="noOfApartment" class="block text-sm font-bold text-slate-700">Number of Units/Apartments *</label>
          <input type="number"
            class="w-full h-12 bg-slate-50 border-slate-200 rounded-xl px-4 outline-none focus:ring-2 focus:ring-primary transition-all"
            name="noOfApartment" id="noOfApartment" min="1" placeholder="Enter number of rentable units">
          <p class="text-xs text-slate-400">Total number of rentable units in this property.</p>
        </div>

        <!-- Property Photos -->
        <div class="space-y-6 border-t border-slate-100 pt-10">
          <div class="flex items-center justify-between">
            <h3 class="text-lg font-bold text-slate-900">Property Photos</h3>
            <span class="text-xs font-medium text-slate-500">Max 5MB per image</span>
          </div>
          <div class="grid grid-cols-1 gap-4">
            <div id="image-upload-zone" class="relative group cursor-pointer">
              <input type="file" name="images[]" id="images" multiple accept="image/*"
                class="absolute inset-0 w-full h-full opacity-0 z-10 cursor-pointer">
              <div
                class="w-full py-12 border-2 border-dashed border-slate-200 rounded-[2rem] bg-slate-50 group-hover:bg-slate-100 group-hover:border-primary/30 transition-all flex flex-col items-center justify-center gap-4">
                <div
                  class="w-16 h-16 bg-white rounded-2xl shadow-sm flex items-center justify-center text-primary group-hover:scale-110 transition-transform">
                  <i class="bi bi-cloud-arrow-up text-3xl"></i>
                </div>
                <div class="text-center">
                  <p class="text-slate-900 font-bold">Click or drag images to upload</p>
                  <p class="text-slate-500 text-sm mt-1">First image will be set as primary photo</p>
                </div>
              </div>
            </div>
            <!-- Preview Grid -->
            <div id="image-previews" class="grid grid-cols-2 sm:grid-cols-4 md:grid-cols-6 gap-4"
              style="display: none;">
              <!-- Dynamic previews will be inserted here -->
            </div>
          </div>
        </div>

        <div class="pt-10">
          <button type="submit"
            class="w-full h-14 bg-primary text-white font-bold rounded-2xl shadow-lg shadow-primary/30 hover:shadow-xl hover:shadow-primary/40 hover:-translate-y-0.5 transition-all text-lg flex items-center justify-center gap-3">
            <i class="bi bi-plus-circle text-xl"></i>
            Create Property
          </button>
        </div>
      </form>
    </div>
  </div>
</section>

<!-- Apartment Panel Section (Hidden by default, shown after property creation) -->
<section id="apartment-panel" class="py-20 bg-white" style="display: none;">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div id="message-apartment" class="mb-8"></div>

    <div class="bg-primary rounded-[2.5rem] shadow-2xl shadow-primary/20 overflow-hidden">
      <div class="p-8 md:p-12 border-b border-primary-foreground/10">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
          <div class="text-white">
            <h2 class="text-2xl font-bold flex items-center gap-3">
              <i class="bi bi-list-stars"></i>
              Manage Apartments/Units
            </h2>
            <p class="mt-2 text-primary-foreground/70">Configure individual units for your new property.</p>
          </div>
          <button type="button"
            class="px-6 py-3 bg-white text-primary font-bold rounded-xl hover:bg-opacity-90 transition-all flex items-center gap-2 shadow-lg"
            onclick="addRow()">
            <i class="bi bi-plus-lg"></i>
            Add Apartment
          </button>
        </div>
      </div>

      <form method="post" action="/apartment" id="ApartmentForm">
        @csrf
        <input type="hidden" id="property-id" name="propertyId">

        <div class="overflow-x-auto">
          <table id="apartmentTable" class="w-full text-left border-collapse">
            <thead>
              <tr class="text-white/50 text-xs uppercase tracking-widest font-black border-b border-white/10">
                <th class="px-8 py-6">No.</th>
                <th class="px-8 py-6">Tenant ID</th>
                <th class="px-8 py-6">From</th>
                <th class="px-8 py-6">To</th>
                <th class="px-8 py-6">Price</th>
                <th class="px-8 py-6">Rental Type</th>
                <th class="px-8 py-6 text-right">Action</th>
              </tr>
            </thead>
            <tbody class="text-white divide-y divide-white/5">
              <!-- Dynamic rows added here -->
            </tbody>
          </table>
        </div>

        <div class="p-8 md:p-12 bg-white/5">
          <button type="submit"
            class="w-full h-14 bg-white text-primary font-bold rounded-2xl hover:bg-opacity-90 transition-all text-lg shadow-xl">
            Finalize and Create Property
          </button>
        </div>
      </form>
    </div>
  </div>
</section>
</div>
</section>


<script>
  // Handle property type change to show/hide conditional fields
  document.getElementById('property-type').addEventListener('change', function () {
    const propType = parseInt(this.value);
    const dynamicDetails = document.getElementById('dynamic-details');

    // Elements to toggle
    const sizeFields = document.getElementById('size-fields');
    const apartmentsField = document.getElementById('apartments-field');
    const warehouseFields = document.getElementById('warehouse-fields');
    const landFields = document.getElementById('land-fields');
    const storeFields = document.getElementById('store-fields');

    // Hide all first
    [sizeFields, apartmentsField, warehouseFields, landFields, storeFields].forEach(el => el.style.display = 'none');
    dynamicDetails.style.display = 'none';

    // Remove required attributes
    document.getElementById('size_value').removeAttribute('required');
    document.getElementById('noOfApartment').removeAttribute('required');

    // Show relevant fields based on property type
    if (propType >= 1 && propType <= 4) { // Residential
      apartmentsField.style.display = 'block';
      document.getElementById('noOfApartment').setAttribute('required', 'required');
    } else {
      // Show dynamic details and size for all other types
      dynamicDetails.style.display = 'block';
      sizeFields.style.display = 'block';
      document.getElementById('size_value').setAttribute('required', 'required');

      if (propType === 5) warehouseFields.style.display = 'block';
      else if (propType === 6 || propType === 7) landFields.style.display = 'block';
      else if (propType === 8 || propType === 9) storeFields.style.display = 'block';
    }
  });

  // Row adding logic (Updated with Tailwind classes)
  let rowCount = 0;
  function addRow() {
    rowCount++;
    const tbody = document.querySelector('#apartmentTable tbody');
    const row = document.createElement('tr');
    row.className = 'hover:bg-white/5 transition-colors';
    row.innerHTML = `
        <td class="px-8 py-4 font-mono text-white/50">${rowCount}</td>
        <td class="px-8 py-4"><input type="text" name="apartments[${rowCount}][tenant_id]" class="w-full bg-white/10 border-white/20 rounded-lg px-3 py-2 text-sm outline-none focus:ring-1 focus:ring-white"></td>
        <td class="px-8 py-4"><input type="date" name="apartments[${rowCount}][from]" class="w-full bg-white/10 border-white/20 rounded-lg px-3 py-2 text-sm"></td>
        <td class="px-8 py-4"><input type="date" name="apartments[${rowCount}][to]" class="w-full bg-white/10 border-white/20 rounded-lg px-3 py-2 text-sm"></td>
        <td class="px-8 py-4"><input type="number" name="apartments[${rowCount}][price]" class="w-full bg-white/10 border-white/20 rounded-lg px-3 py-2 text-sm" placeholder="0.00"></td>
        <td class="px-8 py-4">
            <select name="apartments[${rowCount}][rental_type]" class="w-full bg-white/10 border-white/20 rounded-lg px-3 py-2 text-sm">
                <option value="monthly">Monthly</option>
                <option value="yearly">Yearly</option>
            </select>
        </td>
        <td class="px-8 py-4 text-right">
            <button type="button" onclick="this.closest('tr').remove()" class="text-red-400 hover:text-red-300 transition-colors">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
    tbody.appendChild(row);
  }

  // Image Preview Logic
  document.getElementById('images').addEventListener('change', function (e) {
    const previewGrid = document.getElementById('image-previews');
    const files = e.target.files;

    if (files.length > 0) {
      previewGrid.style.display = 'grid';
      previewGrid.innerHTML = ''; // Clear existing

      Array.from(files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
          const reader = new FileReader();
          reader.onload = function (event) {
            const previewItem = document.createElement('div');
            previewItem.className = 'relative group aspect-square rounded-2xl overflow-hidden border border-slate-200 bg-slate-100';
            previewItem.innerHTML = `
              <img src="${event.target.result}" class="w-full h-full object-cover">
              <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                <span class="text-white text-[10px] font-bold uppercase tracking-wider">${index === 0 ? 'Primary' : 'Gallery'}</span>
              </div>
            `;
            previewGrid.appendChild(previewItem);
          }
          reader.readAsDataURL(file);
        }
      });
    } else {
      previewGrid.style.display = 'none';
    }
  });

  // Initial trigger
  if (document.getElementById('property-type').value) {
    document.getElementById('property-type').dispatchEvent(new Event('change'));
  }
</script>

<!-- Footer area start -->
@include('footer')
<!-- Footer area end -->