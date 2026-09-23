# Configuración del Agente Gemini — Sprint 2: ABM Usuarios y Roles (PHP + MySQL)

> Este archivo define las skills del agente para desarrollar el ABM de
> **Usuarios** y **Roles** solicitado para el sprint. Se reemplaza el
> contexto de portal multipágina por un módulo único: listado, alta,
> edición y baja de Usuarios, en una sola pantalla interactiva, con
> back-end en PHP puro (PDO) y base de datos MySQL gestionada con WAMP +
> phpMyAdmin. Las skills están organizadas por **jerarquía de
> dependencias**: primero el modelo de datos, luego la conexión, luego
> estilos, luego backend, luego frontend, y por último interactividad y
> mantenimiento. Ningún skill de página debe redefinir reglas de acceso a
> datos: esas viven únicamente en `ConexionSprint2` y `BackendRolesCafe` /
> `BackendUsuariosCafe`.

proyecto:
  nombre: "Sprint 2 — ABM Usuarios y Roles"
  tipo: "Módulo CRUD en una sola página, PHP + MySQL, sin frameworks"
  entorno:
    servidor: "WAMP (Apache, puerto 80 estándar)"
    gestor_bd: "phpMyAdmin"
    base_datos: "sprint2"
    motor_conexion: "PDO (mysql:host=localhost;dbname=sprint2), utf8mb4"
  estructura_carpetas:
    - "index.php"            # listado de usuarios + acceso a formulario (tabla principal)
    - "formulario.php"       # alta y edición de usuario (mismo archivo para ambos casos)
    - "eliminar.php"         # procesa baja de usuario vía POST
    - "roles.php"            # listado + alta/edición/baja de roles (mismo patrón que usuarios)
    - "config/conexion.php"  # única fuente de conexión PDO a la BD
    - "backend/usuarios.php" # funciones de acceso a datos de la tabla user
    - "backend/roles.php"    # funciones de acceso a datos de la tabla role
    - "css/styles.css"       # única hoja de estilos global
    - "js/main.js"           # validaciones de formulario y confirmaciones de borrado
    - "sql/sprint2.sql"      # script de creación de la base y tablas
    - "README.md"

convenciones_generales:
  indentacion:
    html: 2 espacios, sin tabs
    css: 2 espacios, una propiedad por línea
    php: 2 espacios, punto y coma obligatorio, comillas simples salvo interpolación
    js: 2 espacios, punto y coma obligatorio, comillas simples
    yaml_este_archivo: 2 espacios por nivel, listas con "- "
  nombres_archivo: "minúsculas, sin espacios ni tildes (ej: formulario.php)"
  metodo_http: >
    Toda operación que crea, modifica o elimina datos (alta, edición,
    eliminación de usuarios y roles) se envía exclusivamente por POST.
    Nunca usar $_GET para acciones destructivas ni para recibir datos de
    formularios de escritura. $_GET solo se permite para parámetros de
    navegación de solo lectura (ej. ?id= para precargar un formulario de
    edición, que luego se reenvía por POST).
  idioma_comentarios: "español, siempre presentes en PHP, HTML, CSS y JS"

