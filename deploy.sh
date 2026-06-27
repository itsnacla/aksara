#!/bin/bash
# ==============================================================================
#   ___   _  __ ___   ___ _  ___  ___  
#  / _ \ | |/ // __| / __| |/ / |/ / \ 
# / ___ \| ' < \__ \| (__| ' <|    / _ \
#/_/   \_\_|\_\|___/ \___|_|\_\_|\_/_/ \_\
#      -- SYSTEM DEPLOYMENT ENGINE --
# ==============================================================================
# Developed & Maintained by Tateta
# ==============================================================================

# Keluar dari skrip jika ada perintah yang gagal
set -e

# ANSI Color Codes
GREEN='\033[0;32m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
BOLD='\033[1m'
NC='\033[0m' # No Color

# Helper Functions
print_header() {
    echo -e "${BLUE}${BOLD}==============================================${NC}"
    echo -e "${CYAN}${BOLD}   ___   _  __ ___   ___ _  ___  ___  ${NC}"
    echo -e "${CYAN}${BOLD}  / _ \ | |/ // __| / __| |/ / |/ / \ ${NC}"
    echo -e "${CYAN}${BOLD} / ___ \| ' < \__ \| (__| ' <|    / _ \\${NC}"
    echo -e "${CYAN}${BOLD}/_/   \_\_|\_\|___/ \___|_|\_\_|\_/_/ \_\\${NC}"
    echo -e "${BLUE}${BOLD}      -- SYSTEM DEPLOYMENT ENGINE --          ${NC}"
    echo -e "${BLUE}${BOLD}==============================================${NC}"
    echo -e "Developed & Maintained by ${YELLOW}${BOLD}Tateta${NC}\n"
}

print_step() {
    echo -e "\n${BLUE}${BOLD}[Langkah $1/6]${NC} ${CYAN}$2...${NC}"
}

print_success() {
    echo -e "${GREEN}${BOLD}✔ $1${NC}"
}

# Tampilkan Branding Header
print_header

# Periksa Argumen --fake atau --dry-run
DRY_RUN=false
if [[ "$1" == "--fake" || "$1" == "--dry-run" ]]; then
    DRY_RUN=true
    echo -e "${YELLOW}${BOLD}⚠️  MENJALANKAN MODE SIMULASI (DRY-RUN / FAKE) ⚠️${NC}"
    echo -e "Perintah tidak akan dieksekusi secara nyata pada sistem.\n"
fi

execute_cmd() {
    local cmd="$1"
    if [ "$DRY_RUN" = true ]; then
        sleep 1.2
    else
        eval "$cmd"
    fi
}

# ------------------------------------------------------------------------------
# 1. Aktifkan Mode Perbaikan
# ------------------------------------------------------------------------------
print_step "1" "Mengaktifkan Mode Perbaikan (Maintenance Mode)"
execute_cmd "php artisan down --message=\"Sistem sedang diperbarui oleh administrator.\" || true"
print_success "Mode perbaikan berhasil diaktifkan."

# ------------------------------------------------------------------------------
# 2. Tarik kode terbaru dari Git
# ------------------------------------------------------------------------------
print_step "2" "Mengambil Kode Terbaru dari Repositori (Git Pull)"
execute_cmd "git pull origin main"
print_success "Kode terbaru berhasil ditarik dari Git."

# ------------------------------------------------------------------------------
# 3. Pasang dependensi PHP Composer
# ------------------------------------------------------------------------------
print_step "3" "Memasang Dependensi PHP (Composer Install)"
execute_cmd "composer install --no-dev -o"
print_success "Dependensi PHP berhasil diperbarui."

# ------------------------------------------------------------------------------
# 4. Jalankan Migrasi Database
# ------------------------------------------------------------------------------
print_step "4" "Menjalankan Migrasi Database PostgreSQL"
execute_cmd "php artisan migrate --force"
print_success "Migrasi database berhasil dijalankan."

# ------------------------------------------------------------------------------
# 5. Bersihkan & Optimalkan Cache
# ------------------------------------------------------------------------------
print_step "5" "Membersihkan & Mengoptimalkan Cache Laravel"
execute_cmd "php artisan optimize"
execute_cmd "php artisan view:cache"
print_success "Cache konfigurasi, rute, dan view berhasil dioptimalkan."

# ------------------------------------------------------------------------------
# 6. Nonaktifkan Mode Perbaikan
# ------------------------------------------------------------------------------
print_step "6" "Menonaktifkan Mode Perbaikan (Sistem Kembali Aktif)"
execute_cmd "php artisan up"
print_success "Sistem Aksara berhasil diaktifkan kembali secara publik."

# ------------------------------------------------------------------------------
# Selesai
# ------------------------------------------------------------------------------
echo -e "\n${GREEN}${BOLD}==============================================${NC}"
echo -e "${GREEN}${BOLD}   PROSES DEPLOYMENT SUKSES SELESAI! 🎉      ${NC}"
echo -e "${GREEN}${BOLD}==============================================${NC}\n"
