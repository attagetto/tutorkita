@props(['schedule', 'index' => 0])
<article class="schedule-item schedule-entrance" style="--schedule-delay: {{ $index * 55 }}ms" data-schedule-item data-category="{{ $schedule['status_key'] }}" data-searchable="{{ Str::lower($schedule['subject'].' '.$schedule['topic'].' '.$schedule['tutor']) }}">
    <div class="schedule-date"><span>{{ $schedule['date_label'] }}</span><strong>{{ \Carbon\Carbon::parse($schedule['date'])->format('d') }}</strong><small>{{ \Carbon\Carbon::parse($schedule['date'])->locale('id')->isoFormat('MMM') }}</small></div>
    <div class="schedule-color color-{{ $schedule['color'] }}"></div>
    <div class="schedule-detail"><div class="schedule-title-row"><h3>{{ $schedule['subject'] }}</h3><span class="schedule-status status-{{ $schedule['status_key'] }}"><i></i>{{ $schedule['status'] }}</span></div><p>{{ $schedule['topic'] }} <span>·</span> {{ $schedule['tutor'] }}</p><div class="schedule-time"><x-icon name="clock" class="icon-sm" /> {{ $schedule['time'] }}</div></div>
    <button class="icon-button schedule-action" type="button" aria-label="Lihat sesi {{ $schedule['subject'] }}" data-toast="{{ $schedule['topic'] }} bersama {{ $schedule['tutor'] }} pukul {{ $schedule['time'] }}."><x-icon name="arrow-right" class="icon-sm" /></button>
</article>
