# Laravel News Aggregator

A modern, scalable news aggregation platform built with Laravel that collects, processes, and serves news articles from multiple sources through a RESTful API. The application features intelligent caching, background processing, and comprehensive search capabilities.

## 🚀 Features

- **Multi-Source News Aggregation**: Integrates with NewsAPI, The Guardian, and New York Times APIs
- **RESTful API**: Clean, well-documented API endpoints with JSON responses
- **Advanced Caching**: Multi-layer caching strategy using Redis for optimal performance
- **Background Processing**: Asynchronous news fetching and processing with Laravel Queues
- **Full-Text Search**: Powerful search capabilities across articles, categories, and authors
- **Docker Support**: Complete containerization with Docker and Docker Compose
- **Rate Limiting**: Intelligent rate limiting for external APIs and application endpoints
- **UTF-8 Support**: Proper handling of international characters and special symbols

## 📋 Requirements

- **PHP**: >= 8.2
- **Laravel**: >= 12.0
- **MySQL**: >= 8.2
- **Redis**: >= 7.0
- **Docker**: >= 26.0
- **Docker Compose**: >= 2.3

## 🛠 Installation

### Using Docker (Recommended)

1. **Clone the repository**
   ```bash
   git clone https://github.com/umarm6/news-aggregator.git
   cd news-aggregator
   ```

2. **Copy environment file**
   ```bash
   cp .env.example .env
   ```


4. **Build and start containers**
   ```bash
   docker-compose up -d --build
   ```

5. **Install dependencies and setup application**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate
   docker-compose exec app php artisan db:seed
   ```

6. **Start news aggregation**
   ```bash
   to run all sources
   docker-compose exec app php artisan news:aggregate
   
   to run specific soruce
   docker-compose exec app php artisan news:aggregate --source=Guardian
 
   ```
   
6. **Start Queue locally execute**
   ```bash 
   docker-compose exec app php artisan queue:work
   ```

##  System Design 
  <img src="https://github.com/umarm6/news-aggregator/blob/6b4d1fd07bca75878fc399b30932d507f249359c/architecture-diagram.png?raw=true" width=80%>

## 📚 API Documentation


### 📁 POSTMAN Collection
```
you can find out postman collection on root and import in to your POSTMAN
folder name:  postman-collection 
```

### Base URL
```
http://localhost:8080
```

### Authentication
Currently, no authentication is required. Rate limiting is applied per IP address.

### Endpoints

#### Articles

**Get All Articles**
```http
GET /api/v1/articles
```

Query Parameters:
- `q` (string): Search query
- `category` (string): Filter by category
- `source` (string): Filter by source name
- `author` (string): Filter by author
- `from` (date): Start date (Y-m-d format)
- `to` (date): End date (Y-m-d format)
- `page` (integer): Page number for pagination
- `per_page` (integer): Items per page (max 100)


**Get Single Article**
```http
GET /api/v1/articles/{id}
```

**Get All Sources**
```http
GET /api/v1/sources
```

**Get All Categories**
```http
GET /api/v1/categories
```

**Get All Authors**
```http
GET /api/v1/authors
```


### Response Format

All API responses follow this structure:
```json
{
    "success": "boolean",
    "data": "mixed",
    "message": "string (optional)",
    "errors": "object (on validation errors)"
}
```

## 🏗 Architecture Overview

### Service Architecture

The application follows a service-oriented architecture:

1. **News Services**: Handle external API integration
2. **Cache Service**: Manages multi-layer caching
4. **Aggregation Jobs**: Process news in background

### Database Schema

Key tables:
- `articles`: Stores news articles with full-text search indexes
- `sources`: Manages news source configurations
- `failed_jobs`: Tracks failed background jobs
- `cache`: cache data

## ⚡ Performance Features

### Caching Strategy

1. **API Response Cache**: 10 minutes for external API calls
2. **Database Query Cache**: 5-30 minutes for heavy queries
3. **Application Cache**: Categories, sources, trending articles

### Background Processing

- **News Aggregation**: Scheduled every 15 minutes
- **Cache Warming**: Automated cache pre-loading
- **Rate Limit Enforcement**: Per-source API limits

### Database Optimization

- **Indexes**: Strategic indexes on filter columns
- **Full-Text Search**: MySQL full-text indexes
- **Query Optimization**: Eager loading, select optimization

## 🔧 Development

### Running Tests
```bash
# Run all tests in (in root folder)
php artisan test

# Run specific test suite 
php artisan test --testsuite=Feature
```

### Debugging
```bash
# View logs
docker-compose exec app tail -f storage/logs/laravel.log
```
### Useful Commands

```bash
# docker app bash
docker exec -it news_aggregator_app bash

# Clear Cache
php artisan cache:clear

# Aggregate news from all sources
php artisan news:aggregate && php artisan queue:work

# Aggregate from specific source
php artisan news:aggregate --source=Guardian && php artisan queue:work

# Clear all caches
php artisan optimize:clear
``` 

### Coding Standards

- Follow PSR-12 coding standards
- Write comprehensive tests
- Document all public methods
- Use meaningful commit messages

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🏷 Changelog

### v1.0.0
- Initial release with NewsAPI, Guardian, and NYT integration
- Docker containerization
- Multi-layer caching implementation
- RESTful API with comprehensive filtering
- Background job processing
- UTF-8 character support

--- 
##  Author
Mohamed Umar
