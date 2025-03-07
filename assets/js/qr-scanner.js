// assets/js/qr-scanner.js
document.addEventListener("DOMContentLoaded", function () {
  // Inicializar el escáner QR cuando se muestra la pestaña de QR
  function initQrScanner() {
    const qrReaderDiv = document.getElementById("qr-reader");
    const qrResultDiv = document.getElementById("qr-result");
    const qrCodeInput = document.getElementById("codigo_qr");

    // Verificar que los elementos existen
    if (!qrReaderDiv || !qrResultDiv || !qrCodeInput) {
      console.error("Elementos de QR no encontrados");
      return;
    }

    // Verificar que la biblioteca se ha cargado
    if (typeof Html5Qrcode === "undefined") {
      qrResultDiv.innerHTML =
        '<p style="color: red;">Error: Librería de escaneo QR no disponible.</p>';
      return;
    }

    // Configurar el escáner
    const html5QrCode = new Html5Qrcode("qr-reader");
    const config = {
      fps: 10,
      qrbox: { width: 250, height: 250 },
    };

    // Iniciar la cámara y el escaneo
    html5QrCode
      .start(
        { facingMode: "environment" }, // Usar cámara trasera
        config,
        (decodedText) => {
          // Cuando se detecta un código QR
          html5QrCode
            .stop()
            .then(() => {
              // Mostrar el resultado
              qrResultDiv.innerHTML =
                '<p style="color: green;">Código QR escaneado con éxito!</p>';
              qrCodeInput.value = decodedText;

              // Enviar automáticamente después de un breve retraso
              setTimeout(() => {
                const form = qrCodeInput.closest("form");
                if (form) form.submit();
              }, 1500);
            })
            .catch((err) => {
              qrResultDiv.innerHTML =
                '<p style="color: red;">Error al detener el escáner: ' +
                err +
                "</p>";
              console.error("Error al detener escáner:", err);
            });
        }
      )
      .catch((err) => {
        qrResultDiv.innerHTML = `<p style="color: red;">Error al iniciar la cámara: ${err}</p>`;
        console.error("Error al iniciar el escáner:", err);
      });
  }

  // Exportar la función para usarla desde otras páginas
  window.initQrScanner = initQrScanner;
});
