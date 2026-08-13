const START_LAT = -0.180653;
const START_LNG = -78.467838;
const STEP_DEGREES = 0.0004; // aprox. 40-45 metros por salto
const TICK_MS = 5000;
const MAX_HISTORY_POINTS = 200;

let token = null;
let intervalId = null;
let lastLat = START_LAT;
let lastLng = START_LNG;
let tickCount = 0;
let authReady = false;

function randomStep(value) {
  return value + (Math.random() - 0.5) * STEP_DEGREES * 2;
}

async function ensureAuth() {
  if (authReady) return;
  await firebase.auth().signInAnonymously();
  authReady = true;
}

async function currentStartingPoint() {
  const snap = await db.ref(`locations/${token}/current`).once('value');
  const loc = snap.val();
  return loc ? { lat: loc.lat, lng: loc.lng } : { lat: START_LAT, lng: START_LNG };
}

async function trimHistory() {
  const snap = await db.ref(`locations/${token}/history`).once('value');
  const keys = [];
  snap.forEach((child) => {
    keys.push(child.key);
  });

  if (keys.length > MAX_HISTORY_POINTS) {
    const updates = {};
    keys.slice(0, keys.length - MAX_HISTORY_POINTS).forEach((k) => {
      updates[k] = null;
    });
    await db.ref(`locations/${token}/history`).update(updates);
  }
}

function updateStatusUI(point) {
  document.getElementById('tick-count').textContent = tickCount;
  document.getElementById('last-coords').textContent = `${point.lat.toFixed(6)}, ${point.lng.toFixed(6)}`;
  document.getElementById('last-time').textContent = new Date(point.timestamp).toLocaleTimeString();
}

async function tick() {
  lastLat = randomStep(lastLat);
  lastLng = randomStep(lastLng);
  tickCount += 1;

  const point = { lat: lastLat, lng: lastLng, accuracy: 5 + Math.random() * 10, timestamp: Date.now() };

  await db.ref(`locations/${token}/current`).set(point);
  await db.ref(`locations/${token}/history`).push(point);

  updateStatusUI(point);
  trimHistory();
}

async function startSimulation() {
  await ensureAuth();

  const start = await currentStartingPoint();
  lastLat = start.lat;
  lastLng = start.lng;
  tickCount = 0;

  document.getElementById('start-btn').classList.add('hidden');
  document.getElementById('stop-btn').classList.remove('hidden');
  document.getElementById('sim-dot').classList.add('live');
  document.getElementById('sim-text').textContent = 'Simulación activa — moviéndose cada 5 segundos';

  await tick();
  intervalId = setInterval(tick, TICK_MS);
}

function stopSimulation() {
  clearInterval(intervalId);
  intervalId = null;

  document.getElementById('start-btn').classList.remove('hidden');
  document.getElementById('stop-btn').classList.add('hidden');
  document.getElementById('sim-dot').classList.remove('live');
  document.getElementById('sim-text').textContent = 'Simulación detenida';
}

document.addEventListener('DOMContentLoaded', () => {
  token = new URLSearchParams(window.location.search).get('token');

  if (!token) {
    document.getElementById('no-token-state').classList.remove('hidden');
    document.getElementById('simulator-controls').classList.add('hidden');
    return;
  }

  document.getElementById('public-link').href = `perfil.html?token=${token}`;
  document.getElementById('start-btn').addEventListener('click', startSimulation);
  document.getElementById('stop-btn').addEventListener('click', stopSimulation);
});
