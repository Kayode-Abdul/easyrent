@props(['title' =&gt; '', 'value' =&gt; '', 'subtext' =&gt; '', 'icon' =&gt; '', 'color' =&gt; 'primary', 'subicon' =&gt; '', 'valueClass' =&gt; 'h5 mb-0 font-weight-bold text-gray-800', 'subtextClass' =&gt; 'text-muted'])

&lt;div class=&quot;card border-left-{{ $color }} shadow h-100 py-2&quot;&gt;
    &lt;div class=&quot;card-body&quot;&gt;
        &lt;div class=&quot;row no-gutters align-items-center&quot;&gt;
            &lt;div class=&quot;col mr-2&quot;&gt;
                &lt;div class=&quot;text-xs font-weight-bold text-{{ $color }} text-uppercase mb-1&quot;&gt;{{ $title }}&lt;/div&gt;
                &lt;div class=&quot;{{ $valueClass }}&quot;&gt;{{ $value }}&lt;/div&gt;
                &lt;div class=&quot;text-xs {{ $subtextClass }}&quot;&gt;
                    @if($subicon)&lt;i class=&quot;{{ $subicon }}&quot;&gt;&lt;/i&gt;@endif
                    {{ $subtext }}
                &lt;/div&gt;
            &lt;/div&gt;
            &lt;div class=&quot;col-auto&quot;&gt;
                &lt;i class=&quot;{{ $icon }} fa-2x text-gray-300&quot;&gt;&lt;/i&gt;
            &lt;/div&gt;
        &lt;/div&gt;
    &lt;/div&gt;
&lt;/div&gt;