/**
 * ==============================================================================
 * Interactividad y Validaciones en Cliente (JavaScript Vanilla)
 * Proyecto: Sprint 2 — ABM Usuarios y Roles
 * ==============================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Inicializar alternador de contraseñas (mostrar / ocultar)
  inicializarTogglePassword();

  // 2. Inicializar cierre automático o manual de alertas flash
  inicializarAlertas();

  // 3. Inicializar buscador dinámico en tabla de usuarios si existe
  inicializarFiltroTabla();
});

/**
 * Muestra una confirmación nativa antes de proceder con una eliminación.
 * Se utiliza en los formularios con método POST para borrar usuarios o roles.
 * 
 * @param {string} mensaje Texto descriptivo de advertencia.
 * @returns {boolean} True si el usuario confirma, False si cancela.
 */
function confirmarEliminacion(mensaje) {
  const texto = mensaje || '¿Estás seguro de que deseas eliminar este registro? Esta acción no se puede deshacer.';
  return window.confirm(texto);
}

/**
 * Valida en el cliente los campos del formulario de usuario antes de enviarlo por POST.
 * Mejora la experiencia de usuario mostrando errores sin recarga de página.
 * 
 * @param {HTMLFormElement} formulario Formulario HTML a validar.
 * @returns {boolean} True si pasa la validación, False si hay errores.
 */
function validarFormularioUsuario(formulario) {
  let esValido = true;
  limpiarErrores(formulario);

  const nombre = formulario.querySelector('[name="name"]');
  const apellido = formulario.querySelector('[name="last_name"]');
  const username = formulario.querySelector('[name="username"]');
  const email = formulario.querySelector('[name="email"]');
  const password = formulario.querySelector('[name="password"]');
  const confirmPassword = formulario.querySelector('[name="confirm_password"]');
  const role = formulario.querySelector('[name="role"]');

  // Validar Nombre
  if (nombre && !nombre.value.trim()) {
    mostrarErrorCampo(nombre, 'El nombre es obligatorio.');
    esValido = false;
  }

  // Validar Apellido
  if (apellido && !apellido.value.trim()) {
    mostrarErrorCampo(apellido, 'El apellido es obligatorio.');
    esValido = false;
  }

  // Validar Username
  if (username) {
    const valUser = username.value.trim();
    if (!valUser) {
      mostrarErrorCampo(username, 'El nombre de usuario es obligatorio.');
      esValido = false;
    } else if (valUser.length < 3) {
      mostrarErrorCampo(username, 'Debe tener al menos 3 caracteres.');
      esValido = false;
    }
  }

  // Validar Email
  if (email) {
    const valEmail = email.value.trim();
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!valEmail) {
      mostrarErrorCampo(email, 'El correo electrónico es obligatorio.');
      esValido = false;
    } else if (!regexEmail.test(valEmail)) {
      mostrarErrorCampo(email, 'Ingresa un correo electrónico válido.');
      esValido = false;
    }
  }

  // Validar Contraseña (si está presente y es obligatoria o requerida)
  if (password) {
    const isRequired = password.hasAttribute('required') || formulario.dataset.modo === 'alta';
    if (isRequired && !password.value) {
      mostrarErrorCampo(password, 'La contraseña es obligatoria.');
      esValido = false;
    } else if (password.value && password.value.length < 6) {
      mostrarErrorCampo(password, 'La contraseña debe tener al menos 6 caracteres.');
      esValido = false;
    }
  }

  // Validar Confirmación de Contraseña
  if (confirmPassword && password) {
    if (confirmPassword.value !== password.value) {
      mostrarErrorCampo(confirmPassword, 'Las contraseñas no coinciden.');
      esValido = false;
    }
  }

  // Validar Rol
  if (role && (!role.value || role.value === '')) {
    mostrarErrorCampo(role, 'Debes seleccionar un rol para el usuario.');
    esValido = false;
  }

  return esValido;
}

/**
 * Valida el formulario de inicio de sesión en el cliente.
 * 
 * @param {HTMLFormElement} formulario Formulario de Login.
 * @returns {boolean}
 */
