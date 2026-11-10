const dashboard = document.querySelector('.dashboard-body');

if (dashboard) {
    document.querySelectorAll('dialog').forEach((dialog) => {
        dialog.addEventListener('click', (event) => {
            if (event.target === dialog && !dialog.hasAttribute('aria-busy')) dialog.close();
        });
        dialog.addEventListener('cancel', (event) => {
            if (dialog.hasAttribute('aria-busy')) event.preventDefault();
        });
    });
    const sidebar = document.querySelector('#main-sidebar');
    const sidebarToggle = document.querySelector('[data-sidebar-toggle]');
    const scrim = document.querySelector('[data-sidebar-close].mobile-scrim');
    const notificationMenu = document.querySelector('[data-notification-menu]');
    const profileMenu = document.querySelector('[data-profile-menu]');
    const toastRegion = document.querySelector('[data-toast-region]');
    const searchInput = document.querySelector('[data-dashboard-search]');
    const spaEnabled = Boolean(document.querySelector('[data-spa-page]'));
    const subjectManagementPage = document.querySelector('[data-integrated-subjects]');
    const subjectSearch = subjectManagementPage?.querySelector('[data-subject-search]');
    const subjectConfirmDialog = document.querySelector('[data-subject-confirm-dialog]');
    const subjectEditDialog = document.querySelector('[data-subject-edit-dialog]');
    let pendingSubjectRemoveForm = null;
    const subjectFilter = document.querySelector('#tutor-subject-filter');
    const levelFilter = document.querySelector('#subject-level-filter');
    const pageTitles = { dashboard: 'Beranda', tutors: 'Katalog Tutor', sessions: 'Sesi Belajar', materials: 'Materi Pembelajaran', assignments: 'Tugas & Penilaian', notifications: 'Notifikasi', history: 'Riwayat Sesi' };
    let activeScheduleFilter = 'all';
    const catalogPage = document.querySelector('[data-spa-page="tutors"]');
    const catalogSearch = document.querySelector('[data-catalog-search]');
    const catalogForm = document.querySelector('[data-catalog-search-form]');
    const catalogGrid = document.querySelector('[data-catalog-grid]');
    const catalogCount = document.querySelector('[data-catalog-total]');
    const catalogPagination = document.querySelector('[data-catalog-pagination]');
    const catalogSort = document.querySelector('[data-catalog-sort]');
    const catalogDialog = document.querySelector('[data-catalog-profile-dialog]');
    let catalogPageNumber = 1;
    const catalogPageSize = 8;

    const showPage = (page, updateHash = true) => {
        if (!pageTitles[page]) page = 'dashboard';
        document.querySelectorAll('[data-spa-page]').forEach((section) => { section.hidden = section.dataset.spaPage !== page; });
        document.querySelectorAll('[data-page-link]').forEach((link) => {
            const active = link.dataset.pageLink === page;
            link.classList.toggle('is-current', active);
            if (active) link.setAttribute('aria-current', 'page'); else link.removeAttribute('aria-current');
        });
        const heading = document.querySelector('[data-page-heading]');
        if (heading) heading.textContent = pageTitles[page];
        document.title = `${pageTitles[page]} · TutorKita`;
        document.body.classList.toggle('catalog-active', page === 'tutors');
        document.body.classList.toggle('sessions-active', page === 'sessions');
        document.body.classList.toggle('materials-active', page === 'materials');
        document.body.classList.toggle('assignments-active', page === 'assignments');
        document.body.classList.toggle('notifications-active', page === 'notifications');
        document.body.classList.toggle('history-active', page === 'history');
        if (searchInput) {
            searchInput.placeholder = page === 'tutors' ? 'Cari tutor, mata pelajaran, atau topik......' : page === 'materials' ? 'Cari materi, mata pelajaran, atau topik...' : page === 'assignments' ? 'Cari tugas atau mata pelajaran...' : page === 'notifications' ? 'Cari notifikasi...' : page === 'history' ? 'Cari sesi, tutor, atau mata pelajaran...' : 'Cari jadwal atau tutor...';
            document.querySelector('[data-search-label]')?.replaceChildren(document.createTextNode(searchInput.placeholder));
        }
        if (page === 'tutors' && searchInput && catalogSearch) searchInput.value = catalogSearch.value;
        if (page === 'materials' && searchInput) searchInput.value = document.querySelector('[data-material-search]')?.value ?? '';
        if (page === 'assignments' && searchInput) searchInput.value = document.querySelector('[data-assignment-search]')?.value ?? '';
        if (page === 'history' && searchInput) searchInput.value = document.querySelector('[data-history-search]')?.value ?? '';
        if (updateHash && location.hash !== `#${page}`) history.pushState({ page }, '', `#${page}`);
        setSidebar(false);
        closeMenus();
        document.querySelector('#main-content')?.scrollTo({ top: 0, behavior: 'instant' });
        window.scrollTo({ top: 0, behavior: 'instant' });
    };

    if (spaEnabled) {
        document.querySelectorAll('[data-page-link]').forEach((link) => link.addEventListener('click', () => showPage(link.dataset.pageLink)));
        window.addEventListener('popstate', () => showPage(location.hash.slice(1) || 'dashboard', false));
    }

    document.querySelectorAll('[data-local-filter]').forEach((input) => input.addEventListener('input', () => {
        const group = input.dataset.localFilter;
        const query = input.value.trim().toLocaleLowerCase('id');
        document.querySelectorAll(`[data-filter-item="${group}"]`).forEach((card) => { card.hidden = query && !card.dataset.searchable.includes(query); });
    }));
    document.querySelectorAll('[data-local-tab]').forEach((tab) => tab.addEventListener('click', () => {
        document.querySelectorAll(`[data-local-tab="${tab.dataset.localTab}"]`).forEach((item) => item.classList.toggle('is-active', item === tab));
    }));
    document.querySelector('[data-mark-all-read]')?.addEventListener('click', () => {
        document.querySelectorAll('.notification-spa-card').forEach((item) => { item.classList.remove('is-unread'); item.querySelector('.unread-dot')?.remove(); });
        document.querySelector('.sidebar-unread-dot')?.remove();
        showToast('Notifikasi ditandai sudah dibaca.');
    });

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

    if (spaEnabled) showPage(location.hash.slice(1) || 'dashboard', false);

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

    const sessionToast = (() => {
        try {
            const message = sessionStorage.getItem('tutorkita-subject-toast');
            sessionStorage.removeItem('tutorkita-subject-toast');
            return message;
        } catch {
            return null;
        }
    })();
    const pageToast = document.querySelector('[data-toast-on-load]')?.textContent.trim();
    if (sessionToast || pageToast) showToast(sessionToast || pageToast);

    const profileDialog = document.querySelector('[data-profile-dialog]');
    const tutorApplicationDialog = document.querySelector('[data-tutor-application-dialog]');
    const adminVerificationDialog = document.querySelector('[data-admin-verification-dialog]');
    const adminUsersDialog = document.querySelector('[data-admin-users-dialog]');
    const openRoleDialog = (dialog) => {
        closeMenus();
        if (dialog && !dialog.open) dialog.showModal();
    };
    document.querySelector('[data-open-profile]')?.addEventListener('click', () => openRoleDialog(profileDialog));
    document.querySelector('[data-open-tutor-application]')?.addEventListener('click', () => openRoleDialog(tutorApplicationDialog));
    document.querySelector('[data-open-admin-verification]')?.addEventListener('click', () => openRoleDialog(adminVerificationDialog));
    document.querySelector('[data-open-admin-users]')?.addEventListener('click', () => openRoleDialog(adminUsersDialog));
    document.querySelectorAll('[data-role-dialog-close]').forEach((button) => button.addEventListener('click', () => button.closest('dialog')?.close()));

    const applicationForm = document.querySelector('[data-tutor-application-form]');
    if (applicationForm) {
        const nameField = applicationForm.elements.applicant_name;
        const educationField = applicationForm.elements.education_level;
        const institutionField = applicationForm.elements.institution;
        const subjectFields = [...applicationForm.querySelectorAll('[name="subject_ids[]"]')];
        const updatePreview = () => {
            const name = applicationForm.querySelector('[data-application-preview-name]');
            const education = applicationForm.querySelector('[data-application-preview-education]');
            const subjects = applicationForm.querySelector('[data-application-preview-subject]');
            if (name) name.textContent = nameField.value || 'Nama calon tutor';
            if (education) education.textContent = [educationField.value, institutionField.value].filter(Boolean).join(' · ') || 'Pendidikan dan institusi';
            if (subjects) {
                const selected = subjectFields.filter((field) => field.checked).map((field) => field.parentElement.textContent.trim());
                subjects.textContent = selected.length ? selected.join(', ') : 'Pilih mata pelajaran di atas';
            }
        };
        applicationForm.addEventListener('input', updatePreview);
        applicationForm.addEventListener('change', updatePreview);
        applicationForm.querySelector('[name="avatar"]')?.addEventListener('change', (event) => {
            const file = event.currentTarget.files?.[0];
            if (file && !['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                event.currentTarget.value = '';
                showToast('Pilih foto JPG, PNG, atau WebP.');
                return;
            }
            if (file) {
                const preview = applicationForm.querySelector('[data-application-photo-preview]');
                const reader = new FileReader();
                reader.addEventListener('load', () => { if (preview && typeof reader.result === 'string') { preview.src = reader.result; preview.hidden = false; } });
                reader.readAsDataURL(file);
            }
        });
        applicationForm.addEventListener('submit', () => {
            const button = applicationForm.querySelector('[data-application-submit]');
            if (button) { button.disabled = true; button.textContent = 'Mengirim pengajuan…'; }
            applicationForm.setAttribute('aria-busy', 'true');
        });
        updatePreview();
        if (applicationForm.querySelector('[data-role-form-error]')?.textContent.trim()) openRoleDialog(tutorApplicationDialog);
    }
    document.querySelectorAll('.role-form').forEach((form) => form.addEventListener('submit', () => {
        const submit = form.querySelector('button[type="submit"]');
        if (submit) { submit.disabled = true; submit.textContent = 'Menyimpan…'; }
    }));
    document.querySelectorAll('.tutor-review-actions form').forEach((form) => form.addEventListener('submit', () => {
        const button = form.querySelector('button[type="submit"]');
        if (button) { button.disabled = true; button.textContent = 'Memproses…'; }
    }));

    if (subjectManagementPage) {
        const materialsArea = document.querySelector('[data-materials-panel]');
        const materialsViewTabs = [...document.querySelectorAll('[data-material-area]')];
        const subjectCards = [...subjectManagementPage.querySelectorAll('[data-subject-card]')];
        const subjectTabs = [...subjectManagementPage.querySelectorAll('[data-subject-tab]')];
        const subjectLevel = subjectManagementPage.querySelector('[data-subject-level-filter]');
        const subjectCategory = subjectManagementPage.querySelector('[data-subject-category-filter]');
        const subjectEmpty = subjectManagementPage.querySelector('[data-subject-empty]');
        const subjectNoSelection = subjectManagementPage.querySelector('[data-subject-no-selection]');
        const subjectResultCount = subjectManagementPage.querySelector('[data-subject-result-count]');
        let subjectDatabaseIds = new Set(subjectCards.map((card) => card.dataset.subjectId));
        let subjectSearchRequest = 0;
        let subjectScope = 'all';
        let pendingSubjectRemoveForm = null;

        const drawSubjectCatalog = async () => {
            const query = subjectSearch?.value.trim().toLocaleLowerCase('id') ?? '';
            const level = subjectLevel?.value ?? 'all';
            const categoryValue = subjectCategory?.value ?? 'all';
            const category = categoryValue.toLocaleLowerCase('id');
            const requestId = ++subjectSearchRequest;
            const params = new URLSearchParams({ search: subjectSearch?.value.trim() ?? '', scope: subjectScope });
            if (level !== 'all') params.set('level', level);
            if (categoryValue !== 'all') params.set('category', categoryValue);
            try {
                const response = await fetch(`${subjectManagementPage.dataset.searchUrl}?${params}`, { headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                if (!response.ok) throw new Error('Pencarian belum dapat dimuat.');
                const result = await response.json();
                if (requestId !== subjectSearchRequest) return;
                subjectDatabaseIds = new Set(result.ids.map(String));
            } catch {
                if (requestId !== subjectSearchRequest) return;
                subjectDatabaseIds = new Set(subjectCards.map((card) => card.dataset.subjectId));
            }
            let visible = 0;
            subjectCards.forEach((card) => {
                const matchesScope = subjectScope === 'all' || card.dataset.selected === '1';
                const matchesSearch = !query || card.dataset.search.includes(query);
                const matchesLevel = level === 'all' || card.dataset.levels.split('|').includes(level.toLocaleLowerCase('id'));
                const matchesCategory = category === 'all' || card.dataset.category === category;
                const matches = subjectDatabaseIds.has(card.dataset.subjectId) && matchesScope && matchesSearch && matchesLevel && matchesCategory;
                card.hidden = !matches;
                if (matches) visible += 1;
            });
            const hasNoSelections = subjectCards.every((card) => card.dataset.selected !== '1');
            if (subjectEmpty) subjectEmpty.hidden = visible > 0 || (subjectScope === 'mine' && hasNoSelections);
            if (subjectNoSelection) subjectNoSelection.hidden = !(subjectScope === 'mine' && hasNoSelections);
            if (subjectResultCount) subjectResultCount.textContent = `${visible} pelajaran ${subjectScope === 'mine' ? 'di daftar belajarmu' : 'tersedia'}`;
        };
        const changeMaterialsArea = (area, updateUrl = true) => {
            const subjectsOpen = area === 'subjects';
            subjectManagementPage.hidden = !subjectsOpen;
            materialsArea.hidden = subjectsOpen;
            materialsViewTabs.forEach((tab) => {
                const active = tab.dataset.materialArea === area;
                tab.classList.toggle('is-active', active);
                tab.setAttribute('aria-pressed', String(active));
            });
            if (subjectsOpen && searchInput && subjectSearch) {
                searchInput.placeholder = 'Cari mata pelajaran...';
                searchInput.value = subjectSearch.value;
            }
            if (!subjectsOpen && searchInput) {
                searchInput.placeholder = 'Cari materi, mata pelajaran, atau topik...';
                searchInput.value = document.querySelector('[data-material-search]')?.value ?? '';
            }
            if (updateUrl && spaEnabled && location.hash !== '#materials') history.pushState({ page: 'materials' }, '', '#materials');
        };

        materialsViewTabs.forEach((tab) => tab.addEventListener('click', () => changeMaterialsArea(tab.dataset.materialArea)));
        subjectTabs.forEach((tab) => tab.addEventListener('click', () => {
            subjectScope = tab.dataset.subjectTab;
            subjectTabs.forEach((item) => {
                const active = item === tab;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', String(active));
            });
            subjectManagementPage.classList.add('is-switching');
            window.setTimeout(() => subjectManagementPage.classList.remove('is-switching'), 220);
            drawSubjectCatalog();
        }));
        subjectSearch?.addEventListener('input', drawSubjectCatalog);
        subjectLevel?.addEventListener('change', drawSubjectCatalog);
        subjectCategory?.addEventListener('change', drawSubjectCatalog);
        if (searchInput && subjectSearch) {
            searchInput.addEventListener('input', () => {
                if (subjectManagementPage.hidden) return;
                subjectSearch.value = searchInput.value;
                drawSubjectCatalog();
            });
            subjectSearch.addEventListener('input', () => {
                if (!subjectManagementPage.hidden && searchInput) searchInput.value = subjectSearch.value;
            });
        }
        subjectManagementPage.addEventListener('click', (event) => {
            const editTrigger = event.target.closest('[data-subject-edit-trigger]');
            if (editTrigger && subjectEditDialog) {
                const card = editTrigger.closest('[data-subject-card]');
                const form = subjectEditDialog.querySelector('[data-subject-edit-form]');
                const subjectSelect = form.querySelector('[data-subject-edit-subject]');
                const levelSelect = form.querySelector('[data-subject-edit-level]');
                form.action = card.dataset.updateUrl;
                subjectSelect.value = card.dataset.subjectId;
                const fillLevels = (selectedId = '') => {
                    const option = subjectSelect.selectedOptions[0];
                    const levels = (option?.dataset.levelMap || '').split('|').filter(Boolean).map((item) => item.split(':'));
                    levelSelect.replaceChildren(...levels.map(([id, name]) => new Option(name, id, false, String(id) === String(selectedId))));
                    if (!selectedId && levels.length) levelSelect.value = levels[0][0];
                };
                fillLevels(card.dataset.selectedLevel);
                subjectSelect.onchange = () => fillLevels();
                subjectEditDialog.showModal();
                return;
            }
            const trigger = event.target.closest('[data-subject-remove-trigger]');
            if (!trigger || !subjectConfirmDialog) return;
            event.preventDefault();
            pendingSubjectRemoveForm = trigger.form;
            subjectConfirmDialog.querySelector('[data-subject-confirm-name]').textContent = trigger.dataset.subjectName || 'mata pelajaran ini';
            subjectConfirmDialog.showModal();
        });
        subjectManagementPage.querySelectorAll('[data-subject-add-form]').forEach((form) => form.addEventListener('submit', async (event) => {
            event.preventDefault();
            const button = form.querySelector('button');
            button.disabled = true;
            try {
                const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]')?.value ?? '' } });
                const result = await response.json();
                if (!response.ok) throw new Error(result.message || 'Mata pelajaran belum dapat ditambahkan.');
                form.closest('[data-subject-card]')?.classList.add('is-adding');
                try { sessionStorage.setItem('tutorkita-subject-toast', result.message); } catch { /* Continue without persisted feedback. */ }
                window.setTimeout(() => { window.location.href = '/dashboard?materials=subjects#materials'; }, 200);
            } catch (error) {
                showToast(error instanceof Error ? error.message : 'Mata pelajaran belum dapat ditambahkan.');
                button.disabled = false;
            }
        }));
        subjectEditDialog?.querySelector('[data-subject-edit-cancel]')?.addEventListener('click', () => subjectEditDialog.close());
        subjectEditDialog?.querySelector('[data-subject-edit-form]')?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const form = event.currentTarget;
            const button = form.querySelector('[type="submit"]');
            button.disabled = true;
            try {
                const response = await fetch(form.action, { method: 'PUT', body: new URLSearchParams(new FormData(form)), headers: { Accept: 'application/json', 'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' } });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Perubahan belum dapat disimpan.');
                try { sessionStorage.setItem('tutorkita-subject-toast', result.message); } catch { /* Continue without persisted feedback. */ }
                window.location.href = '/dashboard?materials=subjects#materials';
            } catch (error) {
                showToast(error instanceof Error ? error.message : 'Perubahan belum dapat disimpan.');
                button.disabled = false;
            }
        });
        let subjectDeleteInFlight = false;
        subjectConfirmDialog?.querySelector('[data-subject-confirm-accept]')?.addEventListener('click', async () => {
            if (!pendingSubjectRemoveForm || subjectDeleteInFlight) return;
            const form = pendingSubjectRemoveForm;
            const card = form.closest('[data-subject-card]');
            const button = subjectConfirmDialog.querySelector('[data-subject-confirm-accept]');
            subjectDeleteInFlight = true;
            button.disabled = true;
            button.setAttribute('aria-busy', 'true');
            button.dataset.originalText ||= button.textContent.trim();
            button.textContent = 'Menghapus…';
            try {
                const response = await fetch(form.action, { method: 'DELETE', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': form.querySelector('[name="_token"]')?.value ?? '' } });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat()[0] || 'Mata pelajaran belum dapat dihapus. Coba lagi.');
                subjectConfirmDialog.close();
                if (card) {
                    card.dataset.selected = '0';
                    card.classList.add('is-removing');
                }
                const selectedCount = subjectCards.filter((item) => item.dataset.selected === '1').length;
                subjectManagementPage.querySelectorAll('[data-subject-selected-count], [data-subject-tab-count]').forEach((count) => { count.textContent = String(selectedCount); });
                try { sessionStorage.setItem('tutorkita-subject-toast', 'Mata pelajaran berhasil dihapus dari daftar belajarmu.'); } catch { /* Continue without persisted feedback. */ }
                window.setTimeout(() => { window.location.href = '/dashboard?materials=subjects#materials'; }, 180);
            } catch (error) {
                showToast(error instanceof Error ? error.message : 'Mata pelajaran belum dapat dihapus. Coba lagi.');
            } finally {
                button.disabled = false;
                button.removeAttribute('aria-busy');
                button.textContent = button.dataset.originalText || 'Hapus dari Daftar';
                subjectDeleteInFlight = false;
                pendingSubjectRemoveForm = null;
            }
        });
        subjectConfirmDialog?.addEventListener('close', () => { pendingSubjectRemoveForm = null; });
        changeMaterialsArea(new URLSearchParams(location.search).get('materials') === 'subjects' ? 'subjects' : 'materials', false);
        drawSubjectCatalog();
    }

    const dismissToast = (toast) => {
        if (!toast.isConnected) return;
        toast.classList.add('is-leaving');
        window.setTimeout(() => toast.remove(), 210);
    };

    const notificationSection = document.querySelector('[data-spa-page="notifications"]');
    if (notificationSection) {
        const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const notificationCards = [...notificationSection.querySelectorAll('[data-notification-card]')];
        const filterButtons = [...notificationSection.querySelectorAll('[data-notification-filter]')];
        const unreadCount = document.querySelector('[data-unread-count]');
        const headerUnread = document.querySelector('[data-header-unread]');
        let activeNotificationCategory = 'all';
        const notificationSearch = () => searchInput?.value.trim().toLocaleLowerCase('id') ?? '';
        const refreshUnread = (count) => {
            if (unreadCount) unreadCount.textContent = String(count);
            if (headerUnread) headerUnread.textContent = `${count} belum dibaca`;
            document.querySelector('[data-notification-toggle]')?.setAttribute('aria-label', `Notifikasi, ${count} belum dibaca`);
            document.querySelector('.notification-dot')?.remove();
            document.querySelector('[data-sidebar-unread-dot]')?.remove();
            if (count > 0) {
                const bell = document.querySelector('[data-notification-toggle]');
                if (bell && !bell.querySelector('.notification-dot')) bell.insertAdjacentHTML('beforeend', '<span class="notification-dot"></span>');
                const nav = document.querySelector('[data-page-link="notifications"]');
                if (nav && !nav.querySelector('.sidebar-unread-dot')) nav.insertAdjacentHTML('beforeend', '<i class="sidebar-unread-dot" data-sidebar-unread-dot></i>');
            }
        };
        const postNotification = async (url, body = {}) => {
            const response = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify(body) });
            const data = await response.json().catch(() => ({}));
            if (!response.ok) throw new Error(data.message || 'Perubahan notifikasi tidak berhasil disimpan.');
            return data;
        };
        const selectNotificationCategory = (category) => {
            activeNotificationCategory = category;
            filterButtons.forEach((button) => {
                const active = button.dataset.notificationFilter === category;
                button.classList.toggle('is-active', active);
                if (button.getAttribute('role') === 'tab') button.setAttribute('aria-selected', String(active));
            });
            let visible = 0;
            const query = notificationSearch();
            notificationCards.forEach((card) => {
                card.hidden = (category !== 'all' && card.dataset.category !== category) || (query && !card.innerText.toLocaleLowerCase('id').includes(query));
                if (!card.hidden) visible += 1;
            });
            notificationSection.querySelectorAll('[data-notification-group]').forEach((group) => { group.hidden = !group.querySelector('.notification-center-card:not([hidden])'); });
            const empty = notificationSection.querySelector('[data-notification-filter-empty]');
            if (empty) empty.hidden = visible > 0;
        };
        filterButtons.forEach((button) => button.addEventListener('click', () => selectNotificationCategory(button.dataset.notificationFilter)));
        searchInput?.addEventListener('input', () => selectNotificationCategory(activeNotificationCategory));

        notificationSection.querySelectorAll('[data-notification-read]').forEach((button) => button.addEventListener('click', async () => {
            const card = button.closest('[data-notification-card]');
            if (!card) return;
            try {
                const data = await postNotification(`/dashboard/notifications/${card.dataset.notificationId}/read`);
                card.classList.remove('is-unread');
                card.querySelector('.notification-unread-dot')?.remove();
                button.remove();
                refreshUnread(data.unread_count);
                showToast('Notifikasi ditandai sudah dibaca.');
            } catch (error) { showToast(error.message); }
        }));
        document.querySelectorAll('[data-mark-all-read]').forEach((button) => button.addEventListener('click', async () => {
            try {
                const data = await postNotification('/dashboard/notifications/read-all');
                notificationCards.forEach((card) => { card.classList.remove('is-unread'); card.querySelector('.notification-unread-dot')?.remove(); card.querySelector('[data-notification-read]')?.remove(); });
                document.querySelectorAll('.notification-item.is-unread').forEach((item) => item.classList.remove('is-unread'));
                refreshUnread(data.unread_count);
                showToast(data.message);
            } catch (error) { showToast(error.message); }
        }));
        document.querySelectorAll('[data-notification-open]').forEach((link) => link.addEventListener('click', async (event) => {
            const id = link.dataset.notificationId;
            const url = link.getAttribute('href') || '#notifications';
            if (!id) return;
            event.preventDefault();
            try {
                const card = link.closest('[data-notification-card]');
                if (card?.classList.contains('is-unread')) {
                    const data = await postNotification(`/dashboard/notifications/${id}/read`);
                    card.classList.remove('is-unread');
                    card.querySelector('.notification-unread-dot')?.remove();
                    card.querySelector('[data-notification-read]')?.remove();
                    document.querySelector(`.notification-item[data-notification-id="${id}"]`)?.classList.remove('is-unread');
                    refreshUnread(data.unread_count);
                }
                const page = url.startsWith('#') ? url.slice(1) : 'notifications';
                if (pageTitles[page]) showPage(page); else showPage('notifications');
            } catch (error) { showToast(error.message); }
        }));
        document.querySelector('[data-open-notifications]')?.addEventListener('click', () => showPage('notifications'));

        const preference = notificationSection.querySelector('[data-notification-preference]');
        preference?.addEventListener('change', async () => {
            const enabled = preference.checked;
            preference.disabled = true;
            try {
                const data = await postNotification('/dashboard/notifications/preferences', { enabled });
                showToast(data.message);
            } catch (error) { preference.checked = !enabled; showToast(error.message); }
            finally { preference.disabled = false; }
        });
    }

    const materialGrid = document.querySelector('[data-material-grid]');
    if (materialGrid) {
        const materialCards = [...materialGrid.querySelectorAll('[data-material-card]')];
        const materialSearch = document.querySelector('[data-material-search]');
        const materialGrade = document.querySelector('[data-material-grade]');
        const materialSort = document.querySelector('[data-material-sort]');
        const materialPager = document.querySelector('[data-material-pagination]');
        const materialDialog = document.querySelector('[data-material-dialog]');
        const materialDetail = document.querySelector('[data-material-detail]');
        const materialCsrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        const materialFormDialog = document.querySelector('[data-material-form-dialog]');
        const materialForm = materialFormDialog?.querySelector('[data-material-form]');
        const materialFormType = materialForm?.querySelector('[data-material-form-type]');
        const materialFileLabel = materialForm?.querySelector('[data-material-file-label]');
        const materialVideoLabel = materialForm?.querySelector('[data-material-video-label]');
        const materialFile = materialForm?.querySelector('[data-material-file]');
        const materialVideoInput = materialForm?.querySelector('[name="video_url"]');
        const materialDeleteDialog = document.querySelector('[data-material-delete-dialog]');
        let pendingMaterialDelete = null;
        const materialPageSize = 8;
        const initialMaterialParams = new URLSearchParams(location.search);
        let materialSubject = initialMaterialParams.get('subject') || 'all';
        if (materialGrade && initialMaterialParams.has('grade')) materialGrade.value = initialMaterialParams.get('grade');
        let materialPage = 1;
        let currentMaterialId = null;
        const syncMaterialAssetInputs = () => {
            const type = materialFormType?.value;
            const video = type === 'video';
            if (materialFileLabel) materialFileLabel.hidden = video;
            if (materialVideoLabel) materialVideoLabel.hidden = !video;
            if (materialFile) materialFile.required = !video && ['module_pdf', 'material_pdf'].includes(type) && (materialForm?.dataset.editing !== 'true' || materialForm?.dataset.hasFile !== 'true');
            if (materialVideoInput) materialVideoInput.required = video;
        };
        const resetMaterialForm = () => {
            if (!materialForm) return;
            materialForm.reset();
            materialForm.dataset.editing = 'false';
            materialForm.dataset.hasFile = 'false';
            materialForm.action = '/dashboard/materials';
            const method = materialForm.querySelector('[data-material-method-field]');
            if (method) method.disabled = true;
            materialFormDialog.querySelector('[data-material-form-title]').textContent = 'Tambah Materi';
            materialFormDialog.querySelector('[data-material-form-submit]').textContent = 'Simpan Materi';
            const error = materialForm.querySelector('[data-material-form-error]');
            if (error) { error.hidden = true; error.textContent = ''; }
            const help = materialForm.querySelector('[data-material-file-help]');
            if (help) help.textContent = 'PDF maksimal 20 MB.';
            syncMaterialAssetInputs();
        };
        const openMaterialForm = (card = null) => {
            if (!materialForm || !materialFormDialog) return;
            resetMaterialForm();
            if (card) {
                materialForm.dataset.editing = 'true';
                materialForm.dataset.hasFile = card.dataset.hasFile === '1' ? 'true' : 'false';
                materialForm.action = card.dataset.updateUrl;
                materialForm.querySelector('[data-material-method-field]').disabled = false;
                materialForm.querySelector('[data-material-method-field]').value = 'PUT';
                materialFormDialog.querySelector('[data-material-form-title]').textContent = 'Edit Materi';
                materialFormDialog.querySelector('[data-material-form-submit]').textContent = 'Simpan Perubahan';
                materialForm.elements.title.value = card.dataset.title || '';
                materialForm.elements.subject.value = card.dataset.subject || '';
                materialForm.elements.grade_level.value = card.dataset.grade || 'SD';
                materialForm.elements.topic.value = card.dataset.topic || '';
                materialForm.elements.description.value = card.dataset.description || '';
                materialForm.elements.material_type.value = card.dataset.type || 'summary';
                materialForm.elements.video_url.value = card.dataset.videoUrl || '';
                materialForm.elements.is_published.value = card.dataset.published || '1';
                if (materialFile && card.dataset.hasFile === '1' && ['module_pdf', 'material_pdf'].includes(card.dataset.type)) {
                    materialFile.required = false;
                    materialForm.querySelector('[data-material-file-help]').textContent = 'PDF saat ini tetap digunakan jika tidak memilih file pengganti.';
                }
            }
            syncMaterialAssetInputs();
            materialFormDialog.showModal();
        };
        document.querySelector('[data-material-create-open]')?.addEventListener('click', () => openMaterialForm());
        materialFormDialog?.querySelectorAll('[data-material-form-close]').forEach((button) => button.addEventListener('click', () => materialFormDialog.close()));
        materialFormType?.addEventListener('change', syncMaterialAssetInputs);
        materialForm?.addEventListener('submit', async (event) => {
            event.preventDefault();
            const submit = materialForm.querySelector('[data-material-form-submit]');
            const error = materialForm.querySelector('[data-material-form-error]');
            submit.disabled = true;
            submit.textContent = 'Menyimpan…';
            materialFormDialog.setAttribute('aria-busy', 'true');
            error.hidden = true;
            try {
                const response = await fetch(materialForm.action, { method: 'POST', body: new FormData(materialForm), headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': materialForm.querySelector('[name="_token"]')?.value ?? materialCsrf } });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(result.message || Object.values(result.errors || {}).flat().join(' ') || 'Materi belum dapat disimpan.');
                materialFormDialog.close();
                try { sessionStorage.setItem('tutorkita-subject-toast', result.message); } catch { /* Continue without persisted feedback. */ }
                window.location.href = '/dashboard#materials';
            } catch (failure) {
                error.textContent = failure instanceof Error ? failure.message : 'Materi belum dapat disimpan. Periksa kembali isianmu.';
                error.hidden = false;
            } finally {
                materialFormDialog.removeAttribute('aria-busy');
                submit.disabled = false;
                submit.textContent = materialForm.dataset.editing === 'true' ? 'Simpan Perubahan' : 'Simpan Materi';
            }
        });
        const beginMaterialDelete = (card) => {
            if (!card || !materialDeleteDialog) return;
            pendingMaterialDelete = card;
            materialDeleteDialog.showModal();
        };
        materialDeleteDialog?.querySelector('[data-material-delete-accept]')?.addEventListener('click', async () => {
            if (!pendingMaterialDelete) return;
            const card = pendingMaterialDelete;
            const button = materialDeleteDialog.querySelector('[data-material-delete-accept]');
            button.disabled = true;
            button.textContent = 'Mengarsipkan…';
            materialDeleteDialog.setAttribute('aria-busy', 'true');
            try {
                const response = await fetch(card.dataset.deleteUrl, { method: 'DELETE', headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': materialCsrf } });
                const result = await response.json().catch(() => ({}));
                if (!response.ok) throw new Error(result.message || 'Materi belum dapat diarsipkan.');
                materialDeleteDialog.close();
                try { sessionStorage.setItem('tutorkita-subject-toast', result.message); } catch { /* Continue without persisted feedback. */ }
                window.location.href = '/dashboard#materials';
            } catch (error) {
                showToast(error instanceof Error ? error.message : 'Materi belum dapat diarsipkan.');
            } finally {
                materialDeleteDialog.removeAttribute('aria-busy');
                button.disabled = false;
                button.textContent = 'Arsipkan Materi';
                pendingMaterialDelete = null;
            }
        });
        materialDeleteDialog?.addEventListener('close', () => { pendingMaterialDelete = null; });
        const filteredMaterials = () => {
            const query = materialSearch.value.trim().toLocaleLowerCase('id');
            const grade = materialGrade.value;
            const list = materialCards.filter((card) =>
                (materialSubject === 'all' || card.dataset.subject === materialSubject)
                && (grade === 'all' || card.dataset.grade === grade)
                && (!query || card.dataset.search.includes(query))
            );
            list.sort((a, b) => materialSort.value === 'popular'
                ? Number(b.dataset.views) - Number(a.dataset.views)
                : Number(b.dataset.date) - Number(a.dataset.date));
            return list;
        };
        const drawMaterials = () => {
            const list = filteredMaterials();
            const pages = Math.max(1, Math.ceil(list.length / materialPageSize));
            materialPage = Math.min(materialPage, pages);
            const start = (materialPage - 1) * materialPageSize;
            materialCards.forEach((card) => { card.hidden = true; });
            list.slice(start, start + materialPageSize).forEach((card, i) => {
                card.hidden = false;
                card.style.animationDelay = `${Math.min(i, 7) * 35}ms`;
            });
            const count = document.querySelector('[data-material-count]');
            if (count) count.textContent = String(list.length);
            const resultLabel = document.querySelector('[data-material-result-label]');
            if (resultLabel) resultLabel.textContent = list.length ? `${start + 1}–${Math.min(start + materialPageSize, list.length)} dari ${list.length}` : '';
            const empty = document.querySelector('[data-material-filter-empty]');
            if (empty) empty.hidden = list.length > 0;
            materialPager.replaceChildren();
            if (pages <= 1) return;
            const addPageButton = (label, page, active = false, disabled = false) => {
                const button = document.createElement('button');
                button.type = 'button'; button.textContent = label; button.disabled = disabled;
                button.classList.toggle('is-active', active);
                if (active) button.setAttribute('aria-current', 'page');
                button.addEventListener('click', () => { materialPage = page; drawMaterials(); });
                materialPager.append(button);
            };
            addPageButton('‹', Math.max(1, materialPage - 1), false, materialPage === 1);
            for (let page = 1; page <= pages; page += 1) addPageButton(String(page), page, page === materialPage);
            addPageButton('›', Math.min(pages, materialPage + 1), false, materialPage === pages);
        };
        let materialSearchTimer;
        materialSearch?.addEventListener('input', () => { window.clearTimeout(materialSearchTimer); materialSearchTimer = window.setTimeout(() => { materialPage = 1; drawMaterials(); }, 120); });
        materialGrade?.addEventListener('change', () => { materialPage = 1; drawMaterials(); });
        materialSort?.addEventListener('change', drawMaterials);
        document.querySelectorAll('[data-material-subject]').forEach((button) => button.addEventListener('click', () => {
            materialSubject = button.dataset.materialSubject;
            document.querySelectorAll('[data-material-subject]').forEach((tab) => tab.classList.toggle('is-active', tab === button));
            document.querySelector('[data-material-more-menu]')?.setAttribute('hidden', '');
            document.querySelector('[data-material-more]')?.setAttribute('aria-expanded', 'false');
            materialPage = 1; drawMaterials();
        }));
        document.querySelectorAll('[data-open-subject-material]').forEach((button) => button.addEventListener('click', (event) => {
            event.preventDefault();
            document.querySelector('[data-material-area="materials"]')?.click();
            materialSubject = button.dataset.openSubjectMaterial;
            const selectedGrade = button.dataset.openSubjectGrade;
            if (selectedGrade && materialGrade) materialGrade.value = selectedGrade;
            const matchingTab = [...document.querySelectorAll('[data-material-subject]')]
                .find((tab) => tab.dataset.materialSubject === materialSubject);
            document.querySelectorAll('[data-material-subject]').forEach((tab) => tab.classList.toggle('is-active', tab === matchingTab));
            document.querySelector('[data-material-more-menu]')?.setAttribute('hidden', '');
            document.querySelector('[data-material-more]')?.setAttribute('aria-expanded', 'false');
            materialPage = 1;
            drawMaterials();
            const params = new URLSearchParams({ subject: materialSubject });
            if (selectedGrade) params.set('grade', selectedGrade);
            history.replaceState({ page: 'materials' }, '', `${location.pathname}?${params.toString()}#materials`);
        }));
        document.querySelector('[data-material-more]')?.addEventListener('click', (event) => {
            const menu = document.querySelector('[data-material-more-menu]');
            const isOpen = menu.hidden;
            menu.hidden = !isOpen;
            event.currentTarget.setAttribute('aria-expanded', String(isOpen));
        });
        document.querySelector('[data-material-see-all]')?.addEventListener('click', () => {
            document.querySelector('[data-material-subject="all"]')?.click();
            materialSearch.value = ''; materialGrade.value = 'all'; materialSort.value = 'newest'; drawMaterials();
        });
        const openMaterial = async (id) => {
            currentMaterialId = id;
            materialDialog.showModal();
            materialDetail.innerHTML = '<p>Memuat detail materi…</p>';
            try {
                const response = await fetch(`/dashboard/materials/${id}`, { headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Detail materi tidak dapat dimuat.');
                const { material } = await response.json();
                const openedCard = document.querySelector(`[data-material-card][data-id="${id}"]`);
                if (openedCard) openedCard.dataset.views = String(material.view_count);
                materialDetail.replaceChildren();
                const subject = document.createElement('p'); subject.className = 'material-detail-meta'; subject.textContent = `${material.subject} · ${material.grade_level} · ${material.material_type} · Oleh ${material.tutor_name}`;
                const title = document.createElement('h2'); title.id = 'material-detail-title'; title.textContent = material.title;
                const description = document.createElement('p'); description.textContent = material.description;
                const topic = document.createElement('p'); topic.textContent = `Topik: ${material.topic || material.subject}`;
                const actions = document.createElement('div'); actions.className = 'material-detail-actions';
                if (material.file_url) { const file = document.createElement('a'); file.className = 'button button-primary'; file.href = material.file_url; file.textContent = 'Buka / Unduh materi'; actions.append(file); }
                if (material.video_url) { const video = document.createElement('a'); video.className = 'button button-primary'; video.href = material.video_url; video.target = '_blank'; video.rel = 'noopener'; video.textContent = 'Putar video'; actions.append(video); }
                if (!material.file_url && !material.video_url) { const missing = document.createElement('p'); missing.textContent = 'File atau tautan materi belum tersedia.'; actions.append(missing); }
                const done = document.createElement('button'); done.type = 'button'; done.className = 'button button-outline'; done.textContent = material.completed ? 'Materi sudah selesai' : 'Tandai selesai'; done.disabled = material.completed;
                done.addEventListener('click', async () => {
                    done.disabled = true;
                    materialDialog.setAttribute('aria-busy', 'true');
                    done.textContent = 'Menyimpan…';
                    try {
                        const response = await fetch(`/dashboard/materials/${id}/complete`, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': materialCsrf, 'X-Requested-With': 'XMLHttpRequest' } });
                        const result = await response.json().catch(() => ({}));
                        if (!response.ok) throw new Error(result.message || 'Progres belum dapat disimpan. Coba lagi.');
                        material.completed = true;
                        done.textContent = 'Materi sudah selesai';
                        document.querySelector(`[data-material-card][data-id="${id}"]`)?.classList.add('is-completed');
                        updateMaterialProgress();
                        showToast(result.message || 'Materi ditandai selesai.');
                    } catch (error) {
                        done.disabled = false;
                        done.textContent = 'Tandai selesai';
                        showToast(error instanceof Error ? error.message : 'Progres belum dapat disimpan. Coba lagi.');
                    } finally {
                        materialDialog.removeAttribute('aria-busy');
                    }
                });
                actions.append(done); materialDetail.append(subject, title, description, topic, actions);
            } catch (error) { materialDetail.textContent = error.message; }
        };
        const updateMaterialProgress = () => {
            const completed = document.querySelectorAll('[data-material-card].is-completed').length;
            const total = materialCards.length;
            const percent = total ? Math.round(completed / total * 100) : 0;
            document.querySelector('[data-progress-ring]')?.style.setProperty('--progress', `${percent}%`);
            const ring = document.querySelector('[data-progress-ring] strong'); if (ring) ring.textContent = `${percent}%`;
            document.querySelector('[data-progress-bar]')?.style.setProperty('width', `${percent}%`);
            const summary = document.querySelector('.materials-progress-summary p'); if (summary) summary.textContent = `${completed} dari ${total} materi selesai`;
            const empty = document.querySelector('[data-material-progress-empty]'); if (empty) empty.hidden = completed > 0;
        };
        materialCards.forEach((card) => {
            card.addEventListener('click', (event) => { if (event.target.closest('button, a, input, select, [data-material-management-actions]')) return; openMaterial(card.dataset.id); });
            card.addEventListener('keydown', (event) => { if (event.target !== card) return; if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); openMaterial(card.dataset.id); } });
            card.querySelector('[data-material-bookmark]')?.addEventListener('click', async (event) => {
                event.stopPropagation(); const button = event.currentTarget; button.disabled = true;
                try {
                    const response = await fetch(button.dataset.url, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': materialCsrf } });
                    const result = await response.json(); if (!response.ok) throw new Error(result.message || 'Bookmark gagal disimpan.');
                    button.classList.toggle('is-saved', result.bookmarked); button.setAttribute('aria-pressed', String(result.bookmarked)); button.setAttribute('aria-label', result.bookmarked ? 'Hapus bookmark' : 'Simpan materi'); showToast(result.message);
                } catch (error) { showToast(error.message); } finally { button.disabled = false; }
            });
        });
        materialGrid.addEventListener('click', (event) => {
            const edit = event.target.closest('[data-material-edit]');
            const remove = event.target.closest('[data-material-delete]');
            if (edit) { event.preventDefault(); openMaterialForm(edit.closest('[data-material-card]')); }
            if (remove) { event.preventDefault(); beginMaterialDelete(remove.closest('[data-material-card]')); }
        });
        if (materialSubject !== 'all') {
            document.querySelectorAll('[data-material-subject]').forEach((tab) => tab.classList.toggle('is-active', tab.dataset.materialSubject === materialSubject));
            drawMaterials();
        }
        document.querySelectorAll('[data-material-open]').forEach((button) => button.addEventListener('click', () => openMaterial(button.dataset.materialOpen)));
        document.querySelector('[data-material-close]')?.addEventListener('click', () => materialDialog.close());
        document.querySelector('[data-material-detail-progress]')?.addEventListener('click', () => document.querySelector('.materials-progress-card')?.scrollIntoView({ behavior: 'smooth', block: 'center' }));
        drawMaterials();
    }

    const assignmentPage = document.querySelector('[data-spa-page="assignments"]');
    const assignmentCards = [...(assignmentPage?.querySelectorAll('[data-assignment-card]') ?? [])];
    const assignmentDialog = assignmentPage?.querySelector('[data-assignment-dialog]');
    const assignmentDetail = assignmentPage?.querySelector('[data-assignment-detail]');
    const assignmentForm = assignmentPage?.querySelector('[data-assignment-submit-form]');
    let assignmentFilter = 'all';
    let currentAssignmentId = null;
    const assignmentCsrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const renderAssignmentCards = () => {
        if (!assignmentPage) return;
        const query = (searchInput?.value ?? '').trim().toLocaleLowerCase('id');
        let visible = 0;
        assignmentCards.forEach((card) => {
            const hasSubmission = card.dataset.submitted === 'true';
            const status = card.dataset.status;
            const matchesFilter = assignmentFilter === 'all'
                || (assignmentFilter === 'active' && !hasSubmission)
                || (assignmentFilter === 'submitted' && hasSubmission && status !== 'graded')
                || (assignmentFilter === 'graded' && status === 'graded');
            const matchesSearch = !query || card.dataset.search.includes(query);
            card.hidden = !(matchesFilter && matchesSearch);
            if (!card.hidden) {
                card.style.animationDelay = `${Math.min(visible, 6) * 45}ms`;
                visible += 1;
            }
        });
        const count = assignmentPage.querySelector('[data-assignment-count]');
        if (count) count.textContent = String(visible);
        const noAssignments = assignmentPage.querySelector('[data-assignment-empty]');
        const noFiltered = assignmentPage.querySelector('[data-assignment-filter-empty]');
        if (noAssignments) noAssignments.hidden = assignmentCards.length > 0;
        if (noFiltered) noFiltered.hidden = visible > 0 || assignmentCards.length === 0;
    };
    assignmentPage?.querySelectorAll('[data-assignment-filter]').forEach((button) => button.addEventListener('click', () => {
        assignmentFilter = button.dataset.assignmentFilter;
        assignmentPage.querySelectorAll('[data-assignment-filter]').forEach((tab) => {
            const active = tab === button;
            tab.classList.toggle('is-active', active);
            tab.setAttribute('aria-pressed', String(active));
        });
        renderAssignmentCards();
    }));
    searchInput?.addEventListener('input', () => { if (document.body.classList.contains('assignments-active')) renderAssignmentCards(); });
    assignmentPage?.querySelectorAll('[data-assignment-show-grades]').forEach((button) => button.addEventListener('click', () => assignmentPage.querySelector('[data-assignment-filter="graded"]')?.click()));
    assignmentPage?.querySelector('[data-assignment-show-list]')?.addEventListener('click', () => assignmentPage.querySelector('[data-assignment-filter="all"]')?.click());
    const openAssignment = async (id) => {
        if (!assignmentDialog || !assignmentDetail || !assignmentForm) return;
        currentAssignmentId = id;
        assignmentDialog.showModal();
        assignmentDetail.replaceChildren();
        const loading = document.createElement('p'); loading.textContent = 'Memuat detail tugas…'; assignmentDetail.append(loading);
        assignmentForm.hidden = true;
        try {
            const response = await fetch(`/dashboard/assignments/${id}`, { headers: { Accept: 'application/json' } });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Detail tugas tidak dapat dimuat.');
            const task = result.assignment;
            assignmentDetail.replaceChildren();
            const meta = document.createElement('p'); meta.className = 'assignment-detail-meta'; meta.textContent = `${task.subject} · Oleh ${task.tutor_name} · Deadline ${task.due_at}`;
            const title = document.createElement('h2'); title.id = 'assignment-detail-title'; title.textContent = task.title;
            const instructions = document.createElement('p'); instructions.className = 'assignment-detail-instructions'; instructions.textContent = task.instructions;
            assignmentDetail.append(meta, title, instructions);
            if (task.is_overdue) { const overdue = document.createElement('p'); overdue.textContent = 'Deadline telah lewat. Jawaban baru akan ditandai terlambat.'; assignmentDetail.append(overdue); }
            const submission = task.submission;
            if (submission) {
                const submitted = document.createElement('p'); submitted.textContent = `Dikumpulkan ${submission.is_late ? 'terlambat' : ''} ${submission.submitted_at || ''}`.trim(); assignmentDetail.append(submitted);
                if (submission.answer_text) { const answer = document.createElement('p'); answer.textContent = `Jawaban: ${submission.answer_text}`; assignmentDetail.append(answer); }
                if (submission.file_url) { const file = document.createElement('a'); file.href = submission.file_url; file.textContent = 'Buka file jawaban'; file.className = 'button button-outline'; assignmentDetail.append(file); }
                if (submission.score !== null) {
                    const resultBox = document.createElement('div'); resultBox.className = 'assignment-result-box';
                    const score = document.createElement('strong'); score.textContent = `Nilai ${submission.score}/100`;
                    const feedback = document.createElement('p'); feedback.textContent = submission.feedback || 'Tutor belum menambahkan feedback.';
                    resultBox.append(score, feedback); assignmentDetail.append(resultBox);
                }
            }
            if (task.editable) {
                assignmentForm.action = `/dashboard/assignments/${id}/submissions`;
                const textWrap = assignmentForm.querySelector('[data-answer-text-wrap]');
                const fileWrap = assignmentForm.querySelector('[data-answer-file-wrap]');
                textWrap.hidden = !task.accepts_text;
                fileWrap.hidden = !task.accepts_file;
                assignmentForm.elements.answer_text.value = submission?.answer_text || '';
                assignmentForm.elements.answer_file.value = '';
                assignmentForm.querySelector('[data-answer-file-help]').textContent = `PDF, dokumen Office, teks, CSV, PNG, JPG · Maksimal ${Math.ceil(task.max_file_kilobytes / 1024)} MB.`;
                assignmentForm.querySelector('[data-assignment-error]').hidden = true;
                assignmentForm.querySelector('[data-assignment-submit-button]').textContent = submission ? 'Perbarui dan Kirim' : 'Kumpulkan Tugas';
                assignmentForm.hidden = false;
            }
        } catch (error) {
            assignmentDetail.textContent = error.message;
        }
    };
    assignmentPage?.querySelectorAll('[data-assignment-open]').forEach((button) => button.addEventListener('click', () => openAssignment(button.dataset.assignmentOpen)));
    assignmentPage?.querySelector('[data-assignment-close]')?.addEventListener('click', () => assignmentDialog?.close());
    assignmentForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const submit = assignmentForm.querySelector('[data-assignment-submit-button]');
        const errorBox = assignmentForm.querySelector('[data-assignment-error]');
        submit.disabled = true; submit.textContent = 'Mengirim…'; errorBox.hidden = true;
        try {
            const payload = new FormData(assignmentForm);
            if (assignmentForm.querySelector('[data-answer-text-wrap]').hidden) payload.delete('answer_text');
            if (assignmentForm.querySelector('[data-answer-file-wrap]').hidden) payload.delete('answer_file');
            const response = await fetch(assignmentForm.action, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': assignmentCsrf }, body: payload });
            const result = await response.json();
            if (!response.ok) throw new Error(result.errors ? Object.values(result.errors).flat().join(' ') : result.message || 'Jawaban belum dapat dikirim.');
            showToast(result.message);
            assignmentDialog.close();
            window.setTimeout(() => window.location.reload(), 600);
        } catch (error) {
            errorBox.textContent = error.message; errorBox.hidden = false;
            submit.disabled = false; submit.textContent = 'Coba Kirim Lagi';
        }
    });
    assignmentDialog?.addEventListener('close', () => { currentAssignmentId = null; });
    renderAssignmentCards();

    const catalogCards = catalogGrid ? [...catalogGrid.querySelectorAll('[data-catalog-card]')] : [];
    const favoriteStorageKey = 'tutorkita-favorite-tutors';
    let favoriteNames = [];
    try { favoriteNames = JSON.parse(localStorage.getItem(favoriteStorageKey) || '[]'); } catch { favoriteNames = []; }

    const renderCatalog = ({ resetPage = false, sortResults = true } = {}) => {
        if (!catalogGrid) return;
        if (resetPage) catalogPageNumber = 1;
        const query = (catalogSearch?.value || '').trim().toLocaleLowerCase('id');
        const filters = Object.fromEntries([...catalogPage.querySelectorAll('[data-catalog-filter]')].filter((control) => control.type !== 'checkbox').map((control) => [control.dataset.catalogFilter, control.value]));
        const availableOnly = catalogPage.querySelector('[data-catalog-filter="available"]')?.checked ?? false;
        const minRating = Number(filters.rating || 0);
        const filtered = catalogCards.filter((card) => {
            const subjects = card.dataset.subjects.split('|');
            const levels = card.dataset.levels.split('|');
            const scheduleMatches = filters.schedule === 'all' || card.dataset.day === filters.schedule || card.dataset.period === filters.schedule;
            return (!query || card.dataset.searchable.includes(query))
                && (filters.subject === 'all' || subjects.includes(filters.subject.toLocaleLowerCase('id')))
                && (filters.level === 'all' || levels.includes(filters.level))
                && scheduleMatches
                && (filters.location === 'all' || card.dataset.location === filters.location)
                && Number(card.dataset.rating) >= minRating
                && (!availableOnly || card.dataset.available === '1');
        });
        const comparator = {
            'rating-desc': (a, b) => Number(b.dataset.rating) - Number(a.dataset.rating),
            'rating-asc': (a, b) => Number(a.dataset.rating) - Number(b.dataset.rating),
            'name-asc': (a, b) => a.dataset.name.localeCompare(b.dataset.name, 'id'),
            'name-desc': (a, b) => b.dataset.name.localeCompare(a.dataset.name, 'id'),
            newest: (a, b) => Number(b.dataset.created) - Number(a.dataset.created),
        }[catalogSort?.value || 'rating-desc'];
        if (sortResults) filtered.sort(comparator);
        filtered.forEach((card) => catalogGrid.append(card));

        const total = filtered.length;
        if (catalogCount) catalogCount.textContent = total;
        const pageCount = Math.ceil(total / catalogPageSize);
        catalogPageNumber = Math.max(1, Math.min(catalogPageNumber, pageCount || 1));
        const start = (catalogPageNumber - 1) * catalogPageSize;
        const currentPageCards = new Set(filtered.slice(start, start + catalogPageSize));
        catalogCards.forEach((card) => { card.hidden = !currentPageCards.has(card); });
        const empty = catalogGrid.querySelector('[data-catalog-empty]');
        if (empty) { empty.hidden = total > 0; catalogGrid.append(empty); }

        if (!catalogPagination) return;
        catalogPagination.replaceChildren();
        if (pageCount < 2) { catalogPagination.hidden = true; return; }
        catalogPagination.hidden = false;
        const addPageButton = (label, page, className = 'catalog-page-number', ariaLabel = '') => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = className;
            button.textContent = label;
            button.dataset.catalogPage = page;
            if (ariaLabel) button.setAttribute('aria-label', ariaLabel);
            if (page === catalogPageNumber) { button.classList.add('is-active'); button.setAttribute('aria-current', 'page'); }
            catalogPagination.append(button);
        };
        addPageButton('‹', Math.max(1, catalogPageNumber - 1), 'catalog-page-arrow', 'Halaman sebelumnya');
        for (let page = 1; page <= pageCount; page += 1) addPageButton(String(page), page);
        addPageButton('›', Math.min(pageCount, catalogPageNumber + 1), 'catalog-page-arrow', 'Halaman berikutnya');
    };

    catalogCards.forEach((card) => {
        const name = card.dataset.name;
        const button = card.querySelector('[data-catalog-favorite]');
        const saved = favoriteNames.includes(name);
        button?.classList.toggle('is-favorite', saved);
        button?.setAttribute('aria-pressed', String(saved));
    });
    if (catalogCards.length) renderCatalog({ sortResults: false });

    catalogForm?.addEventListener('submit', (event) => { event.preventDefault(); renderCatalog({ resetPage: true }); });
    catalogSearch?.addEventListener('input', () => {
        if (document.body.classList.contains('catalog-active') && searchInput) searchInput.value = catalogSearch.value;
        renderCatalog({ resetPage: true });
    });
    catalogPage?.querySelectorAll('[data-catalog-filter]').forEach((control) => control.addEventListener('change', () => renderCatalog({ resetPage: true })));
    catalogSort?.addEventListener('change', () => renderCatalog());
    catalogPagination?.addEventListener('click', (event) => {
        const button = event.target.closest('[data-catalog-page]');
        if (!button) return;
        catalogPageNumber = Number(button.dataset.catalogPage);
        renderCatalog();
    });
    catalogGrid?.addEventListener('click', (event) => {
        const favorite = event.target.closest('[data-catalog-favorite]');
        if (favorite) {
            const name = favorite.dataset.catalogFavorite;
            favoriteNames = favoriteNames.includes(name) ? favoriteNames.filter((item) => item !== name) : [...favoriteNames, name];
            try { localStorage.setItem(favoriteStorageKey, JSON.stringify(favoriteNames)); } catch { /* Keep the in-page favorite state if storage is unavailable. */ }
            favorite.classList.toggle('is-favorite', favoriteNames.includes(name));
            favorite.setAttribute('aria-pressed', String(favoriteNames.includes(name)));
            return;
        }
        const profile = event.target.closest('[data-catalog-profile]');
        if (!profile || !catalogDialog) return;
        const card = profile.closest('[data-catalog-card]');
        const name = card.querySelector('.catalog-tutor-info h2').innerText.replace('✓', '').trim();
        const content = catalogDialog.querySelector('[data-catalog-profile-content]');
        content.replaceChildren();
        const heading = document.createElement('h2');
        heading.id = 'catalog-profile-name';
        heading.textContent = name;
        const details = document.createElement('p');
        details.textContent = `${card.querySelector('.catalog-tutor-info p').textContent} · ${card.querySelectorAll('.catalog-tutor-info p')[1].textContent}`;
        const review = document.createElement('p');
        review.textContent = `${card.querySelector('.catalog-rating').innerText.trim()} · ${card.dataset.location} · ${card.dataset.day}, ${card.dataset.period}`;
        const subjects = document.createElement('p');
        subjects.textContent = `Mata pelajaran: ${[...card.querySelectorAll('.catalog-tags span')].map((tag) => tag.textContent).join(' · ')}`;
        const action = document.createElement('button');
        action.type = 'button'; action.className = 'catalog-dialog-action'; action.textContent = 'Ajukan sesi belajar';
        action.addEventListener('click', () => { catalogDialog.close(); showToast('Pengajuan sesi tutor akan tersedia di versi berikutnya.'); });
        content.append(heading, details, review, subjects, action);
        catalogDialog.showModal();
    });
    document.querySelector('[data-catalog-dialog-close]')?.addEventListener('click', () => catalogDialog?.close());
    document.querySelector('[data-catalog-more-toggle]')?.addEventListener('click', (event) => {
        const button = event.currentTarget;
        const popover = document.querySelector('#catalog-more-filters');
        popover.hidden = !popover.hidden;
        button.setAttribute('aria-expanded', String(!popover.hidden));
    });
    document.querySelector('[data-catalog-reset]')?.addEventListener('click', () => {
        catalogPage.querySelectorAll('[data-catalog-filter]').forEach((control) => { if (control.type === 'checkbox') control.checked = false; else control.value = control.dataset.catalogFilter === 'schedule' ? 'all' : control.dataset.catalogFilter === 'rating' ? '0' : 'all'; });
        if (catalogSearch) catalogSearch.value = '';
        if (searchInput && document.body.classList.contains('catalog-active')) searchInput.value = '';
        renderCatalog({ resetPage: true });
    });

    const sessionPage = document.querySelector('[data-session-config]');
    const sessionList = sessionPage?.querySelector('[data-session-list]');
    const sessionCards = sessionList ? [...sessionList.querySelectorAll('[data-session-card]')] : [];
    const sessionHeading = sessionPage?.querySelector('[data-session-heading]');
    const sessionDescription = sessionPage?.querySelector('[data-session-description]');
    const sessionCount = sessionPage?.querySelector('[data-session-count]');
    const sessionEmpty = sessionPage?.querySelector('[data-session-empty]');
    const sessionFilterEmpty = sessionPage?.querySelector('[data-session-filter-empty]');
    const sessionCreateDialog = sessionPage?.querySelector('[data-session-create-dialog]');
    const sessionDetailDialog = sessionPage?.querySelector('[data-session-detail-dialog]');
    const sessionCreateForm = sessionPage?.querySelector('[data-session-create-form]');
    const sessionTutorSelect = sessionPage?.querySelector('[data-session-tutor]');
    const sessionSubjectSelect = sessionPage?.querySelector('[data-session-subject]');
    const sessionLevelSelect = sessionPage?.querySelector('[data-session-level]');
    const sessionFormError = sessionPage?.querySelector('[data-session-form-error]');
    const sessionDetailContent = sessionPage?.querySelector('[data-session-detail-content]');
    const sessionCancelButton = sessionPage?.querySelector('[data-session-cancel]');
    const sessionHeadings = {
        all: ['Sesi Mendatang', 'Jadwal sesi belajar yang sudah dikonfirmasi atau menunggu konfirmasi.'],
        upcoming: ['Sesi Mendatang', 'Jadwal sesi belajar yang sudah dikonfirmasi atau menunggu konfirmasi.'],
        completed: ['Sesi Selesai', 'Lihat kembali sesi belajar yang sudah kamu jalani.'],
        cancelled: ['Sesi Dibatalkan', 'Riwayat sesi yang dibatalkan.'],
        pending: ['Pengajuan Sesi', 'Pengajuan sesi yang sedang menunggu konfirmasi tutor.'],
    };
    let activeSessionFilter = 'all';

    const renderSessions = () => {
        if (!sessionList) return;
        const query = document.body.classList.contains('sessions-active') ? (searchInput?.value || '').trim().toLocaleLowerCase('id') : '';
        const visible = sessionCards.filter((card) => {
            const status = card.dataset.sessionStatus;
            const matchesTab = activeSessionFilter === 'all'
                || (activeSessionFilter === 'upcoming' && ['pending', 'confirmed', 'in_progress'].includes(status))
                || (activeSessionFilter === 'completed' && status === 'completed')
                || (activeSessionFilter === 'cancelled' && status === 'cancelled')
                || (activeSessionFilter === 'pending' && status === 'pending');
            const searchable = `${card.dataset.sessionTutor} ${card.innerText}`.toLocaleLowerCase('id');
            return matchesTab && (!query || searchable.includes(query));
        });
        sessionCards.forEach((card) => { card.hidden = !visible.includes(card); });
        if (sessionCount) sessionCount.textContent = String(visible.length);
        if (sessionHeading && sessionDescription) [sessionHeading.textContent, sessionDescription.textContent] = sessionHeadings[activeSessionFilter];
        if (sessionEmpty) sessionEmpty.hidden = sessionCards.length > 0 || activeSessionFilter !== 'all' || Boolean(query);
        if (sessionFilterEmpty) sessionFilterEmpty.hidden = sessionCards.length === 0 || visible.length > 0;
    };

    sessionPage?.querySelectorAll('[data-session-filter]').forEach((tab) => tab.addEventListener('click', () => {
        activeSessionFilter = tab.dataset.sessionFilter;
        sessionPage.querySelectorAll('[data-session-filter]').forEach((button) => {
            const active = button === tab;
            button.classList.toggle('is-active', active);
            button.setAttribute('aria-selected', String(active));
        });
        renderSessions();
    }));
    sessionPage?.querySelectorAll('[data-session-filter-shortcut]').forEach((button) => button.addEventListener('click', () => {
        sessionPage.querySelector(`[data-session-filter="${button.dataset.sessionFilterShortcut}"]`)?.click();
    }));
    renderSessions();

    const syncSessionSubjects = () => {
        if (!sessionTutorSelect || !sessionSubjectSelect) return;
        const option = sessionTutorSelect.selectedOptions[0];
        const subjects = (option?.dataset.subjects || '').split('|').filter(Boolean);
        const levels = (option?.dataset.levels || '').split('|').filter(Boolean);
        sessionSubjectSelect.replaceChildren(...subjects.map((subject) => {
            const subjectOption = document.createElement('option');
            subjectOption.value = subject;
            subjectOption.textContent = subject;
            return subjectOption;
        }));
        if (sessionLevelSelect) {
            sessionLevelSelect.replaceChildren(...levels.map((level) => {
                const levelOption = document.createElement('option');
                levelOption.value = level;
                levelOption.textContent = level;
                return levelOption;
            }));
        }
    };
    sessionTutorSelect?.addEventListener('change', syncSessionSubjects);
    syncSessionSubjects();

    sessionPage?.querySelectorAll('[data-session-dialog-open]').forEach((button) => button.addEventListener('click', () => {
        sessionFormError.hidden = true;
        sessionCreateDialog.showModal();
    }));
    sessionPage?.querySelectorAll('[data-session-dialog-close]').forEach((button) => button.addEventListener('click', () => sessionCreateDialog.close()));
    sessionPage?.querySelectorAll('[data-session-detail-close]').forEach((button) => button.addEventListener('click', () => sessionDetailDialog.close()));

    const openSessionDetails = async (card) => {
        if (!card || !sessionDetailDialog || !sessionDetailContent) return;
        sessionDetailContent.replaceChildren();
        const kicker = document.createElement('span'); kicker.className = 'section-kicker'; kicker.textContent = 'DETAIL SESI BELAJAR';
        const loading = document.createElement('h2'); loading.id = 'session-detail-title'; loading.textContent = 'Memuat detail sesi';
        const note = document.createElement('p'); note.textContent = 'Mohon tunggu sebentar.';
        sessionDetailContent.append(kicker, loading, note);
        sessionCancelButton.hidden = true;
        sessionCancelButton.dataset.url = '';
        sessionDetailDialog.showModal();
        try {
            const response = await fetch(card.dataset.sessionDetailUrl, { headers: { Accept: 'application/json' } });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Detail sesi tidak dapat dimuat.');
            const item = result.session;
            const title = document.createElement('h2'); title.id = 'session-detail-title'; title.textContent = `${item.subject} · ${item.topic}`;
            const fields = document.createElement('dl'); fields.className = 'session-detail-fields';
            [['Tutor', item.tutor_name], ['Institusi', item.tutor_university || '—'], ['Tanggal', item.date_label], ['Waktu', item.time_label], ['Jenjang', item.grade_level], ['Status', item.status.replaceAll('_', ' '),], ['Pertemuan', `Online (${item.meeting_platform})`]].forEach(([label, value]) => {
                const term = document.createElement('dt'); term.textContent = label;
                const description = document.createElement('dd'); description.textContent = value;
                fields.append(term, description);
            });
            sessionDetailContent.replaceChildren(kicker, title, fields);
            const canCancel = ['pending', 'confirmed'].includes(item.status) && new Date(item.starts_at) > new Date();
            sessionCancelButton.hidden = !canCancel;
            sessionCancelButton.dataset.url = card.dataset.sessionCancelUrl;
        } catch (error) {
            const message = document.createElement('p'); message.className = 'session-form-error'; message.textContent = error.message;
            sessionDetailContent.append(message);
        }
    };
    sessionList?.addEventListener('click', (event) => {
        const detailButton = event.target.closest('[data-session-detail]');
        if (detailButton) openSessionDetails(detailButton.closest('[data-session-card]'));
    });
    sessionPage?.querySelectorAll('[data-session-side-detail]').forEach((button) => button.addEventListener('click', () => {
        const matching = sessionCards.find((item) => item.dataset.sessionDetailUrl?.endsWith(`/${button.dataset.sessionSideDetail}`));
        openSessionDetails(matching);
    }));

    sessionCreateForm?.addEventListener('submit', async (event) => {
        event.preventDefault();
        sessionFormError.hidden = true;
        const submit = sessionCreateForm.querySelector('[data-session-submit]');
        submit.disabled = true;
        try {
            const response = await fetch(sessionCreateForm.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(sessionCreateForm),
            });
            const result = await response.json();
            if (!response.ok) {
                const validationMessage = result.errors ? Object.values(result.errors).flat().join(' ') : result.message;
                throw new Error(validationMessage || 'Pengajuan sesi gagal disimpan.');
            }
            sessionCreateDialog.close();
            showToast(result.message);
            window.location.reload();
        } catch (error) {
            sessionFormError.textContent = error.message;
            sessionFormError.hidden = false;
        } finally {
            submit.disabled = false;
        }
    });
    sessionCancelButton?.addEventListener('click', async () => {
        if (!sessionCancelButton.dataset.url) return;
        sessionCancelButton.disabled = true;
        try {
            const token = sessionCreateForm.querySelector('[name="_token"]').value;
            const response = await fetch(sessionCancelButton.dataset.url, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Sesi tidak dapat dibatalkan.');
            sessionDetailDialog.close();
            showToast(result.message);
            window.location.reload();
        } catch (error) {
            sessionFormError.textContent = error.message;
            sessionFormError.hidden = false;
        } finally {
            sessionCancelButton.disabled = false;
        }
    });

    const historyPage = document.querySelector('[data-spa-page="history"]');
    const historyCards = historyPage ? [...historyPage.querySelectorAll('[data-history-card]')] : [];
    const historySearch = historyPage?.querySelector('[data-history-search]');
    const historySubject = historyPage?.querySelector('[data-history-subject]');
    const historyPeriod = historyPage?.querySelector('[data-history-period]');
    const historySort = historyPage?.querySelector('[data-history-sort]');
    const historyPagination = historyPage?.querySelector('[data-history-pagination]');
    const historyDetailDialog = historyPage?.querySelector('[data-history-detail-dialog]');
    const historyDetailContent = historyPage?.querySelector('[data-history-detail-content]');
    const historyFilterButtons = historyPage ? [...historyPage.querySelectorAll('[data-history-status]')] : [];
    let historyStatus = 'all';
    let historyPageNumber = 1;
    const historyPageSize = 5;
    const historyFilterEmpty = historyPage?.querySelector('[data-history-filter-empty]');
    const historyEmpty = historyPage?.querySelector('[data-history-empty]');
    const historyCount = historyPage?.querySelector('[data-history-result-count]');

    const renderHistory = () => {
        if (!historyPage) return;
        const query = historySearch?.value.trim().toLocaleLowerCase('id') ?? '';
        const subject = historySubject?.value ?? 'all';
        const periodDays = Number(historyPeriod?.value ?? '0');
        const cutoff = periodDays ? Date.now() - periodDays * 86400000 : 0;
        const list = historyCards.filter((card) => {
            const start = Number(card.dataset.date) * 1000;
            const matchesStatus = historyStatus === 'all' || card.dataset.status === historyStatus;
            const matchesSubject = subject === 'all' || card.dataset.subject === subject;
            const matchesPeriod = !periodDays || (start >= cutoff && start <= Date.now());
            const matchesSearch = !query || card.dataset.search.includes(query);
            return matchesStatus && matchesSubject && matchesPeriod && matchesSearch;
        });
        list.sort((left, right) => historySort?.value === 'oldest' ? Number(left.dataset.date) - Number(right.dataset.date) : Number(right.dataset.date) - Number(left.dataset.date));
        const pages = Math.max(1, Math.ceil(list.length / historyPageSize));
        historyPageNumber = Math.min(historyPageNumber, pages);
        const start = (historyPageNumber - 1) * historyPageSize;
        historyCards.forEach((card) => { card.hidden = true; });
        list.slice(start, start + historyPageSize).forEach((card, index) => {
            card.hidden = false;
            card.style.setProperty('--history-delay', `${Math.min(index, 4) * 35}ms`);
        });
        if (historyCount) historyCount.textContent = list.length ? `${start + 1}–${Math.min(start + historyPageSize, list.length)} dari ${list.length} sesi` : '0 sesi';
        if (historyEmpty) historyEmpty.hidden = historyCards.length > 0;
        if (historyFilterEmpty) historyFilterEmpty.hidden = historyCards.length === 0 || list.length > 0;
        if (!historyPagination) return;
        historyPagination.replaceChildren();
        if (pages <= 1) return;
        const addButton = (label, page, active = false, disabled = false) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = label;
            button.disabled = disabled;
            button.classList.toggle('is-active', active);
            if (active) button.setAttribute('aria-current', 'page');
            button.addEventListener('click', () => { historyPageNumber = page; renderHistory(); });
            historyPagination.append(button);
        };
        addButton('‹', Math.max(1, historyPageNumber - 1), false, historyPageNumber === 1);
        for (let page = 1; page <= pages; page += 1) addButton(String(page), page, page === historyPageNumber);
        addButton('›', Math.min(pages, historyPageNumber + 1), false, historyPageNumber === pages);
    };

    historyFilterButtons.forEach((button) => button.addEventListener('click', () => {
        historyStatus = button.dataset.historyStatus;
        historyPageNumber = 1;
        historyFilterButtons.forEach((item) => {
            const active = item === button;
            item.classList.toggle('is-active', active);
            item.setAttribute('aria-pressed', String(active));
        });
        renderHistory();
    }));
    historySearch?.addEventListener('input', () => { historyPageNumber = 1; renderHistory(); });
    historySubject?.addEventListener('change', () => { historyPageNumber = 1; renderHistory(); });
    historyPeriod?.addEventListener('change', () => { historyPageNumber = 1; renderHistory(); });
    historySort?.addEventListener('change', () => { historyPageNumber = 1; renderHistory(); });
    searchInput?.addEventListener('input', () => {
        if (document.body.classList.contains('history-active') && historySearch) {
            historySearch.value = searchInput.value;
            historyPageNumber = 1;
            renderHistory();
        }
    });
    renderHistory();

    const historyStatusLabels = { pending: 'Menunggu Konfirmasi', confirmed: 'Terkonfirmasi', in_progress: 'Sedang Berlangsung', completed: 'Selesai', cancelled: 'Dibatalkan' };
    const openHistoryDetails = async (card) => {
        if (!card || !historyDetailDialog || !historyDetailContent) return;
        const kicker = document.createElement('span'); kicker.className = 'section-kicker'; kicker.textContent = 'RIWAYAT SESI BELAJAR';
        const loadingTitle = document.createElement('h2'); loadingTitle.id = 'history-detail-title'; loadingTitle.textContent = 'Memuat detail sesi';
        const loadingCopy = document.createElement('p'); loadingCopy.textContent = 'Mohon tunggu sebentar.';
        historyDetailContent.replaceChildren(kicker, loadingTitle, loadingCopy);
        historyDetailDialog.showModal();
        try {
            const response = await fetch(card.dataset.detailUrl, { headers: { Accept: 'application/json' } });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Detail sesi tidak dapat dimuat.');
            const session = result.session;
            const title = document.createElement('h2'); title.id = 'history-detail-title'; title.textContent = `${session.subject} · ${session.topic}`;
            const fields = document.createElement('dl');
            [['Tutor', session.tutor_name], ['Institusi', session.tutor_university || 'Belum tersedia'], ['Tanggal', session.date_label], ['Waktu', `${session.time_label} (${session.duration_minutes} menit)`], ['Jenjang', session.grade_level], ['Pertemuan', `Online (${session.meeting_platform})`], ['Status', historyStatusLabels[session.status] || session.status]]
                .forEach(([label, value]) => { const term = document.createElement('dt'); term.textContent = label; const description = document.createElement('dd'); description.textContent = value; fields.append(term, description); });
            const note = document.createElement('p'); note.className = 'history-detail-note'; note.textContent = 'Catatan dan materi khusus sesi ini belum tersedia.';
            historyDetailContent.replaceChildren(kicker, title, fields, note);
        } catch (error) {
            const message = document.createElement('p'); message.className = 'session-form-error'; message.textContent = error.message;
            historyDetailContent.replaceChildren(kicker, message);
        }
    };
    historyPage?.querySelectorAll('[data-history-detail]').forEach((button) => button.addEventListener('click', () => openHistoryDetails(button.closest('[data-history-card]'))));
    historyPage?.querySelector('[data-history-dialog-close]')?.addEventListener('click', () => historyDetailDialog?.close());
    historyPage?.querySelectorAll('[data-history-browse],[data-history-open-tutors]').forEach((button) => button.addEventListener('click', () => showPage('tutors')));

    const filterContent = () => {
        const query = searchInput?.value.trim().toLocaleLowerCase('id') ?? '';
        if (document.body.classList.contains('catalog-active') && catalogSearch) {
            catalogSearch.value = searchInput?.value ?? '';
            renderCatalog({ resetPage: true });
        }
        if (document.body.classList.contains('materials-active') && searchInput) {
            const materialSearch = document.querySelector('[data-material-search]');
            if (materialSearch && materialSearch.value !== searchInput.value) {
                materialSearch.value = searchInput.value;
                materialSearch.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
        if (document.body.classList.contains('assignments-active') && searchInput) {
            const taskSearch = document.querySelector('[data-assignment-search]');
            if (taskSearch && taskSearch.value !== searchInput.value) {
                taskSearch.value = searchInput.value;
                taskSearch.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
        if (document.body.classList.contains('sessions-active')) renderSessions();
        if (document.body.classList.contains('history-active') && historySearch) {
            historySearch.value = searchInput?.value ?? '';
            historyPageNumber = 1;
            renderHistory();
        }
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
