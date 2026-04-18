/* ===== PRODUCTS DATABASE ===== */
const PLACEHOLDER_IMAGE = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="250" height="250"%3E%3Crect fill="%23f5f5f5" width="250" height="250"/%3E%3Ctext x="50%25" y="50%25" font-size="16" fill="%237a7a7a" text-anchor="middle" dominant-baseline="middle"%3EProduct Image%3C/text%3E%3C/svg%3E';

const PRODUCTS = [
  {
    id: 'prod-001',
    name: 'Italian Marble Tile - White Carrara',
    brand: 'Luxury Tiles Co.',
    category: 'Flooring',
    price: 89.99,
    originalPrice: 119.99,
    sku: 'MTL-WHT-CAR-12X12',
    image: PLACEHOLDER_IMAGE,
    rating: 4.8,
    reviews: 142,
    inStock: true,
    description: 'Premium white Carrara marble tiles for sophisticated interiors',
    specs: {
      dimensions: '12" x 12" x 3/8"',
      weight: '15 lbs per tile',
      material: '100% Italian Marble',
      finish: 'Polished',
      coverage: '10 sq ft per box'
    },
    badge: 'Sale',
    volume: { quantity: 10, discount: 0.15 }
  },
  {
    id: 'prod-002',
    name: 'Solid Oak Hardwood Flooring',
    brand: 'Heritage Floors',
    category: 'Flooring',
    price: 6.99,
    originalPrice: 8.99,
    sku: 'HWD-OAK-3QTR-NAT',
    image: PLACEHOLDER_IMAGE,
    rating: 4.6,
    reviews: 89,
    inStock: true,
    description: '3/4" solid white oak hardwood in natural finish',
    specs: {
      dimensions: '3/4" x 3-1/4" x 6\'-12\'',
      weight: '2.5 lbs per sq ft',
      material: 'Solid White Oak',
      finish: 'Natural',
      coverage: '23-25 sq ft per box'
    }
  },
  {
    id: 'prod-003',
    name: 'Modern Black Pendant Light',
    brand: 'Luminous Designs',
    category: 'Lighting',
    price: 149.99,
    sku: 'LGT-BLK-PND-MD',
    image: PLACEHOLDER_IMAGE,
    rating: 4.9,
    reviews: 267,
    inStock: true,
    description: 'Sleek pendant light with matte black finish and dimmable LED',
    specs: {
      dimensions: 'Diameter 10", Drop 12"-36" (adjustable)',
      wattage: '12W LED (equivalent to 60W)',
      material: 'Aluminum & Tempered Glass',
      finish: 'Matte Black',
      voltage: '110-240V'
    }
  },
  {
    id: 'prod-004',
    name: 'Recessed LED Downlight',
    brand: 'EcoLight Pro',
    category: 'Lighting',
    price: 39.99,
    sku: 'LGT-LED-RCS-4IN',
    image: PLACEHOLDER_IMAGE,
    rating: 4.5,
    reviews: 156,
    inStock: true,
    description: '4" recessed LED downlight with adjustable color temperature',
    specs: {
      dimensions: '4" aperture',
      wattage: '8W LED (equatable to 40W)',
      material: 'Aluminum Housing',
      finish: 'Brushed Nickel',
      colorTemp: '3000K-5000K adjustable',
      coverage: '300 sq ft per fixture'
    },
    volume: { quantity: 20, discount: 0.12 }
  },
  {
    id: 'prod-005',
    name: 'Quartz Countertop - Stellar White',
    brand: 'QuanterStone',
    category: 'Kitchen',
    price: 129.99,
    sku: 'QTZ-WHT-STL-3CM',
    image: PLACEHOLDER_IMAGE,
    rating: 4.7,
    reviews: 203,
    inStock: true,
    description: 'Premium engineered quartz with stain-resistant surface',
    specs: {
      dimensions: '3cm x 25\" x 98\" (standard slab)',
      weight: '85 lbs per sq ft',
      material: '93% Quartz, 7% Resin',
      finish: 'Polished',
      edges: 'Straight edge included'
    }
  },
  {
    id: 'prod-006',
    name: 'Stainless Steel Undermount Sink',
    brand: 'ProSink Industries',
    category: 'Kitchen',
    price: 349.99,
    price: 299.99,
    sku: 'SNK-SS-UND-32',
    image: PLACEHOLDER_IMAGE,
    rating: 4.8,
    reviews: 218,
    inStock: true,
    description: '32" rectangular undermount sink with satin finish',
    specs: {
      dimensions: '32" W x 19" D x 10" H',
      material: '18-gauge stainless steel 304',
      finish: 'Satin',
      capacity: '28 gallons',
      warranty: 'Lifetime'
    }
  },
  {
    id: 'prod-007',
    name: 'Contemporary Faucet - Chrome',
    brand: 'AquaFlow Plus',
    category: 'Kitchen',
    price: 189.99,
    originalPrice: 249.99,
    sku: 'FAU-CHR-ARC-SGL',
    image: PLACEHOLDER_IMAGE,
    rating: 4.6,
    reviews: 124,
    inStock: true,
    description: 'Single-handle arc faucet with pull-down sprayer',
    specs: {
      height: '15.5" (spout)',
      reach: '8.5"',
      material: 'Brass body with ceramic valve',
      finish: 'Polished Chrome',
      flow: '2.0 GPM'
    },
    badge: 'Sale'
  },
  {
    id: 'prod-008',
    name: 'Subway Ceramic Wall Tile',
    brand: 'ClassicTile',
    category: 'Bathroom',
    price: 4.99,
    sku: 'TLE-WHT-SUB-3X6',
    image: PLACEHOLDER_IMAGE,
    rating: 4.7,
    reviews: 456,
    inStock: true,
    description: '3x6" classic white subway tile for walls',
    specs: {
      dimensions: '3" x 6" x 0.25"',
      material: 'Ceramic',
      finish: 'Glazed Matte',
      coverage: '20 sq ft per box',
      grout: '0.125" cross laid'
    },
    volume: { quantity: 50, discount: 0.25 }
  },
  {
    id: 'prod-009',
    name: 'Frameless Glass Shower Enclosure',
    brand: 'ClearFrame',
    category: 'Bathroom',
    price: 799.99,
    originalPrice: 1099.99,
    sku: 'SHR-GLS-FRM-60X36',
    image: PLACEHOLDER_IMAGE,
    rating: 4.9,
    reviews: 87,
    inStock: false,
    description: '60x36" frameless shower door with clear tempered glass',
    specs: {
      dimensions: '60" W x 36" D opening',
      glass: '3/8" clear tempered (safety glass)',
      hardware: 'Stainless steel hinges & handles',
      finish: 'Polished Chrome',
      warranty: '10 year structural'
    },
    badge: 'Sale'
  },
  {
    id: 'prod-010',
    name: 'White Porcelain Toilet - Elongated',
    brand: 'ComfortCore',
    category: 'Bathroom',
    price: 289.99,
    sku: 'TLT-WHT-ELG-16.5',
    image: PLACEHOLDER_IMAGE,
    rating: 4.8,
    reviews: 334,
    inStock: true,
    description: 'Dual-flush elongated toilet with softclose seat',
    specs: {
      type: 'Two-piece, elongated bowl',
      rough: '12" (standard)',
      height: '16.5" (ADA compliant)',
      flow: '1.28 GPF / 0.8 GPF dual flush',
      material: 'Vitreous china',
      warranty: '5 year limited'
    }
  },
  {
    id: 'prod-011',
    name: 'Designer Paint - Matte Finish',
    brand: 'Palette Premium',
    category: 'Paint',
    price: 54.99,
    sku: 'PNT-MAT-PTL-GALL',
    image: PLACEHOLDER_IMAGE,
    rating: 4.5,
    reviews: 76,
    inStock: true,
    description: 'Eco-friendly interior matte paint - 1 gallon',
    specs: {
      coverage: '350-400 sq ft per gallon',
      finish: 'Matte',
      type: 'Low-VOC acrylic latex',
      color: 'Select 100+ colors',
      sheenLevel: '10-15% gloss (matte)'
    },
    volume: { quantity: 5, discount: 0.08 }
  },
  {
    id: 'prod-012',
    name: 'Drywall Sheet - Standard',
    brand: 'BuildRight',
    category: 'Materials',
    price: 16.99,
    sku: 'DRY-WHT-STD-4X8',
    image: PLACEHOLDER_IMAGE,
    rating: 4.3,
    reviews: 102,
    inStock: true,
    description: '1/2" standard drywall sheet 4\'x8\'',
    specs: {
      dimensions: '1/2" thick x 4\' wide x 8\' tall',
      weight: '52 lbs per sheet',
      material: 'Gypsum core with paper facing',
      fireRating: 'Type X available',
      expansion: 'Minimal with proper installation'
    },
    volume: { quantity: 30, discount: 0.15 }
  },
  {
    id: 'prod-013',
    name: 'Wood Stud - 2x4x8',
    brand: 'TimberSource',
    category: 'Materials',
    price: 7.49,
    sku: 'LBR-WD-2X4-8FT',
    image: PLACEHOLDER_IMAGE,
    rating: 4.4,
    reviews: 187,
    inStock: true,
    description: 'Pressure-treated wood stud for framing',
    specs: {
      dimensions: '2" x 4" x 8\'',
      weight: '14 lbs',
      material: 'Southern yellow pine',
      treatment: 'Pressure-treated (fire retardant)',
      grade: 'Grade 2 standard'
    },
    volume: { quantity: 50, discount: 0.12 }
  },
  {
    id: 'prod-014',
    name: 'Interior Door - White Primed',
    brand: 'DoorCraft',
    category: 'Doors',
    price: 129.99,
    sku: 'DOR-WHT-PRM-32X80',
    image: PLACEHOLDER_IMAGE,
    rating: 4.6,
    reviews: 98,
    inStock: true,
    description: '32"x80" pre-hung interior door with frame',
    specs: {
      dimensions: '32" W x 80" H',
      material: 'Solid core with white primer',
      finish: 'Pre-primed for painting',
      hinges: '3x3" ball bearing (2)',
      installation: 'Left or right swing'
    }
  },
  {
    id: 'prod-015',
    name: 'Premium Insulation - Batts',
    brand: 'ThermoGuard',
    category: 'Materials',
    price: 34.99,
    sku: 'INS-FGL-R38-16X96',
    image: PLACEHOLDER_IMAGE,
    rating: 4.7,
    reviews: 156,
    inStock: true,
    description: 'Fiberglass insulation R-38 for attics and walls',
    specs: {
      rValue: 'R-38',
      size: '16" x 96" x 10.25"',
      coverage: '66.67 sq ft per batt',
      material: 'Fiberglass',
      fireRating: 'Class A, Class 1'
    },
    volume: { quantity: 10, discount: 0.10 }
  }
];

function getProductById(id) {
  return PRODUCTS.find(p => p.id === id);
}

function getProductsByCategory(category) {
  return PRODUCTS.filter(p => p.category === category);
}

function searchProducts(query) {
  const q = query.toLowerCase();
  return PRODUCTS.filter(p => 
    p.name.toLowerCase().includes(q) ||
    p.brand.toLowerCase().includes(q) ||
    p.category.toLowerCase().includes(q)
  );
}

function getCategories() {
  return [...new Set(PRODUCTS.map(p => p.category))];
}

function getPriceRange() {
  const prices = PRODUCTS.map(p => p.price);
  return { min: Math.min(...prices), max: Math.max(...prices) };
}
