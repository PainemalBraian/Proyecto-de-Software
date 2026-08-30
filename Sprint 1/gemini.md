# Configuración del Agente Gemini - Portal Cafetería Multipágina

skills:
  - name: CrearHomeCafe
    description: Genera la página principal (index.html) para la cafetería.
    triggers:
      - "crear home"
      - "generar inicio"
    rules:
      - Incluir presentación de la cafetería con imagen destacada.
      - Usar paleta crema (#f5f5dc), chocolate (#4e342e), negro (#000).
      - Estilo visual suave, limpio, no sobrecargado.
      - Incorporar Bootstrap para diseño responsive.

  - name: CrearMenuCafe
    description: Genera menú digital en menu.html.
    triggers:
      - "crear menú"
      - "generar carta"
    rules:
      - Categorías: Cafés, Tés, Pastelería, Sandwiches.
      - Usar listas y tarjetas de Bootstrap.
      - Animaciones suaves en hover.

  - name: CrearPromocionesCafe
    description: Genera página de promociones en promociones.html.
    triggers:
      - "crear promociones"
      - "ofertas especiales"
    rules:
      - Mostrar combos y descuentos destacados.
      - Usar componentes de Bootstrap (cards, grid).
      - Mantener estilo visual suave y limpio.

  - name: CrearEquipoCafe
    description: Genera página equipo.html para presentar baristas y staff.
    triggers:
      - "crear equipo"
      - "presentar staff"
    rules:
      - Usar tarjetas con foto y descripción breve.
      - Animaciones suaves al pasar el mouse.

  - name: CrearContactoCafe
    description: Genera página contacto.html con formulario y ubicación.
    triggers:
      - "crear contacto"
      - "formulario contacto"
    rules:
      - Campos: nombre, email, mensaje.
      - Validación básica con Bootstrap y JavaScript.
      - Incluir mapa o dirección física.
      - Botón destacado color chocolate.

  - name: EstilosGlobalesCafe
    description: Define estilos globales en styles.css.
    triggers:
      - "crear estilos"
      - "generar css"
    rules:
      - Paleta crema, chocolate y negro.
      - Tipografía clara y legible.
      - Animaciones suaves (transiciones en hover).
      - Compatible con Bootstrap.
      - Diseño limpio, no sobrecargado.

  - name: DocumentarCodigo
    description: Agrega comentarios en HTML, CSS y JS para explicar la estructura.
    triggers:
      - "documentar"
      - "agregar comentarios"
    rules:
      - Usar comentarios claros en español.
      - Explicar propósito de cada sección y scripts clave.

  - name: RefactorizarCodigo
    description: Reestructura funciones largas o estilos repetidos.
    triggers:
      - "refactorizar"
      - "optimizar código"
    rules:
      - Mantener la lógica intacta.
      - Seguir convenciones de estilo del proyecto.
      - Evitar duplicación de código.

  - name: InteractividadCafe
    description: Genera scripts JavaScript para interactividad en el portal.
    triggers:
      - "crear interactividad"
      - "generar js"
    rules:
      - Validar formularios con mensajes claros.
      - Animaciones suaves (fade, slide).
      - Navegación dinámica (scroll suave entre secciones).
      - Mantener código modular y limpio.
