docker compose down
docker compose up -d --build
docker exec app php artisan db:seed --class=AdminSeeder
echo Application is running!
