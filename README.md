## Barebones API REST Template
This template was made with the idea of being easy to modify for small projects, no large dependencies and simple structure.

There will be another version which will make use of Dependency injection and Eloquent ORM, still simple but more complete for a real use case.

## Quick start guide

Follow this instructions to start using the API in your local enviroment

### 1. Install dependencies
Using **Composer** execute the next command in the project root:

```bash
composer install

```

### 2. Configure your .env file

Copy the example .env and configure your credentials depending on the
enviroment you're working (Either development or production)

It is **vital** that you set a safe password for the admin account

1. Create `.env` file:
```bash
cp .env.example .env

```


2. Open `.env` and make sure to configure all the needed varaibles
* `DB_*`, etc. (Database configuration)
* `ADMIN_PASSWORD_HASH`: The hash of the admin account password.

**Tip:** To create quickly your admin password (hashed), type the following command:
```bash
php -r "echo password_hash('your_password', PASSWORD_BCRYPT);"

```

**Tip:** To create your JWT, type the following command (Minimun of 32 characters):
```bash

php -r "echo bin2hex(random_bytes(32));"

```
---


## 🔐 Security and Auth

### Single-Admin management

To simplify the API and maximize reutilization, it's designed under a model of **Single-Admin**.
* **No register routes:** There does not exist any route to register users
* **Hash Authentication:** Admin access is managed by a sigle direct password hash that should be stored in the configured `.env` variable.


## 🤝 Contributions

Contributions are what make open source an amazing place to learn and create. This project can improve drastically, therefore **any contribution is welcome**.

If you have any improvement idea for the API (security, performance, new base routes, etc.), feel free to follow these steps to contribute:

1. **Make a fork** of the proyect.
2. **Create a branch** where you specify your contribution (`git checkout -b feature/improvement`).
3. **Make your changes** (would really apreciate if you follow good coding conventions).
4. **Make a commit** with your changes (`git commit -m 'Add: some amazing improvement'`).
5. **Push your changes** to the branch (`git push origin feature/improvement`).
6. **Open a pull request** and explain the changes you've made.


All suggestions, bug reports or documentation will be really apreciated.
