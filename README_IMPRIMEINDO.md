# 🖨️ Imprimeindo — Sistema de Impresiones con Laravel

**Imprimeindo** es un sistema desarrollado en **Laravel** que ofrece una interfaz amigable para gestionar y enviar impresiones directamente a una impresora.  
El proyecto está diseñado para funcionar como una **máquina expendedora de impresiones**, con sesiones únicas por usuario y control total desde un **panel administrativo**.

---

## 🚀 Descripción general

El sistema permite que los usuarios se conecten mediante una **sesión Wi-Fi** que los dirige automáticamente al sitio web del sistema.  
Desde esta interfaz podrán **subir archivos PDF o Word**, configurar cómo desean imprimirlos (color, blanco y negro, rango de páginas, etc.), y confirmar el costo antes de imprimir.

Una vez completada la impresión, los archivos se eliminan automáticamente para mantener el sistema limpio y eficiente.

---

## 🧩 Características principales

### 👥 Usuarios
- Acceso mediante **sesiones únicas con código QR** generado por el sistema.
- Subida de **uno o varios archivos PDF/DOCX**.
- Configuración detallada de impresión:
  - Tipo de impresión: color / blanco y negro.
  - Rango de páginas.
  - Número de copias.
- Vista previa del costo total antes de confirmar la impresión.
- Eliminación automática de archivos una vez impresos.

### 🖨️ Impresora
- Modelo de impresora compatible: **EPSON L325**.
- Conexión a través de **controladores de sistema operativo** o librerías PHP específicas.
- Uso recomendado de librerías:
  - [`mike42/escpos-php`](https://github.com/mike42/escpos-php) para comunicación directa con impresoras Epson.
  - Control de cola de impresión mediante comandos `lp` o `cups` en Linux.

Ejemplo de configuración en Laravel:
```php
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

$connector = new WindowsPrintConnector("EPSON_L325");
$printer = new Printer($connector);
$printer->text("Ejemplo de impresión desde Laravel.\n");
$printer->cut();
$printer->close();
```

> 💡 **Nota:** Asegúrate de instalar los drivers más recientes de EPSON L325 para tu sistema operativo antes de realizar pruebas de impresión.

---

## 🧠 Arquitectura del sistema

- **Frontend y Backend:** Laravel (Blade + Controllers + Models)
- **Base de datos:** MongoDB
- **Autenticación de administradores:** Ruta protegida y aislada para el panel admin.
- **Sesiones de usuario:** Generadas mediante **QR Codes** (biblioteca `simple-qrcode` de Laravel).
- **Almacenamiento temporal:** Archivos subidos se eliminan automáticamente tras su impresión exitosa.
- **Panel de administración:**  
  - Visualización de impresiones realizadas.
  - Monitoreo de sesiones activas.
  - Estadísticas de uso del sistema.

---

## ⚙️ Requerimientos del sistema

### 🧾 Software
- PHP >= 8.2
- Composer
- Laravel 11.x
- MongoDB
- Servidor web (Apache o Nginx)
- Drivers EPSON L325 instalados
- Sistema operativo recomendado: **Ubuntu 22.04 / Windows 10+**

### 📦 Dependencias recomendadas
```bash
composer require mike42/escpos-php
composer require simplesoftwareio/simple-qrcode
composer require mongodb/mongodb
```

### 🔧 Extensiones PHP necesarias
- `ext-mbstring`
- `ext-xml`
- `ext-curl`
- `ext-gd`
- `ext-mongodb`

---

## 🗂️ Estructura del proyecto

```
imprimeindo/
├── app/
│   ├── Http/Controllers/
│   │   ├── PrintController.php
│   │   ├── SessionController.php
│   │   └── AdminController.php
│   ├── Models/
│   │   ├── File.php
│   │   ├── Session.php
│   │   └── PrintJob.php
│   └── Services/
│       └── PrinterService.php
├── resources/views/
│   ├── user/
│   │   ├── upload.blade.php
│   │   ├── summary.blade.php
│   │   └── qr.blade.php
│   └── admin/
│       ├── dashboard.blade.php
│       ├── sessions.blade.php
│       └── prints.blade.php
├── routes/
│   ├── web.php
│   └── api.php
└── database/
    ├── seeders/
    └── migrations/
```

---

## 🔐 Panel de administración

### URL de acceso
```
/admin/login
```

### Funcionalidades
- Autenticación exclusiva para administradores.
- Visualización de estadísticas:
  - Impresiones realizadas.
  - Sesiones creadas.
  - Archivos pendientes.
- Control manual de la impresora (reintentar o cancelar impresiones).
- Limpieza del almacenamiento temporal.

---

## 📱 Flujo de usuario

1. Usuario escanea el **código QR** generado por el sistema.
2. El QR lo dirige a una **URL única de sesión**.
3. Sube uno o varios archivos.
4. Configura los parámetros de impresión.
5. El sistema calcula el costo total (en pesos mexicanos).
6. Usuario confirma la impresión.
7. Archivos se imprimen en la EPSON L325.
8. Los archivos se eliminan del sistema.

---

## 💾 Configuración de MongoDB

Ejemplo `.env`:
```env
DB_CONNECTION=mongodb
DB_HOST=127.0.0.1
DB_PORT=27017
DB_DATABASE=imprimeindo
DB_USERNAME=null
DB_PASSWORD=null
```

Ejemplo de conexión en `config/database.php`:
```php
'mongodb' => [
    'driver' => 'mongodb',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', 27017),
    'database' => env('DB_DATABASE', 'imprimeindo'),
    'username' => env('DB_USERNAME'),
    'password' => env('DB_PASSWORD'),
],
```

---

## 🧾 Módulos clave

| Módulo | Descripción |
|--------|--------------|
| **Gestor de sesiones** | Crea sesiones únicas, genera QR y controla el tiempo de validez |
| **Gestor de archivos** | Permite subida múltiple, validación y eliminación post-impresión |
| **Motor de impresión** | Envía trabajos a la impresora EPSON L325 mediante librería ESC/POS |
| **Panel Admin** | Control central del sistema, estadísticas, y monitoreo de sesiones |
| **Gestor de costos** | Calcula el total a pagar antes de imprimir |

---

## 🧰 Comandos útiles

### Instalar dependencias
```bash
composer install
```

### Configurar entorno
```bash
cp .env.example .env
php artisan key:generate
```

### Iniciar servidor de desarrollo
```bash
php artisan serve
```

### Limpiar caché del sistema
```bash
php artisan optimize:clear
```

---

## 📸 Capturas (futuras)
- Página principal con QR.
- Interfaz de subida de archivos.
- Configuración de impresión.
- Resumen de costos.
- Panel de administración.

---

## 📄 Licencia
Este proyecto es de uso interno y académico. Todos los derechos reservados © **Imprimeindo**.

---

## 🧑‍💻 Desarrollado por
**Aldair Morales Gutiérrez**  
Universidad Tecnológica del Valle de Toluca  
Proyecto: **Imprimeindo — Sistema de Impresiones Inteligente con Laravel y MongoDB**
