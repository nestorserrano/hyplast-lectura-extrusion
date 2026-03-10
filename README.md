# Hyplast Lectura de Extrusión - Sistema de Pesaje y Códigos de Barras

## Descripción
Paquete para lectura de códigos de barras en producción de extrusión, con integración a balanza para consulta de peso en tiempo real.

## Características Principales
- 📊 Lectura de códigos de barras
- ⚖️ Integración con balanza
- 📦 Registro de rollos/bobinas
- 🏷️ Etiquetado automático
- 📈 Consulta de peso en tiempo real
- 🔄 Sincronización con inventario
- 📝 Trazabilidad completa

## Componentes del Sistema
- Servicio de lectura de balanza
- Parser de códigos de barras
- Generador de etiquetas
- API de consulta de pesos

## Modelos Principales
- **Product**: Productos de extrusión
- **Storage**: Almacenamiento de rollos
- **Transfer**: Transferencias
- **BillsContainer**: Contenedores/Bultos

## Estructura de Código de Barras
```
Formato: [PRODUCTO]-[LOTE]-[ROLLO]-[PESO]
Ejemplo: EXT-001-2024-R001-1250
```

## API Endpoints
```
POST   /api/extrusion/read         # Leer código de barras
GET    /api/extrusion/weight       # Consultar peso actual
POST   /api/extrusion/label        # Generar etiqueta
GET    /api/extrusion/products     # Productos disponibles
```

## Hardware Soportado
- Balanza serial RS232
- Lectores de código de barras USB
- Impresoras de etiquetas Zebra

## Configuración de Balanza
```env
BALANZA_PORT=COM3
BALANZA_BAUDRATE=9600
BALANZA_TIMEOUT=5000
```

## Requisitos
- PHP >= 8.1
- Laravel >= 10.x
- Extensión PHP Serial

## Instalación
```bash
composer install
php artisan migrate
php artisan balanza:test
```

## Uso
```php
// Leer peso de balanza
$peso = app('BalanzaService')->leerPeso();

// Generar código de barras
$codigo = app('BarcodeService')->generar($producto, $lote);
```

## Autor y Propietario
**Néstor Serrano**  
Desarrollador Full Stack  
GitHub: [@nestorserrano](https://github.com/nestorserrano)

## Licencia
Propietario - © 2026 Néstor Serrano. Todos los derechos reservados.
