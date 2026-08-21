document.addEventListener('DOMContentLoaded', function () {
  const DEFAULT_CENTER = [-6.8043, 110.8405]; // Kudus, Jawa Tengah (fallback)
  let selected = { lat: DEFAULT_CENTER[0], lng: DEFAULT_CENTER[1], address: '' };

  const map = L.map('map', { zoomControl: false }).setView(DEFAULT_CENTER, 14);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap contributors',
    maxZoom: 19,
  }).addTo(map);

  const marker = L.marker(DEFAULT_CENTER, { draggable: true }).addTo(map);

  const titleEl = document.getElementById('selected-address-title');
  const subEl = document.getElementById('selected-address-sub');
  const searchInput = document.getElementById('map-search-input');
  const confirmBtn = document.getElementById('btn-confirm-location');
  const locateBtn = document.getElementById('btn-locate');
  const editBtn = document.getElementById('btn-edit-address');

  let debounceTimer = null;

  async function reverseGeocode(lat, lng) {
    titleEl.textContent = 'Mencari alamat...';
    subEl.textContent = '';
    try {
      const res = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`);
      const data = await res.json();
      const addr = data.address || {};
      const mainLine = addr.road || addr.neighbourhood || addr.suburb || data.display_name?.split(',')[0] || 'Lokasi dipilih';
      const subLine = [addr.village || addr.suburb, addr.county || addr.city].filter(Boolean).join(', ');

      selected = { lat, lng, address: data.display_name || mainLine };
      titleEl.textContent = mainLine;
      subEl.textContent = subLine;
    } catch (err) {
      selected = { lat, lng, address: `${lat.toFixed(6)}, ${lng.toFixed(6)}` };
      titleEl.textContent = 'Lokasi dipilih';
      subEl.textContent = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;
    }
  }

  function moveMarker(lat, lng, recenter = true) {
    marker.setLatLng([lat, lng]);
    if (recenter) map.setView([lat, lng], map.getZoom());
    reverseGeocode(lat, lng);
  }

  marker.on('dragend', function () {
    const pos = marker.getLatLng();
    reverseGeocode(pos.lat, pos.lng);
  });

  map.on('click', function (e) {
    moveMarker(e.latlng.lat, e.latlng.lng, false);
  });

  // ---- Search alamat (debounced, section 34) ----
  searchInput.addEventListener('input', function () {
    clearTimeout(debounceTimer);
    const q = searchInput.value.trim();
    if (q.length < 3) return;
    debounceTimer = setTimeout(async () => {
      try {
        const res = await fetch(`https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(q)}&limit=1&countrycodes=id`);
        const results = await res.json();
        if (results.length > 0) {
          const r = results[0];
          moveMarker(parseFloat(r.lat), parseFloat(r.lon), true);
        }
      } catch (err) {
        showToast('Pencarian lokasi gagal. Coba lagi.', 'error');
      }
    }, 500);
  });

  // ---- Lokasi saya ----
  locateBtn.addEventListener('click', function () {
    if (!navigator.geolocation) {
      showToast('Perangkat Anda tidak mendukung geolokasi.', 'error');
      return;
    }
    navigator.geolocation.getCurrentPosition(function (pos) {
      moveMarker(pos.coords.latitude, pos.coords.longitude, true);
    }, function () {
      showToast('Gagal mengambil lokasi saat ini.', 'error');
    });
  });

  editBtn.addEventListener('click', function () {
    const custom = prompt('Edit detail alamat:', selected.address || '');
    if (custom !== null && custom.trim() !== '') {
      selected.address = custom.trim();
      titleEl.textContent = custom.trim();
      subEl.textContent = '';
    }
  });

  confirmBtn.addEventListener('click', function () {
    if (!selected.lat || !selected.lng) {
      showToast('Silakan pilih lokasi terlebih dahulu.', 'error');
      return;
    }
    sessionStorage.setItem('lw_selected_location', JSON.stringify({
      latitude: selected.lat,
      longitude: selected.lng,
      address: selected.address,
    }));
    window.location.href = window.BASE_URL + '/masyarakat/buat-laporan.php';
  });

  // Inisialisasi: coba lokasi user secara otomatis saat pertama buka.
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(function (pos) {
      moveMarker(pos.coords.latitude, pos.coords.longitude, true);
    }, function () {
      reverseGeocode(DEFAULT_CENTER[0], DEFAULT_CENTER[1]);
    }, { timeout: 4000 });
  } else {
    reverseGeocode(DEFAULT_CENTER[0], DEFAULT_CENTER[1]);
  }
});
