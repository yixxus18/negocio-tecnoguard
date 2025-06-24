# CRUD de Usuarios Administrativos - API Documentation

## Descripción General
Este documento describe los endpoints disponibles para el manejo de usuarios administrativos. Todos los endpoints requieren autenticación con token Bearer y permisos de Administrador (role_id: 1).

## Base URL
```
https://tu-dominio.com/api/v1/admin/users
```

## Headers Requeridos
```
Authorization: Bearer {token}
Content-Type: application/json
```

---

## 1. Crear Nuevo Usuario Administrativo

### Endpoint
```
POST /api/v1/admin/users
```

### Descripción
Crea un nuevo usuario con un rol específico (jefe_cerrada, guardia, jefe_familia, familiar).

### Body
```json
{
  "name": "Laura Gómez",
  "email": "laura.gomez@email.com",
  "password": "PasswordSeguro123!",
  "phone": "5559876543",
  "role": "jefe_cerrada"
}
```

### Respuesta Exitosa (Código 201 Created)
```json
{
  "message": "Usuario creado y rol asignado exitosamente.",
  "data": {
    "id": 20,
    "name": "Laura Gómez",
    "email": "laura.gomez@email.com",
    "role": "jefe_cerrada"
  },
  "status": 201
}
```

---

## 2. Obtener Lista de Usuarios

### Endpoint
```
GET /api/v1/admin/users
```

### Descripción
Obtiene todos los usuarios activos del sistema.

### Respuesta Exitosa (Código 200 OK)
```json
{
  "message": "Usuarios obtenidos exitosamente.",
  "data": [
    {
      "id": 20,
      "name": "Laura Gómez",
      "email": "laura.gomez@email.com",
      "phone": "5559876543",
      "role_id": 2,
      "role": "jefe_cerrada",
      "is_active": 1,
      "created_at": "2024-01-15T10:30:00Z",
      "updated_at": "2024-01-15T10:30:00Z"
    },
    {
      "id": 21,
      "name": "Carlos Mendoza",
      "email": "carlos.mendoza@cerrada.com",
      "phone": "5551234567",
      "role_id": 3,
      "role": "guardia",
      "is_active": 1,
      "created_at": "2024-01-15T11:00:00Z",
      "updated_at": "2024-01-15T11:00:00Z"
    }
  ],
  "status": 200
}
```

---

## 3. Obtener Usuario Específico

### Endpoint
```
GET /api/v1/admin/users/{id}
```

### Descripción
Obtiene los detalles de un usuario específico por su ID.

### Path Parameters
- `id`: ID del usuario

### Respuesta Exitosa (Código 200 OK)
```json
{
  "message": "Usuario obtenido exitosamente.",
  "data": {
    "id": 20,
    "name": "Laura Gómez",
    "email": "laura.gomez@email.com",
    "phone": "5559876543",
    "role_id": 2,
    "role": "jefe_cerrada",
    "is_active": 1,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T10:30:00Z"
  },
  "status": 200
}
```

---

## 4. Actualizar Usuario

### Endpoint
```
PUT /api/v1/admin/users/{id}
```

### Descripción
Actualiza los datos de un usuario existente.

### Path Parameters
- `id`: ID del usuario

### Body
```json
{
  "name": "Laura Gómez Actualizada",
  "email": "laura.nueva@email.com",
  "phone": "5551234567",
  "role": "guardia"
}
```

### Respuesta Exitosa (Código 200 OK)
```json
{
  "message": "Usuario actualizado exitosamente.",
  "data": {
    "id": 20,
    "name": "Laura Gómez Actualizada",
    "email": "laura.nueva@email.com",
    "phone": "5551234567",
    "role_id": 3,
    "is_active": 1,
    "created_at": "2024-01-15T10:30:00Z",
    "updated_at": "2024-01-15T11:45:00Z"
  },
  "status": 200
}
```

---

## 5. Eliminar Usuario (Soft Delete)

### Endpoint
```
DELETE /api/v1/admin/users/{id}
```

### Descripción
Desactiva un usuario del sistema cambiando su campo `is_active` a 0 (soft delete).

### Path Parameters
- `id`: ID del usuario

### Respuesta Exitosa (Código 200 OK)
```json
{
  "message": "Usuario eliminado exitosamente.",
  "data": {
    "id": 20
  },
  "status": 200
}
```

---

## Catálogo de Errores

### 401 Unauthorized
```json
{
  "error": "unauthorized",
  "message": "Token de autenticación no válido o expirado.",
  "data": null,
  "status": false
}
```

### 403 Forbidden
```json
{
  "error": "forbidden",
  "message": "No tienes los permisos de Administrador para realizar esta acción.",
  "data": null,
  "status": false
}
```

### 404 Not Found
```json
{
  "error": "not_found",
  "message": "Usuario no encontrado.",
  "data": null,
  "status": false
}
```

### 422 Unprocessable Entity
```json
{
  "error": "validation_failed",
  "message": "Los datos proporcionados no son válidos.",
  "data": {
    "errors": {
      "email": ["El email ya está en uso."],
      "role": ["El rol especificado no es válido."],
      "password": ["La contraseña debe tener al menos 8 caracteres."]
    }
  },
  "status": false
}
```

### 500 Internal Server Error
```json
{
  "error": "server_error",
  "message": "Error interno del servidor.",
  "data": null,
  "status": false
}
```

---

## Mapeo de Roles

| Nombre del Rol | ID del Rol | Descripción |
|----------------|------------|-------------|
| admin | 1 | Administrador del sistema |
| jefe_cerrada | 2 | Jefe de cerrada |
| guardia | 3 | Guardia de seguridad |
| jefe_familia | 4 | Jefe de familia |
| familiar | 5 | Miembro de familia |

---

## Ejemplos de Uso

### Crear un Jefe de Cerrada
```bash
curl -X POST https://tu-dominio.com/api/v1/admin/users \
  -H "Authorization: Bearer {tu-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Carlos Mendoza",
    "email": "carlos.mendoza@cerrada.com",
    "password": "Seguro123!",
    "phone": "5551234567",
    "role": "jefe_cerrada"
  }'
```

### Obtener Todos los Usuarios
```bash
curl -X GET "https://tu-dominio.com/api/v1/admin/users" \
  -H "Authorization: Bearer {tu-token}"
```

### Actualizar Datos de Usuario
```bash
curl -X PUT https://tu-dominio.com/api/v1/admin/users/20 \
  -H "Authorization: Bearer {tu-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Laura Gómez Actualizada",
    "phone": "5559876543"
  }'
```

### Eliminar Usuario (Soft Delete)
```bash
curl -X DELETE https://tu-dominio.com/api/v1/admin/users/20 \
  -H "Authorization: Bearer {tu-token}"
```

---

## Notas Importantes

- **Soft Delete**: Al eliminar un usuario, se cambia su campo `is_active` a 0 en lugar de eliminarlo físicamente de la base de datos.
- **Usuarios Activos**: Solo se muestran usuarios con `is_active = 1`.
- **Roles**: Los usuarios se crean con roles específicos que determinan sus permisos en el sistema.
- **Validación**: Todos los campos son validados antes de ser procesados. 
