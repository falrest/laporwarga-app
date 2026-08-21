document.addEventListener('DOMContentLoaded', function () {
  const tbody = document.getElementById('table-body');
  const searchInput = document.getElementById('search-input');
  const paginationInfo = document.getElementById('pagination-info');
  const btnPrev = document.getElementById('btn-prev-page');
  const btnNext = document.getElementById('btn-next-page');
  const modalOverlay = document.getElementById('user-modal-overlay');
  const modalTitle = document.getElementById('modal-title');
  const form = document.getElementById('user-form');
  const btnAdd = document.getElementById('btn-add-user');
  const btnCancel = document.getElementById('btn-cancel-modal');
  const kecamatanSelect = document.getElementById('f-kecamatan_id');
  const desaSelect = document.getElementById('f-desa_id');
  const passwordLabel = document.getElementById('password-label');

  let state = { search: '', page: 1, totalPages: 1 };

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  function renderRow(u) {
    const initial = escapeHtml((u.nama || '?').substring(0, 1).toUpperCase());
    const statusBadge = u.status === 'aktif'
      ? '<span class="badge badge-selesai">Aktif</span>'
      : '<span class="badge badge-ditolak">Nonaktif</span>';
    return `<tr>
      <td><span class="admin-avatar-chip">${initial}</span>${escapeHtml(u.nama)}</td>
      <td>${escapeHtml(u.email || '-')}<br><span class="text-secondary" style="font-size:12px;">${escapeHtml(u.no_hp)}</span></td>
      <td>${escapeHtml(u.kecamatan_name || '-')}</td>
      <td>${statusBadge}</td>
      <td class="text-secondary">${new Date(u.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
      <td>
        <button class="btn-table" onclick="editUser(${u.id})">Edit</button>
        <button class="btn-table" style="background:#FDECEC;color:var(--danger);" onclick="deleteUser(${u.id})">Nonaktifkan</button>
      </td>
    </tr>`;
  }

  window.currentUsers = [];

  async function loadUsers() {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-secondary);">Memuat...</td></tr>';
    const params = new URLSearchParams({ role: window.USER_ROLE, search: state.search, page: state.page });
    try {
      const res = await fetch(window.BASE_URL + '/api/users/admin-list.php?' + params.toString());
      const data = await res.json();
      if (!data.success) { showToast(data.message, 'error'); return; }

      const { items, total, total_pages, page } = data.data;
      window.currentUsers = items;
      state.totalPages = total_pages;

      tbody.innerHTML = items.length
        ? items.map(renderRow).join('')
        : '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-secondary);">Tidak ada data.</td></tr>';

      paginationInfo.textContent = `Halaman ${page} dari ${total_pages || 1} (${total} total)`;
      btnPrev.disabled = page <= 1;
      btnNext.disabled = page >= total_pages;
    } catch (err) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--danger);">Gagal memuat data.</td></tr>';
    }
  }

  let debounceTimer = null;
  searchInput.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { state.search = searchInput.value.trim(); state.page = 1; loadUsers(); }, 400);
  });
  btnPrev.addEventListener('click', () => { if (state.page > 1) { state.page--; loadUsers(); } });
  btnNext.addEventListener('click', () => { if (state.page < state.totalPages) { state.page++; loadUsers(); } });

  async function loadVillages(districtId, preselect) {
    desaSelect.innerHTML = '<option value="">Memuat...</option>';
    if (!districtId) { desaSelect.innerHTML = '<option value="">Pilih kecamatan dulu</option>'; return; }
    const res = await fetch(window.BASE_URL + '/api/locations/villages.php?district_id=' + districtId);
    const data = await res.json();
    desaSelect.innerHTML = '<option value="">Pilih</option>';
    if (data.success) {
      data.data.villages.forEach(v => {
        const opt = document.createElement('option');
        opt.value = v.id; opt.textContent = v.name;
        if (preselect && String(v.id) === String(preselect)) opt.selected = true;
        desaSelect.appendChild(opt);
      });
    }
  }
  kecamatanSelect.addEventListener('change', () => loadVillages(kecamatanSelect.value));

  function openModal(title, isEdit) {
    modalTitle.textContent = title;
    passwordLabel.textContent = isEdit ? 'Password Baru (opsional)' : 'Password';
    modalOverlay.classList.add('open');
  }
  function closeModal() {
    modalOverlay.classList.remove('open');
    form.reset();
    document.getElementById('user_id').value = '';
    desaSelect.innerHTML = '<option value="">Pilih kecamatan dulu</option>';
  }

  btnAdd.addEventListener('click', () => openModal(window.USER_ROLE === 'petugas' ? 'Tambah Petugas' : 'Tambah Warga', false));
  btnCancel.addEventListener('click', closeModal);
  modalOverlay.addEventListener('click', e => { if (e.target === modalOverlay) closeModal(); });

  window.editUser = function (id) {
    const u = window.currentUsers.find(x => x.id === id);
    if (!u) return;
    document.getElementById('user_id').value = u.id;
    document.getElementById('f-nama').value = u.nama;
    document.getElementById('f-no_hp').value = u.no_hp;
    document.getElementById('f-email').value = u.email || '';
    document.getElementById('f-status').value = u.status;
    openModal('Edit ' + (window.USER_ROLE === 'petugas' ? 'Petugas' : 'Warga'), true);
  };

  window.deleteUser = async function (id) {
    if (!confirm('Nonaktifkan pengguna ini? Data laporan terkait tetap tersimpan.')) return;
    try {
      const res = await fetch(window.BASE_URL + '/api/users/admin-delete.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, csrf_token: document.querySelector('input[name=csrf_token]').value }),
      });
      const data = await res.json();
      if (data.success) { showToast(data.message, 'success'); loadUsers(); }
      else showToast(data.message, 'error');
    } catch (err) { showToast('Gagal menonaktifkan pengguna.', 'error'); }
  };

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    const payload = {
      id: document.getElementById('user_id').value || null,
      role: window.USER_ROLE,
      nama: document.getElementById('f-nama').value.trim(),
      nik: document.getElementById('f-nik').value.trim(),
      no_hp: document.getElementById('f-no_hp').value.trim(),
      email: document.getElementById('f-email').value.trim(),
      password: document.getElementById('f-password').value,
      alamat: document.getElementById('f-alamat').value.trim(),
      kecamatan_id: kecamatanSelect.value,
      desa_id: desaSelect.value,
      status: document.getElementById('f-status').value,
      csrf_token: document.querySelector('input[name=csrf_token]').value,
    };

    const btn = document.getElementById('btn-save-user');
    setButtonLoading(btn, true, 'Menyimpan...');
    try {
      const res = await fetch(window.BASE_URL + '/api/users/admin-save.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (data.success) {
        showToast(data.message, 'success');
        closeModal();
        loadUsers();
      } else {
        showToast(data.message || 'Gagal menyimpan data.', 'error');
      }
    } catch (err) {
      showToast('Tidak dapat terhubung ke server.', 'error');
    } finally {
      setButtonLoading(btn, false);
    }
  });

  loadUsers();
});
