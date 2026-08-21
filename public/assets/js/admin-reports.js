document.addEventListener('DOMContentLoaded', function () {
  const tbody = document.getElementById('report-table-body');
  const searchInput = document.getElementById('search-input');
  const categoryFilter = document.getElementById('category-filter');
  const paginationInfo = document.getElementById('pagination-info');
  const btnPrev = document.getElementById('btn-prev-page');
  const btnNext = document.getElementById('btn-next-page');

  let state = { search: '', category_id: 0, page: 1, totalPages: 1, total: 0 };
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

  function renderRow(r) {
    const initial = escapeHtml((r.pelapor_nama || '?').substring(0, 1).toUpperCase());
    return `<tr>
      <td style="font-weight:600;">#${escapeHtml(r.ticket_number)}</td>
      <td><span class="admin-avatar-chip">${initial}</span>${escapeHtml(r.pelapor_nama)}</td>
      <td>${escapeHtml(r.category_name)}</td>
      <td><span class="badge ${STATUS_BADGE_CLASS[r.status] || ''}">${escapeHtml(r.status_label)}</span></td>
      <td class="text-secondary">${new Date(r.created_at).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' })}</td>
      <td><a href="${window.BASE_URL}/admin/laporan-detail.php?id=${r.id}" class="btn-table ${r.status === 'diajukan' ? 'primary' : ''}">${r.status === 'diajukan' ? 'Tinjau' : 'Detail'}</a></td>
    </tr>`;
  }

  async function loadReports() {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-secondary);">Memuat...</td></tr>';

    const params = new URLSearchParams({
      status: window.STATUS_FILTER, search: state.search, category_id: state.category_id, page: state.page,
    });

    try {
      const res = await fetch(window.BASE_URL + '/api/reports/admin-list.php?' + params.toString());
      const data = await res.json();

      if (!data.success) {
        showToast(data.message || 'Gagal memuat data.', 'error');
        return;
      }

      const { items, total, total_pages, page } = data.data;
      state.totalPages = total_pages;
      state.total = total;

      if (items.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--text-secondary);">Tidak ada laporan ditemukan.</td></tr>';
      } else {
        tbody.innerHTML = items.map(renderRow).join('');
      }

      paginationInfo.textContent = `Menampilkan halaman ${page} dari ${total_pages || 1} (${total} total laporan)`;
      btnPrev.disabled = page <= 1;
      btnNext.disabled = page >= total_pages;
    } catch (err) {
      tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:24px;color:var(--danger);">Gagal memuat data. Periksa koneksi Anda.</td></tr>';
    }
  }

  searchInput.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => { state.search = searchInput.value.trim(); state.page = 1; loadReports(); }, 400);
  });
  categoryFilter.addEventListener('change', function () {
    state.category_id = categoryFilter.value; state.page = 1; loadReports();
  });
  btnPrev.addEventListener('click', () => { if (state.page > 1) { state.page--; loadReports(); } });
  btnNext.addEventListener('click', () => { if (state.page < state.totalPages) { state.page++; loadReports(); } });

  loadReports();
});
