function showError(el, message) {
  el.textContent = message;
  el.classList.remove('hidden');
}

function hideError(el) {
  el.textContent = '';
  el.classList.add('hidden');
}

function saveOwnerSession(owner) {
  localStorage.setItem('geomascotas_owner', JSON.stringify(owner));
}

function clearOwnerSession() {
  localStorage.removeItem('geomascotas_owner');
}

function displayOwnerInfo() {
  const el = document.getElementById('owner-email');
  if (!el) return;

  const raw = localStorage.getItem('geomascotas_owner');
  if (!raw) return;

  try {
    el.textContent = JSON.parse(raw).email;
  } catch (e) {
    // sesión guardada con un formato viejo/corrupto: se ignora, no es crítico para mostrar la UI
  }
}

function wireLoginForm() {
  const form = document.getElementById('login-form');
  const errorBox = document.getElementById('error-box');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideError(errorBox);

    const email = form.email.value.trim();
    const password = form.password.value;

    try {
      const { owner } = await Api.post('/auth/login', { email, password });
      saveOwnerSession(owner);
      window.location.href = 'dashboard.html';
    } catch (err) {
      showError(errorBox, err.message);
    }
  });
}

function wireRegisterForm() {
  const form = document.getElementById('register-form');
  const errorBox = document.getElementById('error-box');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    hideError(errorBox);

    const payload = {
      full_name: form.full_name.value.trim(),
      email: form.email.value.trim(),
      password: form.password.value,
      phone: form.phone.value.trim(),
    };

    try {
      const { owner } = await Api.post('/auth/register', payload);
      saveOwnerSession(owner);
      window.location.href = 'dashboard.html';
    } catch (err) {
      showError(errorBox, err.message);
    }
  });
}

function wireLogoutButton() {
  const btn = document.getElementById('logout-btn');
  if (!btn) return;

  btn.addEventListener('click', async () => {
    try {
      await Api.post('/auth/logout', {});
    } finally {
      clearOwnerSession();
      window.location.href = 'login.html';
    }
  });
}

document.addEventListener('DOMContentLoaded', () => {
  wireLoginForm();
  wireRegisterForm();
  wireLogoutButton();
  displayOwnerInfo();
});
