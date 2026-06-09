@props([
    'currencies' => [],
    'decimals' => 0,
    'fallback' => null,
    'class' => 'card-title'
])

@php
    $currencyList = is_array($currencies) ? $currencies : [];
    $count = count($currencyList);
@endphp

@if($count > 0)
    <div class="currency-scroll-wrapper">
        <div class="currency-scroll-track">
            @foreach($currencyList as $code => $data)
                <div class="currency-scroll-slide">
                    <p class="{{ $class }}" style="font-size: 1.2rem; margin-bottom: 0;">
                        @if($count > 1)
                            <span class="currency-code-badge">{{ $code }}</span>
                        @endif
                        {{ $data['symbol'] ?? '' }}{{ number_format($data['amount'] ?? 0, $decimals) }}
                    </p>
                </div>
            @endforeach
        </div>
    </div>
@else
    <p class="{{ $class }}">{{ $fallback ?? format_money(0) }}</p>
@endif
