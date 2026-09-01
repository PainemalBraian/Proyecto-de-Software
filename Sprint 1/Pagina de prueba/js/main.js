/**
 * ====================================================================
 * Pausa Café - Lógica JavaScript Principal (main.js)
 * ====================================================================
 * Controla la interactividad del portal web:
 * 1. Gestión del Carrito de Pedidos con persistencia en localStorage.
 * 2. Actualización en tiempo real del contador del carrito en el navbar.
 * 3. Renderizado reactivo de la lista de pedidos y cálculo del total.
 * 4. Sistema de notificaciones emergentes (Toasts).
 * 5. Validación y envío simulado de formularios (Contacto y Reservas).
 * 6. Actualización automática del año en el pie de página.
 */

(function () {
  "use strict";

  // Clave de almacenamiento en localStorage para el carrito
  const storageKey = "pausa-cafe-cart";

  /**
   * Obtiene la lista de productos guardados en localStorage.
   * @returns {Array<{name: string, price: number, quantity: number}>}
   */
  const getCart = () => JSON.parse(localStorage.getItem(storageKey) || "[]");

  /**
   * Guarda el estado actual del carrito en localStorage y actualiza la UI.
   * @param {Array} cart - Arreglo con los ítems del carrito.
   */
  const saveCart = (cart) => {
    localStorage.setItem(storageKey, JSON.stringify(cart));
    updateCount(cart);
  };

  /**
   * Actualiza el badge del contador de productos en todos los botones/enlaces de navegación.
   * @param {Array} [cart] - Carrito actual (opcional, por defecto lo recupera de storage).
   */
  const updateCount = (cart = getCart()) => {
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    document.querySelectorAll(".cart-count").forEach((node) => {
      node.textContent = count;
    });
  };

  /**
   * Muestra un mensaje flotante temporal (toast notification) al usuario.
   * @param {string} message - Texto a mostrar.
   */
  const toast = (message) => {
    const node = document.querySelector(".toast-message");
    if (!node) return;
    node.textContent = message;
    node.classList.add("show");
    window.setTimeout(() => node.classList.remove("show"), 2200);
  };

  /**
   * Agrega un producto al carrito o incrementa su cantidad si ya existe.
   * @param {string} name - Nombre del producto o promoción.
   * @param {string|number} price - Precio unitario del producto.
   */
  const addToCart = (name, price) => {
    const cart = getCart();
    const item = cart.find((entry) => entry.name === name);
    if (item) {
      item.quantity += 1;
    } else {
      cart.push({ name, price: Number(price), quantity: 1 });
    }
    saveCart(cart);
    toast(name + " se agregó a tu pedido.");
  };

  /**
   * Renderiza dinámicamente los productos dentro del contenedor del carrito en reservas.html
   * y actualiza el valor del total estimado.
   */
  function renderCart() {
    const container = document.getElementById("cartItems");
    const totalNode = document.getElementById("cartTotal");
    if (!container) return;

    const cart = getCart();
    
    // Si el carrito está vacío, mostrar mensaje informativo
    if (!cart.length) {
      container.innerHTML = '<div class="empty-cart">Todavía no agregaste productos.<br><a class="text-link" href="menu.html">Explorar el menú →</a></div>';
      if (totalNode) totalNode.textContent = "$0.00";
      return;
    }

    // Renderizar cada artículo con controles de cantidad y botón para quitar
    let total = 0;
    container.innerHTML = cart.map((item, index) => {
      total += item.price * item.quantity;
      return `<article class="cart-item">
        <div class="cart-placeholder">☕</div>
        <div>
          <h3>${item.name}</h3>
          <p>$${item.price.toFixed(2)} por unidad</p>
          <button class="remove-item" data-remove="${index}">Quitar</button>
        </div>
        <div class="quantity-controls">
          <button aria-label="Restar" data-change="${index}" data-amount="-1">−</button>
          <strong>${item.quantity}</strong>
          <button aria-label="Sumar" data-change="${index}" data-amount="1">+</button>
        </div>
      </article>`;
    }).join("");

    if (totalNode) {
      totalNode.textContent = "$" + total.toFixed(2);
    }
  }

  // ====================================================================
  // Delegación de Eventos Globales de Clic
  // ====================================================================
  document.addEventListener("click", (event) => {
    // 1. Agregar producto al carrito desde menú o promociones
    const addButton = event.target.closest(".add-button");
    if (addButton) {
      addToCart(addButton.dataset.product, addButton.dataset.price);
    }

    // 2. Quitar un producto completo del carrito
    const removeButton = event.target.closest("[data-remove]");
    if (removeButton) {
      const cart = getCart();
      cart.splice(Number(removeButton.dataset.remove), 1);
      saveCart(cart);
      renderCart();
    }

    // 3. Modificar cantidad (+1 / -1) de un ítem en el carrito
    const changeButton = event.target.closest("[data-change]");
    if (changeButton) {
      const cart = getCart();
      const itemIndex = Number(changeButton.dataset.change);
      const item = cart[itemIndex];
      item.quantity += Number(changeButton.dataset.amount);
      if (item.quantity < 1) {
        cart.splice(itemIndex, 1);
      }
      saveCart(cart);
      renderCart();
    }
  });

  // ====================================================================
  // Inicialización al cargar el DOM
  // ====================================================================
  document.addEventListener("DOMContentLoaded", () => {
    // Rellenar dinámicamente el año actual en los footers
    document.querySelectorAll("[data-year]").forEach((node) => {
      node.textContent = new Date().getFullYear();
    });

    // Sincronizar contador y renderizar carrito si la vista lo requiere
    updateCount();
    renderCart();

    // Gestión y validación de formularios (Contacto y Reserva)
    const contactForm = document.getElementById("contactForm");
    const reservationForm = document.getElementById("reservationForm");

    [contactForm, reservationForm].forEach((form) => {
      if (!form) return;

      form.addEventListener("submit", (event) => {
        event.preventDefault();
        form.classList.add("was-validated");

        // Validar campos HTML5 requeridos
        if (!form.checkValidity()) return;

        // Si es formulario de reserva, verificar que el carrito contenga al menos un artículo
        if (form === reservationForm && !getCart().length) {
          toast("Agrega al menos un producto.");
          return;
        }

        // Mostrar notificación de éxito correspondiente
        toast(form === reservationForm ? "Reserva confirmada. ¡Te esperamos!" : "Mensaje enviado. ¡Gracias!");

        // Si se completó la reserva, vaciar carrito
        if (form === reservationForm) {
          localStorage.removeItem(storageKey);
          updateCount([]);
          renderCart();
        }

        // Limpiar formulario y remover estado de validación
        form.reset();
        form.classList.remove("was-validated");
      });
    });
  });
}());
