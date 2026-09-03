# Configuración del Agente Gemini — Portal Pausa Café (Multipágina)

> Este archivo define las skills del agente para mantener y extender el portal
> web de **Pausa Café**. Las skills están organizadas por **jerarquía de
> dependencias**: primero estructura, luego estilos, luego componentes
> reutilizables, luego contenido de cada página, y por último interactividad
> y mantenimiento. Ningún skill de página debe redefinir reglas visuales:
> esas viven únicamente en `EstilosGlobalesCafe` y `ComponentesCafe`.

proyecto:
  nombre: "Pausa Café"
  tipo: "Portal web multipágina, café de especialidad y pastelería artesanal"
  estructura_carpetas:
    - "assets/images/"      # croissant.jpg, espresso.jpg, hero.jpg, interior.jpg
    - "css/styles.css"      # única hoja de estilos global
    - "js/main.js"          # única fuente de lógica JS (carrito, validaciones, toast)
    - "pages/"              # index.html, menu.html, promociones.html, equipo.html, contacto.html, reservas.html
    - "README.md"

convenciones_generales:
  indentacion:
    html: 2 espacios, sin tabs
    css: 2 espacios, una propiedad por línea
    js: 2 espacios, punto y coma obligatorio, comillas simples
    yaml_este_archivo: 2 espacios por nivel, listas con "- "
  nombres_archivo: "minúsculas, sin espacios ni tildes (ej: promociones.html)"
  rutas_internas: "siempre relativas desde /pages/ (ej: ../css/styles.css, ../js/main.js, ../assets/images/hero.jpg)"
  idioma_comentarios: "español, siempre presentes en HTML, CSS y JS"

