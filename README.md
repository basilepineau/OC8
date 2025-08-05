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

Edit the database parameters in `.env`:

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
