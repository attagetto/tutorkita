@props(['tutor', 'index' => 0])
<article class="tutor-card animate-in" style="--reveal-delay: {{ $index * 65 }}ms" data-tutor-card data-subject="{{ $tutor['subject'] }}" data-searchable="{{ Str::lower($tutor['name'].' '.$tutor['subject'].' '.$tutor['description']) }}">
    <div class="tutor-card-top"><div class="tutor-avatar avatar-{{ $tutor['tone'] }}">{{ $tutor['initials'] }}<span class="online-indicator"></span></div><span class="rating"><x-icon name="star" class="icon-sm" /> {{ $tutor['rating'] }}</span></div>
    <div class="tutor-card-info"><span class="subject-chip chip-{{ $tutor['tone'] }}">{{ $tutor['subject'] }}</span><h3>{{ $tutor['name'] }}</h3><p>{{ $tutor['description'] }}</p></div>
    <div class="tutor-card-meta"><span><x-icon name="book" class="icon-sm" /> {{ $tutor['sessions'] }}</span><span class="availability"><i></i>{{ $tutor['availability'] }}</span></div>
    <button class="button button-outline tutor-cta" type="button" data-toast="{{ $tutor['name'] }} siap membantumu belajar {{ strtolower($tutor['subject']) }}.">Kenali tutor <x-icon name="arrow-right" class="icon-sm" /></button>
</article>
