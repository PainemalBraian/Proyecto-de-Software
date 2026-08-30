/* Interacciones compartidas: año, carrito de pedido y formularios. */
(function () {
  "use strict";

  const storageKey = "pausa-cafe-cart";
  const getCart = () => JSON.parse(localStorage.getItem(storageKey) || "[]");
  const saveCart = (cart) => {
    localStorage.setItem(storageKey, JSON.stringify(cart));
    updateCount(cart);
  };
  const updateCount = (cart = getCart()) => {
    const count = cart.reduce((total, item) => total + item.quantity, 0);
    document.querySelectorAll(".cart-count").forEach((node) => { node.textContent = count; });
  };
  const toast = (message) => {
    const node = document.querySelector(".toast-message");
    if (!node) return;
    node.textContent = message;
    node.classList.add("show");
    window.setTimeout(() => node.classList.remove("show"), 2200);
  };
  const addToCart = (name, price) => {
    const cart = getCart();
    const item = cart.find((entry) => entry.name === name);
    if (item) item.quantity += 1;
    else cart.push({ name, price: Number(price), quantity: 1 });
    saveCart(cart);
    toast(name + " se agregó a tu pedido.");
  };

  function renderCart() {
    const container = document.getElementById("cartItems");
    const totalNode = document.getElementById("cartTotal");
    if (!container) return;
    const cart = getCart();
    if (!cart.length) {
      container.innerHTML = '<div class="empty-cart">Todavía no agregaste productos.<br><a class="text-link" href="menu.html">Explorar el menú →</a></div>';
      if (totalNode) totalNode.textContent = "$0.00";
      return;
    }
    let total = 0;
    container.innerHTML = cart.map((item, index) => {
      total += item.price * item.quantity;
      return `<article class="cart-item"><div class="cart-placeholder">☕</div><div><h3>${item.name}</h3><p>$${item.price.toFixed(2)} por unidad</p><button class="remove-item" data-remove="${index}">Quitar</button></div><div class="quantity-controls"><button aria-label="Restar" data-change="${index}" data-amount="-1">−</button><strong>${item.quantity}</strong><button aria-label="Sumar" data-change="${index}" data-amount="1">+</button></div></article>`;
    }).join("");
    if (totalNode) totalNode.textContent = "$" + total.toFixed(2);
  }

  document.addEventListener("click", (event) => {
    const addButton = event.target.closest(".add-button");
    if (addButton) addToCart(addButton.dataset.product, addButton.dataset.price);
    const removeButton = event.target.closest("[data-remove]");
    if (removeButton) {
      const cart = getCart();
      cart.splice(Number(removeButton.dataset.remove), 1);
      saveCart(cart); renderCart();
    }
    const changeButton = event.target.closest("[data-change]");
    if (changeButton) {
      const cart = getCart();
      const item = cart[Number(changeButton.dataset.change)];
      item.quantity += Number(changeButton.dataset.amount);
      if (item.quantity < 1) cart.splice(Number(changeButton.dataset.change), 1);
      saveCart(cart); renderCart();
    }
  });

  document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-year]").forEach((node) => { node.textContent = new Date().getFullYear(); });
    updateCount(); renderCart();
    const contactForm = document.getElementById("contactForm");
    const reservationForm = document.getElementById("reservationForm");
    [contactForm, reservationForm].forEach((form) => {
      if (!form) return;
      form.addEventListener("submit", (event) => {
        event.preventDefault();
        form.classList.add("was-validated");
        if (!form.checkValidity()) return;
        if (form === reservationForm && !getCart().length) { toast("Agrega al menos un producto."); return; }
        toast(form === reservationForm ? "Reserva confirmada. ¡Te esperamos!" : "Mensaje enviado. ¡Gracias!");
        if (form === reservationForm) { localStorage.removeItem(storageKey); updateCount([]); renderCart(); }
        form.reset(); form.classList.remove("was-validated");
      });
    });
  });
}());
