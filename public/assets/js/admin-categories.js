document.addEventListener('DOMContentLoaded', function () {
  const csrfToken = () => document.querySelector('#category-form input[name=csrf_token]').value;

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  // ---------------- Accordion: load & toggle subcategories ----------------
  document.querySelectorAll('.btn-toggle-subs').forEach(btn => {
    btn.addEventListener('click', async function () {
      const row = btn.closest('.category-row');
      const panel = row.querySelector('.subcategory-panel');
      const list = row.querySelector('.subcategory-list');
      const isOpen = panel.style.display !== 'none';

      if (isOpen) { panel.style.display = 'none'; return; }

      panel.style.display = 'block';
      list.innerHTML = '<p class="text-secondary" style="font-size:13px;">Memuat...</p>';

      try {
        const res = await fetch(window.BASE_URL + '/api/categories/admin-subcategory-list.php?category_id=' + row.dataset.id);
        const data = await res.json();
        const subs = data.data.subcategories;
        list.innerHTML = subs.length ? subs.map(s => `
          <div style="display:flex;justify-content:space-between;align-items:center;padding:8px 12px;background:var(--bg);border-radius:8px;">
            <span style="font-size:13px;">${escapeHtml(s.nama)} ${s.status !== 'aktif' ? '<span class="text-secondary">(nonaktif)</span>' : ''}</span>
            <div style="display:flex;gap:6px;">
              <button class="btn-table btn-edit-sub" data-id="${s.id}" data-nama="${escapeHtml(s.nama)}" data-category="${row.dataset.id}">Edit</button>
              <button class="btn-table btn-toggle-sub" data-id="${s.id}" style="background:#FDECEC;color:var(--danger);">${s.status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan'}</button>
            </div>
          </div>`).join('') : '<p class="text-secondary" style="font-size:13px;">Belum ada subkategori.</p>';

        list.querySelectorAll('.btn-edit-sub').forEach(b => b.addEventListener('click', () => openSubcategoryModal(b.dataset.category, b.dataset.id, b.dataset.nama)));
        list.querySelectorAll('.btn-toggle-sub').forEach(b => b.addEventListener('click', () => toggleSubcategory(b.dataset.id, row)));
      } catch (err) {
        list.innerHTML = '<p style="color:var(--danger);font-size:13px;">Gagal memuat subkategori.</p>';
      }
    });
  });

  document.querySelectorAll('.btn-add-subcategory').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = btn.closest('.category-row');
      openSubcategoryModal(row.dataset.id, '', '');
    });
  });

  async function toggleSubcategory(id, row) {
    try {
      const res = await fetch(window.BASE_URL + '/api/categories/subcategory-save.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action: 'toggle', id, csrf_token: csrfToken() }),
      });
      const data = await res.json();
      if (data.success) { showToast(data.message, 'success'); row.querySelector('.btn-toggle-subs').click(); row.querySelector('.btn-toggle-subs').click(); }
      else showToast(data.message, 'error');
    } catch (err) { showToast('Gagal memperbarui subkategori.', 'error'); }
  }

  // ---------------- Modal Kategori ----------------
  const catModal = document.getElementById('category-modal-overlay');
  document.getElementById('btn-add-category').addEventListener('click', () => {
    document.getElementById('category-modal-title').textContent = 'Tambah Kategori';
    document.getElementById('category_id').value = '';
    document.getElementById('f-cat-nama').value = '';
    catModal.classList.add('open');
  });
  document.getElementById('btn-cancel-category-modal').addEventListener('click', () => catModal.classList.remove('open'));
  catModal.addEventListener('click', e => { if (e.target === catModal) catModal.classList.remove('open'); });

  document.querySelectorAll('.btn-edit-category').forEach(btn => {
    btn.addEventListener('click', function () {
      const row = btn.closest('.category-row');
      document.getElementById('category-modal-title').textContent = 'Edit Kategori';
      document.getElementById('category_id').value = row.dataset.id;
      document.getElementById('f-cat-nama').value = row.dataset.nama;
      document.getElementById('f-cat-icon').value = row.dataset.icon;
      catModal.classList.add('open');
    });
  });

  document.getElementById('category-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const payload = {
      id: document.getElementById('category_id').value || null,
      nama: document.getElementById('f-cat-nama').value.trim(),
      icon: document.getElementById('f-cat-icon').value,
      csrf_token: csrfToken(),
    };
    try {
      const res = await fetch(window.BASE_URL + '/api/categories/admin-save.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (data.success) { showToast(data.message, 'success'); setTimeout(() => window.location.reload(), 700); }
      else showToast(data.message, 'error');
    } catch (err) { showToast('Gagal menyimpan kategori.', 'error'); }
  });

  document.querySelectorAll('.btn-toggle-category').forEach(btn => {
    btn.addEventListener('click', async function () {
      const row = btn.closest('.category-row');
      if (!confirm('Ubah status kategori ini?')) return;
      try {
        const res = await fetch(window.BASE_URL + '/api/categories/admin-toggle.php', {
          method: 'POST', headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: row.dataset.id, csrf_token: csrfToken() }),
        });
        const data = await res.json();
        if (data.success) { showToast(data.message, 'success'); setTimeout(() => window.location.reload(), 700); }
        else showToast(data.message, 'error');
      } catch (err) { showToast('Gagal memperbarui status.', 'error'); }
    });
  });

  // ---------------- Modal Subkategori ----------------
  const subModal = document.getElementById('subcategory-modal-overlay');
  function openSubcategoryModal(categoryId, id, nama) {
    document.getElementById('subcategory-modal-title').textContent = id ? 'Edit Subkategori' : 'Tambah Subkategori';
    document.getElementById('subcategory_id').value = id || '';
    document.getElementById('subcategory_category_id').value = categoryId;
    document.getElementById('f-sub-nama').value = nama || '';
    subModal.classList.add('open');
  }
  document.getElementById('btn-cancel-subcategory-modal').addEventListener('click', () => subModal.classList.remove('open'));
  subModal.addEventListener('click', e => { if (e.target === subModal) subModal.classList.remove('open'); });

  document.getElementById('subcategory-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const categoryId = document.getElementById('subcategory_category_id').value;
    const payload = {
      id: document.getElementById('subcategory_id').value || null,
      category_id: categoryId,
      nama: document.getElementById('f-sub-nama').value.trim(),
      csrf_token: csrfToken(),
    };
    try {
      const res = await fetch(window.BASE_URL + '/api/categories/subcategory-save.php', {
        method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload),
      });
      const data = await res.json();
      if (data.success) {
        showToast(data.message, 'success');
        subModal.classList.remove('open');
        const row = document.querySelector(`.category-row[data-id="${categoryId}"]`);
        const toggleBtn = row.querySelector('.btn-toggle-subs');
        row.querySelector('.subcategory-panel').style.display = 'none';
        toggleBtn.click();
      } else {
        showToast(data.message, 'error');
      }
    } catch (err) { showToast('Gagal menyimpan subkategori.', 'error'); }
  });
});
