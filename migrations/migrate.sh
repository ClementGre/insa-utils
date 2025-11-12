#!/bin/sh

sleep 2

echo "Running migrations"

while ! php ./migrations/migrate-update.php; do
    echo "migrate-update.php crashed with exit code $?.  Restarting in 2s..." >&2
    sleep 2
done

echo "migrate-update.php completed successfully."


echo "Migrating meilisearch indexes"

while ! php ./migrations/migrate-meilisearch.php; do
    echo "migrate-meilisearch.php crashed with exit code $?.  Restarting in 2s..." >&2
    sleep 2
done

echo "migrate-meilisearch.php completed successfully."
