document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('login-form');
  const btn = document.getElementById('btn-login');

  document.querySelectorAll('.toggle-visibility').forEach(function (toggle) {
    toggle.addEventListener('click', function () {
      const target = document.getElementById(toggle.dataset.target);
      target.type = target.type === 'password' ? 'text' : 'password';
    });
  });

  function clearErrors() {
    document.querySelectorAll('.field-error').forEach(el => { el.textContent = ''; el.classList.remove('show'); });
    document.querySelectorAll('.input').forEach(el => el.classList.remove('is-invalid'));
  }

  function showFieldError(field, message) {
    const errEl = document.getElementById('err-' + field);
    const inputEl = document.getElementById(field);
    if (errEl) { errEl.textContent = message; errEl.classList.add('show'); }
    if (inputEl) inputEl.classList.add('is-invalid');
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    clearErrors();

    const identifier = document.getElementById('identifier').value.trim();
    const password = document.getElementById('password').value;
    let valid = true;

    if (!identifier) { showFieldError('identifier', 'Email atau nomor HP wajib diisi.'); valid = false; }
    if (!password) { showFieldError('password', 'Password wajib diisi.'); valid = false; }
    if (!valid) return;

    setButtonLoading(btn, true, 'Memproses...');

    try {
      const res = await fetch(window.BASE_URL + '/api/auth/login.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          identifier,
          password,
          remember: document.getElementById('remember').checked,
          csrf_token: document.querySelector('input[name=csrf_token]').value,
        }),
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message || 'Berhasil masuk.', 'success');
        window.location.href = data.data.redirect;
      } else {
        showToast(data.message || 'Login gagal. Periksa kembali data Anda.', 'error');
        if (data.errors) {
          Object.keys(data.errors).forEach(field => showFieldError(field, data.errors[field]));
        }
      }
    } catch (err) {
      showToast('Tidak dapat terhubung ke server. Coba lagi.', 'error');
    } finally {
      setButtonLoading(btn, false);
    }
  });
});
