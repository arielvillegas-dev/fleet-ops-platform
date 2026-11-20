# Fleet Ops Platform (Laravel 13 Full Stack + Telemetry + QA E2E Demo)

Demostración técnica independiente construida con **PHP 8.3 + Laravel 13 + SQLite + Playwright**, enfocada en gestión operativa de flotas, importación masiva CSV con diagnóstico, ingesta idempotente de telemetría GPS y pruebas automatizadas de extremo a extremo.

> **Nota:** Proyecto demostrativo creado desde cero con datos 100 % sintéticos, sin código, marcas ni información operativa de sistemas reales.

## 1. Problema que resuelve
Integra en un único repositorio reproducible el flujo completo entre un panel web administrativo y una API REST de telemetría: registro de flota, carga/actualización de rutas vía CSV, programación de viajes, ingesta en lote deduplicada por `client_event_id`, filtrado de lecturas ruidosas y cálculo de distancia real con fórmula Haversine.

## 2. Tecnologías utilizadas
- **Backend / Full Stack:** PHP `^8.3`, Laravel `^13`, Blade, SQLite.
- **Pruebas:** PHPUnit `^12.5` (Unit + Feature) y Playwright (`@playwright/test`) para pruebas E2E en Chromium.

## 3. Arquitectura general
- `app/Services/GeoDistanceService.php`: Cálculo Haversine y acumulación de segmentos.
- `app/Services/TelemetryBatchService.php`: Procesamiento transaccional idempotente (`client_event_id`), rechazo de lecturas con precisión `> 50 m` o `(0,0)` y recálculo de kilometraje.
- `app/Services/RouteCsvImporterService.php`: Parseo CSV transaccional con reporte de advertencias por línea.
- `app/Http/Controllers/Web/FleetDashboardController.php`: Panel web de flota, rutas, CSV y viajes.
- `app/Http/Controllers/Api/TelemetryApiController.php`: Endpoints REST `v1` compatibles con `fleet-telemetry-mobile`.
- `tests/Unit/`, `tests/Feature/`, `tests/e2e/`: Suite completa PHPUnit + Playwright.

## 4. Funcionalidades implementadas
- CRUD de vehículos e importación/actualización de rutas desde CSV.
- Programación de viajes y tabla operativa en vivo.
- `POST /api/v1/trips/{trip}/telemetry-batch` protegido por Bearer token configurable (`DEMO_TELEMETRY_TOKEN`).
- `GET /api/v1/trips/{trip}/summary` y `GET /api/v1/health`.

## 5. Instrucciones para ejecutarlo

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## 6. Pruebas disponibles
```bash
# Pruebas PHPUnit (Unit + Feature sobre SQLite en memoria)
php artisan test

# Pruebas End-to-End con Playwright (requiere npm install y php en PATH)
npm install
npx playwright install chromium
npm run test:e2e
```

## 7. Contribución real al desarrollo
Arquitectura, contratos API, servicios de telemetría/CSV y estrategia QA diseñados por Carlos Ariel Villegas Flores como demostración técnica independiente, con asistencia de herramientas de IA para generar el scaffolding Laravel/Playwright y validación ejecutada localmente con PHPUnit y Playwright.
