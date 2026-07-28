# Deployment Guide

1. Configure HTTPS for the Laravel domain; camera APIs require a secure context outside localhost.
2. Set production `.env` values, including database credentials, `APP_KEY`, `APP_URL`, `SESSION_SECURE_COOKIE=true`, and AI service values.
3. Run `composer install --no-dev --optimize-autoloader`.
4. Run `npm ci && npm run build`.
5. Run `php artisan migrate --force`.
6. Run `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
7. Serve `public/` as the web root.
8. Run the FastAPI service behind a private network or reverse proxy and protect it with `AI_SERVICE_TOKEN`.
9. Configure backups for the database and `storage/app/private`.
10. Configure `AI_MODEL_PATH`, `AI_MODEL_NAME`, `AI_MODEL_VERSION`, `AI_MODEL_CONFIDENCE_THRESHOLD`, and `AI_MODEL_CLASSES` on the FastAPI service when a trained model is available.
11. Sync model metadata from Admin > Models after deployment.
12. Replace placeholder species data before any field evaluation.
