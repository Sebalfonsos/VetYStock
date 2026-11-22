# Laudi Vet & Stock

Proyecto académico MVC en `PHP 8.2 + MySQL 8 + Apache/XAMPP + Bootstrap 5`.

## Requisitos

- PHP 8.2
- MySQL 8
- Apache/XAMPP
- GitHub

## Arranque con Docker (recomendado)

Solo necesitas Docker Desktop. Cada microservicio corre en su propio contenedor:

| Contenedor | Qué es | URL en tu equipo |
|---|---|---|
| `main` | Proyecto principal | `http://localhost:8000` |
| `auth` | Autenticación, usuarios y roles | `http://localhost:8101/?route=health` |
| `catalog` | Categorías y productos | `http://localhost:8102/?route=health` |
| `inventory` | Movimientos de stock y alertas | `http://localhost:8103/?route=health` |
| `animals` | Animales, propietarios y especies | `http://localhost:8104/?route=health` |
| `assistant` | Asistente veterinario con IA local | `http://localhost:8105/?route=health` |
| `db` | MySQL 8.4 con `database/schema.sql` | `localhost:3307` (usuario `root`) |

1. Levantar todo:
   ```bash
   docker compose up -d --build
   ```
2. Ver el estado de los contenedores:
   ```bash
   docker compose ps
   ```
3. Abrir `http://localhost:8000` e iniciar sesión con las credenciales de prueba.
4. Apagar todo:
   ```bash
   docker compose down
   ```
   Para borrar también los datos y volver a cargar `database/schema.sql` desde cero:
   ```bash
   docker compose down -v
   ```

Para cambiar la contraseña de MySQL, el secreto de los tokens o el modelo de IA, copia `.env.example` como `.env` y edita los valores.

### Asistente veterinario (LM Studio)

El botón de chat abajo a la derecha, en todas las páginas, habla con el contenedor `assistant`. Ese servicio:

1. Pide el catálogo en vivo al servicio `catalog` (nombres, descripción, precio y unidades disponibles).
2. Se lo entrega al modelo junto con las instrucciones de `services/assistant/prompt.md`.
3. Le pregunta al modelo que corre en LM Studio, en tu equipo, fuera de Docker.
4. Detecta qué productos reales del catálogo nombró la respuesta y los muestra con su stock y precio.
5. Si la respuesta recomienda algo y el modelo olvidó el aviso con el número de contacto, lo agrega.

Requisitos en LM Studio:

- Cargar el modelo `openai/gpt-oss-20b` (o el que pongas en `LLM_MODEL`).
- En la pestaña Developer, encender el servidor en el puerto `1234`.

Si LM Studio está apagado, el chat responde "El asistente no está disponible" y el resto del sistema sigue funcionando. Con `openai/gpt-oss-20b` cada respuesta tarda entre 15 y 60 segundos según tu tarjeta gráfica; `qwen2.5-7b-instruct` es más rápido pero recomienda peor.

### Cómo demostrar que son microservicios en Docker

1. Ver en vivo las peticiones que el proyecto principal le hace a cada servicio:
   ```bash
   docker compose logs -f auth catalog inventory animals
   ```
   Al abrir el dashboard aparecen líneas como `GET /?route=products`, que vienen del contenedor `main`.
2. Apagar un servicio y recargar el dashboard o el inventario:
   ```bash
   docker compose stop catalog
   ```
   El resto del sistema sigue funcionando, los datos de ese servicio bajan a `0` y el inventario muestra el aviso "No se pudo conectar con catalog".
3. Volver a encenderlo:
   ```bash
   docker compose start catalog
   ```

## Acceso local (sin Docker)

1. Crear la base de datos `laudi`
2. Importar `database/schema.sql`
3. Verificar en `app/config/database.php`:
   - usuario: `root`
   - contraseña: `root`
4. Ejecutar:
   ```bash
   php -S localhost:8000 -t public
   ```
5. Abrir:
   - `http://localhost:8000/index.php?route=auth/login`

## Credenciales de prueba

- Correo: `admin@laudi.test`
- Contraseña: `Admin1234!`

## Módulos

- Autenticación y roles
- Dashboard
- Usuarios
- Categorías
- Productos
- Inventario
- Animales
- Tratamientos
- Vacunas
- Observaciones
- Historial médico

## Rutas principales

- `auth/login`
- `dashboard/index`
- `users/index`
- `categories/index`
- `products/index`
- `inventory/index`
- `animals/index`
- `treatments/index`
- `vaccines/index`
- `observations/index`
- `medical-history/index`

## API REST

- `GET /public/index.php?route=api/products`
- Respuesta JSON con todos los productos y su categoría

## Microservicios

El proyecto ya queda preparado para ejecutar estos 4 servicios independientes:

- **Auth**: autenticación, usuarios y roles
- **Catalog**: categorías y productos
- **Inventory**: movimientos de stock y alertas
- **Animals**: animales, propietarios y especies

### Arranque local

1. Verificar que `app/config/database.php` apunte a tu MySQL.
2. Ejecutar el script de arranque:
   ```powershell
   .\scripts\start-microservices.ps1
   ```
   O por separado:
   ```powershell
   .\scripts\start-microservices.ps1 -Services auth, catalog
   .\scripts\start-microservices.ps1 -Services main
   .\scripts\start-microservices.ps1 -Services inventory -Force
   ```
3. Abrir cada servicio en su puerto:
   - Proyecto principal: `http://localhost:8000/index.php?route=auth/login`
   - Auth: `http://localhost:8101/?route=health`
   - Catalog: `http://localhost:8102/?route=health`
   - Inventory: `http://localhost:8103/?route=health`
   - Animals: `http://localhost:8104/?route=health`
4. Para detener todo:
   ```powershell
   .\scripts\stop-microservices.ps1
   ```
   O por partes:
   ```powershell
   .\scripts\stop-microservices.ps1 -Services auth
   .\scripts\stop-microservices.ps1 -Services main, inventory
   ```

### Rutas principales

- Auth: `login`, `me`, `users`, `roles`
- Catalog: `categories`, `products`
- Inventory: `movements`, `alerts`
- Animals: `animals`, `owners`, `species`

### Ejemplos rápidos

- Login:
  - `POST http://localhost:8101/?route=login`
- Listar productos:
  - `GET http://localhost:8102/?route=products`
- Crear movimiento:
  - `POST http://localhost:8103/?route=movements`
- Listar animales:
  - `GET http://localhost:8104/?route=animals`

### Cómo demostrar que el proyecto principal usa microservicios

- Abrir `http://localhost:8000/index.php?route=home/index`
- Abrir `http://localhost:8000/index.php?route=dashboard/index` después de iniciar sesión
- Verás que esas pantallas leen datos desde los servicios `auth`, `catalog`, `inventory` y `animals`
- Si detienes un servicio, su bloque de datos baja a `0` o devuelve error controlado
- Las llamadas a `8101`, `8102`, `8103` y `8104` las hace el servidor PHP, no el navegador, así que no aparecen en DevTools

## Flujo Git sugerido

- `main`
- `sebastian-rodriguez`
- `sebastian-sierra`
- `juan-gomez`

Cada quien trabaja en su rama personal y se integra a `main` al cierre de cada sprint.
