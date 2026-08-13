function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str ?? '';
  return div.innerHTML;
}

function petPhotoHtml(pet, sizeClass = 'pet-photo') {
  if (pet.photo_url) {
    return `<img class="${sizeClass}" src="${escapeHtml(pet.photo_url)}" alt="${escapeHtml(pet.name)}">`;
  }
  const emoji = pet.species === 'gato' ? '🐱' : pet.species === 'perro' ? '🐶' : '🐾';
  return `<div class="${sizeClass} pet-photo-placeholder">${emoji}</div>`;
}

function statusLabel(status) {
  return { activo: 'Activo', perdido: 'Perdido', recuperado: 'Recuperado' }[status] || status;
}
