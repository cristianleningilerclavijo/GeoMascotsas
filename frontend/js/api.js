const Api = {
  async request(path, { method = 'GET', body = null, isFormData = false } = {}) {
    const options = { method, credentials: 'same-origin', headers: {} };

    if (body !== null) {
      if (isFormData) {
        options.body = body;
      } else {
        options.headers['Content-Type'] = 'application/json';
        options.body = JSON.stringify(body);
      }
    }

    const res = await fetch(CONFIG.API_BASE_URL + path, options);

    if (res.status === 401 && !path.startsWith('/auth/')) {
      window.location.href = 'login.html';
      return new Promise(() => {});
    }

    const json = await res.json();

    if (!json.success) {
      throw new Error(json.error || 'Error desconocido');
    }

    return json.data;
  },

  get(path) {
    return this.request(path);
  },
  post(path, body, isFormData = false) {
    return this.request(path, { method: 'POST', body, isFormData });
  },
  put(path, body) {
    return this.request(path, { method: 'PUT', body });
  },
  del(path) {
    return this.request(path, { method: 'DELETE' });
  },
};
