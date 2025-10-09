<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Advanced property search with filters
     */
    public function searchProperties(Request $request)
    {
        $query = Property::with(['owner:user_id,first_name,last_name,email,phone', 'apartments']);

        // Text search
        if ($request->filled('q')) {
            $searchTerm = $request->get('q');
            $query->where(function($q) use ($searchTerm) {
                $q->where('address', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('state', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('lga', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Location filters
        if ($request->filled('state')) {
            $query->where('state', $request->get('state'));
        }

        if ($request->filled('lga')) {
            $query->where('lga', $request->get('lga'));
        }

        // Property type filter
        if ($request->filled('type')) {
            $types = is_array($request->get('type')) ? $request->get('type') : [$request->get('type')];
            $query->whereIn('prop_type', $types);
        }

        // Price range filter (based on apartments)
        if ($request->filled('min_price') || $request->filled('max_price')) {
            $query->whereHas('apartments', function($q) use ($request) {
                if ($request->filled('min_price')) {
                    $q->where('amount', '>=', $request->get('min_price'));
                }
                if ($request->filled('max_price')) {
                    $q->where('amount', '<=', $request->get('max_price'));
                }
            });
        }

        // Availability filter
        if ($request->filled('available')) {
            if ($request->get('available') === 'true') {
                $query->whereHas('apartments', function($q) {
                    $q->where('occupied', false);
                });
            }
        }

        // Number of apartments filter
        if ($request->filled('min_apartments')) {
            $query->where('no_of_apartment', '>=', $request->get('min_apartments'));
        }

        if ($request->filled('max_apartments')) {
            $query->where('no_of_apartment', '<=', $request->get('max_apartments'));
        }

        // Date filters
        if ($request->filled('created_after')) {
            $query->where('created_at', '>=', $request->get('created_after'));
        }

        if ($request->filled('created_before')) {
            $query->where('created_at', '<=', $request->get('created_before'));
        }

        // Agent filter
        if ($request->filled('agent_id')) {
            $query->where('agent_id', $request->get('agent_id'));
        }

        // Status filter (if you have property status)
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['created_at', 'prop_type', 'state', 'no_of_apartment'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        // Pagination
        $perPage = min($request->get('per_page', 15), 50);
        $properties = $query->paginate($perPage);

        // Add search metadata
        $metadata = [
            'total_results' => $properties->total(),
            'search_params' => $request->only([
                'q', 'state', 'lga', 'type', 'min_price', 'max_price', 
                'available', 'min_apartments', 'max_apartments', 
                'created_after', 'created_before', 'agent_id', 'status'
            ]),
            'filters_applied' => count(array_filter($request->only([
                'q', 'state', 'lga', 'type', 'min_price', 'max_price', 
                'available', 'min_apartments', 'max_apartments', 
                'created_after', 'created_before', 'agent_id', 'status'
            ]))),
            'suggestions' => $this->getSearchSuggestions($request)
        ];

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $properties->items(),
                'pagination' => [
                    'current_page' => $properties->currentPage(),
                    'per_page' => $properties->perPage(),
                    'total' => $properties->total(),
                    'last_page' => $properties->lastPage(),
                    'has_more' => $properties->hasMorePages()
                ],
                'metadata' => $metadata
            ]);
        }

        return view('search.properties', compact('properties', 'metadata'));
    }

    /**
     * Search apartments with advanced filters
     */
    public function searchApartments(Request $request)
    {
        $query = Apartment::with(['property:id,prop_id,address,state,lga', 'tenant:user_id,first_name,last_name']);

        // Text search (search in property details)
        if ($request->filled('q')) {
            $searchTerm = $request->get('q');
            $query->whereHas('property', function($q) use ($searchTerm) {
                $q->where('address', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('state', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('lga', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Apartment type filter
        if ($request->filled('apartment_type')) {
            $query->where('apartment_type', 'LIKE', '%' . $request->get('apartment_type') . '%');
        }

        // Price range
        if ($request->filled('min_price')) {
            $query->where('amount', '>=', $request->get('min_price'));
        }

        if ($request->filled('max_price')) {
            $query->where('amount', '<=', $request->get('max_price'));
        }

        // Availability
        if ($request->filled('available')) {
            $available = $request->get('available') === 'true';
            $query->where('occupied', !$available);
        }

        // Location filters
        if ($request->filled('state')) {
            $query->whereHas('property', function($q) use ($request) {
                $q->where('state', $request->get('state'));
            });
        }

        if ($request->filled('lga')) {
            $query->whereHas('property', function($q) use ($request) {
                $q->where('lga', $request->get('lga'));
            });
        }

        // Lease duration filters
        if ($request->filled('min_duration')) {
            $query->whereRaw('DATEDIFF(range_end, range_start) >= ?', [$request->get('min_duration')]);
        }

        if ($request->filled('max_duration')) {
            $query->whereRaw('DATEDIFF(range_end, range_start) <= ?', [$request->get('max_duration')]);
        }

        // Move-in date filters
        if ($request->filled('available_from')) {
            $query->where(function($q) use ($request) {
                $q->where('occupied', false)
                  ->orWhere('range_end', '<=', $request->get('available_from'));
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        
        $allowedSortFields = ['created_at', 'amount', 'apartment_type'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $apartments = $query->paginate($perPage);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'data' => $apartments->items(),
                'pagination' => [
                    'current_page' => $apartments->currentPage(),
                    'per_page' => $apartments->perPage(),
                    'total' => $apartments->total(),
                    'last_page' => $apartments->lastPage(),
                    'has_more' => $apartments->hasMorePages()
                ]
            ]);
        }

        return view('search.apartments', compact('apartments'));
    }

    /**
     * Search users (for admin/agent use)
     */
    public function searchUsers(Request $request)
    {
        // Ensure user has permission to search users
        if (!auth()->user() || (auth()->user()->admin != 1 && auth()->user()->role != 4)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $query = User::query();

        // Text search
        if ($request->filled('q')) {
            $searchTerm = $request->get('q');
            $query->where(function($q) use ($searchTerm) {
                $q->where('first_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('last_name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('email', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('username', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('phone', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $roles = is_array($request->get('role')) ? $request->get('role') : [$request->get('role')];
            $query->whereIn('role', $roles);
        }

        // Location filters
        if ($request->filled('state')) {
            $query->where('state', $request->get('state'));
        }

        if ($request->filled('lga')) {
            $query->where('lga', $request->get('lga'));
        }

        // Registration date filters
        if ($request->filled('registered_after')) {
            $query->where('created_at', '>=', $request->get('registered_after'));
        }

        if ($request->filled('registered_before')) {
            $query->where('created_at', '<=', $request->get('registered_before'));
        }

        // Verification status
        if ($request->filled('verified')) {
            $verified = $request->get('verified') === 'true';
            if ($verified) {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Admin status
        if ($request->filled('admin')) {
            $query->where('admin', $request->get('admin') === 'true' ? 1 : 0);
        }

        $perPage = min($request->get('per_page', 15), 50);
        $users = $query->select([
            'user_id', 'first_name', 'last_name', 'email', 'username', 
            'role', 'phone', 'state', 'lga', 'admin', 'created_at'
        ])->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $users->items(),
            'pagination' => [
                'current_page' => $users->currentPage(),
                'per_page' => $users->perPage(),
                'total' => $users->total(),
                'last_page' => $users->lastPage(),
                'has_more' => $users->hasMorePages()
            ]
        ]);
    }

    /**
     * Get search suggestions based on current query
     */
    private function getSearchSuggestions(Request $request)
    {
        $suggestions = [];

        // Location suggestions
        if ($request->filled('q')) {
            $searchTerm = $request->get('q');
            
            // Suggest states
            $states = Property::where('state', 'LIKE', "%{$searchTerm}%")
                ->distinct()
                ->pluck('state')
                ->take(5);
            
            foreach ($states as $state) {
                $suggestions[] = [
                    'type' => 'location',
                    'text' => $state,
                    'filter' => 'state',
                    'value' => $state
                ];
            }

            // Suggest LGAs
            $lgas = Property::where('lga', 'LIKE', "%{$searchTerm}%")
                ->distinct()
                ->pluck('lga')
                ->take(5);
            
            foreach ($lgas as $lga) {
                $suggestions[] = [
                    'type' => 'location',
                    'text' => $lga,
                    'filter' => 'lga',
                    'value' => $lga
                ];
            }
        }

        // Popular searches
        if (empty($suggestions)) {
            $popularStates = Property::select('state', DB::raw('COUNT(*) as count'))
                ->groupBy('state')
                ->orderBy('count', 'desc')
                ->limit(5)
                ->pluck('state');

            foreach ($popularStates as $state) {
                $suggestions[] = [
                    'type' => 'popular',
                    'text' => $state,
                    'filter' => 'state',
                    'value' => $state
                ];
            }
        }

        return $suggestions;
    }

    /**
     * Get filter options for search forms
     */
    public function getFilterOptions()
    {
        $options = [
            'states' => Property::distinct()->orderBy('state')->pluck('state')->filter(),
            'lgas' => Property::distinct()->orderBy('lga')->pluck('lga')->filter(),
            'property_types' => [
                1 => 'Mansion',
                2 => 'Duplex', 
                3 => 'Flat',
                4 => 'Terrace'
            ],
            'apartment_types' => Apartment::distinct()
                ->whereNotNull('apartment_type')
                ->orderBy('apartment_type')
                ->pluck('apartment_type')
                ->filter(),
            'price_ranges' => [
                ['min' => 0, 'max' => 500000, 'label' => 'Under ₦500K'],
                ['min' => 500000, 'max' => 1000000, 'label' => '₦500K - ₦1M'],
                ['min' => 1000000, 'max' => 2000000, 'label' => '₦1M - ₦2M'],
                ['min' => 2000000, 'max' => 5000000, 'label' => '₦2M - ₦5M'],
                ['min' => 5000000, 'max' => null, 'label' => 'Above ₦5M']
            ],
            'user_roles' => [
                1 => 'Admin',
                2 => 'Landlord',
                3 => 'Tenant', 
                4 => 'Agent',
                5 => 'Marketer',
                6 => 'Regional Manager'
            ]
        ];

        return response()->json([
            'success' => true,
            'data' => $options
        ]);
    }

    /**
     * Save search for user (for search history)
     */
    public function saveSearch(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        $request->validate([
            'query' => 'required|string|max:255',
            'filters' => 'array',
            'type' => 'required|in:properties,apartments,users'
        ]);

        try {
            // Create search history table if it doesn't exist
            if (!DB::getSchemaBuilder()->hasTable('search_history')) {
                DB::statement("
                    CREATE TABLE search_history (
                        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                        user_id BIGINT UNSIGNED NOT NULL,
                        query VARCHAR(255) NOT NULL,
                        filters JSON NULL,
                        search_type VARCHAR(50) NOT NULL,
                        results_count INT DEFAULT 0,
                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
                    )
                ");
            }

            DB::table('search_history')->insert([
                'user_id' => auth()->user()->user_id,
                'query' => $request->query,
                'filters' => json_encode($request->filters ?? []),
                'search_type' => $request->type,
                'results_count' => $request->results_count ?? 0,
                'created_at' => now()
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save search'], 500);
        }
    }

    /**
     * Get user's search history
     */
    public function getSearchHistory()
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Authentication required'], 401);
        }

        try {
            if (!DB::getSchemaBuilder()->hasTable('search_history')) {
                return response()->json(['success' => true, 'data' => []]);
            }

            $history = DB::table('search_history')
                ->where('user_id', auth()->user()->user_id)
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $history
            ]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to get search history'], 500);
        }
    }
}