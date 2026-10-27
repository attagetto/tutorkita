@props(['stat'])
<a class="stat-card stat-{{ $stat['tone'] }} animate-in" href="#{{ $stat['target'] }}" data-stat-card>
    <div class="stat-card-top"><span class="stat-icon"><x-icon :name="$stat['icon']" /></span><span class="stat-arrow"><x-icon name="arrow-up-right" class="icon-sm" /></span></div>
    <div class="stat-value">{{ $stat['value'] }}</div><div class="stat-label">{{ $stat['label'] }}</div><div class="stat-detail">{{ $stat['detail'] }}</div>
</a>
