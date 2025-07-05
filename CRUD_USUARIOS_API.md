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
Crea un nuevo usuario con teléfono y cerrada asignada. Se crea automáticamente una familia y el usuario podrá completar su registro posteriormente.

### Headers
```
Authorization: Bearer {token}
Content-Type: application/json
```

### Query Params
No aplica.

### Path Params
No aplica.

### Body
```json
{
  "phone": "8711055582",
  "cerrada_id": 1
}
```

### Respuesta Exitosa (Código 201 Created)
```json
{
  "message": "Usuario creado exitosamente. El usuario podrá completar su registro con su teléfono.",
  "data": {
    "id": 20,
    "phone": "8711055582",
    "family_id": 15,
    "cerrada": {
      "id": 1,
      "nombre": "Cerrada San Patricio"
    },
    "status": "pendiente_registro"
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
