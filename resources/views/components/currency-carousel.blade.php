@props([
    'currencies' => [],
    'decimals' => 2,
    'fallback' => null,
    'id' => null
])

@php
    $currencyList = is_array($currencies) ? $currencies : (is_object($currencies) && method_exists($currencies, 'toArray') ? $currencies->toArray() : []);
    $count = count($currencyList);
    $carouselId = $id ?? 'carousel-' . uniqid();
@endphp

<style>
    .currency-carousel-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        position: relative;
    }
    .currency-carousel-inner {
        flex-grow: 1;
        text-align: center;
        overflow: hidden;
        position: relative;
    }
    .currency-carousel-item {
        display: none;
        width: 100%;
        animation: fadeIn 0.3s ease-in-out;
    }
    .currency-carousel-item.active {
        display: block;
    }
    .currency-carousel-nav {
        cursor: pointer;
        padding: 5px 10px;
        color: #6c757d;
        transition: color 0.2s;
        user-select: none;
    }
    .currency-carousel-nav:hover {
        color: #343a40;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(2px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<div id="{{ $carouselId }}" class="currency-carousel-wrapper">
    @if($count > 1)
        <div class="currency-carousel-nav" onclick="slideCarousel('{{ $carouselId }}', -1)">
            <i class="fa fa-chevron-left" style="font-size: 0.7em;"></i>
        </div>
    @endif

    <div class="currency-carousel-inner">
        @if($count > 0)
            @php $index = 0; @endphp
            @foreach($currencyList as $code => $data)
                <div class="currency-carousel-item {{ $index == 0 ? 'active' : '' }}" data-index="{{ $index }}">
                    <h3 class="mb-0" style="font-size: 1.5rem; font-weight: 700; color: inherit; line-height: 1.2;">
                        {{ $data['symbol'] ?? '' }}{{ number_format($data['amount'] ?? 0, $decimals) }}
                    </h3>
                </div>
                @php $index++; @endphp
            @endforeach
        @else
            <div class="currency-carousel-item active" data-index="0">
                <h3 class="mb-0" style="font-size: 1.5rem; font-weight: 700; color: inherit; line-height: 1.2;">{{ $fallback ?? format_money(0) }}</h3>
            </div>
        @endif
    </div>

    @if($count > 1)
        <div class="currency-carousel-nav" onclick="slideCarousel('{{ $carouselId }}', 1)">
            <i class="fa fa-chevron-right" style="font-size: 0.7em;"></i>
        </div>
    @endif
</div>

<script>
    if (typeof slideCarousel !== 'function') {
        function slideCarousel(carouselId, direction) {
            const wrapper = document.getElementById(carouselId);
            if (!wrapper) return;
            const items = wrapper.querySelectorAll('.currency-carousel-item');
            if (items.length <= 1) return;
            
            let activeIndex = -1;
            items.forEach((item, index) => {
                if (item.classList.contains('active')) activeIndex = index;
            });
            
            if (activeIndex === -1) activeIndex = 0;
            items[activeIndex].classList.remove('active');
            
            let nextIndex = activeIndex + direction;
            if (nextIndex >= items.length) nextIndex = 0;
            if (nextIndex < 0) nextIndex = items.length - 1;
            
            items[nextIndex].classList.add('active');
        }
    }
</script>
