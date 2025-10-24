@extends('layout')
@section('content')
<div class="content">
    <h2>Compose Message</h2>
    <form method="POST" action="{{ route('messages.send') }}">
        @csrf
        <div class="form-group">
            <label for="receiver_id">To</label>
            <select name="receiver_id" id="receiver_id" class="form-control" required @if(request('to'))disabled @endif>
                <option value="" disabled selected>Select recipient</option>
                @foreach($users as $user)
                    <option value="{{ $user->user_id }}" 
                        @if(old('receiver_id') == $user->user_id || request('to') == $user->user_id) selected @endif>
                        {{ $user->username }} ({{ $user->email }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="form-group">
            <label for="subject">Subject</label>
            <input type="text" name="subject" id="subject" class="form-control" maxlength="255">
        </div>
        <div class="form-group">
            <label for="body">Message</label>
            <textarea name="body" id="body" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Send</button>
    </form>
</div>
@endsection
