### Project Setup & Usage

1. **Install dependencies**

   ```bash
   composer install
   npm install
   ```

2. **Start services**

   - In one terminal, run:

     ```bash
     docker compose up
     ```

   - In a second terminal, run:

     ```bash
     npm run dev
     ```

3. **Seed the database**
   In a third terminal, run:

   ```bash
   php artisan db:seed --class=OrganisationSeeder
   ```

4. **Access the application**

   - Go to `/register` to create a new account
   - Or go to `/login` to sign in

5. **Create a course**

   - Once on the dashboard, you can create a course just like in SBP
   - A new quiz type, **Personality Quiz**, is available in addition to existing ones
