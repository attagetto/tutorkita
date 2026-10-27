const landingPage = document.querySelector('.landing-body');

if (landingPage) {
    const navigation = document.querySelector('[data-landing-navigation]');
    const menuToggle = document.querySelector('[data-landing-menu-toggle]');
    const menuScrim = document.querySelector('[data-landing-menu-scrim]');
    const toastRegion = document.querySelector('[data-landing-toast]');
    const tutorSearch = document.querySelector('[data-landing-tutor-search]');
    const tutorEmpty = document.querySelector('[data-landing-tutor-empty]');
    const tutorModal = document.querySelector('[data-tutor-modal]');
    const tutorModalPanel = tutorModal?.querySelector('.tutor-modal-panel');
    const landingHeader = document.querySelector('.landing-header');
    const navLinks = [...(navigation?.querySelectorAll('a[href*="#"]') ?? [])];
    const navTargets = navLinks.map((link) => ({
        link,
        target: document.getElementById(new URL(link.href, window.location.href).hash.slice(1)),
    })).filter(({ target }) => target);
    let lastTutorTrigger = null;
    let menuCloseTimer;
    let scrollFramePending = false;

    const notify = (message) => {
        const toast = document.createElement('div');
        toast.className = 'toast';
        toast.innerHTML = '<svg class="icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"></circle><path d="M12 11v5m0-8h.01"></path></svg>';
        const copy = document.createElement('span');
        copy.textContent = message;
        const close = document.createElement('button');
        close.type = 'button';
        close.setAttribute('aria-label', 'Tutup notifikasi');
        close.textContent = '×';
        toast.append(copy, close);
        toastRegion.append(toast);
        const dismiss = () => {
            if (!toast.isConnected) return;
            toast.classList.add('is-leaving');
            window.setTimeout(() => toast.remove(), 210);
        };
        close.addEventListener('click', dismiss);
        window.setTimeout(dismiss, 4200);
    };

    const setNavigation = (open, { restoreFocus = false } = {}) => {
        window.clearTimeout(menuCloseTimer);
        if (open) {
            if (menuScrim) {
                menuScrim.hidden = false;
                requestAnimationFrame(() => menuScrim.classList.add('is-visible'));
            }
            navigation?.classList.add('is-open');
            requestAnimationFrame(() => navigation?.querySelector('a')?.focus());
        } else {
            navigation?.classList.remove('is-open');
            menuScrim?.classList.remove('is-visible');
            menuCloseTimer = window.setTimeout(() => {
                if (!navigation?.classList.contains('is-open') && menuScrim) menuScrim.hidden = true;
            }, 220);
        }
        menuToggle?.setAttribute('aria-expanded', String(open));
        if (!open && restoreFocus && window.matchMedia('(max-width: 700px)').matches) menuToggle?.focus();
    };

    menuToggle?.addEventListener('click', () => {
        const open = !navigation.classList.contains('is-open');
        setNavigation(open);
    });

    menuScrim?.addEventListener('click', () => setNavigation(false, { restoreFocus: true }));
    navigation?.querySelectorAll('a').forEach((link) => link.addEventListener('click', () => setNavigation(false)));
    document.addEventListener('click', (event) => {
        if (!event.target.closest('.landing-header')) setNavigation(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setNavigation(false, { restoreFocus: navigation?.classList.contains('is-open') });
            closeTutorModal();
        }
    });

    const updateLandingHeader = () => {
        const scrollPosition = window.scrollY + Math.max(
            (landingHeader?.offsetHeight ?? 0) + 24,
            Math.round(window.innerHeight * 0.5),
        );
        landingHeader?.classList.toggle('is-scrolled', window.scrollY > 10);

        let activeTarget = navTargets[0]?.target;
        let activePosition = Number.NEGATIVE_INFINITY;
        navTargets.forEach(({ target }) => {
            const targetPosition = target.getBoundingClientRect().top + window.scrollY;
            if (targetPosition <= scrollPosition && targetPosition > activePosition) {
                activeTarget = target;
                activePosition = targetPosition;
            }
        });

        const pageIsAtBottom = window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 2;
        if (pageIsAtBottom && navTargets.length) {
            activeTarget = navTargets[navTargets.length - 1].target;
        }

        navTargets.forEach(({ link, target }) => {
            const active = target === activeTarget;
            link.classList.toggle('is-active', active);
            if (active) link.setAttribute('aria-current', 'location');
            else link.removeAttribute('aria-current');
        });
    };

    const scheduleLandingHeaderUpdate = () => {
        if (scrollFramePending) return;
        scrollFramePending = true;
        requestAnimationFrame(() => {
            updateLandingHeader();
            scrollFramePending = false;
        });
    };

    window.addEventListener('scroll', scheduleLandingHeaderUpdate, { passive: true });
    window.addEventListener('resize', scheduleLandingHeaderUpdate);
    updateLandingHeader();

    document.querySelectorAll('[data-feature-info]').forEach((button) => button.addEventListener('click', () => {
        const [title, description] = button.dataset.featureInfo.split('|');
        notify(`${title}: ${description}`);
    }));

    const openTutorModal = (trigger) => {
        if (!tutorModal || !tutorModalPanel) return;
        lastTutorTrigger = trigger;
        const data = trigger.dataset;
        tutorModal.querySelector('[data-modal-name]').textContent = data.name;
        tutorModal.querySelector('[data-modal-university]').textContent = data.university;
        tutorModal.querySelector('[data-modal-major]').textContent = data.major;
        tutorModal.querySelector('[data-modal-subject]').textContent = data.subject;
        tutorModal.querySelector('[data-modal-description]').textContent = data.description;
        tutorModal.querySelector('[data-modal-rating]').textContent = data.rating;
        tutorModal.querySelector('[data-modal-availability]').textContent = data.availability;
        tutorModal.querySelector('[data-modal-initials]').textContent = data.name.split(/\s+/).map((part) => part[0]).slice(0, 2).join('');
        tutorModal.hidden = false;
        document.body.classList.add('modal-open');
        requestAnimationFrame(() => {
            tutorModal.classList.add('is-open');
            tutorModal.querySelector('[data-modal-close]')?.focus();
        });
    };

    const closeTutorModal = () => {
        if (!tutorModal || tutorModal.hidden) return;
        tutorModal.classList.remove('is-open');
        document.body.classList.remove('modal-open');
        window.setTimeout(() => {
            if (!tutorModal.classList.contains('is-open')) {
                tutorModal.hidden = true;
                lastTutorTrigger?.focus();
            }
        }, 180);
    };

    document.querySelectorAll('[data-tutor-detail]').forEach((button) => button.addEventListener('click', () => openTutorModal(button)));
    tutorModal?.querySelectorAll('[data-modal-close]').forEach((button) => button.addEventListener('click', closeTutorModal));
    tutorModalPanel?.addEventListener('keydown', (event) => {
        if (event.key !== 'Tab') return;
        const focusable = [...tutorModalPanel.querySelectorAll('button:not([disabled]), a[href], input:not([disabled]), [tabindex="0"]')];
        if (!focusable.length) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });

    tutorSearch?.addEventListener('input', () => {
        const query = tutorSearch.value.trim().toLocaleLowerCase('id');
        let visible = 0;
        document.querySelectorAll('.landing-tutor-card').forEach((card) => {
            const searchable = `${card.dataset.searchable ?? ''} ${card.textContent}`.toLocaleLowerCase('id');
            const matches = !query || searchable.includes(query);
            card.hidden = !matches;
            if (matches) visible += 1;
        });
        if (tutorEmpty) tutorEmpty.hidden = visible > 0;
    });

    const revealItems = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window) {
        const revealObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -25px 0px' });
        revealItems.forEach((item) => revealObserver.observe(item));
    } else {
        revealItems.forEach((item) => item.classList.add('is-visible'));
    }

    document.querySelectorAll('[data-social-info]').forEach((button) => button.addEventListener('click', () => {
        notify('Terima kasih sudah mendukung TutorKita.');
    }));

    document.querySelector('[data-newsletter-form]')?.addEventListener('submit', (event) => {
        event.preventDefault();
        const form = event.currentTarget;
        const email = form.querySelector('input[type="email"]');
        if (!email.value.trim() || !email.validity.valid) {
            email.focus();
            notify('Masukkan alamat email yang valid terlebih dahulu.');
            return;
        }
        notify('Terima kasih atas minatmu pada kabar terbaru TutorKita.');
        form.reset();
    });

    window.matchMedia('(min-width: 701px)').addEventListener('change', (event) => {
        if (event.matches) setNavigation(false);
    });
}
