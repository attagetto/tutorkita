@props(['unreadCount' => 0])
<svg class="notification-hero-art" viewBox="0 0 410 210" fill="none" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
    <ellipse cx="202" cy="157" rx="185" ry="43" fill="#D4E4FB"/>
    <path d="m254 24 10 21M348 42l15-14M379 65l13-5M229 54l-16-12" stroke="#2F73E9" stroke-width="4" stroke-linecap="round"/>
    <rect x="268" y="17" width="122" height="61" rx="13" fill="white"/><rect x="282" y="31" width="29" height="29" rx="7" fill="#2E72E8"/><path d="m289 45 6 6 11-13" stroke="white" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M322 36h54M322 49h49M322 61h41" stroke="#BFD3F5" stroke-width="6" stroke-linecap="round"/>
    <rect x="245" y="94" width="145" height="67" rx="13" fill="white"/><rect x="260" y="110" width="29" height="29" rx="7" stroke="#3777E5" stroke-width="4"/><path d="m266 124 6 6 11-13" stroke="#3777E5" stroke-width="4" stroke-linecap="round"/><path d="M302 111h69M302 126h69M302 141h54" stroke="#BFD3F5" stroke-width="6" stroke-linecap="round"/>
    <g class="notification-bell-art"><path d="M160 39c-31 0-54 25-54 57v41c0 10-9 17-21 29h150c-12-12-21-19-21-29V96c0-32-23-57-54-57Z" fill="#F7B928"/><path d="M105 136c20-7 38-10 55-10s35 3 55 10v3c0 10 9 17 21 29H84c12-12 21-19 21-29v-3Z" fill="#F5AA12"/><circle cx="160" cy="33" r="11" fill="#EAA30E"/><path d="M149 172a11 11 0 0 0 22 0" fill="#EAA30E"/></g>
    @if ($unreadCount > 0)<circle cx="214" cy="57" r="26" fill="#F04444" stroke="white" stroke-width="5"/><text x="214" y="67" text-anchor="middle" fill="white" font-size="28" font-weight="700" font-family="Arial,sans-serif">{{ min($unreadCount, 9) }}</text>@endif
</svg>
