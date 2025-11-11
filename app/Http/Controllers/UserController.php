<?php
 
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\View\View;
use App\Models\User;
 
class UserController extends Controller
{
    /**
     * Get tenant details by ID.
     */
    public function getTenantDetails($id): JsonResponse
    {
        try {
            $tenant = DB::table('users')
                ->select(
                    'user_id',
                    'first_name',
                    'last_name',
                    'email',
                    'phone',
                    'address',
                    'lga',
                    'state',
                    'created_at'
                )
                ->where('user_id', $id)
                ->first();
        
            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'messages' => 'Tenant not found'
                ], 404);
            }
        
            return response()->json([
                'success' => true,
                'data' => [
                    'tenant' => $tenant
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'messages' => 'Error fetching tenant details: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Show the profile for a given user.
     */
    public function show(string $id)
    {
        try {
            // Use user_id field to find the user
            $user = User::where('user_id', $id)->firstOrFail();
            
            return view('user.profile', [
                'user' => $user
            ]);
        } catch (\Exception $e) {
            // If we're on the old route path, redirect to dashboard/users
            if (request()->is('users/*')) {
                return redirect('/dashboard/users');
            }
            abort(404, 'User not found');
        }
    }

    public function showAgent(string $id): View
    {
        $agent = User::where('user_id', $id)
            ->where(function ($q) {
                // Legacy numeric role (6 = property manager/agent)
                $q->where('role', 6)
                  // Modern roles via pivot
                  ->orWhere(function ($q2) {
                      $q2->whereHas('roles', function ($r) {
                          $r->whereIn('name', ['property_manager', 'agent']);
                      });
                  });
            })
            ->with('managedProperties.apartments')
            ->firstOrFail();

        return view('user.agent', [
            'agent' => $agent,
            'properties' => $agent->managedProperties
        ]);
    }
    public function register(Request $request)
    {
        $request->validate([
            'f_name' => 'required|string|max:255',
            'l_name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'role' => 'required|integer',
            'occupation' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        //array('1' => 'Landlord', '2'=>'Tenant', '3'=>'Artisan', '4'=>'Property Manager');
        $validator = array('success' => false, 'messages' => array());

        $jsons = File::get(resource_path('/states-and-cities.json'));
        $logged_in = auth()->check() ? 1 : 0;
        
        $rtn = "";
        if(isset($request->email) && $request->isMethod('post')){
            $password = Hash::make($request->password);
            // removed unused $password2
            $email = $this->sanitizeInput($request->email);
            $name = $this->sanitizeInput($request->f_name);
            $lname = $this->sanitizeInput($request->l_name);
            $occupation = $this->sanitizeInput($request->occupation);
            $phone = (int) $this->sanitizeInput($request->phone);
            $address = $this->sanitizeInput($request->address);
            $lga = $this->sanitizeInput($request->city);
            $state = $this->sanitizeInput($request->state);
            $username= $this->sanitizeInput($request->username);  
            $role=(int) $this->sanitizeInput($request->role);  
            $user_id = $this->generateUniqueUserId();
            $dt = date('Y-m-d H:i:s');

            // Handle photo upload
            $photoPath = null;
            if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
                $photo = $request->file('photo');
                $photoName = 'user_' . $user_id . '_' . time() . '.' . $photo->getClientOriginalExtension();
                $photo->move(public_path('assets/photos'), $photoName);
                $photoPath = 'assets/photos/' . $photoName;
            }

            if($request->password === $request->repassword){
                $user_created = DB::insert('insert into users (user_id, first_name, last_name, username, email, role, occupation, phone, address, state, lga, password, date_created, photo) values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)',[$user_id, $name, $lname, $username, $email, $role, $occupation, $phone, $address, $state, $lga, $password, $dt, $photoPath]);
                  if($user_created){
                    $validator['success'] = true;
                    $validator['messages'] =  'Account Created Successfully';
                  } else {                    
                    $validator['success'] = false;
                    $validator['messages'] = "Server Error! Couldn't create User Account "; 
                  }
                } else {                    
                  $validator['success'] = false;
                  $validator['messages'] = "Both password does not match"; 
                }
                return $validator;
           }
        
        
        return view('register', ['locations'=>$jsons,'rtn'=>$rtn, 'logged_in'=>$logged_in]);
    }
    
    private function generateUniqueUserId(): int
    {
        do {
            $id = mt_rand(100000, 999999);
        } while (User::where('user_id', $id)->exists());

        return $id;
    }
    public function login(Request $request)
    {
        if (auth()->check()) {
            return redirect('/dashboard');
        }
        if ($request->isMethod('post')) {
            $credentials = $request->only('email', 'password');
            if (auth()->attempt($credentials, $request->filled('remember'))) {
                $request->session()->regenerate();
                return redirect()->intended('/dashboard');
            }
            return back()->withErrors([
                'email' => 'Invalid Email or Password',
            ])->withInput();
        }
        return view('login');
    }

    public function logout(Request $request)
    {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login')->with('message', 'You have been successfully logged out');
    }
      
    public function blog(Request $request){
        $logged_in = session('loggedIn') ? 1 : 0;
        
        // Use the Blog model instead of raw SQL
        $blog = \App\Models\Blog::published()->recent()->get();
     
        return view('blog', ["logged_in"=>$logged_in, 'blog'=>$blog]);
   }
   public function appointment(Request $request){
        $logged_in = session('loggedIn') ? 1 : 0;
        $rtn="";
        if($request->input('rtn')){
              $rtn = $request->input('rtn');
          }
       
       
       if($request->name){
           
           $name= $this->sanitizeInput($request->name);
           $phone= $this->sanitizeInput($request->phone);
           $email= $this->sanitizeInput($request->email);
           $health_type= $this->sanitizeInput($request->health_type);
           $patient_type= $this->sanitizeInput($request->patient_type);
           $patient_type= $this->sanitizeInput($request->patient_type);
           $appointment_date= $this->sanitizeInput($request->appointment_date);
           $appointment_date=  date("d-M-Y", strtotime($appointment_date));
           
           $blk= $this->sanitizeInput($request->blk);
              
            $dt = date("d-M-Y");
            
                if($blk==""){
                    DB::insert('insert into appointments (name, email, phone, health_type, patient_type, appointment_date, date) values(?,?,?,?,?,?,?)',[$name, $email, $phone, $health_type, $patient_type, $appointment_date, $dt]);
                                
                    redirect()->to("/appointment?rtn=1")->send();
            }
       }
       
    
       return view('appointment', ["logged_in"=>$logged_in, "rtn"=>$rtn]);
   }
     
    public function allUsers(Request $request)
    {
        if (!auth()->check()) {
            $logged_in = 0;
        } else {
            $logged_in = 1;
        }
        $group = \App\Models\User::all();
        return view('users', ["logged_in" => $logged_in, 'users' => $group]);
    }
   public function user(Request $request)
   {
       if (!auth()->check()) {
           return redirect('/login');
       }
       $profile = auth()->user();
       return view('user', [
           'profile' => $profile,
           'roles' => [
               2 => 'Landlord',
               1 => 'Tenant',
               5 => 'Artisan',
               6 => 'Property Manager',
               7 => 'Marketer'
           ]
       ]);
   }
   
    /**
     * Return agent details as JSON for AJAX requests.
     */
    public function getAgentJson(Request $request, string $id): JsonResponse
    {
        $agent = User::where('user_id', $id)->first();

        if (!$agent) {
            return response()->json(['error' => 'Agent not found'], 404);
        }

        // Determine if user qualifies as an agent/property manager (legacy or modern)
        $propertyManagerRoleId = \Illuminate\Support\Facades\DB::table('roles')->where('name', 'property_manager')->value('id');
        $hasAgentRole = ((int) $agent->role === $propertyManagerRoleId);
        try {
            if (method_exists($agent, 'roles') && $agent->roles()->whereIn('name', ['property_manager', 'agent'])->exists()) {
                $hasAgentRole = true;
            }
        } catch (\Throwable $t) {}

        if (!$hasAgentRole) {
            return response()->json(['error' => 'Agent not found'], 404);
        }

        // Determine if current user can remove agent from this property
        $canRemove = false;
        $propertyId = $request->input('property_id');
        if ($propertyId && auth()->check()) {
            $property = \App\Models\Property::where('prop_id', $propertyId)->first();
            if ($property && $property->user_id == auth()->user()->user_id && $property->agent_id == $agent->user_id) {
                $canRemove = true;
            }
        }

        return response()->json([
            'id' => $agent->user_id,
            'first_name' => $agent->first_name,
            'last_name' => $agent->last_name,
            'email' => $agent->email,
            'phone' => $agent->phone,
            'occupation' => $agent->occupation,
            'lga' => $agent->lga,
            'state' => $agent->state,
            'photo' => $agent->photo ? asset($agent->photo) : asset('assets/images/default-avatar.png'),
            'can_remove' => $canRemove,
        ]);
    }

    /**
     * AJAX: Search for verified agents by name, city, or specialty.
     * Returns JSON array of agents.
     */
    public function searchAgents(Request $request)
    {
        $query = User::query()
            ->where(function ($q) {
                // Legacy numeric role
                $q->where('role', 6)
                  // Or modern role via pivot
                  ->orWhereExists(function ($sub) {
                      $sub->select(DB::raw(1))
                          ->from('role_user')
                          ->join('roles', 'roles.id', '=', 'role_user.role_id')
                          ->whereColumn('role_user.user_id', 'users.user_id')
                          ->whereIn('roles.name', ['property_manager', 'agent']);
                  });
            })
            ->withAvg('agentRatings', 'rating');

        if ($request->filled('name')) {
            $query->where(function($q) use ($request) {
                $q->where('first_name', 'like', '%' . $request->name . '%')
                  ->orWhere('last_name', 'like', '%' . $request->name . '%')
                  ->orWhere('username', 'like', '%' . $request->name . '%');
            });
        }
        if ($request->filled('city')) {
            $query->where('lga', 'like', '%' . $request->city . '%');
        }
        if ($request->filled('specialty')) {
            $query->where('occupation', 'like', '%' . $request->specialty . '%');
        }

        $agents = $query->orderBy('first_name')
            ->limit(20)
            ->get([
                'user_id', 'first_name', 'last_name', 'email', 'phone', 'occupation', 'lga', 'state'
            ])
            ->map(function ($agent) {
                // Normalize property name for average rating
                $agent->average_rating = isset($agent->agent_ratings_avg_rating)
                    ? round($agent->agent_ratings_avg_rating, 2)
                    : null;
                return $agent;
            });

        return response()->json($agents);
    }

    function sanitizeInput($input){
        //sanitze an input
        //include("connect.php");
        $input = strip_tags(htmlspecialchars(trim($input)));
        //$input = mysqli_real_escape_string($link, $input);
        return $input;
    }

    public function update(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'lga' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if (!auth()->check()) {
            return redirect('/login');
        }
        $user = auth()->user();

        // Handle photo upload
        if ($request->hasFile('photo') && $request->file('photo')->isValid()) {
            $photo = $request->file('photo');
            $photoName = 'user_' . $user->user_id . '_' . time() . '.' . $photo->getClientOriginalExtension();
            $photo->move(public_path('assets/photos'), $photoName);
            $photoPath = 'assets/photos/' . $photoName;
            // Optionally delete old photo if not default
            if ($user->photo && file_exists(public_path($user->photo)) && $user->photo !== 'assets/images/default-avatar.png') {
                @unlink(public_path($user->photo));
            }
            $user->photo = $photoPath;
        }

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->username = $request->input('username');
        $user->phone = $request->input('phone') ?? $user->phone;
        $user->address = $request->input('address') ?? $user->address;
        $user->lga = $request->input('lga') ?? $user->lga;
        $user->state = $request->input('state') ?? $user->state;

        if (!empty($request->input('password'))) {
            $user->password = Hash::make($request->input('password'));
        }

        $user->save();

        // Log activity
        \App\Models\ActivityLog::create([
            'user_id' => $user->user_id,
            'action' => 'profile_update',
            'description' => 'User updated their profile.',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Profile updated successfully!');
    }
}