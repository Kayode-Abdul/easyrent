@extends('layouts.app')
@section('content')
<div class="container">
    <h2>My Earnings</h2>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Referred User</th>
                <th>Reward Level</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rewards as $reward)
                <tr>
                    <td>{{ $reward->referral->referred->first_name ?? 'N/A' }} {{ $reward->referral->referred->last_name ?? '' }}</td>
                    <td>{{ ucfirst($reward->reward_level) }}</td>
                    <td>{{ number_format($reward->amount, 2) }}</td>
                    <td><span class="badge badge-{{ $reward->status == 'paid' ? 'success' : ($reward->status == 'approved' ? 'info' : 'warning') }}">{{ ucfirst($reward->status) }}</span></td>
                    <td>{{ $reward->created_at->format('M d, Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">
        <strong>Total Earned:</strong> {{ number_format($totalEarned, 2) }}<br>
        <strong>Total Pending:</strong> {{ number_format($totalPending, 2) }}
    </div>
</div>
@endsection
