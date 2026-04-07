# UI/UX Improvements Plan

## Status: Ready to Implement

### 1. Simplify User Registration ⏳
**Current**: Multi-step form with many fields (name, email, password, first_name, last_name, username, role, state, lga, occupation, phone, address, photo)

**Target**: Simplified to essential fields only:
- Username (required)
- First Name (required)
- Last Name (required)
- Email (required)
- Phone Number (required)
- Password (required)
- Confirm Password (required)
- Image/Photo (optional)

**Files to modify**:
- `app/Http/Controllers/Auth/RegisterController.php` - Update validator
- `resources/views/auth/register.blade.php` - Simplify form fields
- Keep role selection but make other fields optional or remove them

---

### 2. Make Complaints Table Search More Flexible ⏳
**Current**: Only 3 search options in complaints table filter

**Target**: More flexible search with additional filter options

**Files to modify**:
- `resources/views/complaints/landlord-dashboard.blade.php` - Add more search/filter options

---

### 3. Hide Mobile Floating Footer on Desktop ⏳
**Current**: `.mobile-floating-footer` shows on desktop

**Target**: Hide on desktop, show only on mobile

**Files to modify**:
- `public/assets/css/mobile-enhanced.css` or `public/assets/css/style.css` - Add media query

---

### 4. Desktop Navbar - Login/Signup Links (Not Logged In) ⏳
**Current**: No login/signup links on desktop navbar when user not logged in

**Target**: Show "Login" and "Sign Up" buttons/links on desktop navbar

**Files to modify**:
- `resources/views/header.blade.php` - Add conditional login/signup links

---

### 5. Desktop Navbar - User Dropdown (Logged In) ⏳
**Current**: No user icon/dropdown on desktop when logged in

**Target**: Show user icon with dropdown containing:
- Dashboard link
- Logout link

**Files to modify**:
- `resources/views/header.blade.php` - Add user dropdown

---

### 6. Mobile - Hide Floating Footer When Not Logged In ⏳
**Current**: Floating footer always visible

**Target**: 
- Hide floating footer when not logged in
- Replace icons beside hamburger with "Sign Up" link
- Remove "Sign Up" link when logged in

**Files to modify**:
- `resources/views/components/bottom-nav.blade.php` - Add auth check
- `resources/views/header.blade.php` - Add mobile sign up link

---

### 7. Mobile - Keep Floating Footer Visible on Scroll (Logged In) ⏳
**Current**: Floating footer may hide on scroll

**Target**: Keep visible when user is logged in, regardless of scroll

**Files to modify**:
- `public/assets/js/mobile-enhanced.js` - Update scroll behavior
- `public/assets/css/mobile-enhanced.css` - Ensure fixed positioning

---

## Implementation Priority

1. **High Impact, Quick Wins**:
   - Hide mobile footer on desktop (CSS only)
   - Add login/signup to desktop navbar
   - Add user dropdown on desktop

2. **Medium Impact**:
   - Mobile footer visibility based on auth
   - Simplify registration

3. **Lower Priority**:
   - Complaints search flexibility
   - Mobile footer scroll behavior

## Estimated Time
- Quick wins: 15-20 minutes
- Full implementation: 30-45 minutes
