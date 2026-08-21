document.addEventListener('DOMContentLoaded', function () {
  const listEl = document.getElementById('notif-list');
  const skeletonEl = document.getElementById('notif-skeleton');
  const emptyEl = document.getElementById('notif-empty');
  const markAllBtn = document.getElementById('btn-mark-all-read');

  const TYPE_ICON = {
    status: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m20 6-11 11-5-5"/></svg>',
    assignment: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="8" r="3"/><path d="M2 20c0-3.5 3-5 7-5s7 1.5 7 5"/><path d="m17 11 2 2 4-4"/></svg>',
    default: '<svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/></svg>',
  };

  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  function renderItem(n) {
    const icon = TYPE_ICON[n.type] || TYPE_ICON.default;
    const readClass = n.is_read == 1 ? 'is-read' : '';
    return `
      <button class="notif-item ${readClass}" data-id="${n.id}">
        <span class="notif-icon" style="background:${n.is_read == 1 ? 'var(--border)' : 'var(--white)'};color:var(--primary);">${icon}</span>
        <span style="flex:1;min-width:0;">
          <span style="display:flex;justify-content:space-between;gap:8px;">
            <strong style="font-size:14px;">${escapeHtml(n.title)}</strong>
            <span class="text-secondary" style="font-size:11.5px;white-space:nowrap;">${escapeHtml(n.time_label)}</span>
          </span>
          <span class="text-secondary" style="font-size:13px;display:block;margin-top:2px;line-height:1.4;">${escapeHtml(n.message)}</span>
        </span>
      </button>`;
  }

  async function loadNotifications() {
    try {
      const res = await fetch(window.BASE_URL + '/api/notifications/list.php');
      const data = await res.json();
      skeletonEl.style.display = 'none';

      if (!data.success) {
        showToast(data.message || 'Gagal memuat notifikasi.', 'error');
        return;
      }

      const items = data.data.notifications;
      if (items.length === 0) {
        emptyEl.style.display = 'block';
        return;
      }

      listEl.innerHTML = items.map(renderItem).join('');
      listEl.querySelectorAll('.notif-item').forEach(el => {
        el.addEventListener('click', () => markRead(el.dataset.id, el));
      });
    } catch (err) {
      skeletonEl.style.display = 'none';
      showToast('Tidak dapat memuat notifikasi.', 'error');
    }
  }

  async function markRead(id, el) {
    if (el.classList.contains('is-read')) return;
    el.classList.add('is-read');
    el.querySelector('.notif-icon').style.background = 'var(--border)';
    try {
      await fetch(window.BASE_URL + '/api/notifications/mark-read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, csrf_token: window.CSRF_TOKEN }),
      });
    } catch (err) { /* gagal senyap, UI tetap terlihat dibaca */ }
  }

  markAllBtn.addEventListener('click', async function () {
    try {
      const res = await fetch(window.BASE_URL + '/api/notifications/mark-all-read.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ csrf_token: window.CSRF_TOKEN }),
      });
      const data = await res.json();
      if (data.success) {
        showToast('Semua notifikasi ditandai sebagai dibaca.', 'success');
        loadNotifications();
      }
    } catch (err) {
      showToast('Gagal menandai semua sebagai dibaca.', 'error');
    }
  });

  loadNotifications();
});
