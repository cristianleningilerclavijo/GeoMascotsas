let token = null;
let mapsReady = false;
let profileLoaded = false;
let firebaseListenersAttached = false;

function tryStartLiveTracking() {
  if (mapsReady && profileLoaded && !firebaseListenersAttached) {
    firebaseListenersAttached = true;
    attachFirebaseListeners();
  }
}

window.onGoogleMapsReady = () => {
  mapsReady = true;
  tryStartLiveTracking();
};

function showNotFound(message) {
  document.getElementById('loading-state').classList.add('hidden');
  const box = document.getElementById('not-found-state');
  box.querySelector('.error-box').textContent = message;
  box.classList.remove('hidden');
}

function renderProfile(pet, owner) {
  document.getElementById('loading-state').classList.add('hidden');
  document.getElementById('profile-content').classList.remove('hidden');

  if (pet.status === 'perdido') {
    document.getElementById('lost-banner').classList.remove('hidden');
  }

  document.getElementById('photo-container').innerHTML = petPhotoHtml(pet, 'pet-photo');
  document.getElementById('pet-name').textContent = pet.name;
  document.getElementById('pet-species').textContent = pet.species;
  document.getElementById('pet-breed').textContent = pet.breed || '—';
  document.getElementById('pet-color').textContent = pet.color || '—';
  document.getElementById('pet-medical').textContent = pet.medical_notes || 'Sin notas médicas';
  document.getElementById('owner-name').textContent = owner.full_name;
  document.getElementById('owner-phone').textContent = owner.phone;
}

function logScan(petToken) {
  Api.post('/public/scans', { token: petToken }).catch(() => {});
}

async function loadProfile() {
  token = new URLSearchParams(window.location.search).get('token');

  if (!token) {
    showNotFound('Falta el código en el enlace. Escanea el QR de la placa nuevamente.');
    return;
  }

  try {
    const { pet, owner_contact } = await Api.get(`/public/pets/${token}`);
    renderProfile(pet, owner_contact);
    logScan(token);
    profileLoaded = true;
    tryStartLiveTracking();
  } catch (err) {
    showNotFound(err.message);
  }
}

function attachFirebaseListeners() {
  const statusEl = document.getElementById('map-status');

  db.ref(`locations/${token}/current`).on('value', (snap) => {
    const loc = snap.val();
    if (!loc) return;

    MapaGeoMascotas.setMarkerPosition(loc.lat, loc.lng);
    const time = loc.timestamp ? new Date(loc.timestamp).toLocaleTimeString() : '';
    statusEl.textContent = `Última actualización: ${time}`;
  });

  db.ref(`locations/${token}/history`).limitToLast(200).on('child_added', (snap) => {
    const point = snap.val();
    if (point) MapaGeoMascotas.addHistoryPoint(point.lat, point.lng);
  });
}

document.addEventListener('DOMContentLoaded', () => {
  loadGoogleMaps('onGoogleMapsReady');
  loadProfile();
});
