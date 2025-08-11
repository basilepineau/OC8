
# Contributing Guide

Welcome to the **ToDo & Co** project ! This document will help you contribute effectively and follow the established development workflow.

---

## Project Workflow

We use the **GitFlow** branching model :

- `main`: contains production-ready code (tagged versions).
- `develop`: contains the latest development version.
- `feature/*`: created from `develop` for new features or improvements.
- `release/*`: created from `develop` to prepare a new production release.
- `hotfix/*`: created from `main` to fix critical issues in production.

> All merges into `main` must also be merged back into `develop` to keep it up to date.

---

## How to Contribute

### Add a New Feature

1. Create a GitHub issue to describe the feature or improvement.
2. Create a `feature/*` branch from `develop`.
3. Implement the changes locally.
4. Write unit and/or functional tests.
5. Commit using the convention: `feat(scope): short description` (see below).
6. Push to `origin/feature/your-feature-name`.
7. Create a pull request (PR) to `develop`.
8. Run and validate code quality checks and tests.
9. Ask for code review and update your code if needed.
10. Once validated, the feature will be merged into `develop`.

### Release a New Version

1. Create a `release/vX.X.X` branch from `develop`.
2. Finalize documentation, code cleanup, and tests.
3. Create a pull request to `main`.
4. Tag the merge commit with the release version.
5. Merge the release into `main`.
6. Merge the same release branch back into `develop`.

### Fix a Production Bug

1. Create a GitHub issue to describe the bug.
2. Create a `hotfix/your-fix-name` branch from `main`.
3. Fix the bug and add regression tests.
4. Create a pull request to `main`.
5. Tag the commit with a patch version.
6. Merge the hotfix into `main`.
7. Merge the hotfix into `develop` to keep it up to date.
8. Delete the `hotfix` branch.

---

## Code Quality Guidelines

### Code Standards

- Follow **PSR-12** and [Symfony code standards](https://symfony.com/doc/current/contributing/code/standards.html).
- Use **Composer** for PHP package management.

### Testing

- Use **PHPUnit** for both unit and functional tests.
- Every new feature or bugfix must include appropriate tests.
- Target **at least 70% test coverage**.

### Commits & Pull Requests

Use conventional commits:
- `feat(scope): description` for new features
- `fix(scope): description` for bug fixes
- `refactor(scope): description` for code refactoring
- `test(scope): description` for adding tests
- `docs(scope): description` for documentation updates
- `chore(scope): description` for tooling and config changes

> Example: `feat(auth): add login with remember me`

Pull requests must be reviewed and approved before merging. Only the project lead may merge into `main`.

---

## Architecture

- Respect Symfony’s file structure (`src/Controller`, `templates`, etc.)
- Place new views in `templates/`
- Create UML diagrams (Use Case, Class, Sequence) for complex features

---

## Code of Conduct

We foster a respectful and inclusive environment. Harassment, discrimination, or toxic behavior will not be tolerated.

---

Thanks for contributing ! 
