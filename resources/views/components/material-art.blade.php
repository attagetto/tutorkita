@props(['kind' => 'book'])
<span {{ $attributes->class(['material-illustration', 'illustration-'.$kind]) }} aria-hidden="true">
    <svg viewBox="0 0 240 120" fill="none" xmlns="http://www.w3.org/2000/svg">
        @switch($kind)
            @case('graph')
                <path d="M38 91h164M49 83V31" stroke="currentColor" stroke-width="4" stroke-linecap="round"/><path d="m63 72 31-22 22 12 38-34 27 11" stroke="#F4A623" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/><circle cx="94" cy="50" r="5" fill="#fff" stroke="#2472D3" stroke-width="4"/><circle cx="154" cy="28" r="5" fill="#fff" stroke="#2472D3" stroke-width="4"/><path d="M70 98h99" stroke="#8FB8EE" stroke-width="7" stroke-linecap="round"/>
                @break
            @case('lungs')
                <path d="M119 25v35m0-25c-8 9-13 23-16 36m16-25c8 9 13 23 16 36" stroke="#168F73" stroke-width="7" stroke-linecap="round"/><path d="M103 50c-17 3-29 23-29 42 0 12 8 18 19 13 15-7 21-23 22-41m22-14c17 3 29 23 29 42 0 12-8 18-19 13-15-7-21-23-22-41" fill="#67CFAF" stroke="#26A981" stroke-width="4"/><circle cx="78" cy="40" r="12" fill="#B4E8DA"/><circle cx="171" cy="31" r="8" fill="#B4E8DA"/>
                @break
            @case('book')
                <path d="M120 91c-24-17-48-19-76-14V31c28-5 52-3 76 14v46Zm0 0c24-17 48-19 76-14V31c-28-5-52-3-76 14v46Z" fill="#fff" stroke="#8264C9" stroke-width="5" stroke-linejoin="round"/><path d="M59 47c13-2 25 0 35 5m-35 12c13-2 25 0 35 5m51-17c12-5 24-7 35-5m-35 17c12-5 24-7 35-5" stroke="#B3A2E3" stroke-width="4" stroke-linecap="round"/>
                @break
            @case('english')
                <path d="M45 37c0-9 7-16 16-16h58c9 0 16 7 16 16v28c0 9-7 16-16 16H86l-21 17V81c-11-2-20-10-20-21V37Z" fill="#3478DD"/><path d="M110 58c0-8 7-15 15-15h50c8 0 15 7 15 15v25c0 8-7 15-15 15h-19l-18 13V98c-9-1-13-7-13-15V58Z" fill="#F4A623"/><path d="M65 62V40h15c8 0 8 12 0 12H65m0 0h15c8 0 8 12 0 12H65m57 15h39m-31-11 12-18 12 18" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                @break
            @case('cube')
                <path d="m120 22 57 29v49l-57 29-57-29V51l57-29Z" fill="#D7E7FF" stroke="#3478DD" stroke-width="5" stroke-linejoin="round"/><path d="m63 51 57 30 57-30m-57 30v48" stroke="#3478DD" stroke-width="5" stroke-linejoin="round"/><path d="m84 40 57 29" stroke="#9AC0F0" stroke-width="4"/>
                @break
            @case('plant')
                <path d="M42 91c39-15 117-15 156 0l-12 12H55L42 91Z" fill="#A96938"/><path d="M120 88V50" stroke="#548A43" stroke-width="7" stroke-linecap="round"/><path d="M119 67c-27 1-44-13-44-35 25-2 42 9 44 35Zm3-14c5-25 22-37 48-33 0 23-18 36-48 33Z" fill="#72B94E" stroke="#4E9538" stroke-width="4" stroke-linejoin="round"/><circle cx="64" cy="82" r="5" fill="#D79A64"/><circle cx="178" cy="84" r="4" fill="#D79A64"/>
                @break
            @case('temple')
                <path d="M48 91h144m-132-9h120m-108-9h96m-84-10h72m-59-10h46m-33-11h20" stroke="#A77955" stroke-width="8" stroke-linecap="round"/><path d="m72 52 48-30 48 30H72Z" fill="#D3AB83"/><path d="M106 62V49h28v13m-19 29V73h10v18" stroke="#9C704D" stroke-width="4"/>
                @break
            @case('civic')
                <path d="m120 20 54 19v33c0 27-21 42-54 53-33-11-54-26-54-53V39l54-19Z" fill="#E34651"/><path d="M120 34v72m-35-44h70" stroke="#fff" stroke-width="10"/><path d="m54 37 10 6m122-6-10 6M45 54l12 2m138-2-12 2" stroke="#E6AF35" stroke-width="5" stroke-linecap="round"/>
                @break
            @default
                <path d="M120 24v76m0-76c-23-14-51-14-79-6v68c28-8 56-8 79 6m0-68c23-14 51-14 79-6v68c-28-8-56-8-79 6" stroke="currentColor" stroke-width="5" stroke-linejoin="round"/>
        @endswitch
    </svg>
</span>
