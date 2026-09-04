/**
 * ====================================================================
 * Pausa Café - Lógica JavaScript Principal (main.js)
 * ====================================================================
 * Controla la interactividad del portal web:
 * 1. Gestión del Carrito de Pedidos con persistencia en localStorage.
 * 2. Catálogo de imágenes de productos para visualización en el carrito.
 * 3. Actualización en tiempo real del contador del carrito en el navbar.
 * 4. Renderizado reactivo de pedidos con mini imágenes correspondientes.
 * 5. Sistema de notificaciones emergentes (Toasts).
 * 6. Validación avanzada de formularios (Contacto y Reservas):
 *    - Validación estricta de formato y dominios de email (.com, etc.).
 *    - Validación de fechas futuras/presentes en reservas.
 *    - Restricción de carrito no vacío.
 * 7. Actualización automática del año en el pie de página.
 */

(function () {
  "use strict";

  // Clave de almacenamiento en localStorage para el carrito
  const storageKey = "pausa-cafe-cart";

  // Catálogo de imágenes asociadas a cada artículo de menú y promociones
  const productImages = {
    "Espresso": "../images/espresso.jpg",
    "Cappuccino": "https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=700&q=85",
    "Latte": "https://images.unsplash.com/photo-1541167760496-1628856ab772?auto=format&fit=crop&w=700&q=85",
    "Cold brew": "https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=700&q=85",
    "Croissant": "../images/croissant.jpg",
    "Brownie de cacao": "https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=700&q=85",
    "Cheesecake": "https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=700&q=85",
    "Combo mañanero": "https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=1000&q=85",
    "Combo tarde dulce": "https://images.unsplash.com/photo-1509042239860-f550ce710b93?auto=format&fit=crop&w=1000&q=85",
    "Brunch de la casa": "https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=1000&q=85",
    "Pausa Helada": "https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=1000&q=85",
    "Degustación de Especialidad": "https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=1000&q=85"
  };

  /**
   * Obtiene la lista de productos guardados en localStorage.
   * @returns {Array<{name: string, price: number, quantity: number, image?: string}>}
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
   * Valida que un correo electrónico tenga estructura adecuada y finalización de dominio válida (.com, etc.).
   * @param {string} email
   * @returns {boolean}
   */
  const isValidEmail = (email) => {
    if (!email || typeof email !== "string") return false;
    const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    return emailRegex.test(email.trim());
  };

  /**
   * Verifica que la fecha y hora seleccionada no sea previa al momento actual.
   * @param {string} dateString - Valor del input datetime-local.
   * @returns {boolean}
   */
  const isFutureOrPresentDate = (dateString) => {
    if (!dateString) return false;
    const selected = new Date(dateString);
    if (isNaN(selected.getTime())) return false;
    const now = new Date();
    // Tolerancia de 1 minuto para desfases al enviar formulario
    now.setMinutes(now.getMinutes() - 1);
    return selected >= now;
  };

  /**
   * Agrega un producto al carrito o incrementa su cantidad si ya existe.
   * @param {string} name - Nombre del producto o promoción.
   * @param {string|number} price - Precio unitario del producto.
   * @param {string} [image] - URL opcional de la imagen.
   */
  const addToCart = (name, price, image) => {
    const cart = getCart();
    const resolvedImage = image || productImages[name] || "../images/espresso.jpg";
    const item = cart.find((entry) => entry.name === name);
    if (item) {
      item.quantity += 1;
      if (!item.image) item.image = resolvedImage;
    } else {
      cart.push({ name, price: Number(price), quantity: 1, image: resolvedImage });
    }
    saveCart(cart);
    toast(name + " se agregó a tu pedido.");
  };

  /**
   * Renderiza dinámicamente los productos dentro del contenedor del carrito en reservas.html
   * con su mini imagen representativa y actualiza el total estimado.
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

    // Renderizar cada artículo con mini imagen, detalles, controles de cantidad y botón para quitar
    let total = 0;
    container.innerHTML = cart.map((item, index) => {
      total += item.price * item.quantity;
      const imgSrc = item.image || productImages[item.name] || "../images/espresso.jpg";
      return `<article class="cart-item">
        <img src="${imgSrc}" alt="${item.name}" class="cart-item-img" />
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
      addToCart(addButton.dataset.product, addButton.dataset.price, addButton.dataset.image);
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

    // Restricción de fecha mínima en el selector de fecha y hora de reservas
    const dateInput = document.getElementById("reservationDate");
    if (dateInput) {
      const now = new Date();
      const pad = (n) => String(n).padStart(2, "0");
      const minDateTime = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
      dateInput.min = minDateTime;
    }

    // Gestión y validación del Formulario de Contacto
    const contactForm = document.getElementById("contactForm");
    if (contactForm) {
      const nameInput = document.getElementById("name");
      const emailInput = document.getElementById("email");
      const messageInput = document.getElementById("message");

      contactForm.addEventListener("submit", (event) => {
        event.preventDefault();
        let isValid = true;

        // Validar nombre
        if (!nameInput.value.trim() || nameInput.value.trim().length < 2) {
          nameInput.setCustomValidity("Ingresa tu nombre.");
          isValid = false;
        } else {
          nameInput.setCustomValidity("");
        }

        // Validar email con finalización .com o dominio válido
        if (!isValidEmail(emailInput.value)) {
          emailInput.setCustomValidity("Ingresa un correo electrónico válido (ej: tu@email.com).");
          isValid = false;
        } else {
          emailInput.setCustomValidity("");
        }

        // Validar mensaje
        if (!messageInput.value.trim() || messageInput.value.trim().length < 10) {
          messageInput.setCustomValidity("El mensaje debe contener al menos 10 caracteres.");
          isValid = false;
        } else {
          messageInput.setCustomValidity("");
        }

        contactForm.classList.add("was-validated");

        if (!isValid || !contactForm.checkValidity()) return;

        toast("Mensaje enviado. ¡Gracias!");
        contactForm.reset();
        contactForm.classList.remove("was-validated");
      });

      [nameInput, emailInput, messageInput].forEach((input) => {
        if (input) {
          input.addEventListener("input", () => {
            input.setCustomValidity("");
          });
        }
      });
    }

    // Gestión y validación del Formulario de Reservas (Mi pedido)
    const reservationForm = document.getElementById("reservationForm");
    if (reservationForm) {
      const resName = document.getElementById("reservationName");
      const resDate = document.getElementById("reservationDate");
      const resEmail = document.getElementById("reservationEmail");

      reservationForm.addEventListener("submit", (event) => {
        event.preventDefault();
        let isValid = true;

        // Validar nombre
        if (!resName.value.trim() || resName.value.trim().length < 2) {
          resName.setCustomValidity("Por favor ingresa tu nombre completo.");
          isValid = false;
        } else {
          resName.setCustomValidity("");
        }

        // Validar fecha no previa a la actual
        if (!resDate.value || !isFutureOrPresentDate(resDate.value)) {
          resDate.setCustomValidity("La fecha seleccionada no puede ser anterior a la fecha y hora actual.");
          isValid = false;
        } else {
          resDate.setCustomValidity("");
        }

        // Validar email con dominio
        if (!isValidEmail(resEmail.value)) {
          resEmail.setCustomValidity("Ingresa un correo electrónico válido con dominio (ej: tu@email.com).");
          isValid = false;
        } else {
          resEmail.setCustomValidity("");
        }

        // Comprobar que el carrito contenga al menos un artículo
        if (!getCart().length) {
          toast("Agrega al menos un producto a tu pedido.");
          return;
        }

        reservationForm.classList.add("was-validated");

        if (!isValid || !reservationForm.checkValidity()) return;

        toast("Reserva confirmada. ¡Te esperamos!");
        localStorage.removeItem(storageKey);
        updateCount([]);
        renderCart();
        reservationForm.reset();
        reservationForm.classList.remove("was-validated");
      });

      [resName, resDate, resEmail].forEach((input) => {
        if (input) {
          input.addEventListener("input", () => {
            input.setCustomValidity("");
          });
        }
      });
    }
  });
}());
