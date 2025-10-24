@extends('layout')

@section('content')
<div class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title"><i class="nc-icon nc-single-02"></i> Assign Roles</h4>
                    <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Modern Role Assignment</h5>
                            <form method="POST" action="{{ route('admin.roles.assign.post') }}" id="modernRoleForm">
                                @csrf
                                <input type="hidden" name="role_type" value="modern">
                                <div class="form-group">
                                    <label>User <span class="small text-muted">(type to search)</span></label>
                                    <input type="text" id="modernUserInput" class="form-control" list="modernUserDatalist" placeholder="Start typing name or email" autocomplete="off" required>
                                    <datalist id="modernUserDatalist">
                                        @foreach($users as $u)
                                            <option value="{{ $u->first_name }} {{ $u->last_name }} ({{ $u->email }})"></option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="user_id" id="modernUserId" required>
                                    <small class="form-text text-muted" id="modernUserFeedback"></small>
                                </div>
                                <div class="form-group">
                                    <label>Role <span class="small text-muted" title="Some lifecycle roles are hidden and auto-managed">(managed list)</span></label>
                                    <select name="role_id" class="form-control" id="roleSelect" required>
                                        <option value="">-- Select Role --</option>
                                        @foreach(($assignableRoles ?? $allRoles) as $r)
                                            <option value="{{ $r->id }}" data-name="{{ strtolower($r->name) }}">{{ $r->display_name ?? ucfirst(str_replace('_',' ', $r->name)) }}</option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">Hidden roles (tenant, landlord, marketer, super marketer, property manager, artisan) are granted by user actions.</small>
                                </div>

                                <div id="regionalScopeWrapper" class="border rounded p-3 mb-3" style="display:none;">
                                    <h6 class="mb-2">Assign Region(s)</h6>
                                    <p class="small text-muted mb-2">Select a state and optionally an LGA (city). Add multiple scopes if needed.</p>
                                    <div id="scopesContainer"></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addScopeBtn"><i class="fa fa-plus"></i> Add Scope</button>
                                </div>

                                <button type="submit" class="btn btn-primary">Assign Role</button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h5>Legacy Role Assignment</h5>
                            <form method="POST" action="{{ route('admin.roles.assign.post') }}">
                                @csrf
                                <input type="hidden" name="role_type" value="legacy">
                                <div class="form-group">
                                    <label>User <span class="small text-muted">(type to search)</span></label>
                                    <input type="text" id="legacyUserInput" class="form-control" list="legacyUserDatalist" placeholder="Start typing name or email" autocomplete="off" required>
                                    <datalist id="legacyUserDatalist">
                                        @foreach($users as $u)
                                            <option value="{{ $u->first_name }} {{ $u->last_name }} ({{ $u->email }})"></option>
                                        @endforeach
                                    </datalist>
                                    <input type="hidden" name="user_id" id="legacyUserId" required>
                                    <small class="form-text text-muted" id="legacyUserFeedback"></small>
                                </div>
                                <div class="form-group">
                                    <label>Legacy Role (Numeric)</label>
                                    <select name="legacy_role" class="form-control" required id="legacyRoleSelect">
                                        <option value="">-- Select Legacy Role --</option>
                                        <option value="1">Landlord</option>
                                        <option value="2">Tenant</option>
                                        <option value="3">Artisan</option>
                                        <option value="4">Property Manager</option>
                                        <option value="5">(Reserved)</option>
                                        <option value="6" data-legacy-rm="1">Regional Manager</option>
                                        <option value="7">Marketer</option>
                                    </select>
                                </div>
                                <div id="legacyRegionalScopeWrapper" class="border rounded p-3 mb-3" style="display:none;">
                                    <h6 class="mb-2">Assign Region(s)</h6>
                                    <div id="legacyScopesContainer"></div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" id="addLegacyScopeBtn"><i class="fa fa-plus"></i> Add Scope</button>
                                </div>
                                <button type="submit" class="btn btn-warning">Assign Legacy Role</button>
                            </form>
                        </div>
                    </div>
                    <hr>
                    <div class="row mt-3">
                        <div class="col-12">
                            <h5 class="mb-3">Recent Assignment Activity</h5>
                            <div class="table-responsive" style="max-height:220px; overflow:auto;">
                                @if(session('success'))
                                    <div style="padding:8px;background:#d1fae5;color:#065f46;margin-bottom:10px;">{{ session('success') }}</div>
                                @endif
                                @if(session('error'))
                                    <div style="padding:8px;background:#fee2e2;color:#991b1b;margin-bottom:10px;">{{ session('error') }}</div>
                                @endif
                                <div style="display:flex;gap:12px;margin-bottom:12px;align-items:center;flex-wrap:wrap;">
                                    <form method="GET" action="{{ route('admin.roles.audits.export') }}" style="display:flex;gap:4px;align-items:center;">
                                        <label>Last
                                            <input type="number" name="days" value="30" min="1" max="365" style="width:70px;"> days
                                        </label>
                                        <button type="submit">Export CSV</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.roles.audits.prune') }}" onsubmit="return confirm('Delete audit records older than the keep window?');" style="display:flex;gap:4px;align-items:center;">
                                        @csrf
                                        <label>Keep last
                                            <input type="number" name="keep_days" value="30" min="1" max="365" style="width:70px;"> days
                                        </label>
                                        <button type="submit" style="background:#b91c1c;color:#fff;">Prune Old</button>
                                    </form>
                                </div>
                                <table class="table table-sm table-striped mb-0">
                                    <thead>
                                        <tr>
                                            <th>When</th>
                                            <th>Actor</th>
                                            <th>Target User</th>
                                            <th>Role / Legacy</th>
                                            <th>Action</th>
                                            <th>Reason</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse(($recentAudits ?? []) as $a)
                                            <tr>
                                                <td>{{ $a->created_at->diffForHumans() }}</td>
                                                <td>{{ $a->actor?->first_name }} {{ $a->actor?->last_name }}</td>
                                                <td>{{ $a->user?->first_name }} {{ $a->user?->last_name }}</td>
                                                <td>
                                                    @if($a->role)
                                                        {{ $a->role->display_name ?? ucfirst(str_replace('_',' ', $a->role->name)) }}
                                                    @elseif($a->legacy_role)
                                                        Legacy #{{ $a->legacy_role }}
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $a->action === 'assigned' ? 'success' : ($a->action === 'blocked' ? 'danger' : 'secondary') }}">{{ $a->action }}</span>
                                                </td>
                                                <td class="small">{{ $a->reason }}</td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="6" class="text-muted">No recent activity.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Guard against redeclaration across partials/pages
    if (typeof window.statesData === 'undefined') {
        // Convert array of state objects to object with state names as keys
        const statesArray = @json(json_decode(file_get_contents(resource_path('states-and-cities.json')), true));
        window.statesData = {};
        statesArray.forEach(state => {
            // Ensure we handle states without cities properly
            window.statesData[state.name] = Array.isArray(state.cities) ? state.cities : [];
        });
    }

    function buildScopeRow(prefix) {
        const idx = Date.now() + Math.random();
        const stateOptions = Object.keys(window.statesData).map(s => `<option value="${s}">${s}</option>`).join('');
        return `<div class="scope-row mb-2 p-2 border rounded" data-idx="${idx}">
            <div class="form-row">
                <div class="col-md-5 mb-2">
                    <label class="small mb-1">State</label>
                    <select name="${prefix}_state[]" class="form-control state-select" required>
                        <option value="">-- Select State --</option>${stateOptions}
                    </select>
                </div>
                <div class="col-md-5 mb-2">
                    <label class="small mb-1">LGA (Optional)</label>
                    <select name="${prefix}_lga[]" class="form-control lga-select">
                        <option value="">-- Any LGA --</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end mb-2">
                    <button type="button" class="btn btn-outline-danger btn-sm removeScopeBtn">&times;</button>
                </div>
            </div>
        </div>`;
    }

    function attachScopeEvents(container) {
        container.addEventListener('change', function(e){
            if(e.target.classList.contains('state-select')) {
                const state = e.target.value;
                const cities = Array.isArray(window.statesData[state]) ? window.statesData[state] : [];
                const lgaSelect = e.target.closest('.scope-row').querySelector('.lga-select');
                lgaSelect.innerHTML = `<option value="">-- Any LGA --</option>` + cities.map(c => `<option value="${c}">${c}</option>`).join('');
            }
        });
        container.addEventListener('click', function(e){
            if(e.target.classList.contains('removeScopeBtn')) {
                e.target.closest('.scope-row').remove();
            }
        });
    }

    document.addEventListener('DOMContentLoaded', function(){
        const roleSelect = document.getElementById('roleSelect');
        const regionalWrapper = document.getElementById('regionalScopeWrapper');
        const scopesContainer = document.getElementById('scopesContainer');
        const addScopeBtn = document.getElementById('addScopeBtn');

        const legacyRoleSelect = document.getElementById('legacyRoleSelect');
        const legacyWrapper = document.getElementById('legacyRegionalScopeWrapper');
        const legacyScopesContainer = document.getElementById('legacyScopesContainer');
        const addLegacyScopeBtn = document.getElementById('addLegacyScopeBtn');

        attachScopeEvents(scopesContainer);
        attachScopeEvents(legacyScopesContainer);

        function toggleRegional(wrapper, isRM, container){
            if(isRM){
                wrapper.style.display = 'block';
                if(!container.querySelector('.scope-row')){
                    container.insertAdjacentHTML('beforeend', buildScopeRow(container.id.startsWith('legacy') ? 'legacy' : 'modern'));
                }
            } else {
                wrapper.style.display = 'none';
                container.innerHTML='';
            }
        }

        if (roleSelect) {
            roleSelect.addEventListener('change', function(){
                const selected = roleSelect.options[roleSelect.selectedIndex];
                const isRM = (selected.dataset.name || '').toLowerCase().replace(/\s+/g,'_') === 'regional_manager';
                toggleRegional(regionalWrapper, isRM, scopesContainer);
            });
        }
        if (legacyRoleSelect) {
            legacyRoleSelect.addEventListener('change', function(){
                const selected = legacyRoleSelect.options[legacyRoleSelect.selectedIndex];
                const isRM = selected.getAttribute('data-legacy-rm') === '1';
                toggleRegional(legacyWrapper, isRM, legacyScopesContainer);
            });
        }

        if (addScopeBtn) addScopeBtn.addEventListener('click', function(){
            scopesContainer.insertAdjacentHTML('beforeend', buildScopeRow('modern'));
        });
        if (addLegacyScopeBtn) addLegacyScopeBtn.addEventListener('click', function(){
            legacyScopesContainer.insertAdjacentHTML('beforeend', buildScopeRow('legacy'));
        });
    });
