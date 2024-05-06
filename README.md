## Installation

To set up this project, follow these steps:

1. **Install dependencies:**
   ```bash
   cd taskManager
   composer install
   ```

2. **Set up the environment file:**
   - Duplicate the `.env.example` file and rename it to `.env`.
   - Update the database and other configuration details in the `.env` file.

3. **Generate application key:**
   ```bash
   php artisan key:generate
   ```

4. **Run database migrations:**
   ```bash
   php artisan migrate
   ```

5. **Start the development server:**
   ```bash
   php artisan serve
   ```

Now you can access your Laravel application at `http://localhost:8000`.
