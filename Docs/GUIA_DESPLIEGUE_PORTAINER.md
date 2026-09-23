# Guía Definitiva de Despliegue y Operación Segura de Portainer CE

> **Entorno:** Servidor Ubuntu de pruebas / producción (`10.129.1.105`)  
> **Propósito:** Administración visual centralizada de todos los contenedores de COSMOL (`Cosmol-Chatbot`, `COSMOL-Reportes`, `COSMOL-app`, n8n, PostgreSQL, Caddy).  
> **Modo de Instalación:** Contenedor aislado e independiente mediante `docker-compose.yml`.  
> **Fecha de Actualización:** Septiembre 2026 (Versión 2.0 - Guía Práctica de Seguridad)

---

## 1. Arquitectura y Estructura en el Servidor

Portainer se ubicará en su **propia carpeta independiente**, fuera de cualquier proyecto particular. De esta manera, si detienes o reinicias el Chatbot o Reportes, **Portainer jamás se apagará**.

Estructura de directorios recomendada en `/home/franco1455`:
```text
/home/franco1455/
├── Cosmol-Chatbot/       ← docker-compose del Chatbot y WhatsApp
├── COSMOL-Reportes/      ← docker-compose del Sistema de Reportes
├── COSMOL-app/           ← docker-compose de la App Móvil
└── portainer/            ← Carpeta aislada y exclusiva de Portainer
    └── docker-compose.yml
```

> **¿Cómo ve Portainer a todos los proyectos?**  
> Gracias al volumen `- /var/run/docker.sock:/var/run/docker.sock`, Portainer se comunica directamente con el motor global de Docker en Linux, detectando en tiempo real todos los contenedores de los 3 proyectos sin necesidad de estar en la misma red.

---

## 2. Paso a Paso: Despliegue en la Terminal Ubuntu

Ejecuta los siguientes comandos en la consola de tu servidor Ubuntu (`franco1455@ubuntuserver`):

### Paso 2.1: Crear la carpeta dedicada
```bash
mkdir -p ~/portainer
cd ~/portainer
```

### Paso 2.2: Crear el archivo `docker-compose.yml`
Abre el editor de texto:
```bash
nano docker-compose.yml
```

Pega la siguiente configuración blindada:

```yaml
services:
  portainer:
    image: portainer/portainer-ce:latest
    container_name: portainer
    restart: always
    ports:
      # Puerto HTTPS nativo (recomendado)
      - "9443:9443"
      # Puerto HTTP local (alternativa directa sin certificados autofirmados)
      - "9000:9000"
    volumes:
      # Conexión al socket del demonio Docker del servidor
      - /var/run/docker.sock:/var/run/docker.sock
      # Persistencia de cuentas, contraseñas y configuraciones
      - portainer_data:/data

volumes:
  portainer_data:
    name: portainer_data
```

> 💡 **Nota de Seguridad Extra:** Si deseas que Portainer responda **únicamente** a la tarjeta de red interna del servidor y no a ninguna otra interfaz, puedes cambiar la línea de puertos por:  
> `- "10.129.1.105:9443:9443"`

Guarda el archivo en `nano` con `Ctrl + O`, presiona `Enter`, y sal con `Ctrl + X`.

### Paso 2.3: Habilitar los puertos en el Firewall UFW (¡Paso Crítico!)
En Ubuntu Server, si el firewall UFW está activo, **bloqueará silenciosamente los puertos 9443 y 9000**, haciendo que el navegador se quede en *"Cargando..."* indefinidamente.
Ejecuta en la consola:
```bash
# Permitir puerto HTTPS (9443) y HTTP (9000)
sudo ufw allow 9443/tcp
sudo ufw allow 9000/tcp
sudo ufw reload
```
*(O si prefieres restringirlo exclusivamente a la red de COSMOL: `sudo ufw allow from 10.129.1.0/24 to any port 9443 proto tcp`)*

### Paso 2.4: Levantar Portainer
```bash
cd ~/portainer
sudo docker compose up -d
```
Docker descargará la imagen oficial (`portainer/portainer-ce:latest`), creará el volumen `portainer_data` y levantará el servicio en segundo plano.

---

## 3. Protocolo Inmediato de Seguridad (Primer Acceso Web)

> [!WARNING]
> ### ⏱️ Ventana de Seguridad Inicial (5 Minutos)
> Por diseño de seguridad, cuando Portainer arranca por primera vez, concede un temporizador de **5 minutos** para registrar la contraseña del usuario `admin`.  
> **Si nadie se registra en ese tiempo, Portainer se apaga automáticamente** para evitar que un intruso reclame el control del servidor.  
> *(Si te ocurre esto o se queda cargando sin responder, simplemente ejecuta `sudo docker compose restart` en la carpeta `~/portainer` para reiniciar el temporizador).*

Sigue estos pasos **inmediatamente después** de ejecutar `docker compose up -d`:

1. Desde tu computadora conectada a la red de COSMOL, abre el navegador web y entra a cualquiera de estas dos direcciones:
   * **Opción A (Recomendada - HTTPS):**
     ```text
     https://10.129.1.105:9443
     ```
   * **Opción B (Alternativa directa sin alertas SSL - HTTP):**
     ```text
     http://10.129.1.105:9000
     ```

