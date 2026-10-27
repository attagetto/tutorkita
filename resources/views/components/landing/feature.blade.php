@props(['feature', 'index' => 0])
<button class="feature-item" type="button" data-reveal style="--reveal-delay: {{ $index * 90 }}ms" data-feature-info="{{ $feature['title'] }}|{{ $feature['description'] }}">
    <div class="feature-icon"><x-icon :name="$feature['icon']" /></div>
    <h3>{{ $feature['title'] }}</h3>
    <p>{{ $feature['description'] }}</p>
</button>
