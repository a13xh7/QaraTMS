## Getting Started
You can configure various parameters by setting the desired values in the [.env_docker](.env_docker) file.
1. ```docker compose --env-file=.env_docker up -d --build```
2. only on first run ```docker exec app php artisan migrate``` 
3. only on first run ```docker exec app php artisan db:seed --class=AdminSeeder```
