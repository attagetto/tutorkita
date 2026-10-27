@props(['tutor', 'index' => 0])
<article class="landing-tutor-card" data-reveal style="--reveal-delay: {{ $index * 65 }}ms">
    <div class="portrait-placeholder" aria-hidden="true"><span>{{ $tutor['initials'] }}</span></div>
    <div class="landing-tutor-info"><h3>{{ $tutor['name'] }}</h3><p>{{ $tutor['university'] }}</p><p>{{ $tutor['major'] }}</p><span class="landing-subject">{{ $tutor['subject'] }}</span></div>
    <button class="landing-tutor-action" type="button" aria-label="Lihat profil tutor {{ $tutor['name'] }}" data-tutor-detail data-name="{{ $tutor['name'] }}" data-university="{{ $tutor['university'] }}" data-major="{{ $tutor['major'] }}" data-subject="{{ $tutor['subject'] }}" data-description="{{ $tutor['description'] }}" data-rating="{{ $tutor['rating'] }}" data-availability="{{ $tutor['availability'] }}">Lihat Profil</button>
</article>