</script>
<script>
    (function(){
        const users = @json($users->map(fn($u) => [
            'id' => $u->user_id,
            'label' => trim($u->first_name.' '.$u->last_name).' ('.$u->email.')'
        ]));
        function bindUserDatalist(textId, hiddenId, feedbackId){
            const input = document.getElementById(textId);
            const hidden = document.getElementById(hiddenId);
            const feedback = document.getElementById(feedbackId);
            if(!input) return;
            input.addEventListener('input', function(){
                const val = input.value.trim().toLowerCase();
                const match = users.find(u => u.label.toLowerCase() === val);
                if(match){
                    hidden.value = match.id;
                    feedback.textContent = 'Selected user ID: '+match.id;
                    feedback.className = 'form-text text-success';
                } else {
                    hidden.value = '';
                    if(val.length){
                        const partial = users.filter(u => u.label.toLowerCase().includes(val)).slice(0,5).map(u=>u.label).join(' | ');
                        feedback.textContent = partial ? 'Suggestions: '+partial : 'No exact match';
                        feedback.className = 'form-text text-warning';
                    } else {
                        feedback.textContent='';
                    }
                }
            });
            input.form && input.form.addEventListener('submit', function(e){
                if(!hidden.value){
                    e.preventDefault();
                    feedback.textContent = 'Please choose a user from the suggestions (exact match required).';
                    feedback.className = 'form-text text-danger';
                    input.focus();
                }
            });
        }
        bindUserDatalist('modernUserInput','modernUserId','modernUserFeedback');
        bindUserDatalist('legacyUserInput','legacyUserId','legacyUserFeedback');
    })();
</script>
@endpush
