document.addEventListener('DOMContentLoaded', function () {
  const searchInput = document.getElementById('search-input');
  const listEl = document.getElementById('report-list');
  const skeletonEl = document.getElementById('list-skeleton');
  const emptyEl = document.getElementById('empty-state');
  const loadMoreBtn = document.getElementById('btn-load-more');
  const tabs = document.querySelectorAll('.filter-tab');

  let state = { search: '', status: 'semua', page: 1, totalPages: 1, items: [] };
  let debounceTimer = null;

  const STATUS_BADGE_CLASS = {
    diajukan: 'badge-diajukan', diverifikasi: 'badge-diverifikasi', diteruskan: 'badge-diteruskan',
    diproses: 'badge-diproses', selesai: 'badge-selesai', ditolak: 'badge-ditolak',
  };

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  function renderCard(r) {
    const photo = r.photo_url
      ? `<img src="${escapeHtml(r.photo_url)}" alt="" style="width:100%;height:100%;object-fit:cover;" loading="lazy">`
      : '';
    const rejectionBox = r.status === 'ditolak' && r.rejection_reason
      ? `<div style="margin-top:10px;background:#FDECEC;color:var(--danger);font-size:12.5px;border-radius:10px;padding:10px 12px;display:flex;gap:8px;">
           <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:1px;"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>
           <span>${escapeHtml(r.rejection_reason)}</span>
         </div>` : '';
    const progressBar = r.status !== 'ditolak'
      ? `<div style="height:5px;border-radius:3px;background:var(--border);overflow:hidden;margin-top:12px;">
           <div style="height:100%;width:${r.progress}%;background:var(--primary);"></div>
         </div>
         <div style="display:flex;justify-content:space-between;font-size:11px;color:var(--text-secondary);margin-top:6px;">
           <span>Menunggu</span><span>Penanganan</span><span>Selesai</span>
         </div>` : '';

    return `
      <a href="${window.BASE_URL}/masyarakat/detail-laporan.php?id=${r.id}" class="card report-card" style="padding:0;overflow:hidden;">
        <div style="display:flex;gap:12px;padding:14px;">
          <div style="width:76px;height:76px;border-radius:12px;overflow:hidden;background:#DDE6F2;flex-shrink:0;">${photo}</div>
          <div style="flex:1;min-width:0;">
            <div style="display:flex;justify-content:space-between;gap:8px;">
              <h3 style="margin:0 0 4px;font-size:14.5px;line-height:1.3;">${escapeHtml(r.title)}</h3>
              <span class="badge ${STATUS_BADGE_CLASS[r.status] || ''}" style="flex-shrink:0;">${escapeHtml(r.status_label)}</span>
            </div>
            <p class="text-secondary" style="margin:0 0 4px;font-size:12.5px;">📍 ${escapeHtml(r.address)}</p>
            <p class="text-secondary" style="margin:0;font-size:12px;">${new Date(r.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</p>
          </div>
        </div>
        <div style="padding:0 14px 14px;">${progressBar}${rejectionBox}</div>
      </a>`;
  }

  async function loadReports(reset) {
    if (reset) {
      state.page = 1;
      listEl.innerHTML = '';
      skeletonEl.style.display = 'flex';
      emptyEl.style.display = 'none';
    }

    const params = new URLSearchParams({ search: state.search, status: state.status, page: state.page });
    try {
      const res = await fetch(window.BASE_URL + '/api/reports/list.php?' + params.toString());
      const data = await res.json();
      skeletonEl.style.display = 'none';

      if (!data.success) {
        showToast(data.message || 'Gagal memuat laporan.', 'error');
        return;
      }

      const { items, total_pages, page } = data.data;
      state.totalPages = total_pages;

      if (reset && items.length === 0) {
        emptyEl.style.display = 'block';
        loadMoreBtn.style.display = 'none';
        return;
      }

      listEl.insertAdjacentHTML('beforeend', items.map(renderCard).join(''));
      loadMoreBtn.style.display = page < total_pages ? 'block' : 'none';
    } catch (err) {
      skeletonEl.style.display = 'none';
      showToast('Tidak dapat memuat laporan. Periksa koneksi Anda.', 'error');
    }
  }

  searchInput.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
      state.search = searchInput.value.trim();
      loadReports(true);
    }, 400); // debounce - section 34
  });

  tabs.forEach(tab => {
    tab.addEventListener('click', function () {
      tabs.forEach(t => t.classList.remove('active'));
      tab.classList.add('active');
      state.status = tab.dataset.status;
      loadReports(true);
    });
  });

  loadMoreBtn.addEventListener('click', function () {
    state.page += 1;
    loadReports(false);
  });

  loadReports(true);
});
