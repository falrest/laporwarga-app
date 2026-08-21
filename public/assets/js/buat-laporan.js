document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('report-form');
  const btn = document.getElementById('btn-submit-report');
  const categorySelect = document.getElementById('category_id');
  const subcategorySelect = document.getElementById('subcategory_id');
  const addressInput = document.getElementById('address');
  const latInput = document.getElementById('latitude');
  const lngInput = document.getElementById('longitude');
  const evidenceInput = document.getElementById('evidence');
  const previewList = document.getElementById('preview-list');
  const btnOpenMap = document.getElementById('btn-open-map');
  const btnUseLocation = document.getElementById('btn-use-location');

  const DRAFT_KEY = 'lw_report_draft';
  const LOCATION_KEY = 'lw_selected_location';

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

  // ---- Load subkategori via AJAX saat kategori berubah ----
  async function loadSubcategories(categoryId, preselect) {
    subcategorySelect.innerHTML = '<option value="">Memuat...</option>';
    try {
      const res = await fetch(window.BASE_URL + '/api/categories/subcategories.php?category_id=' + encodeURIComponent(categoryId));
      const data = await res.json();
      subcategorySelect.innerHTML = '<option value="">Pilih subkategori</option>';
      if (data.success) {
        data.data.subcategories.forEach(sc => {
          const opt = document.createElement('option');
          opt.value = sc.id;
          opt.textContent = sc.nama;
          if (preselect && String(sc.id) === String(preselect)) opt.selected = true;
          subcategorySelect.appendChild(opt);
        });
      }
    } catch (err) {
      showToast('Gagal memuat subkategori.', 'error');
    }
  }

  categorySelect.addEventListener('change', () => loadSubcategories(categorySelect.value));

  // ---- Preview file sebelum upload ----
  evidenceInput.addEventListener('change', function () {
    previewList.innerHTML = '';
    const files = Array.from(evidenceInput.files || []);
    files.forEach(file => {
      if (file.size > window.MAX_UPLOAD_SIZE) {
        showToast(`File "${file.name}" melebihi batas ukuran maksimal.`, 'error');
        return;
      }
      const box = document.createElement('div');
      box.style.cssText = 'aspect-ratio:1;border-radius:10px;overflow:hidden;background:var(--light-blue);display:flex;align-items:center;justify-content:center;';
      if (file.type.startsWith('image/')) {
        const img = document.createElement('img');
        img.style.cssText = 'width:100%;height:100%;object-fit:cover;';
        img.src = URL.createObjectURL(file);
        box.appendChild(img);
      } else {
        box.innerHTML = '<span style="font-size:11px;color:var(--primary);padding:4px;text-align:center;">🎬 Video</span>';
      }
      previewList.appendChild(box);
    });
  });

  // ---- Gunakan lokasi saya (geolocation + reverse geocoding via Nominatim) ----
  btnUseLocation.addEventListener('click', function () {
    if (!navigator.geolocation) {
      showToast('Perangkat Anda tidak mendukung geolokasi.', 'error');
      return;
    }
    setButtonLoading(btnUseLocation, true, 'Mencari...');
    navigator.geolocation.getCurrentPosition(async function (pos) {
      const { latitude, longitude } = pos.coords;
      latInput.value = latitude;
      lngInput.value = longitude;
      try {
        const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${latitude}&lon=${longitude}`);
        const data = await res.json();
        addressInput.value = data.display_name || `${latitude}, ${longitude}`;
      } catch (err) {
        addressInput.value = `${latitude}, ${longitude}`;
      }
      setButtonLoading(btnUseLocation, false);
      showToast('Lokasi berhasil ditemukan.', 'success');
    }, function () {
      setButtonLoading(btnUseLocation, false);
      showToast('Gagal mengambil lokasi. Izinkan akses lokasi di browser.', 'error');
    });
  });

  // ---- Simpan draft form sebelum pindah ke halaman peta, lalu kembali otomatis terisi ----
  function saveDraft() {
    const draft = {
      category_id: categorySelect.value,
      subcategory_id: subcategorySelect.value,
      title: document.getElementById('title').value,
      description: document.getElementById('description').value,
      incident_date: document.getElementById('incident_date').value,
    };
    sessionStorage.setItem(DRAFT_KEY, JSON.stringify(draft));
  }

  btnOpenMap.addEventListener('click', function () {
    saveDraft();
    // biarkan navigasi <a> berjalan normal ke pilih-lokasi.php
  });

  function restoreDraft() {
    const raw = sessionStorage.getItem(DRAFT_KEY);
    if (!raw) return;
    try {
      const draft = JSON.parse(raw);
      if (draft.title) document.getElementById('title').value = draft.title;
      if (draft.description) document.getElementById('description').value = draft.description;
      if (draft.incident_date) document.getElementById('incident_date').value = draft.incident_date;
      if (draft.category_id && draft.category_id !== categorySelect.value) {
        categorySelect.value = draft.category_id;
      }
    } catch (err) { /* abaikan draft rusak */ }
  }

  function restoreLocationFromMap() {
    const raw = sessionStorage.getItem(LOCATION_KEY);
    if (!raw) return;
    try {
      const loc = JSON.parse(raw);
      addressInput.value = loc.address || '';
      latInput.value = loc.latitude || '';
      lngInput.value = loc.longitude || '';
      sessionStorage.removeItem(LOCATION_KEY);
      showToast('Lokasi berhasil dipilih.', 'success');
    } catch (err) { /* abaikan */ }
  }

  restoreDraft();
  restoreLocationFromMap();
  loadSubcategories(categorySelect.value, subcategorySelect.value || new URLSearchParams(window.location.search).get('subcategory_id'));

  // ---- Submit ----
  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    clearErrors();

    let valid = true;
    if (document.getElementById('title').value.trim().length < 5) { showFieldError('title', 'Judul minimal 5 karakter.'); valid = false; }
    if (document.getElementById('description').value.trim().length < 10) { showFieldError('description', 'Deskripsi minimal 10 karakter.'); valid = false; }
    if (!subcategorySelect.value) { showFieldError('subcategory_id', 'Pilih subkategori.'); valid = false; }
    if (!addressInput.value.trim() || !latInput.value || !lngInput.value) { showFieldError('location', 'Pilih lokasi kejadian melalui peta atau lokasi Anda.'); valid = false; }
    if (!document.getElementById('incident_date').value) { showFieldError('incident_date', 'Tanggal kejadian wajib diisi.'); valid = false; }
    if (!valid) { showToast('Periksa kembali form Anda.', 'error'); return; }

    setButtonLoading(btn, true, 'Mengirim laporan...');

    const formData = new FormData();
    formData.append('category_id', categorySelect.value);
    formData.append('subcategory_id', subcategorySelect.value);
    formData.append('title', document.getElementById('title').value.trim());
    formData.append('description', document.getElementById('description').value.trim());
    formData.append('address', addressInput.value.trim());
    formData.append('latitude', latInput.value);
    formData.append('longitude', lngInput.value);
    formData.append('incident_date', document.getElementById('incident_date').value);
    formData.append('csrf_token', document.querySelector('input[name=csrf_token]').value);
    Array.from(evidenceInput.files || []).forEach(file => formData.append('evidence[]', file));

    try {
      const res = await fetch(window.BASE_URL + '/api/reports/create.php', {
        method: 'POST',
        headers: { 'X-CSRF-Token': document.querySelector('input[name=csrf_token]').value },
        body: formData,
      });
      const data = await res.json();

      if (data.success) {
        sessionStorage.removeItem(DRAFT_KEY);
        showToast(data.message || 'Laporan berhasil dikirim.', 'success');
        setTimeout(() => { window.location.href = data.data.redirect; }, 900);
      } else {
        showToast(data.message || 'Laporan gagal dikirim. Silakan coba lagi.', 'error');
        if (data.errors) {
          Object.keys(data.errors).forEach(field => {
            const mapped = field === 'location' ? 'location' : field;
            showFieldError(mapped, data.errors[field]);
          });
        }
      }
    } catch (err) {
      showToast('Tidak dapat terhubung ke server. Coba lagi.', 'error');
    } finally {
      setButtonLoading(btn, false);
    }
  });
});