function validarFormularioLogin(formulario) {
  let esValido = true;
  limpiarErrores(formulario);

  const identificador = formulario.querySelector('[name="identificador"]');
  const password = formulario.querySelector('[name="password"]');

  if (identificador && !identificador.value.trim()) {
    mostrarErrorCampo(identificador, 'Ingresa tu usuario o correo electrónico.');
    esValido = false;
  }

  if (password && !password.value) {
    mostrarErrorCampo(password, 'Ingresa tu contraseña.');
    esValido = false;
  }

  return esValido;
}

/**
 * Muestra el mensaje de error visual debajo del input correspondiente.
 * 
 * @param {HTMLElement} input Elemento input o select con error.
 * @param {string} mensaje Texto del error a mostrar.
 */
function mostrarErrorCampo(input, mensaje) {
  input.classList.add('is-invalid');
  const parent = input.closest('.form-group');
  if (parent) {
    let feedback = parent.querySelector('.error-feedback');
    if (!feedback) {
      feedback = document.createElement('div');
      feedback.className = 'error-feedback';
      parent.appendChild(feedback);
    }
    feedback.innerHTML = `<span>⚠️ ${mensaje}</span>`;
  }
}

/**
 * Limpia los estilos y mensajes de error previos de un formulario.
 * 
 * @param {HTMLFormElement} formulario 
 */
function limpiarErrores(formulario) {
  const invalidInputs = formulario.querySelectorAll('.is-invalid');
  invalidInputs.forEach(input => input.classList.remove('is-invalid'));

  const feedbacks = formulario.querySelectorAll('.error-feedback');
  feedbacks.forEach(fb => fb.remove());
}

/**
 * Configura los botones de alternar visualización de contraseñas.
 */
function inicializarTogglePassword() {
  const toggleButtons = document.querySelectorAll('.btn-toggle-password');
  toggleButtons.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-target');
      const input = document.getElementById(targetId);
      if (input) {
        if (input.type === 'password') {
          input.type = 'text';
          btn.innerHTML = '👁️‍🗨️';
          btn.setAttribute('title', 'Ocultar contraseña');
        } else {
          input.type = 'password';
          btn.innerHTML = '👁️';
          btn.setAttribute('title', 'Mostrar contraseña');
        }
      }
    });
  });
}

/**
 * Configura el cierre de alertas y su desvanecimiento automático tras 5 segundos.
 */
function inicializarAlertas() {
  const alerts = document.querySelectorAll('.alert');
  alerts.forEach(alert => {
    // Botón de cerrar manual
    const closeBtn = alert.querySelector('.alert-close');
    if (closeBtn) {
      closeBtn.addEventListener('click', () => {
        alert.style.transition = 'opacity 0.25s ease, transform 0.25s ease';
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => alert.remove(), 250);
      });
    }

    // Auto-cierre progresivo si no es de error crítico
    if (!alert.classList.contains('alert-error')) {
      setTimeout(() => {
        if (document.body.contains(alert)) {
          alert.style.transition = 'opacity 0.4s ease, transform 0.4s ease';
          alert.style.opacity = '0';
          alert.style.transform = 'translateY(-6px)';
          setTimeout(() => alert.remove(), 400);
        }
      }, 6000);
    }
  });
}

/**
 * Filtra en tiempo real los registros de la tabla según el texto ingresado en el buscador.
 */
function inicializarFiltroTabla() {
  const searchInput = document.getElementById('tabla-buscador');
  const tabla = document.querySelector('.data-table tbody');

  if (searchInput && tabla) {
    searchInput.addEventListener('input', (e) => {
      const termino = e.target.value.toLowerCase().trim();
      const filas = tabla.querySelectorAll('tr');

      filas.forEach(fila => {
        const texto = fila.textContent.toLowerCase();
        if (texto.includes(termino)) {
          fila.style.display = '';
        } else {
          fila.style.display = 'none';
        }
      });
    });
  }
}
