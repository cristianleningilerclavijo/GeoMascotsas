let petId = null;
let currentToken = null;

function getQueryParam(name) {
  return new URLSearchParams(window.location.search).get(name);
}

function renderQr(token) {
  const container = document.getElementById('qrcode');
  container.innerHTML = '';

  // eslint-disable-next-line no-undef
  new QRCode(container, {
    text: `${CONFIG.PUBLIC_BASE_URL}/perfil.html?token=${token}`,
    width: 220,
    height: 220,
  });

  document.getElementById('qr-section').classList.remove('hidden');
  document.getElementById('link-ficha').href = `${CONFIG.PUBLIC_BASE_URL}/perfil.html?token=${token}`;
  document.getElementById('link-simulador').href = `simulador.html?token=${token}`;
}

function downloadQr() {
  const container = document.getElementById('qrcode');
  const canvas = container.querySelector('canvas');
  const link = document.createElement('a');
  link.download = 'geomascotas-qr.png';
  link.href = canvas ? canvas.toDataURL('image/png') : container.querySelector('img').src;
  link.click();
}

async function loadPetForEdit(id) {
  try {
    const { pet } = await Api.get(`/pets/${id}`);

    document.getElementById('name').value = pet.name;
    document.getElementById('species').value = pet.species;
    document.getElementById('breed').value = pet.breed || '';
    document.getElementById('color').value = pet.color || '';
    document.getElementById('medical_notes').value = pet.medical_notes || '';
    document.getElementById('status').value = pet.status;

    document.getElementById('photo-field').classList.add('hidden');
    document.getElementById('status-field').classList.remove('hidden');
    document.getElementById('regenerate-qr-btn').classList.remove('hidden');
    document.getElementById('form-title').textContent = `Editar a ${pet.name}`;

    currentToken = pet.qr_token;
    renderQr(currentToken);
  } catch (err) {
    alert('Error cargando la mascota: ' + err.message);
    window.location.href = 'dashboard.html';
  }
}

async function handleSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const errorBox = document.getElementById('error-box');
  hideError(errorBox);

  try {
    if (petId) {
      await Api.put(`/pets/${petId}`, {
        name: form.name.value.trim(),
        species: form.species.value,
        breed: form.breed.value.trim(),
        color: form.color.value.trim(),
        medical_notes: form.medical_notes.value.trim(),
        status: form.status.value,
      });
      alert('Cambios guardados');
      return;
    }

    const fd = new FormData();
    fd.append('name', form.name.value.trim());
    fd.append('species', form.species.value);
    fd.append('breed', form.breed.value.trim());
    fd.append('color', form.color.value.trim());
    fd.append('medical_notes', form.medical_notes.value.trim());
    fd.append('qr_token', currentToken);

    const photoFile = document.getElementById('photo').files[0];
    if (photoFile) fd.append('photo', photoFile);

    const { pet } = await Api.post('/pets', fd, true);

    petId = pet.id;
    document.getElementById('form-title').textContent = `Editar a ${pet.name}`;
    document.getElementById('photo-field').classList.add('hidden');
    document.getElementById('status-field').classList.remove('hidden');
    document.getElementById('status').value = pet.status;
    document.getElementById('regenerate-qr-btn').classList.remove('hidden');
    renderQr(pet.qr_token);
    history.replaceState(null, '', `mascota-form.html?id=${pet.id}`);
  } catch (err) {
    showError(errorBox, err.message);
  }
}

async function handleRegenerateQr() {
  if (!confirm('El código QR actual dejará de funcionar en cuanto generes uno nuevo. ¿Continuar?')) return;

  const newToken = generateUUID();
  try {
    await Api.post(`/pets/${petId}/qr`, { qr_token: newToken });
    currentToken = newToken;
    renderQr(currentToken);
  } catch (err) {
    alert('Error regenerando el QR: ' + err.message);
  }
}

document.addEventListener('DOMContentLoaded', () => {
  petId = getQueryParam('id');

  document.getElementById('mascota-form').addEventListener('submit', handleSubmit);
  document.getElementById('download-qr-btn').addEventListener('click', downloadQr);
  document.getElementById('regenerate-qr-btn').addEventListener('click', handleRegenerateQr);

  if (petId) {
    loadPetForEdit(petId);
  } else {
    currentToken = generateUUID();
  }
});
