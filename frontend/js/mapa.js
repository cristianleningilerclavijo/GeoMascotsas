// Envuelve Google Maps detrás de una interfaz mínima. Para cambiar a Leaflet/OpenStreetMap
// (si no se puede asociar tarjeta a Google Cloud Billing) solo hay que reescribir este archivo;
// perfil.js y simulador.js no conocen la API de mapas subyacente.
const MapaGeoMascotas = (() => {
  let map = null;
  let marker = null;
  let polyline = null;
  const path = [];

  function ensureMap(lat, lng) {
    if (map) return;

    map = new google.maps.Map(document.getElementById('map'), {
      center: { lat, lng },
      zoom: 16,
    });

    marker = new google.maps.Marker({
      position: { lat, lng },
      map,
      title: 'Última ubicación conocida',
    });

    polyline = new google.maps.Polyline({
      path,
      geodesic: true,
      strokeColor: '#2563eb',
      strokeOpacity: 0.85,
      strokeWeight: 3,
    });
    polyline.setMap(map);
  }

  function setMarkerPosition(lat, lng) {
    ensureMap(lat, lng);
    const pos = { lat, lng };
    marker.setPosition(pos);
    map.panTo(pos);
  }

  function addHistoryPoint(lat, lng) {
    path.push({ lat, lng });
    if (polyline) polyline.setPath(path);
  }

  return { setMarkerPosition, addHistoryPoint };
})();

function loadGoogleMaps(callbackName) {
  const script = document.createElement('script');
  script.src = `https://maps.googleapis.com/maps/api/js?key=${CONFIG.GOOGLE_MAPS_API_KEY}&callback=${callbackName}`;
  script.async = true;
  document.head.appendChild(script);
}
