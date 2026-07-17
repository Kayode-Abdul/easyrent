@props([
    'currencies' => [],
    'decimals' => 2,
    'fallback' => null,
    'id' => null
])

@php
    $rawList = is_array($currencies) ? $currencies : (is_object($currencies) && method_exists($currencies, 'toArray') ? $currencies->toArray() : []);
    
    // Aggregate by currency code
    $aggregated = [];
    foreach ($rawList as $key => $data) {
        $code = 'NGN';
        $symbol = '₦';
        $amount = 0;
        
        if (is_array($data)) {
            if (isset($data['code'])) $code = $data['code'];
            elseif (isset($data['currency']['code'])) $code = $data['currency']['code'];
            elseif (is_string($key)) $code = $key;
            
            if (isset($data['symbol'])) $symbol = $data['symbol'];
            elseif (isset($data['currency']['symbol'])) $symbol = $data['currency']['symbol'];
            
            $amount = $data['amount'] ?? $data['total'] ?? $data['total_amount'] ?? 0;
        } elseif (is_object($data)) {
            $code = $data->code ?? $data->currency->code ?? (is_string($key) ? $key : 'NGN');
            $symbol = $data->symbol ?? $data->currency->symbol ?? '₦';
            $amount = $data->amount ?? $data->total ?? $data->total_amount ?? 0;
        }

        if (!isset($aggregated[$code])) {
            $aggregated[$code] = ['amount' => 0, 'symbol' => $symbol, 'code' => $code];
        }
        $aggregated[$code]['amount'] += (float)$amount;
    }
    
    $currencyList = $aggregated;
    $count = count($currencyList);
    $carouselId = $id ?? 'carousel-' . uniqid();
@endphp

<style>
    .cc-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        width: 100%;
    }
    .cc-card .cc-figure {
        font-weight: 700;
        color: inherit;
        font-size: clamp(1rem, 1.6vw, 1.4rem) !important;
        margin: 0;
        line-height: 1.3;
        text-align: center;
        word-break: break-word;
        overflow-wrap: break-word;
        white-space: normal;
        max-width: 100%;
    }
    .cc-card .cc-code {
        font-size: 0.65rem;
        opacity: 0.7;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-top: 2px;
    }
    .cc-card .cc-slide {
        display: none;
        width: 100%;
        text-align: center;
        animation: ccFadeIn 0.3s ease-in-out;
    }
    .cc-card .cc-slide.active {
        display: block;
    }
    .cc-card .cc-nav-row {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        margin-top: 4px;
    }
    .cc-card .cc-nav-btn {
        cursor: pointer;
        width: 22px;
        height: 22px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(0,0,0,0.08);
        color: inherit;
        opacity: 0.6;
        transition: all 0.2s;
        user-select: none;
        flex-shrink: 0;
    }
    .cc-card .cc-nav-btn:hover {
        opacity: 1;
        background: rgba(0,0,0,0.15);
    }
    .cc-card .cc-dots {
        display: flex;
        gap: 4px;
    }
    .cc-card .cc-dot {
        width: 5px;
        height: 5px;
        border-radius: 50%;
        background: currentColor;
        opacity: 0.25;
        transition: opacity 0.2s;
    }
    .cc-card .cc-dot.active {
        opacity: 0.8;
    }
    @keyframes ccFadeIn {
        from { opacity: 0; transform: translateY(2px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div id="{{ $carouselId }}" class="cc-card">
    {{-- Slides area --}}
    <div style="width:100%;">
        @if($count > 0)
            @php $index = 0; @endphp
            @foreach($currencyList as $code => $data)
                <div class="cc-slide {{ $index == 0 ? 'active' : '' }}" data-index="{{ $index }}">
                    <p class="cc-figure">{{ $data['symbol'] ?? '' }}{{ number_format($data['amount'] ?? 0, $decimals) }}</p>
                    @if($count > 1)
                        <span class="cc-code">{{ $data['code'] ?? $code }}</span>
                    @endif
                </div>
                @php $index++; @endphp
            @endforeach
        @else
            <div class="cc-slide active" data-index="0">
                <p class="cc-figure">{{ $fallback ?? format_money(0) }}</p>
            </div>
        @endif
    </div>

    {{-- Navigation row (below the figure) --}}
    @if($count > 1)
        <div class="cc-nav-row">
            <div class="cc-nav-btn" onclick="slideCc('{{ $carouselId }}', -1)">
                <i class="fa fa-chevron-left" style="font-size: 0.55em;"></i>
            </div>
            <div class="cc-dots">
                @for($d = 0; $d < $count; $d++)
                    <span class="cc-dot {{ $d == 0 ? 'active' : '' }}" data-dot="{{ $d }}"></span>
                @endfor
            </div>
            <div class="cc-nav-btn" onclick="slideCc('{{ $carouselId }}', 1)">
                <i class="fa fa-chevron-right" style="font-size: 0.55em;"></i>
            </div>
        </div>
    @endif
</div>

<script>
    if (typeof slideCc !== 'function') {
        function slideCc(carouselId, direction) {
            const wrapper = document.getElementById(carouselId);
            if (!wrapper) return;
            const items = wrapper.querySelectorAll('.cc-slide');
            const dots = wrapper.querySelectorAll('.cc-dot');
            if (items.length <= 1) return;
            
            let activeIndex = -1;
            items.forEach((item, index) => {
                if (item.classList.contains('active')) activeIndex = index;
            });
            
            if (activeIndex === -1) activeIndex = 0;
            items[activeIndex].classList.remove('active');
            if (dots[activeIndex]) dots[activeIndex].classList.remove('active');
            
            let nextIndex = activeIndex + direction;
            if (nextIndex >= items.length) nextIndex = 0;
            if (nextIndex < 0) nextIndex = items.length - 1;
            
            items[nextIndex].classList.add('active');
            if (dots[nextIndex]) dots[nextIndex].classList.add('active');
        }
    }
</script>
