const authForm = document.querySelector('[data-auth-form]');

if (authForm) {
    const feedback = document.querySelector('[data-auth-feedback]');
    const serverFeedback = document.querySelector('[data-server-auth-feedback]');
    const fields = [...authForm.querySelectorAll('[data-auth-field]')];
    const submitButton = authForm.querySelector('[type="submit"]');

    const showFeedback = (message, type = 'success') => {
        feedback.textContent = message;
        feedback.classList.toggle('is-error', type === 'error');
        feedback.classList.toggle('is-info', type === 'info');
        feedback.hidden = false;
    };

    const validate = () => {
        fields.forEach((field) => {
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');
        });

        const value = (name) => authForm.elements.namedItem(name)?.value ?? '';
        const errors = [];
        const contact = authForm.elements.namedItem('contact');
        const password = authForm.elements.namedItem('password');
        const isValidContact = (input) => {
            const value = input.trim();
            const email = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
            const phone = /^\+?[\d\s().-]{8,20}$/.test(value) && value.replace(/\D/g, '').length >= 8;
            return email || phone;
        };

        if (authForm.dataset.authForm === 'register' && !value('name').trim()) {
            errors.push([authForm.elements.namedItem('name'), 'Nama lengkap perlu diisi.']);
        }
        if (!value('contact').trim()) errors.push([contact, 'Email atau nomor telepon perlu diisi.']);
        else if (!isValidContact(value('contact'))) errors.push([contact, 'Masukkan email atau nomor telepon yang valid.']);
        if (authForm.dataset.authForm === 'register' && !authForm.elements.namedItem('terms').checked) {
            errors.push([authForm.elements.namedItem('terms'), 'Persetujuan diperlukan untuk melanjutkan.']);
        }
        if (!value('password')) errors.push([password, 'Password perlu diisi.']);
        else if (authForm.dataset.authForm === 'register' && value('password').length < 8) {
            errors.push([password, 'Gunakan password minimal 8 karakter.']);
        }
        if (authForm.dataset.authForm === 'register' && value('password_confirmation') !== value('password')) {
            errors.push([authForm.elements.namedItem('password_confirmation'), 'Konfirmasi password belum sama.']);
        }

        errors.forEach(([field]) => {
            field?.classList.add('is-invalid');
            field?.setAttribute('aria-invalid', 'true');
        });

        if (errors.length) {
            showFeedback(errors[0][1], 'error');
            errors[0][0]?.focus();
            return false;
        }

        return true;
    };

    fields.forEach((field) => {
        const clearError = () => {
            field.classList.remove('is-invalid');
            field.removeAttribute('aria-invalid');
            feedback.hidden = true;
        };
        field.addEventListener('input', clearError);
        field.addEventListener('change', clearError);
        field.addEventListener('input', () => {
            if (serverFeedback) serverFeedback.hidden = true;
        });
        field.addEventListener('change', () => {
            if (serverFeedback) serverFeedback.hidden = true;
        });
    });

    authForm.addEventListener('submit', (event) => {
        if (!validate()) {
            event.preventDefault();
            return;
        }

        submitButton.disabled = true;
        submitButton.textContent = authForm.dataset.authForm === 'register'
            ? 'Menyimpan akun…'
            : 'Memeriksa akun…';
    });

    document.querySelector('[data-password-help]')?.addEventListener('click', () => {
        showFeedback('Pemulihan kata sandi belum tersedia. Silakan hubungi tim TutorKita untuk bantuan.', 'info');
    });

    document.querySelectorAll('[data-social-auth]').forEach((button) => {
        button.addEventListener('click', () => {
            showFeedback(`Lanjutkan dengan ${button.dataset.socialAuth} belum tersedia. Gunakan formulir untuk melanjutkan.`, 'info');
        });
    });
}
