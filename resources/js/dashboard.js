const dashboard = document.querySelector('.dashboard-body');

if (dashboard) {
    const sidebar = document.querySelector('#main-sidebar');
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const scrim = document.querySelector('[data-sidebar-close].mobile-scrim');
    const notificationMenu = document.querySelector('[data-notification-menu]');
    const profileMenu = document.querySelector('[data-profile-menu]');
    const toastRegion = document.querySelector('[data-toast-region]');
    const searchInput = document.querySelector('[data-dashboard-search]');
    const subjectFilter = document.querySelector('#tutor-subject-filter');
    const levelFilter = document.querySelector('#subject-level-filter');
    let activeScheduleFilter = 'all';

    const setSidebar = (open) => {
        sidebar?.classList.toggle('is-open', open);
        sidebarToggle?.setAttribute('aria-expanded', String(open));
        if (scrim) {
            scrim.hidden = !open;
            requestAnimationFrame(() => scrim.classList.toggle('is-visible', open));
        }
        document.body.classList.toggle('drawer-open', open);
    };

    const closeMenus = (except = null) => {
        if (except !== notificationMenu) {
            notificationMenu.hidden = true;
            document.querySelector('[data-notification-toggle]')?.setAttribute('aria-expanded', 'false');
        }
        if (except !== profileMenu) {
            profileMenu.hidden = true;
            document.querySelectorAll('[data-profile-toggle]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
        }
    };

    const showToast = (message) => {
        if (!message || !toastRegion) return;
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v5m0-8h.01"></path></svg>';
        const text = document.createElement('span');
        text.textContent = message;
        const close = document.createElement('button');
        close.type = 'button';
        close.setAttribute('aria-label', 'Tutup notifikasi');
        close.textContent = '×';
        close.addEventListener('click', () => dismissToast(toast));
        toast.append(text, close);
        toastRegion.append(toast);
        window.setTimeout(() => dismissToast(toast), 4200);
    };

    const dismissToast = (toast) => {
        if (!toast.isConnected) return;
        toast.classList.add('is-leaving');
        window.setTimeout(() => toast.remove(), 210);
    };

    const filterContent = () => {
        const query = searchInput?.value.trim().toLocaleLowerCase('id') ?? '';
        let visibleSchedules = 0;
        document.querySelectorAll('[data-schedule-item]').forEach((item) => {
            const matchesTab = activeScheduleFilter === 'all' || item.dataset.category === activeScheduleFilter;
            const matchesSearch = !query || item.dataset.searchable.includes(query);
            const visible = matchesTab && matchesSearch;
            item.hidden = !visible;
            if (visible) visibleSchedules += 1;
        });
        const scheduleEmpty = document.querySelector('[data-schedule-empty]');
        if (scheduleEmpty) scheduleEmpty.hidden = visibleSchedules > 0;

        let visibleTutors = 0;
        const selectedSubject = subjectFilter?.value ?? 'all';
        document.querySelectorAll('[data-tutor-card]').forEach((card) => {
            const matchesSubject = selectedSubject === 'all' || card.dataset.subject === selectedSubject;
            const matchesSearch = !query || card.dataset.searchable.includes(query);
            const visible = matchesSubject && matchesSearch;
            card.hidden = !visible;
            if (visible) visibleTutors += 1;
        });
        const tutorEmpty = document.querySelector('[data-tutor-empty]');
        if (tutorEmpty) tutorEmpty.hidden = visibleTutors > 0;
        const count = document.querySelector('.tutor-count');
        if (count) count.textContent = `${visibleTutors} tutor`;

        const selectedLevel = levelFilter?.value ?? 'all';
        let visibleSubjects = 0;
        document.querySelectorAll('[data-subject-card]').forEach((card) => {
            const levels = card.dataset.level.split(',');
            const matchesLevel = selectedLevel === 'all' || levels.includes(selectedLevel);
            const matchesSearch = !query || card.dataset.searchable.includes(query);
            const visible = matchesLevel && matchesSearch;
            card.hidden = !visible;
            if (visible) visibleSubjects += 1;
        });
        const subjectEmpty = document.querySelector('[data-subject-empty]');
        if (subjectEmpty) subjectEmpty.hidden = visibleSubjects > 0;
    };

    sidebarToggle?.addEventListener('click', () => setSidebar(!sidebar.classList.contains('is-open')));
    scrim?.addEventListener('click', () => setSidebar(false));
    document.querySelectorAll('.sidebar [href^="#"]').forEach((link) => link.addEventListener('click', () => setSidebar(false)));
    document.querySelector('.sidebar-close')?.addEventListener('click', () => setSidebar(false));

    document.querySelector('[data-notification-toggle]')?.addEventListener('click', (event) => {
        event.stopPropagation();
        closeMenus(notificationMenu);
        notificationMenu.hidden = !notificationMenu.hidden;
        event.currentTarget.setAttribute('aria-expanded', String(!notificationMenu.hidden));
    });

    document.querySelectorAll('[data-profile-toggle]').forEach((button) => button.addEventListener('click', (event) => {
        event.stopPropagation();
        closeMenus(profileMenu);
        profileMenu.hidden = !profileMenu.hidden;
        document.querySelectorAll('[data-profile-toggle]').forEach((trigger) => trigger.setAttribute('aria-expanded', String(!profileMenu.hidden)));
    }));

    document.querySelector('[data-mark-read]')?.addEventListener('click', () => {
        document.querySelectorAll('.notification-item.is-unread').forEach((item) => item.classList.remove('is-unread'));
        const unreadLabel = document.querySelector('.dropdown-heading > div > span');
        if (unreadLabel) unreadLabel.textContent = 'Semua sudah dibaca';
        document.querySelector('.notification-dot')?.remove();
        showToast('Notifikasi ditandai sudah dibaca.');
    });

    document.querySelectorAll('[data-schedule-filter]').forEach((tab) => tab.addEventListener('click', () => {
        activeScheduleFilter = tab.dataset.scheduleFilter;
        document.querySelectorAll('[data-schedule-filter]').forEach((button) => {
            const active = button === tab;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-selected', String(active));
        });
        filterContent();
    }));

    searchInput?.addEventListener('input', filterContent);
    subjectFilter?.addEventListener('change', filterContent);
    levelFilter?.addEventListener('change', filterContent);

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.dropdown-wrap')) closeMenus();
        const action = event.target.closest('[data-toast]');
        if (action) showToast(action.dataset.toast);
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeMenus();
            setSidebar(false);
        }
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            searchInput?.focus();
        }
    });

    window.matchMedia('(min-width: 961px)').addEventListener('change', (event) => {
        if (event.matches) setSidebar(false);
    });
}
