<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Property;
use App\Models\Currency;
use App\Models\State;
use App\Models\PropertyImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\Registered;
use App\Services\Commission\ReferralChainService;
use App\Services\Logging\EasyRentLogger;

class OnboardingController extends Controller
{
    /**
     * Create a new controller instance.
     * Only allow guests (unauthenticated users) to access this onboarding flow.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Show the property listing & account creation onboarding form.
     */
    public function index()
    {
        $states = State::where('country_name', 'Nigeria')->with('lgas')->get();
        $currencies = Currency::where('is_active', true)->get();

        // Load countries from JSON for the country dropdown
        $countries = [];
        try {
            $jsonPath = resource_path('countries.json');
            if (\Illuminate\Support\Facades\File::exists($jsonPath)) {
                $countries = json_decode(\Illuminate\Support\Facades\File::get($jsonPath), true);
            }
        } catch (\Exception $e) {
            Log::error('Failed to load countries JSON: ' . $e->getMessage());
        }

        return view('onboarding', compact('states', 'currencies', 'countries'));
    }

    /**
     * Handle the submission of the property and user details.
     */
    public function store(Request $request, ReferralChainService $referralChainService, EasyRentLogger $logger)
    {
        // 1. Validate the request (both User and Property fields)
        $validator = Validator::make($request->all(), [
            // User Validation
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            
            // Property Validation (similar to PropertyRequest)
            'propertyType' => ['required'],
            'country' => ['required', 'string'],
            'state' => ['nullable', 'string'],
            'state_id' => ['nullable'],
            'city' => ['nullable', 'string'],
            'lga_id' => ['nullable'],
            'address' => ['required', 'string'],
            'images.*' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048']
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'messages' => $validator->errors()->first()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // 2. Create the User (Landlord Role is 2 typically)
            $landlordRoleId = DB::table('roles')->where('name', 'landlord')->value('id') ?? 2;
            
            // Generate unique user_id
            do {
                $user_id = mt_rand(100000, 999999);
            } while (User::where('user_id', $user_id)->exists());

            $username = explode('@', $request->email)[0] . '_' . substr($user_id, -4);

            $user = User::create([
                'user_id' => $user_id,
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'username' => $username,
                'email' => $request->email,
                'role' => $landlordRoleId,
                'phone' => $request->phone,
                'state' => $request->state,
                'lga' => $request->city,
                'created_at' => now(),
                'password' => Hash::make($request->password),
                'registration_source' => 'onboarding',
            ]);

            // Log user registration
            $logger->logRegistration($request, $user, false);

            // 3. Create the Property
            do {
                $property_id = mt_rand(1000000, 9999999);
            } while (Property::where('property_id', $property_id)->exists());

            $property = Property::create([
                'user_id' => $user->user_id,
                'property_id' => $property_id,
                'prop_type' => $request->propertyType,
                'address' => $request->address,
                'country' => $request->country ?? 'Nigeria',
                'state' => $request->state,
                'lga' => $request->city,
                'country_name' => $request->country ?? 'Nigeria',
                'state_id' => $request->state_id,
                'lga_id' => $request->lga_id,
                'no_of_apartment' => $request->noOfApartment ?? null,
                'size_value' => $request->size_value ?? null,
                'size_unit' => $request->size_unit ?? null,
                'currency_id' => $request->currency_id ?? null,
                'status' => 'pending',
                'created_at' => now()
            ]);

            // Handle Image Uploads for Property
            if ($request->hasFile('images')) {
                foreach ($request->file('images') as $index => $image) {
                    $originalName = $image->getClientOriginalName();
                    $extension = $image->getClientOriginalExtension();
                    $fileName = 'prop_' . Str::random(10) . '_' . time() . '.' . $extension;
                    $filePath = 'properties/' . $property->property_id . '/images';
                    
                    $path = $image->storeAs('public/' . $filePath, $fileName);
                    $storagePath = str_replace('public/', '', $path);

                    PropertyImage::create([
                        'property_id' => $property->property_id,
                        'uploaded_by' => $user->user_id,
                        'file_name' => $fileName,
                        'file_path' => $storagePath,
                        'original_name' => $originalName,
                        'file_size' => $image->getSize(),
                        'mime_type' => $image->getMimeType(),
                        'is_main' => ($index === 0),
                        'order' => $index
                    ]);
                }
            }

            // Fire Registered event (sends verification email usually, depending on setup)
            event(new Registered($user));
            
            // For standard RegisterController, they send verification notification manually sometimes, 
            // but event(new Registered) handles it if listener is set up. Let's explicitly trigger it
            // if event doesn't do it. But `Registered` event does by default.
            
            // Login the user so they can access the verification notice page
            Auth::login($user);
            
            // Log them out if we want to follow RegisterController's pattern exactly, 
            // but if they are logged out they can't access `verification.notice`.
            // The plan said we replicate `RegisterController` which logs them out.
            Auth::logout();

            DB::commit();

            return response()->json([
                'success' => true,
                'messages' => [
                    'message' => 'Account and Property created successfully!',
                    'redirect' => route('verification.notice')
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Onboarding failed: ' . $e->getMessage(), [
                'exception' => $e,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'messages' => 'Server Error! Cannot complete onboarding. ' . $e->getMessage()
            ], 500);
        }
    }
}
