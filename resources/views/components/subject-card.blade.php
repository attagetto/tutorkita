@props(['subject', 'index' => 0])
<article class="subject-card animate-in" style="--reveal-delay: {{ $index * 55 }}ms" data-subject-card data-level="{{ implode(',', $subject['levels']) }}" data-searchable="{{ Str::lower($subject['name'].' '.$subject['category'].' '.implode(' ', $subject['levels'])) }}">
    <div class="subject-card-top"><span class="subject-icon subject-icon-{{ $subject['tone'] }}"><x-icon :name="$subject['icon']" /></span><span class="subject-category">{{ $subject['category'] }}</span></div>
    <h3>{{ $subject['name'] }}</h3><p>{{ $subject['description'] }}</p>
    <div class="subject-levels">@foreach ($subject['levels'] as $level)<span>{{ $level }}</span>@endforeach</div>
    <div class="subject-card-progress"><div><span>Progress belajarmu</span><strong>{{ $subject['progress'] }}%</strong></div><div class="progress-track"><span class="progress-fill fill-blue" style="width: {{ $subject['progress'] }}%"></span></div></div>
    <div class="subject-card-bottom"><span><x-icon name="users" class="icon-sm" /> {{ $subject['tutor_count'] }} tutor relawan</span><button type="button" class="subject-action" data-toast="Ayo lanjutkan belajar {{ $subject['name'] }} bersama tutor relawan.">Lihat materi <x-icon name="arrow-right" class="icon-sm" /></button></div>
</article>
