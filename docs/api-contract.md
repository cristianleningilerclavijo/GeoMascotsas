# Contrato de API — GeoMascotas

Base URL local: `http://localhost/geomascotas/backend/api`

## Sobre de respuesta (todos los endpoints)

Todas las respuestas usan el mismo sobre y pasan por `backend/helpers/response.php`:

```json
{ "success": true, "data": { ... }, "error": null }
```

En error: `{ "success": false, "data": null, "error": "mensaje" }` con el código HTTP correspondiente (400, 401, 404, 409, 500).

## Formato JSON/XML

Cualquier endpoint acepta `?format=json` (default) o `?format=xml`. Sin el parámetro, se usa el header `Accept` (si contiene `xml` y no `json`, responde XML). El body es igual en ambos, solo cambia el envoltorio:

```xml
<response>
  <success>true</success>
  <data>...</data>
  <error/>
</response>
```

## Autenticación

Sesión nativa de PHP (`$_SESSION['owner_id']`), cookie de sesión compartida porque frontend y backend se sirven desde el mismo origen (`http://localhost/geomascotas/...` vía el junction de XAMPP). El frontend debe llamar `fetch(url, { credentials: 'same-origin', ... })` explícitamente.

## Endpoints

| Método | Ruta | Auth | Body / Query |
|---|---|---|---|
| POST | `/auth/register` | No | `{ full_name, email, password, phone }` |
| POST | `/auth/login` | No | `{ email, password }` |
| POST | `/auth/logout` | Sesión | — |
| GET | `/pets` | Sesión | — (lista las del owner en sesión) |
| POST | `/pets` | Sesión | `multipart/form-data`: `name, species, breed, color, medical_notes, qr_token, photo?` |
| GET | `/pets/{id}` | Sesión | — |
| PUT | `/pets/{id}` | Sesión | JSON: campos editables de `pets` |
| DELETE | `/pets/{id}` | Sesión | — |
| POST | `/pets/{id}/qr` | Sesión | — (desactiva el token viejo, crea uno nuevo, lo retorna) |
| GET | `/pets/{id}/scans` | Sesión | — (historial de `scan_logs`) |
| GET | `/public/pets/{token}` | **No** | — (ficha pública) |
| POST | `/public/scans` | **No** | `{ token }` (fire-and-forget al abrir `perfil.html`) |

**Regla de diseño**: rutas privadas usan el `id` numérico (protegido por `WHERE owner_id = sesión`); rutas públicas usan el `token` UUID opaco. Nunca se expone un `id` numérico en una URL pública.

## Forma exacta del objeto `pet`

Este es el contrato que el frontend da por hecho — cualquier cambio de nombre de campo aquí rompe `perfil.js`, `dashboard.js` y `mascota-form.js`:

```json
{
  "id": 12,
  "name": "Firulais",
  "species": "perro",
  "breed": "Mestizo",
  "color": "Café y blanco",
  "photo_url": "/geomascotas/backend/uploads/pets/12_firulais.jpg",
  "medical_notes": "Alérgico a la penicilina",
  "status": "perdido",
  "qr_token": "3f9a1c2e-8b7d-4e21-9c3a-1a2b3c4d5e6f"
}
```

- `photo_url` es siempre una ruta **relativa a la raíz del sitio** (empieza con `/geomascotas/...`), lista para usar directo en un `<img src>`.
- `qr_token` es el campo dentro del JSON. `token` es el nombre del **query param** en la URL pública (`perfil.html?token=...`). Son dos nombres distintos a propósito — no confundirlos.

## Endpoint bloqueante #1: `GET /public/pets/{token}`

Es la dependencia que el frontend necesita primero para poder construir `perfil.js` en paralelo. Respuesta esperada:

```json
{
  "success": true,
  "data": {
    "pet": {
      "id": 12, "name": "Firulais", "species": "perro", "breed": "Mestizo",
      "color": "Café y blanco", "photo_url": "/geomascotas/backend/uploads/pets/12_firulais.jpg",
      "medical_notes": "Alérgico a la penicilina", "status": "perdido",
      "qr_token": "3f9a1c2e-8b7d-4e21-9c3a-1a2b3c4d5e6f"
    },
    "owner_contact": { "full_name": "Cristian Giler", "phone": "0991234567" }
  },
  "error": null
}
```

404 si el token no existe o `is_active = 0`: `{ "success": false, "data": null, "error": "Token no encontrado o inactivo" }`.

## Dato importante para quien implemente Firebase (frontend)

El **mismo `token`** de esta tabla es la clave que se usa en Firebase Realtime Database (`/locations/{token}/current`). La API PHP no sabe nada de Firebase — el frontend une ambas fuentes solo porque tiene el mismo string en la URL.
