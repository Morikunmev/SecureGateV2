document.addEventListener("DOMContentLoaded", function () {
  // Agregar un log más visible al inicio
  console.clear(); // Limpiar la consola antes
  console.log(
    "%c QR SCANNER INICIADO",
    "background: #4CAF50; color: white; padding: 5px; font-size: 16px; font-weight: bold;"
  );

  // Inicializar el escáner QR cuando se muestra la pestaña de QR
  function initQrScanner() {
    console.log(
      "%c INICIANDO ESCÁNER QR",
      "background: #2196F3; color: white; padding: 3px;"
    );

    const qrReaderDiv = document.getElementById("qr-reader");
    const qrResultDiv = document.getElementById("qr-result");
    const qrCodeInput = document.getElementById("codigo_qr");

    // Verificar que los elementos existen
    if (!qrReaderDiv || !qrResultDiv || !qrCodeInput) {
      console.error(
        "%c ERROR: ELEMENTOS NO ENCONTRADOS",
        "background: red; color: white; padding: 3px;",
        {
          qrReaderDiv: !!qrReaderDiv,
          qrResultDiv: !!qrResultDiv,
          qrCodeInput: !!qrCodeInput,
        }
      );
      return;
    }

    console.log("Elementos del DOM encontrados correctamente");

    // Verificar que la biblioteca se ha cargado
    if (typeof Html5Qrcode === "undefined") {
      console.error(
        "%c ERROR: LIBRERÍA QR NO DISPONIBLE",
        "background: red; color: white; padding: 3px;"
      );
      qrResultDiv.innerHTML =
        '<p style="color: red;">Error: Librería de escaneo QR no disponible.</p>';
      return;
    }

    // Configurar el escáner
    console.log("Configurando escáner QR");
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
          console.log(
            "%c CÓDIGO QR DETECTADO!",
            "background: #4CAF50; color: white; padding: 3px; font-size: 14px;"
          );
          console.log("Contenido:", decodedText);

          html5QrCode
            .stop()
            .then(() => {
              console.log("Escáner detenido correctamente");

              // Mostrar mensaje simple en la interfaz
              qrResultDiv.innerHTML = `
            <div style="background-color: #d4edda; border: 1px solid #c3e6cb; border-radius: 4px; padding: 10px; margin-bottom: 15px;">
              <p style="color: #155724; margin: 0;">Código QR escaneado con éxito!</p>
              <p style="font-family: monospace; background: #f8f9fa; padding: 5px; border-radius: 3px; margin-top: 10px;">${decodedText}</p>
            </div>
          `;

              // Asignar el valor al campo oculto
              qrCodeInput.value = decodedText;

              // Crear un botón para enviar manualmente (esto te dará tiempo para ver los logs)
              const btnEnviar = document.createElement("button");
              btnEnviar.type = "button";
              btnEnviar.className = "btn-qr-submit";
              btnEnviar.textContent = "Iniciar sesión con este código";
              btnEnviar.style.backgroundColor = "#4CAF50";
              btnEnviar.style.color = "white";
              btnEnviar.style.border = "none";
              btnEnviar.style.padding = "10px 15px";
              btnEnviar.style.borderRadius = "4px";
              btnEnviar.style.cursor = "pointer";
              btnEnviar.style.marginTop = "10px";
              btnEnviar.style.width = "100%";

              btnEnviar.onclick = function () {
                console.log(
                  "%c ENVIANDO FORMULARIO",
                  "background: #ff9800; color: white; padding: 3px;"
                );
                console.log("Código enviado:", decodedText);

                // Agrega un mensaje visible al usuario
                qrResultDiv.innerHTML += `
              <div style="background-color: #fff3cd; border: 1px solid #ffeeba; border-radius: 4px; padding: 10px; margin-top: 10px; margin-bottom: 15px;">
                <p style="color: #856404; margin: 0;">Enviando código, por favor espera...</p>
              </div>
            `;

                // Enviar el formulario
                const form = qrCodeInput.closest("form");
                if (form) {
                  form.submit();
                } else {
                  console.error("No se encontró el formulario");
                }
              };

              qrResultDiv.appendChild(btnEnviar);
            })
            .catch((err) => {
              console.error("Error al detener escáner:", err);
              qrResultDiv.innerHTML =
                '<p style="color: red;">Error al detener el escáner</p>';
            });
        },
        (errorMessage) => {
          // No mostrar errores transitorios en la consola durante el escaneo normal
        }
      )
      .catch((err) => {
        console.error(
          "%c ERROR AL INICIAR CÁMARA",
          "background: red; color: white; padding: 3px;"
        );
        console.error("Detalles:", err);
        qrResultDiv.innerHTML = `<p style="color: red;">Error al iniciar la cámara: ${
          err.message || err
        }</p>`;
      });
  }

  // Exportar la función para usarla desde otras páginas
  window.initQrScanner = initQrScanner;
  console.log("Función initQrScanner disponible globalmente");
});