# ---------------------------------------------------------------------------
# NIVEL 0 — MODELADO DE ENTIDADES Y BASE DE DATOS (sin dependencias)
# ---------------------------------------------------------------------------
skills:
  - name: ModeloDatosSprint2
    description: >
      Define el modelado de entidades y el script SQL de creación de la
      base de datos `sprint2` en phpMyAdmin/MySQL. Es la base de la que
      dependen todas las demás skills.
    depends_on: []
    triggers:
      - "crear modelo de datos"
      - "generar script sql"
      - "crear base de datos"
    rules:
      - Entidad Role (tabla `role`): id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL, description VARCHAR(255) NULL.
      - Entidad User (tabla `user`): id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE, email VARCHAR(100) NOT NULL
        UNIQUE, name VARCHAR(100) NOT NULL, last_name VARCHAR(100) NOT NULL,
        role INT NOT NULL.
      - Relación: `user.role` es FOREIGN KEY hacia `role.id`. Un Usuario
        tiene un único Rol; un Rol puede estar asociado a más de un
        Usuario (1 a N desde role hacia user).
      - Definir la FK con ON UPDATE CASCADE y ON DELETE RESTRICT: no se
        debe poder eliminar un Rol que tenga Usuarios asociados (esa
        restricción se refuerza también a nivel de BackendRolesCafe).
      - Charset utf8mb4_spanish_ci en la base y las tablas.
      - El script `sql/sprint2.sql` debe incluir: creación de la base
        `sprint2`, creación de ambas tablas en el orden correcto (role
        antes que user, por la FK), y un INSERT inicial de al menos 2 o 3
        roles de ejemplo (ej. Administrador, Editor, Invitado) para poder
        probar el CRUD de usuarios sin pasos previos manuales.
      - Comentar en español cada bloque del script (ej:
        -- Creación de la tabla de roles).

  # -------------------------------------------------------------------------
  # NIVEL 1 — CONEXIÓN (depende de ModeloDatosSprint2)
  # -------------------------------------------------------------------------
  - name: ConexionSprint2
    description: >
      Única fuente de conexión a la base de datos vía PDO. Ninguna otra
      skill debe abrir su propia conexión: todas incluyen/requieren este
      archivo.
    depends_on: [ModeloDatosSprint2]
    triggers:
      - "crear conexion"
      - "generar pdo"
    rules:
      - Archivo único config/conexion.php, retorna una instancia PDO.
      - DSN: "mysql:host=localhost;dbname=sprint2;charset=utf8mb4".
      - Usuario/clave por defecto de WAMP (root sin clave), definidos como
        constantes al inicio del archivo para fácil edición.
      - PDO::ATTR_ERRMODE en PDO::ERRMODE_EXCEPTION.
      - Todo acceso a datos usa esta conexión mediante `require_once`,
        nunca `mysqli` ni conexiones sueltas por archivo.
      - Nunca exponer credenciales ni mensajes de error de PDO directamente
        al usuario final: capturar excepciones y mostrar un mensaje
        genérico, logueando el detalle con error_log().

  # -------------------------------------------------------------------------
  # NIVEL 2 — ESTILOS (depende de ConexionSprint2 solo por orden de armado)
  # -------------------------------------------------------------------------
  - name: EstilosCRUD
    description: >
      Única fuente de verdad para paleta de colores, tipografía y
      espaciados del módulo. Ninguna otra skill debe redefinir estos
      valores.
    depends_on: [ConexionSprint2]
    triggers:
      - "crear estilos"
      - "generar css"
    rules:
      - Definir variables CSS en :root para colores de fondo, texto,
        bordes, y estados (éxito, error, advertencia para mensajes del
        CRUD).
      - Tabla de listado con estilo simple tipo grilla, filas alternadas
        legibles, botones de acción (Editar / Eliminar) diferenciados por
        color.
      - Formulario con estilo de tarjeta centrada, inputs con foco visible,
        mensajes de validación en color de error debajo de cada campo.
      - Responsive básico: la tabla debe scrollear horizontalmente en
        pantallas angostas en vez de romper el layout.
      - Diseño limpio y funcional: prioridad a la legibilidad de datos por
        sobre la estética decorativa, dado que es un módulo administrativo.
      - Comentar cada bloque de variables y cada sección del archivo.

  # -------------------------------------------------------------------------
  # NIVEL 3 — BACKEND (depende de ConexionSprint2)
  # -------------------------------------------------------------------------
  - name: BackendRolesCafe
    description: >
      Funciones PHP de acceso a datos para la entidad Role
      (backend/roles.php): listar, obtener por id, crear, actualizar y
      eliminar roles.
    depends_on: [ConexionSprint2]
    triggers:
      - "crear backend de roles"
      - "generar crud de roles"
    rules:
      - listarRoles(): SELECT de todos los roles, usado para poblar el
        <select> del formulario de usuario y la tabla de roles.php.
      - obtenerRolPorId($id): SELECT preparado por id.
      - crearRol($name, $description): INSERT preparado, valida que
        `name` no esté vacío.
      - actualizarRol($id, $name, $description): UPDATE preparado.
      - eliminarRol($id): antes de eliminar, verificar con un SELECT COUNT
        en `user` si existen usuarios con ese role; si existen, no
        eliminar y devolver un mensaje de error claro indicando que el
        rol está en uso.
      - Todas las consultas con sentencias preparadas (prepare/execute),
        nunca concatenar variables directamente en el SQL.
      - Todas las funciones reciben los datos ya llegados por $_POST desde
        el archivo que las invoca (roles.php), no leen $_POST internamente,
        para mantenerlas testeables y desacopladas del formulario.

  - name: BackendUsuariosCafe
    description: >
      Funciones PHP de acceso a datos para la entidad User
      (backend/usuarios.php): listar (con join a role), obtener por id,
      crear, actualizar y eliminar usuarios.
    depends_on: [ConexionSprint2, BackendRolesCafe]
    triggers:
      - "crear backend de usuarios"
      - "generar crud de usuarios"
    rules:
      - listarUsuarios(): SELECT de user con INNER JOIN a role para traer
        el nombre del rol junto a cada usuario (evitar mostrar solo el id
        numérico en la tabla del listado).
      - obtenerUsuarioPorId($id): SELECT preparado por id, incluye el
        role para precargar el <select> en edición.
      - crearUsuario($username, $email, $name, $lastName, $role): INSERT
        preparado. Antes de insertar, valida con SELECT que username y
        email no existan ya en la tabla (ya que son UNIQUE); si existen,
        devuelve un array de errores en vez de lanzar excepción.
      - actualizarUsuario($id, $username, $email, $name, $lastName, $role):
        UPDATE preparado. La validación de unicidad de username/email debe
        excluir el propio $id (para permitir guardar sin cambiar esos
        campos).
      - eliminarUsuario($id): DELETE preparado por id.
      - Validaciones de formato de email y campos obligatorios se hacen
        primero en el backend (server-side, con filter_var(FILTER_VALIDATE_EMAIL))
        y de forma redundante en JS (InteractividadCRUD) solo como mejora
        de experiencia, nunca como única barrera.
      - Todas las consultas con sentencias preparadas.

  # -------------------------------------------------------------------------
  # NIVEL 4 — FRONTEND (depende de Backend + Estilos)
  # -------------------------------------------------------------------------
  - name: FrontendListadoUsuarios
    description: >
      Genera index.php: tabla de listado de usuarios con acciones de
      editar y eliminar, y acceso al alta de un nuevo usuario.
    depends_on: [BackendUsuariosCafe, EstilosCRUD]
    triggers:
      - "crear listado de usuarios"
      - "generar tabla de usuarios"
    rules:
      - Tabla con columnas: Nombre, Apellido, Usuario, Email, Rol, Acciones.
      - Botón "Nuevo usuario" que lleva a formulario.php sin parámetros
        (modo alta).
      - Por cada fila: botón "Editar" que enlaza a formulario.php?id=X
        (solo para precargar datos vía GET de solo lectura) y botón
        "Eliminar" dentro de un <form method="POST" action="eliminar.php">
        con un input hidden id=X, mostrando un confirm() de JS antes de
        enviar.
      - Mostrar mensajes de éxito/error (usuario creado, editado o
        eliminado; rol en uso al intentar eliminarlo) recibidos por
        parámetro o por sesión ($_SESSION flash message), nunca por GET
        exponiendo datos sensibles.
      - Toda la maquetación reutiliza EstilosCRUD, no declara estilos
        inline.
      - Enlace secundario hacia roles.php para gestionar el catálogo de
        roles.

  - name: FrontendFormularioUsuario
    description: >
      Genera formulario.php: formulario único de alta y edición de
      usuario, reutilizado según venga o no un id.
    depends_on: [BackendUsuariosCafe, BackendRolesCafe, EstilosCRUD]
    triggers:
      - "crear formulario de usuario"
      - "generar alta y edición"
    rules:
      - Si llega $_GET['id'], precargar los datos del usuario (modo
        edición) llamando a obtenerUsuarioPorId(); si no llega, mostrar el
        formulario vacío (modo alta).
      - El <form> siempre usa method="POST" action="formulario.php",
        incluyendo un input hidden con el id cuando se está editando, para
        que el propio formulario.php distinga alta de edición al recibir
        el POST.
      - Campos: Nombre, Apellido, Nombre de usuario, Email, Rol (<select>
        poblado con listarRoles()). Todos obligatorios.
      - Al recibir el POST: si hay id, llama a actualizarUsuario(); si no
        hay id, llama a crearUsuario(). Según el resultado, redirige a
        index.php con mensaje de éxito, o vuelve a mostrar el formulario
        con los errores y los valores ya cargados (no se pierden los datos
        tipeados ante un error).
      - Mostrar los mensajes de error de validación (username/email
        duplicado, email inválido, campos vacíos) debajo de cada campo
        correspondiente.

  - name: FrontendRoles
    description: >
      Genera roles.php: listado, alta, edición y baja de Roles, en la
      misma página (mismo patrón simplificado que Usuarios pero sin
      página de formulario separada, dado que Rol solo tiene 2 campos).
    depends_on: [BackendRolesCafe, EstilosCRUD]
    triggers:
      - "crear listado de roles"
      - "generar abm de roles"
    rules:
      - Tabla de roles (Nombre, Descripción, Acciones) y, en la misma
        página, un formulario compacto de alta/edición (misma lógica de
        precarga por id que FrontendFormularioUsuario, pero todo dentro de
        roles.php en vez de un archivo aparte).
      - El formulario de roles también usa method="POST".
      - Eliminar un rol también va por un <form method="POST"
        action="roles.php"> con un input hidden de acción (ej.
        accion=eliminar) e id, con confirm() de JS antes de enviar.
      - Si BackendRolesCafe::eliminarRol() devuelve error por rol en uso,
        mostrarlo como mensaje claro en la misma página, sin eliminar
        nada.

  # -------------------------------------------------------------------------
  # NIVEL 5 — INTERACTIVIDAD (depende de que todo el frontend exista)
  # -------------------------------------------------------------------------
  - name: InteractividadCRUD
    description: >
      Genera y mantiene la lógica JavaScript de mejora de experiencia en
      js/main.js: validaciones en cliente y confirmaciones de borrado. No
      reemplaza nunca la validación del backend.
    depends_on:
      - FrontendListadoUsuarios
      - FrontendFormularioUsuario
      - FrontendRoles
    triggers:
      - "crear interactividad"
      - "generar js de validaciones"
    rules:
      - confirmarEliminacion(mensaje): confirm() reutilizable, usado antes
        de enviar cualquier <form> de eliminación (usuarios y roles).
      - validarFormularioUsuario(): valida en el submit que ningún campo
        obligatorio esté vacío y que el email tenga formato válido antes
        de dejar enviar el formulario; muestra los mensajes de error sin
        recargar la página si falla.
      - Nunca deshabilitar el envío real del formulario si JS falla o está
        deshabilitado: las validaciones de backend (BackendUsuariosCafe,
        BackendRolesCafe) son la barrera real.
      - Código modular: una función, una responsabilidad.
      - Comentar en español el propósito de cada función.

  # -------------------------------------------------------------------------
  # NIVEL 6 — MANTENIMIENTO (transversal, se aplican bajo demanda)
  # -------------------------------------------------------------------------
  - name: DocumentarCodigo
    description: Agrega o mejora comentarios en PHP, HTML, CSS y JS existentes.
    depends_on: []
    triggers:
      - "documentar"
      - "agregar comentarios"
    rules:
      - Comentarios claros y en español, sin redundancia.
      - Explicar el propósito de cada función PHP (qué recibe, qué
        devuelve, qué valida) y de cada bloque HTML/CSS/JS clave.
      - Mantener el mismo estilo de comentario en todo el proyecto:
        <!-- Comentario --> en HTML, /* Comentario */ en CSS, // Comentario
        en JS, // Comentario en PHP.
      - No modificar lógica ni estilos existentes: solo agregar
        documentación.

  - name: RefactorizarCodigo
    description: Reestructura funciones largas o consultas repetidas.
    depends_on: []
    triggers:
      - "refactorizar"
      - "optimizar código"
    rules:
      - Mantener la lógica y el comportamiento intactos.
      - Seguir las convenciones de indentación y nombres ya definidas en
        convenciones_generales.
      - Evitar duplicación: si una consulta SQL o una validación se repite
        en Usuarios y Roles, extraerla a una función compartida (ej. en un
        helper común) en vez de copiarla.
      - Verificar que el cambio no rompa las dependencias declaradas por
        las demás skills (ej. no cambiar la firma de una función de
        backend sin actualizar quién la invoca).
      - Documentar en el commit o comentario qué se refactorizó y por qué.

# ---------------------------------------------------------------------------
# ORDEN DE EJECUCIÓN RECOMENDADO PARA EL SPRINT
# ---------------------------------------------------------------------------
orden_recomendado:
  1: ModeloDatosSprint2
  2: ConexionSprint2
  3: EstilosCRUD
  4: BackendRolesCafe
  5: BackendUsuariosCafe
  6: [FrontendListadoUsuarios, FrontendFormularioUsuario, FrontendRoles]
  7: InteractividadCRUD
  8: [DocumentarCodigo, RefactorizarCodigo]   # bajo demanda, en cualquier momento posterior
