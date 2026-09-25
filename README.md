# ParkManager

Aplikacija za rezervaciju parking mesta u kancelariji. Zaposleni rezervišu slobodna mesta za jedan dan, više dana ili celu nedelju; admin upravlja mestima, korisnicima, rezervacijama i trajnim dodelama.

| Deo | Tehnologija |
|---|---|
| Backend | Laravel 13 (PHP 8.4) |
| Frontend | Vue 3, Pinia, Vue Router |
| Baza | PostgreSQL 17 |
| Okruženje | Docker Compose |

## Struktura

```
parkmanager/
├── backend/             # Laravel API
├── frontend/            # Vue 3 aplikacija (Vite)
└── docker-compose.yml   # postgres, backend, frontend
```

## Šta ti treba

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (pokrenut)
- Git

PHP, Composer, Node i Postgres **ne** instaliraš lokalno — sve radi u Docker kontejnerima.

## Pokretanje (prvi put)

```bash
# 1. Kloniraj repo
git clone git@github.com:viktorija117/parkmanager.git
cd parkmanager

# 2. Napravi .env za backend
cp backend/.env.example backend/.env

# 3. Napravi Docker image-e
docker compose build

# 4. Instaliraj PHP pakete (vendor/)
docker compose run --rm backend composer install

# 5. Generiši Laravel ključ aplikacije
docker compose run --rm backend php artisan key:generate

# 6. Podigni sve kontejnere
docker compose up -d

# 7. Napravi tabele u bazi
docker compose exec backend php artisan migrate
```

Otvori u browseru:

| Servis | Adresa |
|---|---|
| Frontend | http://localhost:5173 |
| Backend (API) | http://localhost:8000 |
| Postgres | `localhost:5432` — baza `parkmanager`, korisnik `parkmanager`, lozinka `secret` |
