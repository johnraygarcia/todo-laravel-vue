## How to run this Task API
1. Install docker desktop
2. Adjust the .env file accordingly(database credentials can be found in the docker-compose.yaml file)
3. Go to the root directory of the project and run `docker compose up --build`
4. Run `docker-compose exec -t app php artisan migrate:fresh` to initialize the database


