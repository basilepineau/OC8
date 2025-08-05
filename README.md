# ToDo & Co - Refactoring and Enhancement

This project is part of **Project 8 of the PHP/Symfony Developer path** at OpenClassrooms.
The objective is to **analyze an existing Symfony application**, understand its architecture, and propose **technical and functional improvements** while applying best practices in web development (architecture, testing, security, etc.).

---

## Project Goals

- Understand a legacy Symfony codebase
- Identify good and bad practices
- Fix bugs
- Add new features
- Write automated tests
- Ensure code quality and security

---

## Technologies Used

- PHP ^8.x
- Symfony ^6.x
- Composer
- Doctrine ORM
- PHPUnit
- Twig
- Bootstrap
- MySQL

---

## Project Structure

```
OC8/
├── bin/                 # Executables (Symfony console, PHPUnit)
├── config/              # Symfony configuration
├── public/              # Web root directory
├── src/                 # Application source code (controllers, entities, services)
├── templates/           # Twig templates
├── tests/               # Automated tests
├── translations/        # i18n translations
├── var/                 # Temp files (cache, logs)
├── vendor/              # Composer dependencies
├── .env / .env.test     # Environment files
├── composer.json        # PHP dependencies
└── phpunit.xml.dist     # Test configuration
```

---

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/basilepineau/OC8
cd OC8
```

### 2. Install dependencies

```bash
composer install
```

### 3. Configure environment

Copy the `.env` file if needed:

```bash
cp .env .env.local
```

Edit the database parameters in `.env.local`:

```dotenv
DATABASE_URL="mysql://username:password@127.0.0.1:3306/OC8"
```

### 4. Create and populate the database

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console doctrine:fixtures:load
```

### 5. Start the development server

```bash
symfony server:start
```

---

## Run tests

```bash
php bin/phpunit
```

---

## Demo Users for Testing

After running the fixtures (`php bin/console doctrine:fixtures:load`), several users are automatically created in the database for testing and demonstration purposes:

| Username       | Email               | Password     | Role         |
|----------------|---------------------|--------------|--------------|
| `user1`        | user1@example.com    | password1    | ROLE_USER    |
| `user2`        | user2@example.com    | password2    | ROLE_USER    |
| `user3`        | user3@example.com    | password3    | ROLE_USER    |
| `admin1`       | admin1@example.com   | adminpass1   | ROLE_ADMIN   |
| `admin2`       | admin2@example.com   | adminpass2   | ROLE_ADMIN   |
| `admin3`       | admin3@example.com   | adminpass3   | ROLE_ADMIN   |
| `anonyme`      | anonyme@example.com  | anonyme      | ROLE_ANONYMOUS |

These accounts can be used to log in and test role-based access control across the app.

---

## Key Features

- Task management (CRUD)
- User authentication
- Role-based access control (user/admin)
- Security (CSRF, validation, access rights)
- Unit and functional testing
- Responsive UI with Bootstrap

---

## Improvements Implemented

- Bug fixes and code cleanup
- Refactoring components
- PHPUnit tests added
- Security enhancements
- Technical documentation

---

## License

This project was created for educational purposes.  
License: [MIT](LICENSE)
