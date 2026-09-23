# ⚡ Sistema de Gestión: ABM de Usuarios y Roles (Sprint 2)

Sistema web para la gestión integral de **Usuarios** y **Roles** con autenticación segura (**Login y Registro**), desarrollado en **PHP puro (PDO)**, base de datos **MySQL**, **CSS moderno** y **JavaScript Vanilla**.

---

## 🚀 Características Principales

- 🔐 **Autenticación Completa**: Inicio de sesión seguro con `password_verify()` y registro con validación en tiempo real.
- 👥 **ABM de Usuarios**: Listado con búsqueda dinámica, alta y edición con precarga y asignación de roles.
- 🛡️ **ABM de Roles**: Gestión de perfiles de usuario con protección de integridad referencial (no permite borrar roles con usuarios asignados).
- 🎨 **Interfaz Moderna y Responsiva**: Diseño basado en tarjetas, sistema de badges por rol, alertas automáticas y tipografía *Plus Jakarta Sans*.
- 🔒 **Seguridad**:
  - Sentencias preparadas con **PDO** en todas las consultas (protección contra SQL Injection).
  - Mutaciones de datos ejecutadas exclusivamente vía **POST**.
  - Encriptación de contraseñas con `password_hash()` (BCRYPT).
  - Protección de sesión activa y prevención de autoeliminación de cuentas en sesión.

---

## 📂 Estructura del Proyecto

```
Proyecto-de-Software/
├── config/
│   └── conexion.php           # Conexión única PDO a MySQL
├── backend/
│   ├── auth.php               # Login, registro, sesiones y mensajes flash
│   ├── roles.php              # Lógica de acceso a datos para Roles
│   └── usuarios.php           # Lógica de acceso a datos para Usuarios (JOIN con roles)
├── css/
│   └── styles.css             # Hoja de estilos global, variables CSS y diseño responsivo
├── js/
│   └── main.js                # Validaciones en cliente, toggle de claves y confirmaciones
├── sql/
│   └── sprint2.sql            # Script de creación de base de datos, tablas y datos iniciales
├── index.php                  # Dashboard principal con listado de usuarios y métricas
├── login.php                  # Pantalla de inicio de sesión
├── registro.php               # Pantalla de registro de usuarios
├── logout.php                 # Cierre de sesión
├── formulario.php             # Formulario de alta y edición de usuario
├── roles.php                  # Gestión integral de roles (listado + formulario)
├── eliminar.php               # Procesamiento seguro de baja de usuarios vía POST
└── README.md                  # Documentación del proyecto
```

---

## 🛠️ Instalación y Configuración (WAMP / XAMPP)

1. **Copiar el proyecto** en la carpeta web de tu servidor:
   - En WAMP: `C:\wamp64\www\Proyecto-de-Software\`
   - En XAMPP: `C:\xampp\htdocs\Proyecto-de-Software\`

2. **Importar la Base de Datos**:
   - Abre **phpMyAdmin** (`http://localhost/phpmyadmin`).
   - Ve a la pestaña **Importar** y selecciona el archivo [`sql/sprint2.sql`](file:///c:/Users/braia/Desktop/Proyectos%20Antigravity/Proyecto-de-Software/sql/sprint2.sql).
   - Haz clic en **Continuar / Importar**. Se creará la base de datos `sprint2` con los roles y el usuario administrador inicial.

3. **Verificar Conexión** (opcional):
   - Si tu MySQL tiene contraseña distinta a vacía, edita las constantes en [`config/conexion.php`](file:///c:/Users/braia/Desktop/Proyectos%20Antigravity/Proyecto-de-Software/config/conexion.php):
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'sprint2');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Tu contraseña si la hubiere
   ```

4. **Acceder a la Aplicación**:
   - Ingresa desde tu navegador a: `http://localhost/Proyecto-de-Software/login.php`

---

## 🔑 Credenciales de Prueba Iniciales

| Usuario | Contraseña | Rol |
| :--- | :--- | :--- |
| `admin` | `admin123` | Administrador |

---

## 🧪 Pruebas Recomendadas

1. **Iniciar Sesión**: Ingresa con `admin` / `admin123`.
2. **Crear Roles**: Ve a la sección *Roles* y crea nuevos perfiles de usuario.
3. **Crear Usuarios**: Agrega usuarios desde el formulario con diferentes roles asignados.
4. **Editar Usuario**: Modifica nombres, correos o contraseñas sin perder consistencia.
5. **Integridad de Roles**: Intenta eliminar un rol que tenga usuarios asociados para comprobar el bloqueo de seguridad.
6. **Cerrar Sesión**: Comprueba la destrucción segura de la sesión y la redirección.
