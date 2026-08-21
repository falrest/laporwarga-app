document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('edit-profile-form');
  const btn = document.getElementById('btn-save-profile');
  const fotoInput = document.getElementById('foto');
  const fotoPreview = document.getElementById('foto-preview');
  const kecamatanSelect = document.getElementById('kecamatan_id');
  const desaSelect = document.getElementById('desa_id');
  const currentDesaId = desaSelect.value;

  fotoInput.addEventListener('change', function () {
    const file = fotoInput.files[0];
    if (file) fotoPreview.src = URL.createObjectURL(file);
  });

  async function loadVillages(districtId, preselect) {
    desaSelect.innerHTML = '<option value="">Memuat...</option>';
    if (!districtId) { desaSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>'; return; }
    try {
      const res = await fetch(window.BASE_URL + '/api/locations/villages.php?district_id=' + encodeURIComponent(districtId));
      const data = await res.json();
      desaSelect.innerHTML = '<option value="">Pilih Desa/Kelurahan</option>';
      if (data.success) {
        data.data.villages.forEach(v => {
          const opt = document.createElement('option');
          opt.value = v.id;
          opt.textContent = v.name;
          if (preselect && String(v.id) === String(preselect)) opt.selected = true;
          desaSelect.appendChild(opt);
        });
      }
    } catch (err) {
      showToast('Gagal memuat daftar desa/kelurahan.', 'error');
    }
  }

  kecamatanSelect.addEventListener('change', () => loadVillages(kecamatanSelect.value));

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

    const formData = new FormData();
    formData.append('nama', document.getElementById('nama').value.trim());
    formData.append('no_hp', document.getElementById('no_hp').value.trim());
    formData.append('email', document.getElementById('email').value.trim());
    formData.append('alamat', document.getElementById('alamat').value.trim());
    formData.append('kecamatan_id', kecamatanSelect.value);
    formData.append('desa_id', desaSelect.value);
    formData.append('csrf_token', document.querySelector('input[name=csrf_token]').value);
    if (fotoInput.files[0]) formData.append('foto', fotoInput.files[0]);

    setButtonLoading(btn, true, 'Menyimpan...');

    try {
      const res = await fetch(window.BASE_URL + '/api/users/update-profile.php', {
        method: 'POST',
        headers: { 'X-CSRF-Token': document.querySelector('input[name=csrf_token]').value },
        body: formData,
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message || 'Profil berhasil diperbarui.', 'success');
        setTimeout(() => { window.location.href = data.data.redirect; }, 900);
      } else {
        showToast(data.message || 'Gagal menyimpan perubahan.', 'error');
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
