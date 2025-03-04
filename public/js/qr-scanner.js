// qr-scanner.js - Script para escanear códigos QR

// qr-scanner.js - Script para escanear códigos QR
document.addEventListener("DOMContentLoaded", function () {
  const qrReaderDiv = document.getElementById("qr-reader");
  const qrResultDiv = document.getElementById("qr-result");
  const qrCodeInput = document.getElementById("codigo_qr");

  // Si los elementos necesarios existen, iniciar el escáner
  if (qrReaderDiv && qrResultDiv && qrCodeInput) {
    if (typeof Html5Qrcode === "undefined") {
      qrResultDiv.innerHTML =
        '<p class="error">Error: Librería de escaneo QR no disponible.</p>';
      return;
    }

    // Configuración del escáner QR
    const html5QrCode = new Html5Qrcode("qr-reader");
    const config = { fps: 10, qrbox: { width: 250, height: 250 } };

    // Iniciar la cámara y el escaneo
    html5QrCode
      .start(
        { facingMode: "environment" }, // Usar cámara trasera si está disponible
        config,
        (decodedText, decodedResult) => {
          // Detener el escáner
          html5QrCode.stop().then(() => {
            // Mostrar el código escaneado
            qrResultDiv.innerHTML =
              '<p class="success">QR escaneado con éxito!</p>';

            // Asignar el valor al campo oculto
            qrCodeInput.value = decodedText;
            // Añade esto justo antes de form.submit()
            qrResultDiv.innerHTML +=
              "<p>Enviando código QR: " + qrCodeInput.value + "</p>";
            console.log("Enviando código QR al servidor:", qrCodeInput.value);

            // Enviar formulario automáticamente
            const form = qrCodeInput.closest("form");
            if (form) form.submit();
          });
        },
        (errorMessage) => {
          // No mostrar errores de escaneo (son normales durante el proceso)
          console.error(errorMessage);
        }
      )
      .catch((err) => {
        qrResultDiv.innerHTML = `<p class="error">Error al iniciar la cámara: ${err}</p>`;
      });
  }
});