2. **Aviso de Certificado SSL (Si usas la Opción A):**
   * Te saldrá una pantalla roja de advertencia (*"La conexión no es privada"* o *"Certificado no válido"*). Esto es normal porque Portainer genera un certificado SSL propio autofirmado. Dale clic en **Configuración avanzada** y luego en **Continuar a 10.129.1.105 (no seguro)**.
   * *Nota para Google Chrome/Edge:* Si el navegador no te muestra el botón de avanzar, haz clic en cualquier lugar en blanco de la pantalla y escribe con tu teclado la palabra secreta: `thisisunsafe`. La pantalla cargará de inmediato.

3. **Creación de la Cuenta Administrador:**
   * **Username:** `admin`
   * **Password:** Crea una contraseña robusta (mínimo 16 caracteres, combinando mayúsculas, minúsculas, números y caracteres especiales).
4. **Desactivar Telemetría:** Desmarca la casilla que dice *"Allow collection of anonymous statistics"* (para máxima privacidad institucional de COSMOL).
5. Haz clic en el botón azul **"Create user"**.
6. **Conectar el Entorno:** En la siguiente pantalla, selecciona la tarjeta **"Get Started"** (que conecta automáticamente con el entorno Docker local `local`).

---

## 4. Solución de Problemas Frecuentes (Troubleshooting)

### ¿Por qué mi navegador se queda "Cargando..." y no abre Portainer?

Si al ingresar a `https://10.129.1.105:9443` el navegador se queda girando y nunca muestra la pantalla ni la opción de "Configuración avanzada", sigue estos 4 diagnósticos rápidos en la terminal de Ubuntu:

#### 1. Verificar si el temporizador de 5 minutos apagó Portainer
Ejecuta:
```bash
sudo docker logs portainer --tail 20
```
Si ves un mensaje que dice: `Your Portainer instance timed out for security purposes...`, Portainer se auto-apagó porque pasaron más de 5 minutos sin registrar la cuenta admin.  
**Solución:** Reinícialo inmediatamente para que te dé otros 5 minutos:
```bash
cd ~/portainer
sudo docker compose restart
```

#### 2. Verificar que el Firewall UFW no esté bloqueando los paquetes
Si UFW está activo y no tiene abierto el puerto, descartará la conexión en silencio y el navegador se colgará esperando.  
**Solución:**
```bash
sudo ufw allow 9443/tcp
sudo ufw allow 9000/tcp
sudo ufw reload
```

#### 3. Probar si Portainer responde dentro del propio servidor
Para asegurarte de que el contenedor funciona al 100%:
```bash
curl -k -I https://localhost:9443
```
Si te responde con `HTTP/2 200` o `HTTP/1.1 200 OK`, Portainer está activo y escuchando perfectamente.

#### 4. Entrar por el puerto HTTP alternativo (9000)
Si tu navegador o antivirus corporativo bloquea el protocolo TLS autofirmado del puerto 9443:
```text
http://10.129.1.105:9000
```
Al ser HTTP plano dentro de tu red local, cargará de forma instantánea sin mostrar pantalla roja de advertencia ni requerir certificados.

---

## 5. Medidas de Blindaje Post-Instalación (Recomendadas)

Una vez dentro del panel de Portainer:

### 5.1 Activar Autenticación de Doble Factor (2FA / TOTP)
Para que nadie pueda entrar solo con la contraseña:
1. Haz clic en el icono de usuario (arriba a la derecha) y entra a **User settings**.
2. Desplázate hasta la sección **Two-factor authentication**.
3. Haz clic en **Enable two-factor authentication**.
4. Escanea el código QR con tu celular usando Google Authenticator, Microsoft Authenticator o Authy.
5. Ingresa el código de 6 dígitos para confirmar y guarda los códigos de recuperación.

### 5.2 Ajustar el Tiempo de Cierre de Sesión por Inactividad
Para que la sesión web se cierre sola si dejas la pestaña abierta en tu computadora:
1. En el menú lateral izquierdo, ve a **Settings**.
2. En el campo **Session lifetime**, cámbialo a `30m` (30 minutos) o `15m`.
3. Haz clic en **Save settings**.

---

## 6. Guía de Uso Diario para el Equipo de COSMOL

Al hacer clic en el entorno **"local"** y luego en **"Containers"**, verás reunidos todos los contenedores del servidor:

| Icono / Función | ¿Para qué sirve en COSMOL? |
|---|---|
| **Logs (Icono de hoja 📄)** | Muestra los errores de Apache, PHP o n8n en vivo y con actualización automática, sin necesidad de conectarse por SSH. |
| **Stats (Icono de gráfico 📊)** | Permite ver el uso real de CPU y memoria RAM de PostgreSQL, el Chatbot y la App para comprobar que no se sature el servidor. |
| **Console (Icono `>_`)** | Permite abrir una terminal dentro de cualquier contenedor (ej. para correr comandos de Composer o revisar archivos de logs) con un solo clic. |
| **Restart (Botón Reiniciar)** | Si un servicio se traba o actualizas una variable de entorno, puedes reiniciarlo de forma limpia con un clic. |

> 🚫 **Regla de Oro:** Nunca presionar el botón **"Remove"** (Eliminar) en contenedores marcados como parte de un Stack existente a menos que se desee borrar el servicio de forma definitiva.

---

## 7. Comandos Útiles de Mantenimiento

Todos estos comandos se ejecutan dentro de la carpeta `~/portainer`:

```bash
# Ver estado del contenedor de Portainer
sudo docker compose ps

# Reiniciar Portainer (si hubo cambio de red o expiró el timeout de inicio)
sudo docker compose restart

# Ver los logs en vivo
sudo docker compose logs -f

# Detener Portainer temporalmente
sudo docker compose down

# Desinstalación limpia total (elimina contenedor y borra el volumen de datos)
sudo docker compose down -v
```

