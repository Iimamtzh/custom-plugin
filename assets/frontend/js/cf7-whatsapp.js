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
      var email = $form.find('[name="your-email"]').val() || "";
      var whatsapp = $form.find('[name="your-whatsapp"]').val() || "";
      var city = $form.find('[name="your-city"]').val() || "";
      var experience = $form.find('[name="trading-experience"]').val() || "";
      var readiness = $form.find('[name="learning-readiness"]').val() || "";

      // Ambil checkbox yang dicentang
      var challenges = [];
      $form.find('[name="trading-challenge[]"]:checked').each(function () {
        challenges.push($(this).val());
      });
      var challengeText = challenges.join("\n- ");

      // Pesan WhatsApp
      var message =
        "Halo, saya tertarik untuk belajar trading dan ingin bergabung.\n\n" +
        "*DATA PENDAFTAR*\n\n" +
        "*Nama Lengkap:* " +
        name +
        "\n\n" +
        "*Email:* " +
        email +
        "\n\n" +
        "*Nomor WhatsApp Aktif:* " +
        whatsapp +
        "\n\n" +
        "*Kota Domisili Saat Ini:* " +
        city +
        "\n\n" +
        "*Lama Terjun di Dunia Trading:* " +
        experience +
        "\n\n" +
        "*Tantangan Terbesar:*\n- " +
        challengeText +
        "\n\n" +
        "*Kesiapan Belajar:* " +
        readiness;

      // URL WhatsApp
      var whatsappURL =
        "https://wa.me/" +
        adminWhatsApp +
        "?text=" +
        encodeURIComponent(message);

      // Langsung buka WhatsApp
      window.location.href = whatsappURL;
    }
  });
});
