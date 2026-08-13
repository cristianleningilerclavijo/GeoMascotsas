# GeoMascotas

Localizador de mascotas con QR único + ubicación GPS en vivo. Cada mascota tiene un código QR: si se pierde y alguien lo escanea, ve una ficha pública con sus datos y su última ubicación conocida en un mapa, actualizada casi al instante.

Ver `docs/arquitectura.md` para el diseño completo y `docs/api-contract.md` para el contrato de la API.

## Stack

- **Frontend**: HTML/CSS/JS puro (sin build step) + Firebase Web SDK + Google Maps JavaScript API + qrcodejs.
- **Backend**: PHP + PDO (sin framework) sobre XAMPP/Apache, con salida dual JSON/XML.
- **Base de datos**: MySQL (probado contra MySQL Server 8.0 + Workbench).
- **Tiempo real**: Firebase Realtime Database (sin servidor WebSocket propio — el SDK ya usa WebSocket internamente).

## Setup (una sola vez por integrante)

### 1. Servir el proyecto con Apache (sin CORS)

Crear un *directory junction* apuntando este repo hacia `htdocs` (no requiere permisos de administrador):

```powershell
New-Item -ItemType Junction -Path "C:\xampp\htdocs\geomascotas" -Target "C:\ruta\a\GeoMascotsas"
```

Encender Apache desde el panel de XAMPP (o `C:\xampp\apache_start.bat`). El sitio queda en `http://localhost/geomascotas/frontend/public/`.

### 2. Base de datos MySQL

Con MySQL Workbench (o `mysql` por línea de comandos), ejecutar en orden:

```
sql/geomascotas_schema.sql
sql/geomascotas_seed.sql
```

Ajustar credenciales en `backend/config/config.php` (`DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS`) según tu instalación de MySQL. Usuario de prueba tras el seed: `demo@geomascotas.test` / `demo1234`.

### 3. Firebase (tiempo real)

1. Crear proyecto en https://console.firebase.google.com
2. Build → Realtime Database → crear base de datos.
3. Reglas de seguridad (pestaña "Reglas"): copiar el bloque de `docs/arquitectura.md`.
4. Authentication → Sign-in method → habilitar **Anónimo**.
5. Project settings → "Tus apps" → registrar app Web (`</>`) → copiar el objeto de configuración a `frontend/js/firebase-config.js` (reemplazar los valores `TU_...`).

### 4. Google Maps

1. Crear/usar un proyecto en https://console.cloud.google.com y habilitar **Maps JavaScript API**.
2. Requiere asociar una cuenta de facturación (tarjeta) — Google lo exige incluso para el uso gratuito; el volumen de esta demo no debería generar cobro real. Configura una alerta de presupuesto de USD 1 como red de seguridad.
3. Crear una API key, restringirla a `localhost/*`, y pegarla en `frontend/js/config.js` (`GOOGLE_MAPS_API_KEY`).
4. **Alternativa 100% gratuita sin tarjeta**: reemplazar la implementación de `frontend/js/mapa.js` por Leaflet.js + tiles de OpenStreetMap. `perfil.js` y `simulador.js` no necesitan cambios porque solo usan `MapaGeoMascotas.setMarkerPosition()` / `.addHistoryPoint()`.

## Probar el flujo completo

1. Abrir `http://localhost/geomascotas/frontend/public/registro.html` y crear una cuenta (o usar el usuario demo del seed).
2. En el dashboard, crear una mascota — se genera su QR automáticamente.
3. Click en "Simular GPS" (en una pestaña) e "Iniciar simulación".
4. Click en "Ficha pública" (en otra pestaña) — el marcador debe moverse solo cada ~5 segundos.
5. Escanear el QR descargado con el celular (misma red / IP LAN) para ver la ficha pública desde un dispositivo real.

## Estructura

```
frontend/   HTML/CSS/JS — páginas, Firebase, Google Maps, QR, simulador
backend/    API REST en PHP (auth, CRUD de mascotas, endpoints públicos)
sql/        Esquema y datos de prueba de MySQL
docs/       Arquitectura y contrato de API
```

## Notas

- Las credenciales de `backend/config/config.php` no son secretas en este contexto académico (base de datos local de desarrollo).
- `frontend/js/firebase-config.js` con placeholders `TU_...` no es un archivo secreto — lo que protege los datos son las reglas de seguridad de Firebase, no ocultar esos valores.
