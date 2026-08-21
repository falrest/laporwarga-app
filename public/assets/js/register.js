document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('register-form');
  const btn = document.getElementById('btn-register');
  const kecamatanSelect = document.getElementById('kecamatan_id');
  const desaSelect = document.getElementById('desa_id');

  document.querySelectorAll('.toggle-visibility').forEach(function (toggle) {
    toggle.addEventListener('click', function () {
      const target = document.getElementById(toggle.dataset.target);
      target.type = target.type === 'password' ? 'text' : 'password';
    });
  });

  // Load desa/kelurahan secara dinamis dari database berdasarkan kecamatan (AJAX).
  kecamatanSelect.addEventListener('change', async function () {
    desaSelect.innerHTML = '<option value="">Memuat...</option>';
    desaSelect.disabled = true;
    if (!kecamatanSelect.value) {
      desaSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
      return;
    }
    try {
      const res = await fetch(window.BASE_URL + '/api/locations/villages.php?district_id=' + encodeURIComponent(kecamatanSelect.value));
      const data = await res.json();
      desaSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
      if (data.success) {
        data.data.villages.forEach(v => {
          const opt = document.createElement('option');
          opt.value = v.id;
          opt.textContent = v.name;
          desaSelect.appendChild(opt);
        });
        desaSelect.disabled = false;
      }
    } catch (err) {
      showToast('Gagal memuat daftar desa/kelurahan.', 'error');
    }
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

  function validateClientSide() {
    let valid = true;
    const nama = document.getElementById('nama').value.trim();
    const nik = document.getElementById('nik').value.trim();
    const no_hp = document.getElementById('no_hp').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const password_confirm = document.getElementById('password_confirm').value;
    const alamat = document.getElementById('alamat').value.trim();

    if (!nama) { showFieldError('nama', 'Nama lengkap wajib diisi.'); valid = false; }
    if (!/^\d{16}$/.test(nik)) { showFieldError('nik', 'NIK harus 16 digit angka.'); valid = false; }
    if (!/^08\d{8,12}$/.test(no_hp)) { showFieldError('no_hp', 'Nomor HP tidak valid.'); valid = false; }
    if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showFieldError('email', 'Format email tidak valid.'); valid = false; }
    if (password.length < 8) { showFieldError('password', 'Password minimal 8 karakter.'); valid = false; }
    if (password !== password_confirm) { showFieldError('password_confirm', 'Konfirmasi password tidak sama.'); valid = false; }
    if (!alamat) { showFieldError('alamat', 'Alamat wajib diisi.'); valid = false; }
    if (!kecamatanSelect.value) { showFieldError('kecamatan_id', 'Pilih kecamatan.'); valid = false; }
    if (!desaSelect.value) { showFieldError('desa_id', 'Pilih desa/kelurahan.'); valid = false; }
    if (!document.getElementById('consent').checked) {
      showToast('Anda harus menyetujui Kebijakan Privasi dan Syarat & Ketentuan.', 'error');
      valid = false;
    }
    return valid;
  }

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    clearErrors();
    if (!validateClientSide()) return;

    setButtonLoading(btn, true, 'Membuat akun...');

    const payload = {
      nama: document.getElementById('nama').value.trim(),
      nik: document.getElementById('nik').value.trim(),
      no_hp: document.getElementById('no_hp').value.trim(),
      email: document.getElementById('email').value.trim(),
      password: document.getElementById('password').value,
      password_confirm: document.getElementById('password_confirm').value,
      alamat: document.getElementById('alamat').value.trim(),
      kecamatan_id: kecamatanSelect.value,
      desa_id: desaSelect.value,
      csrf_token: document.querySelector('input[name=csrf_token]').value,
    };

    try {
      const res = await fetch(window.BASE_URL + '/api/auth/register.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message || 'Akun berhasil dibuat.', 'success');
        setTimeout(() => { window.location.href = window.BASE_URL + '/public/login.php'; }, 1200);
      } else {
        showToast(data.message || 'Pendaftaran gagal.', 'error');
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
