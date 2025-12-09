# Testing Configuration

## Database Configuration

Tests are configured to use **MySQL** instead of SQLite to match your production environment.

### Test Database Setup

Before running tests, you need to create a test database:

```sql
CREATE DATABASE dinger_payment_test;
```

Or use a different name and set it in your environment.

### Configuration

The test database configuration is in `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="mysql"/>
<env name="DB_HOST" value="${DB_HOST:-127.0.0.1}"/>
<env name="DB_PORT" value="${DB_PORT:-3306}"/>
<env name="DB_DATABASE" value="${DB_DATABASE_TEST:-dinger_payment_test}"/>
<env name="DB_USERNAME" value="${DB_USERNAME:-root}"/>
<env name="DB_PASSWORD" value="${DB_PASSWORD:-}"/>
```

### Environment Variables

You can override these values by setting environment variables:

- `DB_HOST` - MySQL host (default: 127.0.0.1)
- `DB_PORT` - MySQL port (default: 3306)
- `DB_DATABASE_TEST` - Test database name (default: dinger_payment_test)
- `DB_USERNAME` - MySQL username (default: root)
- `DB_PASSWORD` - MySQL password (default: empty)

### Running Tests

```bash
# Run all tests
php artisan test

# Run only Product tests
php artisan test --filter=Product

# Run only service tests (no database needed)
php artisan test --filter=ProductServiceTest

# Run only repository tests (requires MySQL)
php artisan test --filter=ProductRepositoryTest
```

### Important Notes

1. **Test Database**: The `RefreshDatabase` trait will automatically migrate and rollback the test database for each test
2. **Isolation**: Each test runs in a transaction that is rolled back after the test
3. **Performance**: MySQL tests are slightly slower than SQLite but match your production environment

