#!/bin/bash
# Helper script to run the FAQ generator in the background
echo "Please run this command with your actual credentials:"
echo 'OPENROUTER_KEYS="sk-or-v1-..." DB_NAME="..." DB_USER="..." DB_PASSWORD="..." DB_PREFIX="wp_" npx pm2 start generate-faqs.js --name "faq-generator" --no-autorestart'
