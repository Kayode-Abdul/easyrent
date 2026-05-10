@extends('layout')
@section('content')
<div class="content">
    <div class="d-flex align-items-center mb-3">
         <!--<a href="{ route('messages.inbox') }" class="btn btn-outline-secondary btn-sm me-3">
            <i class="nc-icon nc-minimal-left"></i> Back to Inbox 
        </a>-->
        <h2 class="mb-0">Compose Message</h2>
    </div>
    <form method="POST" action="{{ route('messages.send') }}">
        @csrf
        <div class="form-group">
            <label for="receiver_id">To</label>

            {{-- Toggle between contact list and manual input --}}
            <div class="mb-2">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="recipient_mode" id="mode_contacts" value="contacts" checked onclick="toggleRecipientMode('contacts')">
                    <label class="form-check-label" for="mode_contacts">Select from contacts</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="recipient_mode" id="mode_manual" value="manual" onclick="toggleRecipientMode('manual')">
                    <label class="form-check-label" for="mode_manual">Enter user ID or email</label>
                </div>
            </div>

            {{-- Contact list select --}}
            <div id="contacts_field">
                <select name="receiver_id" id="receiver_id" class="form-control" @if(request('to'))disabled @endif>
                    <option value="" disabled selected>Select recipient</option>
                    @foreach($users as $user)
                        <option value="{{ $user->user_id }}" 
                            @if(old('receiver_id') == $user->user_id || request('to') == $user->user_id) selected @endif>
                            {{ $user->username ?? ($user->first_name . ' ' . $user->last_name) }} ({{ $user->email }})
                        </option>
                    @endforeach
                </select>
                @if(request('to'))
                    <input type="hidden" name="receiver_id" value="{{ request('to') }}">
                @endif
            </div>

            {{-- Manual input field --}}
            <div id="manual_field" style="display: none;">
                <input type="text" name="receiver_lookup" id="receiver_lookup" class="form-control" 
                       placeholder="Enter user ID or email address" value="{{ old('receiver_lookup') }}">
                <small class="form-text text-muted">Type the recipient's user ID or registered email address.</small>
                <div id="lookup_result" class="mt-1"></div>
            </div>

            @error('receiver_id')
                <span class="text-danger">{{ $message }}</span>
            @enderror
            @error('receiver_lookup')
                <span class="text-danger">{{ $message }}</span>
            @enderror
        </div>
        <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" name="subject" id="subject" class="form-control" maxlength="255" value="{{ old('subject') }}">
        </div>
        <div class="form-group">
            <label for="body">Message</label>
            <textarea name="body" id="body" class="form-control" rows="5" required>{{ old('body') }}</textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>

<script>
function toggleRecipientMode(mode) {
    const contactsField = document.getElementById('contacts_field');
    const manualField = document.getElementById('manual_field');
    const selectEl = document.getElementById('receiver_id');
    const lookupEl = document.getElementById('receiver_lookup');

    if (mode === 'contacts') {
        contactsField.style.display = 'block';
        manualField.style.display = 'none';
        if (selectEl) selectEl.setAttribute('name', 'receiver_id');
        if (lookupEl) lookupEl.removeAttribute('required');
    } else {
        contactsField.style.display = 'none';
        manualField.style.display = 'block';
        if (selectEl) selectEl.removeAttribute('name');
        if (lookupEl) lookupEl.setAttribute('required', 'required');
    }
}

// Live lookup for manual input
const lookupInput = document.getElementById('receiver_lookup');
if (lookupInput) {
    let debounceTimer;
    lookupInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const val = this.value.trim();
        const resultDiv = document.getElementById('lookup_result');
        
        if (val.length < 3) {
            resultDiv.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/api/user/lookup/${encodeURIComponent(val)}`)
                .then(r => r.json())
                .then(data => {
                    if (data.success && data.user) {
                        resultDiv.innerHTML = `<span class="text-success"><i class="fa fa-check-circle"></i> Found: ${data.user.first_name} ${data.user.last_name} (${data.user.email})</span>`;
                    } else {
                        resultDiv.innerHTML = `<span class="text-warning"><i class="fa fa-exclamation-triangle"></i> No user found with that ID or email.</span>`;
                    }
                })
                .catch(() => {
                    resultDiv.innerHTML = '';
                });
        }, 500);
    });
}
</script>
@endsection
