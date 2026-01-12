# Forge

Laravel application with Docker setup for easy development.

## Prerequisites

- Docker & Docker Compose
- Git

## Quick Start

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd Forge
   ```

2. **Set up environment**
   ```bash
   cp .env.example .env
   ```

3. **Start with Docker**
   ```bash
   docker-compose up -d
   ```

4. **Install dependencies and set up the application**
   ```bash
   docker exec forge-app composer setup
   ```

5. **Access the application**
   - App: http://localhost:8000
   - Database: localhost:5433 (PostgreSQL)
   - Redis: localhost:6380

## Development

### Running the application
```bash
# Start all services
docker-compose up -d

# View logs
docker-compose logs -f app

# Stop services
docker-compose down
```

### Commands

```bash
# Install PHP dependencies
docker exec forge-app composer install

# Install Node dependencies
docker exec forge-app npm install

# Run migrations
docker exec forge-app php artisan migrate

# Generate application key
docker exec forge-app php artisan key:generate

# Run tests
docker exec forge-app composer test

# Development mode (with hot reload)
docker exec forge-app composer dev
```

### Database

- **Engine:** PostgreSQL 15
- **Host:** localhost:5433
- **Database:** forge
- **Username:** forge
- **Password:** secret

## Services

- **App:** Laravel 12 with PHP 8.2-FPM
- **Web Server:** Nginx (port 8000)
- **Database:** PostgreSQL 15 (port 5433)
- **Cache:** Redis (port 6380)

## Testing

```bash
# Run all tests
docker exec forge-app composer test

# Run specific test
docker exec forge-app php artisan test tests/Feature/AuthTest.php
```

## Troubleshooting

### Reset everything
```bash
docker-compose down -v
docker-compose up -d
docker exec forge-app composer setup
```

### View container logs
```bash
docker-compose logs [service-name]
```

### Access container shell
```bash
docker exec -it forge-app bash
```

### For Permission issues run this command:
```bash
sudo chown -R $USER:$USER /home/legion/Storage/Projects/Forge {Path to project}
```

### For Permission error when hitting a route
```bash
docker exec forge-app chmod -R 775 storage bootstrap/cache
docker exec forge-app chown -R www-data:www-data storage bootstrap/cache
```