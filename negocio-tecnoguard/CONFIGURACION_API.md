# Configuración de la API de Negocio TecnoGuard

## Variables de Entorno Requeridas

Para que el CRUD de usuarios funcione correctamente, necesitas configurar las siguientes variables en tu archivo `.env`:

### Configuración de la API de Autenticación

```env
# URL de la API de autenticación externa
AUTH_API_URL=http://localhost:8000

# Token de autenticación para comunicarse con la API externa
AUTH_API_TOKEN=your_auth_api_token_here
```

### Configuración de Base de Datos

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=negocio_tecnoguard
DB_USERNAME=root
DB_PASSWORD=tu_password
```

### Configuración de la Aplicación

```env
APP_NAME="TecnoGuard Business API"
APP_ENV=local
APP_KEY=base64:tu_app_key_aqui
APP_DEBUG=true
APP_URL=http://localhost:8000
```

## Instalación y Configuración

### 1. Instalar Dependencias
```bash
composer install
```

### 2. Configurar Variables de Entorno
```bash
cp .env.example .env
# Editar el archivo .env con tus configuraciones
```

### 3. Generar Clave de Aplicación
```bash
php artisan key:generate
```

### 4. Ejecutar Migraciones
```bash
php artisan migrate
```

### 5. Ejecutar Seeders (opcional)
```bash
php artisan db:seed
```

## Estructura de la API de Autenticación

La API de autenticación externa debe tener los siguientes endpoints disponibles:

### Endpoints Requeridos

1. **POST /api/users** - Crear usuario
   - Body: `{name, email, password, phone, role_id}`
   - Response: `{id, name, email, phone, role_id, created_at, updated_at}`

2. **GET /api/users** - Listar usuarios
   - Query params: `page, per_page, search, role_id`
   - Response: `{current_page, data: [...], total, per_page}`

3. **GET /api/users/{id}** - Obtener usuario
   - Response: `{id, name, email, phone, role_id, created_at, updated_at}`

4. **PUT /api/users/{id}** - Actualizar usuario
   - Body: `{name?, email?, phone?, role_id?}`
   - Response: `{id, name, email, phone, role_id, created_at, updated_at}`

5. **DELETE /api/users/{id}** - Eliminar usuario
   - Response: `{message: "Usuario eliminado"}`

6. **PUT /api/users/{id}/password** - Cambiar contraseña
   - Body: `{password}`
   - Response: `{message: "Contraseña actualizada"}`

### Headers Requeridos

La API de autenticación debe aceptar:
```
Authorization: Bearer {AUTH_API_TOKEN}
Content-Type: application/json
```

## Mapeo de Roles

| Nombre del Rol | ID del Rol | Descripción |
|----------------|------------|-------------|
| admin | 1 | Administrador del sistema |
| jefe_cerrada | 2 | Jefe de cerrada |
| guardia | 3 | Guardia de seguridad |
| jefe_familia | 4 | Jefe de familia |
| familiar | 5 | Miembro de familia |

## Pruebas

### Probar la Conexión
```bash
curl -X GET "http://localhost:8000/api/health"
```

### Probar Autenticación
```bash
curl -X POST "http://localhost:8000/api/v1/admin/users" \
  -H "Authorization: Bearer {tu-token}" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Usuario Test",
    "email": "test@example.com",
    "password": "Password123!",
    "phone": "5551234567",
    "role": "guardia"
  }'
```

## Troubleshooting

### Error: "AUTH_API_URL not configured"
- Verifica que `AUTH_API_URL` esté configurado en tu `.env`
- Asegúrate de que la URL sea accesible

### Error: "AUTH_API_TOKEN not configured"
- Verifica que `AUTH_API_TOKEN` esté configurado en tu `.env`
- Asegúrate de que el token sea válido

### Error: "Connection refused"
- Verifica que la API de autenticación esté ejecutándose
- Verifica que la URL y puerto sean correctos

### Error: "Unauthorized"
- Verifica que el token de autenticación sea válido
- Verifica que tengas permisos de administrador (role_id: 1) 
