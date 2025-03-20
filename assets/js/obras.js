jQuery(function ($) {
  $(".form-descarga-pdf").on("submit", function (e) {
    e.preventDefault();
    const $form = $(this);
    const $feedback = $form.find(".descarga-feedback");

    $.ajax({
      url: obrasData.ajaxurl,
      type: "POST",
      data: {
        action: "verificar_clave_dossier",
        nonce: obrasData.nonce,
        post_id: $form.data("post-id"),
        clave: $form.find('[name="clave"]').val(),
      },
      beforeSend: () => {
        $feedback
          .removeClass("error success")
          .html('<div class="loading-spinner"></div>');
      },
      success: (response) => {
        if (response.success) {
          $feedback.addClass("success").html("✓ Verificación exitosa...");
          setTimeout(() => (window.location.href = response.data.url), 1000);
        } else {
          $feedback.addClass("error").html("✗ Clave incorrecta");
        }
      },
      error: () => {
        $feedback.addClass("error").html("Error de conexión");
      },
    });
  });
});
