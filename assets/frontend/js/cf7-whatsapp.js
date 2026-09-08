jQuery(document).ready(function ($) {
  $(document).on("wpcf7submit", function (event) {
    var $form = $(event.target);

    // Pastikan form berhasil divalidasi
    if (event.detail.status !== "validation_failed") {
      // Nomor WhatsApp tujuan (dari Setting WP: Contact > WhatsApp)
      var adminWhatsApp =
        (typeof CustomPluginCf7Wa !== "undefined" && CustomPluginCf7Wa.number
          ? CustomPluginCf7Wa.number
          : "6285806522700"
        ).replace(/\D/g, "") || "6285806522700";

      // Ambil data
      var name = $form.find('[name="your-name"]').val() || "";
      var city = $form.find('[name="your-city"]').val() || "";

      // Pesan WhatsApp
      var message =
        "Halo Admin Tradershood, saya " +
        name +
        " dari " +
        city +
        ". Saya sudah mengisi formulir pendaftaran di website dan berkomitmen untuk belajar bersama menuju kelompok 2% trader sukses. Mohon info langkah selanjutnya untuk bergabung di komunitas. Terima kasih!";

      // URL WhatsApp
      var whatsappURL =
        "https://wa.me/" +
        adminWhatsApp +
        "?text=" +
        encodeURIComponent(message);

      // Langsung buka WhatsApp di tab baru
      window.open(whatsappURL, "_blank", "noopener,noreferrer");
    }
  });
});
