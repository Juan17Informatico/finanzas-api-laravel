# 📊 API de Finanzas Personales

Esta es una API RESTful desarrollada con Laravel 11 y PHP 8.3 que permite a los usuarios gestionar su información financiera, incluyendo ingresos, gastos, categorías y presupuestos. Utiliza **Laravel Sanctum** para autenticación basada en tokens.

---

## 🚀 Características

- Registro e inicio de sesión de usuarios
- Gestión de categorías (ingresos/gastos)
- Registro de transacciones con filtros avanzados
- Creación y seguimiento de presupuestos por categoría
- Endpoints protegidos mediante tokens con Sanctum
- Respuestas estructuradas y paginadas

---

## 🛠️ Tecnologías

- PHP 8.3
- Laravel 11.31
- Laravel Sanctum
- MySQL / PostgreSQL
- PHPUnit (testing)

---

## 📌 Instalación

1. Clona el repositorio:

```bash
git clone https://github.com/Juan17Informatico/api-finanzas-personales.git
cd api-finanzas-personales
```

2. Instala dependencias:

```bash
composer install
```

3. Copia el archivo de entorno y configura:

```bash
cp .env.example .env
php artisan key:generate
```

4. Configura la base de datos en `.env`.
5. Ejecuta las migraciones:

```bash
php artisan migrate
```

6. (Opcional) Ejecuta los tests:

```bash
php artisan test
```

## 🔐 Autenticación

Usa **Laravel Sanctum** con tokens API. Luego de registrarte o iniciar sesión, obtendrás un token que debes enviar en el encabezado:

```
Authorization: Bearer TU_TOKEN
```

## 📚 Endpoints

| Método | URI | Propósito | Protegido |
|--------|-----|-----------|-----------|
| POST | /api/v1/register | Registro de usuario | ❌ |
| POST | /api/v1/login | Inicio de sesión | ❌ |
| POST | /api/v1/logout | Cierre de sesión | ✅ |
| GET | /api/v1/me | Datos del usuario autenticado | ✅ |
| GET | /api/v1/budgets/reports | Reporte general de presupuestos | ✅ |
| GET/POST/PUT/DELETE | /api/v1/budgets | CRUD de presupuestos | ✅ |
| GET/POST/PUT/DELETE | /api/v1/categories | CRUD de categorías | ✅ |
| GET/POST/PUT/DELETE | /api/v1/transactions | CRUD de transacciones + filtros | ✅ |

## 🔎 Filtro de Transacciones

Puedes aplicar los siguientes parámetros a `/api/v1/transactions`:

| Parámetro | Tipo | Ejemplo | Descripción |
|-----------|------|---------|-------------|
| `start_date` | string (fecha) | `2024-01-01` | Fecha mínima |
| `end_date` | string (fecha) | `2024-12-31` | Fecha máxima |
| `category_id` | int | `3` | Filtra por categoría |
| `type` | string | `income` o `expense` | Filtra ingresos o gastos |

## 🧩 Relaciones entre modelos

* **User**
   * Tiene muchos `transactions`
   * Tiene muchos `budgets`
* **Category**
   * Tiene muchas `transactions`
   * Tiene muchos `budgets`
* **Transaction**
   * Pertenece a un `user`
   * Pertenece a una `category`
* **Budget**
   * Pertenece a un `user`
   * Pertenece a una `category`

## ✅ Campos obligatorios

**Category**
```json
{
  "name": "Salario",
  "type": "income"
}
```

**Transaction**
```json
{
  "category_id": 1,
  "amount": 5000,
  "description": "Pago mensual",
  "date": "2024-04-01"
}
```

**Budget**
```json
{
  "category_id": 2,
  "limit_amount": 1000
}
```

## 📬 Contacto

Desarrollado por Juan Campuzano ✨ [GitHub](https://github.com/Juan17Informatico) | [Email](mailto:juancampuzano2356@gmail.com)
