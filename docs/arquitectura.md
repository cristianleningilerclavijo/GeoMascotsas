# Arquitectura — GeoMascotas

```
[Navegador] --HTML/JS--> [Frontend estático: frontend/]
     |                         |
     | fetch() JSON/XML        | Firebase Web SDK (lectura/escritura directa)
     v                         v
[Backend PHP + PDO: backend/]   [Firebase Realtime Database]
     |                         ^
     v                         |
 [MySQL: geomascotas]    [simulador.html envía coordenadas]
 (owners, pets,
  qr_tokens, scan_logs)
```

## Principio de separación

- **MySQL** guarda todo lo estructurado y persistente: dueños, mascotas, tokens de QR, logs de escaneo.
- **Firebase Realtime Database** guarda *solo* la posición GPS en vivo, indexada por el **token UUID** del QR (no por el id numérico de la mascota).
- El backend PHP **nunca** llama a Firebase. El frontend **nunca** llama a MySQL directamente. Las dos mitades solo se conectan a través del `token`, lo que permite que cada integrante trabaja en su parte sin bloquear al otro.

## Por qué no hay servidor WebSocket propio

Firebase Realtime Database ya usa WebSocket internamente entre el SDK y los servidores de Google, y empuja los cambios a todos los clientes suscritos casi al instante. Montar un servidor WS propio (Node/`ws`, PHP Ratchet, Socket.IO) sería una pieza redundante y un proceso más que mantener corriendo.

## Por qué NO se usa `google-services.json`

Ese archivo es exclusivo del SDK de Firebase para **Android nativo** (se descarga al registrar una app Android con un `applicationId` en Firebase Console). GeoMascotas es una web responsive, así que en su lugar se usa un objeto JavaScript `firebaseConfig` (`apiKey`, `authDomain`, `databaseURL`, `projectId`, ...), obtenido al registrar una "Web app" (ícono `</>`) en Firebase Console, y pegado en `frontend/js/firebase-config.js`. Ese objeto no es secreto: lo que protege los datos son las *reglas de seguridad* de Firebase (ver abajo), no ocultar esas claves.

## Estructura de datos en Firebase Realtime Database

```
/locations/{token}/current   { lat, lng, accuracy, timestamp }
/locations/{token}/history/{pushId}   { lat, lng, timestamp }
```

- `current`: última posición conocida, para el marcador en el mapa (`onValue`).
- `history`: un punto por cada tick del simulador (`push`), para dibujar la polyline del recorrido. El simulador limita esto a los últimos ~200 puntos por mascota.
- Se indexa por `token` (no por `pet_id`) porque el frontend público ya tiene el token en la URL (`perfil.html?token=...`), sin necesitar una llamada extra a la API para "traducir" id→token, y porque no conviene exponer ids numéricos correlativos en una ruta pública.

### Reglas de seguridad recomendadas (Firebase Console → Realtime Database → Reglas)

```json
{
  "rules": {
    ".read": false,
    ".write": false,
    "locations": {
      "$token": {
        ".read": true,
        ".write": "auth != null",
        "current": { ".validate": "newData.hasChildren(['lat','lng','timestamp'])" }
      }
    }
  }
}
```

Lectura pública (cualquiera con el link del QR debe poder ver el mapa sin loguearse), escritura solo si el cliente hizo `signInAnonymously()` (gratis, sin tarjeta, una línea de JS — ver `frontend/js/firebase-config.js`).

## Setup manual requerido (fuera del alcance de este repo, lo hace cada integrante una vez)

1. **Firebase**: crear proyecto en https://console.firebase.google.com → Build → Realtime Database → crear base de datos (modo bloqueado, luego pegar las reglas de arriba) → Authentication → habilitar el proveedor "Anónimo" → Project settings → registrar app Web (`</>`) → copiar el objeto `firebaseConfig` en `frontend/js/firebase-config.js`.
2. **Google Maps**: crear proyecto en https://console.cloud.google.com → habilitar "Maps JavaScript API" → crear una API key → restringirla a `localhost/*` y al dominio final → pegarla en `frontend/js/config.js`. Requiere asociar una tarjeta de crédito a la cuenta de facturación (exigido por Google incluso para uso gratuito); para un volumen de demo académica no debería generar cobro real. Configurar una alerta de presupuesto de USD 1 en Google Cloud Billing como red de seguridad. Si no se cuenta con tarjeta, `frontend/js/mapa.js` puede reemplazarse por Leaflet.js + tiles de OpenStreetMap (gratis, sin key, sin tarjeta) sin tocar `perfil.js` ni `simulador.js`, porque ambos solo llaman a la interfaz `initMap/setMarkerPosition/addHistoryPoint`.
3. **XAMPP**: instalar, iniciar Apache y MySQL desde el panel de control. Crear el junction para servir este repo sin CORS:
   ```powershell
   New-Item -ItemType Junction -Path "C:\xampp\htdocs\geomascotas" -Target "C:\GeoMascotsas"
   ```
   Luego todo se sirve desde `http://localhost/geomascotas/...`.
4. **MySQL Workbench**: conectar a `127.0.0.1` (usuario `root`, sin password — default de XAMPP), ejecutar `sql/geomascotas_schema.sql` y `sql/geomascotas_seed.sql`, o importar el `.sql` desde phpMyAdmin.
