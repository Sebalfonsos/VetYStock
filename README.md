# Laudi Vet & Stock

Proyecto académico MVC en `PHP 8.2 + MySQL 8 + Apache/XAMPP + Bootstrap 5`.

## Requisitos

- PHP 8.2
- MySQL 8
- Apache/XAMPP
- GitHub

## Acceso local

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
- Útil para consumo desde un chatbot externo

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
- También puedes abrir DevTools y ver las llamadas HTTP a `8101`, `8102`, `8103` y `8104`

## Flujo Git sugerido

- `main`
- `sebastian-rodriguez`
- `sebastian-sierra`
- `juan-gomez`

Cada quien trabaja en su rama personal y se integra a `main` al cierre de cada sprint.
