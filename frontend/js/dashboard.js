function updateStats(pets) {
  const counts = { activo: 0, perdido: 0, recuperado: 0 };
  pets.forEach((pet) => {
    if (counts[pet.status] !== undefined) counts[pet.status] += 1;
  });

  document.getElementById('stat-total').textContent = pets.length;
  document.getElementById('stat-activo').textContent = counts.activo;
  document.getElementById('stat-perdido').textContent = counts.perdido;
  document.getElementById('stat-recuperado').textContent = counts.recuperado;
}

async function loadPets() {
  const tableCard = document.getElementById('pets-table-card');
  const tableBody = document.getElementById('pets-table-body');
  const empty = document.getElementById('empty-state');

  try {
    const { pets } = await Api.get('/pets');
    updateStats(pets);

    if (pets.length === 0) {
      empty.classList.remove('hidden');
      tableCard.classList.add('hidden');
      tableBody.innerHTML = '';
      return;
    }

    empty.classList.add('hidden');
    tableCard.classList.remove('hidden');
    tableBody.innerHTML = pets.map(renderPetRow).join('');

    tableBody.querySelectorAll('[data-action="delete"]').forEach((btn) => {
      btn.addEventListener('click', () => handleDelete(btn.dataset.id, btn.dataset.name));
    });
  } catch (err) {
    alert('Error cargando mascotas: ' + err.message);
  }
}

function renderPetRow(pet) {
  const publicUrl = `${CONFIG.PUBLIC_BASE_URL}/perfil.html?token=${pet.qr_token}`;

  return `
    <tr>
      <td>
        <div class="table-pet-name">
          ${petPhotoHtml(pet)}
          ${escapeHtml(pet.name)}
        </div>
      </td>
      <td>${escapeHtml(pet.species)} · ${escapeHtml(pet.breed || 'Sin raza especificada')}</td>
      <td>${escapeHtml(pet.color || '—')}</td>
      <td><span class="badge badge-${pet.status}">${statusLabel(pet.status)}</span></td>
      <td>
        <div class="table-actions">
          <a class="btn btn-secondary btn-sm" href="mascota-form.html?id=${pet.id}">Editar / QR</a>
          <a class="btn btn-secondary btn-sm" href="simulador.html?token=${pet.qr_token}">Simular GPS</a>
          <a class="btn btn-secondary btn-sm" href="${publicUrl}" target="_blank" rel="noopener">Ficha pública</a>
          <button class="btn btn-danger btn-sm" data-action="delete" data-id="${pet.id}" data-name="${escapeHtml(pet.name)}">Eliminar</button>
        </div>
      </td>
    </tr>
  `;
}

async function handleDelete(id, name) {
  if (!confirm(`¿Eliminar a ${name}? Esta acción no se puede deshacer.`)) return;

  try {
    await Api.del(`/pets/${id}`);
    loadPets();
  } catch (err) {
    alert('Error eliminando: ' + err.message);
  }
}

document.addEventListener('DOMContentLoaded', loadPets);
