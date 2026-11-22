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
| `health` | Tratamientos, vacunas, observaciones e historial médico | `http://localhost:8106/?route=health` |
| `notifications` | Avisos de vacunas, controles y stock bajo | `http://localhost:8107/?route=health` |
| `notifications-worker` | Revisa cada 5 minutos si hay avisos nuevos (sin puerto) | `docker compose logs -f notifications-worker` |
| `reports` | Reportes exportables y bitácora de auditoría | `http://localhost:8108/?route=health` |
| `cart` | Carrito de compras y pedidos | `http://localhost:8109/?route=health` |
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

### Salud animal (`health`)

Las pantallas de Tratamientos, Vacunas, Observaciones e Historial médico ya no tocan la base de datos: todo pasa por este servicio. El historial médico se puede filtrar por tipo de registro y rango de fechas, y muestra el responsable de cada evento.

### Notificaciones (`notifications` y `notifications-worker`)

El worker revisa cada 5 minutos (`NOTIFICATIONS_INTERVAL`, en segundos):

- vacunas cuya próxima dosis vence en los próximos 7 días o ya venció (`NOTIFICATIONS_DAYS`),
- controles de tratamientos en el mismo plazo,
- alertas de stock bajo del inventario.

Cada evento genera un solo aviso, aunque se revise muchas veces. Los avisos aparecen en el menú "Notificaciones" con un contador; el botón "Revisar ahora" lanza la revisión sin esperar al worker. Los avisos son de la finca, no de cada usuario: si uno los marca como leídos, quedan leídos para todos.

### Reportes y bitácora (`reports`)

Solo el rol Administrador ve el menú "Reportes". Incluye cinco reportes, todos exportables a CSV (se abre en Excel):

- Inventario actual, filtrable por categoría.
- Movimientos de inventario, por fechas y tipo.
- Historial por animal, por fechas y tipo de registro.
- Consultas al asistente: qué preguntó cada usuario y qué productos se le sugirieron.
- Bitácora del sistema: quién hizo qué, cuándo y desde qué IP.

Todos los servicios envían sus registros de auditoría a `reports`. Si ese servicio está apagado, las operaciones siguen funcionando pero esas acciones no quedan en la bitácora.

### Carrito y pedidos (`cart`)

Cualquier visitante, con o sin sesión, puede armar un carrito:

- En la Tienda, cada producto con existencias tiene "Agregar al carrito" con su cantidad.
- En el chat, cada producto recomendado tiene un botón para agregarlo y, si son varios, "Agregar todos al carrito".
- El menú muestra cuántas unidades hay en el carrito.

Precios y existencias siempre se consultan al catálogo en el momento; no se confía en lo que manda el navegador. No se puede pedir más de lo que hay en stock.

Al confirmar el pedido (nombre y teléfono, pago al recoger o recibir), el servicio le pide a `inventory` descontar cada producto con la referencia `PED-<número>`. Si alguno falla, devuelve lo ya descontado y el pedido no se crea. El personal ve los pedidos en el menú "Pedidos" y los marca como entregados o los cancela; al cancelar, los productos vuelven al inventario.

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
4. Ver el worker de notificaciones trabajando solo:
   ```bash
   docker compose logs -f notifications-worker
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

El proyecto ya queda preparado para ejecutar estos 8 servicios independientes:

- **Auth**: autenticación, usuarios y roles
- **Catalog**: categorías y productos
- **Inventory**: movimientos de stock y alertas
- **Animals**: animales, propietarios y especies
- **Assistant**: asistente veterinario con IA local
- **Health**: tratamientos, vacunas, observaciones e historial médico
- **Notifications**: avisos de vacunas, controles y stock bajo
- **Reports**: reportes y bitácora de auditoría

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
- Assistant: `chat`
- Health: `treatments`, `vaccines`, `observations`, `history`, `upcoming` (piden token)
- Notifications: `notifications`, `notifications/count`, `notifications/{id}/read`, `notifications/read-all`, `scan` (piden token)
- Reports: `audit`, `reports/inventory`, `reports/movements`, `reports/animal-history`, `reports/consultations`, `reports/audit` (piden token; los reportes, rol Administrador)
- Cart: `cart/{llave}`, `cart/{llave}/items`, `cart/{llave}/items/{producto}`, `cart/{llave}/checkout`, `orders`, `orders/{id}/status` (piden token; los pedidos, un usuario con sesión)

Sin Docker, el worker de notificaciones se arranca aparte con `php services/notifications/worker.php`.

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
