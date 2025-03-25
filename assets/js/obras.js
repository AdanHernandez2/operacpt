/**
 * =============================================
 * GALERÍA MASONRY PARA EL SINGLE DE OBRA
 * =============================================
 */
document.addEventListener("DOMContentLoaded", function () {
  // Inicializa Masonry si está disponible
  if (typeof Masonry !== "undefined") {
    $("[data-masonry]").each(function () {
      new Masonry(this, $(this).data("masonry"));
    });
  }
});

/**
 * Maneja la descarga del dossier protegido por contraseña
 */
document.addEventListener("DOMContentLoaded", function () {
  // Selecciona todos los formularios de dossier
  const forms = document.querySelectorAll(".dossier-form");

  // Si no hay formularios, salimos
  if (!forms.length) {
    console.log("No se encontraron formularios .dossier-form");
    return;
  }

  // Procesamos cada formulario
  forms.forEach((form) => {
    form.addEventListener("submit", function (e) {
      e.preventDefault(); // Evita el envío tradicional

      // Obtiene los datos del formulario
      const postId = form.getAttribute("data-post-id");
      const claveInput = form.querySelector('input[type="password"]');
      const clave = claveInput.value.trim();
      const botonSubmit = form.querySelector('button[type="submit"]');

      // Validación básica
      if (!clave) {
        alert("Por favor ingresa la clave");
        claveInput.focus();
        return;
      }

      // Deshabilita el botón para evitar múltiples envíos
      if (botonSubmit) {
        botonSubmit.disabled = true;
        botonSubmit.textContent = "Verificando...";
      }

      // Realiza la petición AJAX
      fetch(progresiObrasVars.ajax_url, {
        method: "POST",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams({
          action: "verificar_clave_dossier",
          post_id: postId,
          clave: clave,
          nonce: progresiObrasVars.nonce,
        }),
      })
        .then((response) => {
          if (!response.ok) {
            throw new Error("Error en la red");
          }
          return response.json();
        })
        .then((data) => {
          if (data.success) {
            // Redirige a la URL de descarga
            window.location.href = data.data.url;
          } else {
            // Muestra error y reactiva el botón
            alert(data.data.message || "Clave incorrecta");
            if (botonSubmit) {
              botonSubmit.disabled = false;
              botonSubmit.textContent = "Descargar";
            }
            claveInput.select();
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          alert("Ocurrió un error. Por favor intenta nuevamente.");
          if (botonSubmit) {
            botonSubmit.disabled = false;
            botonSubmit.textContent = "Descargar";
          }
        });
    });
  });
});
