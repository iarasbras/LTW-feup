sqlite3 << 'EOF'
.open db/cosyztays.db
.read db/cozystays.sql

EOF

php -S localhost:9000