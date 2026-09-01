# Documentación del Portal Web - Pausa Café

Este repositorio contiene el desarrollo del portal web multipágina para **Pausa Café**, un sitio web moderno, responsivo y dinámico enfocado en café de especialidad y pastelería artesanal.

---

## 📁 Estructura del Proyecto

```
Pagina de prueba/
├── assets/
│   └── images/
│       ├── croissant.jpg      # Fotografía de pastelería artesanal
│       ├── espresso.jpg       # Fotografía de café espresso
│       ├── hero.jpg           # Imagen principal para la sección Hero
│       └── interior.jpg       # Fotografía del interior de la cafetería
├── css/
│   └── styles.css             # Hoja de estilos global, variables, componentes y media queries
├── js/
│   └── main.js                # Lógica del carrito (localStorage), validaciones y feedback (Toast)
├── pages/
│   ├── index.html             # Página de Inicio (Hero, pilares de marca y filosofía)
│   ├── menu.html              # Menú digital interactivo con opciones para agregar al pedido
│   ├── promociones.html       # Catálogo de combos semanales y programa Club Pausa
│   ├── equipo.html            # Presentación del equipo de baristas, tostadores y pastelería
│   ├── contacto.html          # Formulario de contacto interactivo, ubicación y horarios
│   └── reservas.html          # Carrito de pedidos dinámico y formulario de reserva demostrativo
└── README.md                  # Documentación técnica y funcional del proyecto
```

---

## 📄 Descripción Detallada de Archivos

### 1. Páginas (`/pages/`)

* **`index.html` (Inicio)**:
  * Presenta la identidad de marca (*Pausa Café*), propuesta de valor, horario de atención y estado en tiempo real.
  * Secciones: *Hero*, *Pilares de Calidad* (01 Café con origen, 02 Hecho en casa, 03 Un lugar para ti), *Historia y Filosofía*, y llamado a la acción.
* **`menu.html` (Carta Digital)**:
  * Menú categorizado en *Cafés* (Espresso, Cappuccino, Latte, Cold brew) y *Para acompañar* (Croissant, Brownie de cacao, Cheesecake).
  * Cada producto incluye botón interactivo `+ Agregar` con atributos `data-product` y `data-price`.
* **`promociones.html` (Ofertas y Fidelización)**:
  * Cuadrícula con combos destacados (*Combo mañanero*, *Tarde dulce*, *Brunch de la casa*).
  * Tarjeta de fidelización *Club Pausa* ("Tu sexto café va por la casa").
* **`equipo.html` (Nosotros / Staff)**:
  * Presentación del equipo humano detrás de la experiencia (Valentina - Barista, Martín - Tostador, Lucía - Pastelera, Tomás - Encargado).
* **`contacto.html` (Contacto y Ubicación)**:
  * Formulario con validación en tiempo real (Nombre, Email y Mensaje).
  * Información física: dirección en Buenos Aires, horarios de lunes a domingo, correo y teléfono de contacto.
* **`reservas.html` (Mi Pedido y Reserva Demostrativa)**:
  * Carrito de compras interactivo que renderiza los productos agregados, permite ajustar cantidades (+ / -), eliminar artículos y calcula el total estimado.
  * Formulario de reserva de mesa/pedido con validación.

---

### 2. Estilos (`/css/styles.css`)

* **Paleta de Colores y Tokens de Diseño (`:root`)**:
  * `--cream` (`#f5f5dc`): Tono crema suave para tarjetas y contrastes secundarios.
  * `--paper` (`#fbfaf5`): Fondo principal con estilo papel cálido.
  * `--coffee` (`#4e342e`): Tono café tostado para tipografía, cabeceras y botones principales.
  * `--coffee-dark` (`#36231f`): Estado hover de botones y elementos interactivos.
  * `--ink` (`#171310`): Color de lectura de alto contraste.
  * `--muted` (`#766c65`): Tono para textos secundarios.
  * `--accent` (`#c89454`): Tono caramelo / dorado para etiquetas *eyebrow* y acentos.
  * `--shadow`: Sombra suave difusa con tinte café para elevación de componentes.
* **Tipografías**:
  * *Playfair Display* para títulos (`h1`, `h2`, `h3`).
  * *DM Sans* para textos, menús y formularios.
* **Diseño Responsivo**:
  * Breakpoints adaptados para pantallas móviles (`<= 575px`) y tablets/pantallas medianas (`<= 991px`).

---

### 3. Lógica JavaScript (`/js/main.js`)

* **Carrito de Pedidos con `localStorage`**:
  * `getCart()` / `saveCart()`: Persiste los artículos entre recargas de página bajo la clave `pausa-cafe-cart`.
  * `updateCount()`: Mantiene sincronizado el contador visual en el navbar (`.cart-count`).
  * `renderCart()`: Genera dinámicamente los artículos en `reservas.html`, controles de cantidad (+ / -), botón de eliminación y cálculo del total en `$`.
* **Notificaciones Emergentes (Toast)**:
  * `toast(message)`: Muestra avisos flotantes de feedback al agregar productos o enviar formularios.
* **Validación de Formularios**:
  * Aplica clases de validación de Bootstrap 5 (`was-validated`).
  * Comprueba que haya al menos un producto en el carrito antes de procesar una reserva.
* **Automatización del Año**:
  * Inserta el año corriente automáticamente en los elementos con atributo `[data-year]`.

---

## 🚀 Instrucciones de Uso y Visualización

1. Abre el directorio del proyecto en tu navegador o mediante un servidor local (por ejemplo, Live Server en VS Code).
2. Ingresa a `pages/index.html` para navegar por todo el portal.
3. Al hacer clic en `+ Agregar` en cualquier producto del menú o promociones, observarás:
   * Notificación Toast emergente en la esquina inferior derecha.
   * Actualización instantánea del contador en el navbar.
4. Ingresa a `Mi pedido` (`reservas.html`) para ver el desglose del pedido y simular una reserva.