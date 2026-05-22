# 🚗 LaraFleet API

A fleet management system built with Laravel 13, Docker, and Kubernetes — designed to demonstrate modern backend architecture in a real-world scenario.

> **Frontend repository:** [larafleet-frontend](https://github.com/dassetto45/larafleet-frontend)

---

## Architecture

```
                        ┌─────────────────────────┐
                        │      Browser / Client   │
                        └────────────┬────────────┘
                                     │ HTTPS
                        ┌────────────▼────────────┐
                        │   React 19 + Vite +     │
                        │      Tailwind CSS       │
                        └────────────┬────────────┘
                                     │ REST API + Bearer Token
                        ┌────────────▼─────────────┐
                        │   Laravel 13 API         │
                        │   (Sanctum Auth)         │
                        └──────┬──────────┬────────┘
                               │          │
              ┌────────────────▼─┐    ┌───▼───────────────────┐
              │   MySQL 8.0      │    │   Redis               │
              │   (Database)     │    │   (Cache + Queue)     │
              └──────────────────┘    └───┬──────────────────-┘
                                          │
                         ┌────────────────▼────────────────┐
                         │  Worker          Scheduler      │
                         │  (Queue Jobs)    (Cron Tasks)   │
                         └─────────────────────────────────┘

                    ─────────────────────────────────────────
                              Docker / Kubernetes
                    ─────────────────────────────────────────

                    ┌────────────────────────────────────────┐
                    │          GitHub Actions CI             │
                    │     Pest Tests → Build → Deploy        │
                    └────────────────────────────────────────┘
```

---

## Database Schema

```
users
├── id
├── name
├── email
├── password
├── role (admin | user)
└── timestamps

vehicles
├── id
├── plate (unique)
├── brand
├── model
├── year
├── km
├── type (car | truck | scooter | van | bus)
├── status (available | in_use | maintenance)
└── timestamps

bookings
├── id
├── user_id → users
├── vehicle_id → vehicles
├── start_at
├── end_at
├── status (active | completed | cancelled)
├── notes
└── timestamps

maintenances
├── id
├── vehicle_id → vehicles
├── user_id → users
├── description
├── scheduled_at
├── completed_at
└── timestamps
```

---

## Tech Stack

| Layer | Technology |
|---|---|
| Backend | Laravel 13 |
| Database | MySQL 8.0 |
| Cache + Queue | Redis |
| Authentication | Laravel Sanctum |
| Testing | Pest |
| Containerization | Docker + Docker Compose |
| Orchestration | Kubernetes (Minikube) |
| CI Pipeline | GitHub Actions |

---

## Features

**API REST**
- JWT-style token authentication via Sanctum
- Vehicle management with status tracking
- Booking system with conflict prevention
- Maintenance scheduling and tracking
- Role-based access control (admin / user)

**Background Jobs**
- `SendBookingConfirmationJob` — sends confirmation email on booking creation
- `ReleaseExpiredBookingsJob` — hourly job that releases vehicles with expired bookings
- `MaintenanceReminderJob` — daily job that notifies admins of upcoming maintenances

**Testing**
- Feature tests on all API endpoints
- Unit tests on models and business logic
- Job tests with queue faking and mail faking

---

## API Endpoints

```
POST   /api/v1/login                          Public
POST   /api/v1/logout                         Auth required
GET    /api/v1/me                             Auth required

GET    /api/v1/vehicles                       Auth required
GET    /api/v1/vehicles/{id}                  Auth required
POST   /api/v1/vehicles                       Admin only
PUT    /api/v1/vehicles/{id}                  Admin only
DELETE /api/v1/vehicles/{id}                  Admin only

GET    /api/v1/bookings                       Auth required
POST   /api/v1/bookings                       Auth required
DELETE /api/v1/bookings/{id}                  Auth required (owner or admin)

GET    /api/v1/maintenances                   Auth required
POST   /api/v1/maintenances                   Admin only
PATCH  /api/v1/maintenances/{id}/complete     Admin only
```

---

## Getting Started

### Prerequisites

- Docker Desktop with WSL2 integration
- WSL2 (Ubuntu)

### With Docker Compose

```bash
# Clone the repository
git clone https://github.com/dassetto45/larafleet-api
cd larafleet-api

# Copy environment file
cp .env.example .env

# Update .env with your values:
# DB_HOST=mysql
# REDIS_HOST=redis
# QUEUE_CONNECTION=redis

# Build and start containers
docker compose build
docker compose up -d

# Run migrations and seeders
docker compose exec app php artisan migrate:fresh --seed

# Run tests
docker compose exec app ./vendor/bin/pest
```

The API will be available at `http://localhost:8080/api/v1`.

Email previews (Mailpit) at `http://localhost:8025`.

### With Kubernetes (Minikube)

```bash
# Start Minikube
minikube start --driver=docker

# Point Docker to Minikube registry
eval $(minikube docker-env)

# Build the image inside Minikube
docker build -f docker/php/Dockerfile -t larafleet-api:latest .

# Deploy all resources
kubectl apply -f k8s/namespace.yaml
kubectl apply -f k8s/configmap.yaml
kubectl apply -f k8s/secret.yaml
kubectl apply -f k8s/mysql/
kubectl apply -f k8s/redis/
kubectl apply -f k8s/app/
kubectl apply -f k8s/nginx/
kubectl apply -f k8s/worker/
kubectl apply -f k8s/scheduler/

# Check pod status
kubectl get pods -n larafleet

# Run migrations
kubectl exec -n larafleet deployment/app -- php artisan migrate --seed --force

# Access the API via port-forward
kubectl port-forward -n larafleet service/nginx 8081:80
```

The API will be available at `http://localhost:8081/api/v1`.

---

## Default Credentials (after seeding)

| Role | Email | Password |
|---|---|---|
| Admin | admin@larafleet.test | password |
| User | utente1@larafleet.test | password |

---

## Project Structure

```
larafleet-api/
├── app/
│   ├── Http/Controllers/Api/   # AuthController, VehicleController,
│   │                           # BookingController, MaintenanceController
│   ├── Jobs/                   # SendBookingConfirmationJob,
│   │                           # ReleaseExpiredBookingsJob,
│   │                           # MaintenanceReminderJob
│   ├── Mail/                   # BookingConfirmedMail
│   └── Models/                 # User, Vehicle, Booking, Maintenance
├── docker/
│   ├── php/Dockerfile
│   └── nginx/default.conf
├── k8s/
│   ├── namespace.yaml
│   ├── configmap.yaml
│   ├── secret.yaml
│   ├── app/
│   ├── mysql/
│   ├── redis/
│   ├── nginx/
│   ├── worker/
│   └── scheduler/
├── tests/
│   └── Feature/
│       ├── Api/                # AuthTest, VehicleTest, BookingTest
│       └── Jobs/               # SendBookingConfirmationJobTest,
│                               # ReleaseExpiredBookingsJobTest
├── .github/
│   └── workflows/
│       └── ci.yml
└── docker-compose.yml
```

---

## CI/CD

Every push to `main` triggers the GitHub Actions pipeline:

1. Spins up MySQL and Redis services
2. Installs PHP 8.4 and Composer dependencies
3. Runs the full Pest test suite
4. Reports pass/fail status on the commit

---

## License

MIT