# ---------------------------------------------------------------------------
# NIVEL 0 — ESTRUCTURA BASE (sin dependencias)
# ---------------------------------------------------------------------------
skills:
  - name: EstructuraBaseCafe
    description: >
      Define el esqueleto HTML compartido por todas las páginas: <head>,
      metadatos, navbar y footer. Es la base de la que dependen todas las
      demás skills de página.
    depends_on: []
    triggers:
      - "crear estructura base"
      - "generar navbar"
      - "generar footer"
    rules:
      - Usar HTML5 semántico: <header>, <nav>, <main>, <section>, <footer>.
      - Incluir en <head> charset UTF-8, viewport responsive, title único
        por página y meta description breve.
      - Navbar idéntico en todas las páginas, con enlaces a las 6 páginas
        del portal (Inicio, Menú, Promociones, Equipo, Contacto, Mi pedido)
        y el contador de carrito (.cart-count).
      - Footer idéntico en todas las páginas: datos de contacto, horarios
        resumidos y año dinámico vía atributo [data-year].
      - Referenciar siempre ../css/styles.css y ../js/main.js al final del
        <body>.
      - No declarar estilos inline ni scripts embebidos: todo vive en
        styles.css y main.js.
      - Comentar en español cada bloque estructural (ej:
        <!-- Navbar principal --> / <!-- Footer con datos de contacto -->).

  # -------------------------------------------------------------------------
  # NIVEL 1 — ESTILOS GLOBALES (depende de EstructuraBaseCafe)
  # -------------------------------------------------------------------------
  - name: EstilosGlobalesCafe
    description: >
      Única fuente de verdad para paleta de colores, tipografía, espaciados
      y animaciones del portal. Ninguna otra skill debe redefinir estos
      valores; deben reutilizarlos por referencia.
    depends_on: [EstructuraBaseCafe]
    triggers:
      - "crear estilos"
      - "generar css"
      - "actualizar paleta"
    rules:
      - Definir variables CSS en :root:
          --cream: #f5f5dc;        /* tarjetas y contrastes secundarios */
          --paper: #fbfaf5;        /* fondo principal */
          --coffee: #4e342e;       /* tipografía de cabeceras y botones principales */
          --coffee-dark: #36231f;  /* estado hover de botones y links */
          --ink: #171310;          /* texto de alto contraste */
          --muted: #766c65;        /* texto secundario */
          --accent: #c89454;       /* etiquetas eyebrow y acentos */
          --shadow: [sombra suave con tinte café para elevación]
      - Tipografía: "Playfair Display" para h1/h2/h3, "DM Sans" para cuerpo,
        menús y formularios. Cargar ambas vía Google Fonts en <head>.
      - Verificar contraste AA mínimo entre texto e--ink sobre --paper y
        --cream antes de dar por cerrado cualquier componente nuevo.
      - Transiciones suaves (150–250ms, ease) en hover/focus de botones,
        tarjetas y links. Nada abrupto ni parpadeante.
      - Breakpoints responsivos: móvil (<= 575px) y tablet/mediano (<= 991px).
      - Compatible con Bootstrap 5 (grid, cards, forms): las variables CSS
        deben poder sobrescribir o convivir con las clases de Bootstrap sin
        conflictos de especificidad.
      - Diseño limpio, espaciado generoso, nunca sobrecargado.
      - Comentar cada bloque de variables y cada sección del archivo
        (ej: /* === Paleta y tokens de diseño === */).

  # -------------------------------------------------------------------------
  # NIVEL 2 — COMPONENTES REUTILIZABLES (depende de EstilosGlobalesCafe)
  # -------------------------------------------------------------------------
  - name: ComponentesCafe
    description: >
      Patrones visuales reutilizables (tarjeta genérica, botón CTA, toast,
      badge de precio) que consumen las páginas de contenido. Evita que cada
      página "invente" su propia tarjeta.
    depends_on: [EstilosGlobalesCafe]
    triggers:
      - "crear componente"
      - "generar tarjeta reutilizable"
    rules:
      - Tarjeta genérica (.card-cafe): imagen/ícono, título, descripción
        breve, precio o etiqueta opcional, usada en menú, promociones y
        equipo con la misma estructura HTML base.
      - Botón CTA principal (.btn-coffee): fondo --coffee, hover
        --coffee-dark, usado en formularios y llamados a la acción.
      - Componente Toast: notificación flotante inferior derecha para
        feedback de acciones (agregar producto, enviar formulario).
      - Todo componente debe funcionar dentro del grid/cards de Bootstrap 5
        sin duplicar sus estilos base.
      - Documentar en comentarios qué variables de EstilosGlobalesCafe
        consume cada componente.

  # -------------------------------------------------------------------------
  # NIVEL 3 — PÁGINAS DE CONTENIDO (dependen de Niveles 0, 1 y 2)
  # -------------------------------------------------------------------------
  - name: CrearHomeCafe
    description: Genera la página de inicio en pages/index.html.
    depends_on: [EstructuraBaseCafe, EstilosGlobalesCafe, ComponentesCafe]
    triggers:
      - "crear home"
      - "generar inicio"
    rules:
      - Sección Hero con imagen destacada (assets/images/hero.jpg) y
        propuesta de valor de la marca.
      - Sección "Pilares de calidad": 01 Café con origen, 02 Hecho en casa,
        03 Un lugar para ti, usando .card-cafe.
      - Sección de historia y filosofía de la marca.
      - Mostrar horario de atención y estado en tiempo real (abierto/cerrado).
      - Llamado a la acción final hacia menu.html o reservas.html.
      - Solo contenido y estructura específica de esta página: el estilo
        visual viene de EstilosGlobalesCafe y ComponentesCafe.

  - name: CrearMenuCafe
    description: Genera el menú digital interactivo en pages/menu.html.
    depends_on: [EstructuraBaseCafe, EstilosGlobalesCafe, ComponentesCafe]
    triggers:
      - "crear menú"
      - "generar carta"
    rules:
      - Categorías: Cafés (Espresso, Cappuccino, Latte, Cold brew) y Para
        acompañar (Croissant, Brownie de cacao, Cheesecake).
      - Cada producto usa .card-cafe con botón "+ Agregar" y atributos
        data-product y data-price para integrarse con el carrito de main.js.
      - Usar grid de Bootstrap para distribución responsiva de tarjetas.
      - No implementar lógica de carrito aquí: solo marcado y atributos de
        datos; la lógica vive en InteractividadCafe / main.js.

  - name: CrearPromocionesCafe
    description: Genera la página de ofertas en pages/promociones.html.
    depends_on: [EstructuraBaseCafe, EstilosGlobalesCafe, ComponentesCafe]
    triggers:
      - "crear promociones"
      - "ofertas especiales"
    rules:
      - Cuadrícula de combos destacados (Combo mañanero, Tarde dulce,
        Brunch de la casa) con .card-cafe.
      - Tarjeta de fidelización "Club Pausa" con su beneficio destacado
        (ej. sexto café gratis).
      - Reutilizar el mismo botón "+ Agregar" con data-product/data-price
        cuando el combo sea agregable al carrito.

  - name: CrearEquipoCafe
    description: Genera la página de staff en pages/equipo.html.
    depends_on: [EstructuraBaseCafe, EstilosGlobalesCafe, ComponentesCafe]
    triggers:
      - "crear equipo"
      - "presentar staff"
    rules:
      - Tarjetas de equipo (.card-cafe) con foto, nombre, rol y descripción
        breve (ej. Valentina - Barista, Martín - Tostador, Lucía - Pastelera,
        Tomás - Encargado).
      - Animación suave de elevación/opacidad al pasar el mouse, heredada
        de las transiciones definidas en EstilosGlobalesCafe.

  - name: CrearContactoCafe
    description: Genera la página de contacto en pages/contacto.html.
    depends_on: [EstructuraBaseCafe, EstilosGlobalesCafe, ComponentesCafe]
    triggers:
      - "crear contacto"
      - "formulario contacto"
    rules:
      - Formulario con campos nombre, email y mensaje, usando validación
        de Bootstrap 5 (clases was-validated, gestionadas por main.js).
      - Botón de envío con estilo .btn-coffee.
      - Incluir dirección física (Buenos Aires), horarios de lunes a
        domingo, correo y teléfono de contacto.
      - Incluir mapa embebido o referencia visual de ubicación.
      - No duplicar lógica de validación: debe reutilizar las funciones
        centralizadas en main.js (ver InteractividadCafe).

  - name: CrearReservasCafe
    description: >
      Genera pages/reservas.html: carrito de pedidos dinámico y formulario
      de reserva demostrativo.
    depends_on: [EstructuraBaseCafe, EstilosGlobalesCafe, ComponentesCafe]
    triggers:
      - "crear reservas"
      - "generar carrito"
      - "página mi pedido"
    rules:
      - Contenedor donde main.js renderiza los productos del carrito
        (getCart/renderCart), con controles de cantidad (+ / -), botón de
        eliminar y total calculado en $.
      - Formulario de reserva de mesa/pedido con validación en tiempo real.
      - Antes de confirmar la reserva, verificar que el carrito tenga al
        menos un producto (regla aplicada por main.js, no por esta página).
      - Estado vacío del carrito debe mostrar un mensaje claro y amigable.

  # -------------------------------------------------------------------------
  # NIVEL 4 — INTERACTIVIDAD (depende de que TODAS las páginas existan)
  # -------------------------------------------------------------------------
  - name: InteractividadCafe
    description: >
      Genera y mantiene la lógica JavaScript centralizada del portal en
      js/main.js. Es la única fuente de lógica de carrito, validaciones,
      notificaciones y utilidades.
    depends_on:
      - EstructuraBaseCafe
      - CrearMenuCafe
      - CrearPromocionesCafe
      - CrearContactoCafe
      - CrearReservasCafe
    triggers:
      - "crear interactividad"
      - "generar js"
    rules:
      - Carrito con localStorage bajo la clave "pausa-cafe-cart":
          - getCart() / saveCart(): persisten los artículos entre recargas.
          - updateCount(): sincroniza el contador visual del navbar
            (.cart-count) en todas las páginas.
          - renderCart(): dibuja los artículos en reservas.html, con
            controles de cantidad, eliminación y cálculo del total.
      - toast(message): notificación flotante de feedback (agregar
        producto, enviar formulario), reutilizando el componente Toast de
        ComponentesCafe.
      - Validación de formularios con clases was-validated de Bootstrap 5;
        mensajes de error claros y en español.
      - Antes de procesar una reserva, comprobar que haya al menos un
        producto en el carrito.
      - Insertar automáticamente el año actual en los elementos con
        atributo [data-year].
      - Scroll suave entre secciones ancladas dentro de una misma página.
      - Código modular: una función, una responsabilidad. Evitar funciones
        largas; si crecen, aplicar RefactorizarCodigo.
      - Comentar en español el propósito de cada función y de cada bloque
        de eventos (ej: // Recupera el carrito almacenado en localStorage).

  # -------------------------------------------------------------------------
  # NIVEL 5 — MANTENIMIENTO (transversal, se aplican bajo demanda)
  # -------------------------------------------------------------------------
  - name: DocumentarCodigo
    description: Agrega o mejora comentarios en HTML, CSS y JS existentes.
    depends_on: []
    triggers:
      - "documentar"
      - "agregar comentarios"
    rules:
      - Comentarios claros y en español, sin redundancia (no repetir lo
        obvio que ya dice el nombre de la variable o función).
      - Explicar el propósito de cada sección HTML, cada bloque CSS y cada
        función o listener de JS clave.
      - Mantener el mismo estilo de comentario en todo el proyecto:
        <!-- Comentario --> en HTML, /* Comentario */ en CSS, // Comentario
        en JS.
      - No modificar lógica ni estilos existentes: solo agregar
        documentación.

  - name: RefactorizarCodigo
    description: Reestructura funciones largas o estilos repetidos.
    depends_on: []
    triggers:
      - "refactorizar"
      - "optimizar código"
    rules:
      - Mantener la lógica y el comportamiento visual intactos.
      - Seguir las convenciones de indentación y nombres ya definidas en
        convenciones_generales.
      - Evitar duplicación de código: si dos páginas repiten el mismo
        bloque, moverlo a ComponentesCafe (CSS/HTML) o a una función
        compartida en main.js (JS).
      - Verificar que el cambio no rompa las dependencias declaradas por
        las demás skills (ej. no renombrar variables CSS que consumen
        ComponentesCafe o las páginas de contenido).
      - Documentar en el commit o comentario qué se refactorizó y por qué.

# ---------------------------------------------------------------------------
# ORDEN DE EJECUCIÓN RECOMENDADO PARA UN SITIO NUEVO
# ---------------------------------------------------------------------------
orden_recomendado:
  1: EstructuraBaseCafe
  2: EstilosGlobalesCafe
  3: ComponentesCafe
  4: [CrearHomeCafe, CrearMenuCafe, CrearPromocionesCafe, CrearEquipoCafe, CrearContactoCafe, CrearReservasCafe]
  5: InteractividadCafe
  6: [DocumentarCodigo, RefactorizarCodigo]   # bajo demanda, en cualquier momento posterior
