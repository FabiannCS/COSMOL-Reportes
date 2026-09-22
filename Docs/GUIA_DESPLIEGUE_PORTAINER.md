# Guía de Despliegue y Operación Segura de Portainer CE

> **Entorno:** Servidor Ubuntu de pruebas / producción (`10.129.1.105`)  
> **Propósito:** Administración visual centralizada de los contenedores Docker de COSMOL (`Cosmol-Chatbot`, `COSMOL-Reportes`, n8n, PostgreSQL, Caddy).  
> **Estado:** Documentado para futura integración / activación cuando sea requerida.

---

## 1. Principios de Seguridad Estrictos

1. **Aislamiento en Red Local:** El puerto `9443` (HTTPS) debe ser accesible **únicamente dentro de la red privada de COSMOL** (`10.129.1.X`) o mediante VPN/SSH. **Bajo ninguna circunstancia debe exponerse a internet público ni enrutarse con subdominio público en Caddy**.
2. **Ventana de Seguridad Inicial (5-10 min):** Al arrancar por primera vez, Portainer concede una ventana de pocos minutos para registrar la contraseña de `admin`. El primer acceso debe ser inmediato tras levantarlo.
3. **Persistencia:** Requiere un volumen dedicado (`portainer_data`) para mantener credenciales y configuraciones entre reinicios del host.

---

## 2. Comandos de Instalación (Terminal Ubuntu)

```bash
# 1. Crear volumen persistente
sudo docker volume create portainer_data

# 2. Desplegar el contenedor de Portainer CE
sudo docker run -d \
  -p 9443:9443 \
  --name portainer \
  --restart=always \
  -v /var/run/docker.sock:/var/run/docker.sock \
  -v portainer_data:/data \
  portainer/portainer-ce:latest
```

---

## 3. Primer Acceso y Configuración

1. Abrir en un navegador dentro de la red local:
   ```text
   https://10.129.1.105:9443
   ```
2. Aceptar el certificado autofirmado temporal (*Avanzado -> Continuar*).
3. Crear el usuario `admin` con contraseña robusta (mínimo 16 caracteres recomendados).
4. Desmarcar la casilla de telemetría y hacer clic en **"Create user"**.
5. Seleccionar **"Get Started"** para conectar automáticamente con el entorno local de Docker (`local`).

---

## 4. Convivencia con los Proyectos Existentes

* **Cero interferencia:** Portainer se conecta a `/var/run/docker.sock` en modo pasivo. No detiene, altera ni reinicia contenedores existentes por su cuenta.
* **Compatibilidad con Terminal:** Se puede seguir utilizando `sudo docker compose up -d` y la consola normalmente; los cambios se reflejan en tiempo real en Portainer.
* **Consumo:** ~30 MB de RAM y 0.0% CPU en reposo.

---

## 5. Procedimiento de Desinstalación Limpia (Rollback)

Si en algún momento se desea remover Portainer completamente sin dejar rastro:

```bash
sudo docker stop portainer && sudo docker rm portainer
sudo docker volume rm portainer_data
```
