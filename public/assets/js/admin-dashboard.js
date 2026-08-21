document.addEventListener('DOMContentLoaded', function () {
  // ================= Trend line chart (vanilla SVG, no external lib) =================
  (function renderTrendChart() {
    const container = document.getElementById('trend-chart');
    const data = window.TREND_DATA || {};
    const entries = Object.entries(data);
    if (entries.length === 0) { container.innerHTML = '<p class="text-secondary">Belum ada data.</p>'; return; }

    const W = container.clientWidth || 560, H = 220, PAD = 30;
    const values = entries.map(e => e[1]);
    const max = Math.max(...values, 1);
    const stepX = (W - PAD * 2) / (entries.length - 1 || 1);

    const points = entries.map((e, i) => {
      const x = PAD + i * stepX;
      const y = H - PAD - (e[1] / max) * (H - PAD * 2 - 20);
      return [x, y];
    });

    const linePath = points.map((p, i) => (i === 0 ? 'M' : 'L') + p[0] + ' ' + p[1]).join(' ');
    const areaPath = linePath + ` L${points[points.length - 1][0]} ${H - PAD} L${points[0][0]} ${H - PAD} Z`;

    const monthLabels = entries.map(([ym]) => {
      const [y, m] = ym.split('-');
      return new Date(y, m - 1, 1).toLocaleDateString('id-ID', { month: 'short' });
    });

    let svg = `<svg viewBox="0 0 ${W} ${H}" width="100%" height="${H}" preserveAspectRatio="none" style="overflow:visible;">
      <defs><linearGradient id="trendFill" x1="0" y1="0" x2="0" y2="1">
        <stop offset="0%" stop-color="#0053A6" stop-opacity="0.18"/>
        <stop offset="100%" stop-color="#0053A6" stop-opacity="0"/>
      </linearGradient></defs>
      <path d="${areaPath}" fill="url(#trendFill)" stroke="none"/>
      <path d="${linePath}" fill="none" stroke="#0053A6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>`;

    points.forEach((p, i) => {
      svg += `<circle cx="${p[0]}" cy="${p[1]}" r="4" fill="#0053A6" stroke="#fff" stroke-width="2"/>`;
      svg += `<text x="${p[0]}" y="${H - 6}" font-size="11" fill="#667085" text-anchor="middle">${monthLabels[i]}</text>`;
    });
    svg += '</svg>';
    container.innerHTML = svg;
  })();

  // ================= Category donut chart (vanilla SVG) =================
  (function renderDonut() {
    const container = document.getElementById('category-donut');
    const data = (window.CATEGORY_DATA || []).filter(d => d.total > 0);
    const total = data.reduce((sum, d) => sum + parseInt(d.total, 10), 0);

    if (total === 0) {
      container.innerHTML = '<p class="text-secondary" style="font-size:13px;">Belum ada laporan.</p>';
      return;
    }

    const colors = ['#0053A6', '#475467', '#93A3B8', '#C7D2E0', '#0B2F55'];
    const radius = 60, cx = 80, cy = 80, strokeWidth = 20;
    const circumference = 2 * Math.PI * radius;
    let offset = 0;

    let svg = `<svg viewBox="0 0 160 160" width="160" height="160" style="display:block;margin:0 auto;">`;
    data.forEach((d, i) => {
      const frac = d.total / total;
      const dash = frac * circumference;
      svg += `<circle cx="${cx}" cy="${cy}" r="${radius}" fill="none" stroke="${colors[i % colors.length]}"
                stroke-width="${strokeWidth}" stroke-dasharray="${dash} ${circumference - dash}"
                stroke-dashoffset="${-offset}" transform="rotate(-90 ${cx} ${cy})"/>`;
      offset += dash;
    });
    svg += `<text x="80" y="76" text-anchor="middle" font-size="20" font-weight="800" fill="#172033">${total.toLocaleString('id-ID')}</text>
            <text x="80" y="94" text-anchor="middle" font-size="11" fill="#667085">Total</text></svg>`;

    let legend = '<div style="margin-top:16px;display:flex;flex-direction:column;gap:9px;">';
    data.forEach((d, i) => {
      const pct = Math.round((d.total / total) * 100);
      legend += `<div style="display:flex;align-items:center;gap:8px;font-size:12.5px;">
        <span style="width:9px;height:9px;border-radius:50%;background:${colors[i % colors.length]};flex-shrink:0;"></span>
        <span style="flex:1;color:var(--text);">${d.nama}</span>
        <strong>${pct}%</strong>
      </div>`;
    });
    legend += '</div>';

    container.innerHTML = svg + legend;
  })();

  // ================= Map of report locations =================
  (function renderMap() {
    const points = window.MAP_POINTS || [];
    if (!document.getElementById('admin-map')) return;

    const center = points.length > 0
      ? [parseFloat(points[0].latitude), parseFloat(points[0].longitude)]
      : [-6.8043, 110.8405];

    const map = L.map('admin-map', { scrollWheelZoom: false }).setView(center, 12);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '&copy; OpenStreetMap contributors', maxZoom: 19,
    }).addTo(map);

    const statusColor = {
      diajukan: '#0053A6', diverifikasi: '#0053A6', diteruskan: '#0053A6',
      diproses: '#F59E0B', selesai: '#16A34A', ditolak: '#DC2626',
    };

    points.forEach(p => {
      const lat = parseFloat(p.latitude), lng = parseFloat(p.longitude);
      if (!lat || !lng) return;
      L.circleMarker([lat, lng], {
        radius: 7, color: '#fff', weight: 2,
        fillColor: statusColor[p.status] || '#667085', fillOpacity: 0.9,
      }).bindPopup(`<strong>${p.title.replace(/</g, '&lt;')}</strong>`).addTo(map);
    });
  })();
});
