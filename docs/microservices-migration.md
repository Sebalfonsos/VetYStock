# Plan de migración a microservicios

Este plan parte del monolito actual de VetYStock y lo divide en 4 dominios:

1. **Autenticación y usuarios**
2. **Catálogo**
3. **Inventario**
4. **Animales / pacientes**

## Objetivo

Separar el sistema por límites de negocio para que cada dominio pueda desplegarse, escalarse y evolucionar de forma independiente.

## Orden de migración

### 1) Catálogo

**Incluye**
- Categorías
- Productos

**Por qué primero**
- Tiene frontera clara.
- Ya expone lectura pública de productos.
- Tiene menor acoplamiento que inventario o autenticación.

**Primeros pasos**
- Extraer la consulta de productos a una capa de servicio.
- Crear un endpoint standalone para catálogo.
- Mantener el monolito consumiendo la misma lógica durante la transición.

### 2) Autenticación y usuarios

**Incluye**
- Login
- Usuarios
- Roles

**Por qué después**
- Tiene impacto transversal.
- Requiere estrategia de sesión o token.
- Conviene moverlo cuando ya exista una forma estable de consumir servicios.

### 3) Animales / pacientes

**Incluye**
- Animales
- Propietarios
- Especies

**Por qué después**
- Es un dominio central para clínica.
- Tiene relaciones con varios módulos.

### 4) Inventario

**Incluye**
- Movimientos de stock
- Alertas

**Por qué al final**
- Tiene reglas transaccionales.
- Depende de productos.
- Es el dominio más sensible a consistencia.

## Criterios de separación

- Cada microservicio debe ser dueño de su propia base de datos o esquema.
- El monolito no debe hacer joins directos entre dominios una vez migrados.
- Las vistas agregadas deben convertirse en consultas compuestas o proyecciones.
- Los cambios deben entrar por API, no por acceso directo a tablas ajenas.

## Primer entregable

La primera pieza de la migración será **Catálogo**:
- un servicio standalone para consultar productos y categorías,
- una capa de lectura compartida para no duplicar lógica,
- y una transición gradual desde el endpoint actual del monolito.
