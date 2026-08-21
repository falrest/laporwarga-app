document.addEventListener('DOMContentLoaded', function () {
  // ---- Peta read-only ----
  const loc = window.REPORT_LOCATION;
  if (loc && loc.lat && document.getElementById('detail-map')) {
    const map = L.map('detail-map', { zoomControl: false, dragging: false, scrollWheelZoom: false }).setView([loc.lat, loc.lng], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors' }).addTo(map);
    L.marker([loc.lat, loc.lng]).addTo(map);
  }

  // ---- Nama file bukti penanganan ----
  const evidenceInput = document.getElementById('evidence');
  const evidenceLabel = document.getElementById('evidence-filename');
  if (evidenceInput) {
    evidenceInput.addEventListener('change', function () {
      evidenceLabel.textContent = evidenceInput.files[0] ? '📎 ' + evidenceInput.files[0].name : '';
    });
  }

  // ---- Submit aksi petugas ----
  const form = document.getElementById('staff-action-form');
  const btn = document.getElementById('btn-save-action');

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    const status = document.getElementById('status').value;
    const note = document.getElementById('note').value.trim();

    if (status === 'ditolak' && !note) {
      showToast('Alasan penolakan wajib diisi pada kolom Tanggapan / Catatan.', 'error');
      return;
    }

    setButtonLoading(btn, true, 'Menyimpan...');

    const formData = new FormData(form);
    if (!formData.get('assigned_to')) formData.delete('assigned_to');

    try {
      const res = await fetch(window.BASE_URL + '/api/reports/admin-update.php', {
        method: 'POST',
        headers: { 'X-CSRF-Token': document.querySelector('input[name=csrf_token]').value },
        body: formData,
      });
      const data = await res.json();

      if (data.success) {
        showToast(data.message || 'Perubahan berhasil disimpan.', 'success');
        setTimeout(() => window.location.reload(), 900);
      } else {
        showToast(data.message || 'Gagal menyimpan perubahan.', 'error');
      }
    } catch (err) {
      showToast('Tidak dapat terhubung ke server. Coba lagi.', 'error');
    } finally {
      setButtonLoading(btn, false);
    }
  });
});
