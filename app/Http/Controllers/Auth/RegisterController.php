<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'role' => ['required', 'integer'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'lga' => ['nullable', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    /**
     * Show the registration form.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function showRegistrationForm(Request $request)
    {
        // Store referrer in session if present
        if ($request->has('ref')) {
            session(['referrer_id' => $request->query('ref')]);
            
            // Track campaign if present
            if ($request->has('campaign')) {
                session(['campaign_code' => $request->query('campaign')]);
                
                // Increment campaign clicks
                $campaign = \App\Models\ReferralCampaign::where('campaign_code', $request->query('campaign'))
                    ->where('status', 'active')
                    ->first();
                    
                if ($campaign && $campaign->isWithinDateRange()) {
                    $campaign->incrementClicks();
                }
            }
        }
        return view('auth.register');
    }

    /**
     * Override the default register method to handle file upload.
     */
    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        $data = $request->all();

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photo = $request->file('photo');
            $photoName = 'user_' . time() . '_' . uniqid() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('assets/photos'), $photoName);
            $photoPath = 'assets/photos/' . $photoName;
        }
        $data['photo'] = $photoPath;

        $user = $this->create($data);

        // Enhanced referral logic with campaign tracking
        $referrerId = session('referrer_id');
        $campaignCode = session('campaign_code');
        
        if ($referrerId && $user) {
            // Only create referral if referrer exists and is not the same as referred
            if ($referrerId != $user->user_id && \App\Models\User::where('user_id', $referrerId)->exists()) {
                $referralData = [
                    'referrer_id' => $referrerId,
                    'referred_id' => $user->user_id,
                ];
                
                // Add campaign tracking if available
                if ($campaignCode) {
                    $campaign = \App\Models\ReferralCampaign::where('campaign_code', $campaignCode)
                        ->where('marketer_id', $referrerId)
                        ->where('status', 'active')
                        ->first();
                        
                    if ($campaign && $campaign->isWithinDateRange()) {
                        $referralData['campaign_id'] = $campaignCode;
                        $referralData['referral_source'] = 'qr_code';
                        
                        // Increment conversions
                        $campaign->incrementConversions();
                    }
                }
                
                $referral = \App\Models\Referral::create($referralData);
                
                // Create commission reward if user registers as landlord
                $landlordRoleId = DB::table('roles')->where('name', 'landlord')->value('id');
                if ($user->role == $landlordRoleId) { // Landlord role
                    $this->createCommissionReward($referrerId, $referral->id);
                }
            }
            
            session()->forget(['referrer_id', 'campaign_code']);
        }

        $this->guard()->login($user);
        return redirect($this->redirectPath());
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        // Generate unique user_id
        do {
            $user_id = mt_rand(100000, 999999);
        } while (User::where('user_id', $user_id)->exists());

        return User::create([
            'user_id' => $user_id,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'role' => $data['role'],
            'occupation' => $data['occupation'] ?? null,
            'phone' => $data['phone'] ?? null,
            'address' => $data['address'] ?? null,
            'state' => $data['state'] ?? null,
            'lga' => $data['lga'] ?? null,
            'admin' => $data['admin'] ?? 0,
            'created_at' => now(),
            'password' => Hash::make($data['password']),
            'photo' => $data['photo'] ?? null,
        ]);
    }

    /**
     * The user has been registered.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  mixed  $user
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function registered($request, $user)
    {
        // Log the user out if auto-login is enabled by the trait
        $this->guard()->logout();
        // Redirect to login with a success message
        return redirect('/login')->with('status', 'Registration successful! Please log in.');
    }
    
    /**
     * Create commission reward for successful referral
     */
    private function createCommissionReward($marketerId, $referralId)
    {
        $marketer = \App\Models\User::where('user_id', $marketerId)->first();
        
        if (!$marketer || !$marketer->isMarketer() || !$marketer->isActiveMarketer()) {
            return;
        }
        
        // Calculate commission amount (default 5% of average property rent)
        $commissionRate = $marketer->commission_rate ?? 5.0;
        $averageRent = \App\Models\Property::avg('price') ?? 100000; // Default to ₦100k if no properties
        $commissionAmount = ($averageRent * $commissionRate) / 100;
        
        \App\Models\ReferralReward::create([
            'marketer_id' => $marketerId,
            'referral_id' => $referralId,
            'reward_type' => 'commission',
            'amount' => $commissionAmount,
            'description' => 'Commission for landlord referral',
            'status' => 'pending'
        ]);
        
        // Update marketer profile stats
        $marketer->marketerProfile?->increment('total_referrals');
    }
}
