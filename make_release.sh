#!/bin/bash
# Script para empaquetar el plugin IssueSpotlight para OJS 3.3+
# Ubicación: DENTRO de la carpeta del plugin.

PLUGIN_NAME="issueSpotlight"
OUTPUT_FILE="issueSpotlight.tar.gz"

# Detectar rutas
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
PARENT_DIR="$( dirname "$SCRIPT_DIR" )"
FOLDER_NAME="$( basename "$SCRIPT_DIR" )"

echo "===================================================="
echo "  Generando paquete de Release: $PLUGIN_NAME"
echo "===================================================="

# Moverse a la carpeta padre para que el tar incluya la ruta relativa correcta
cd "$PARENT_DIR"

# 1. Limpieza de versiones antiguas
if [ -f "$OUTPUT_FILE" ]; then
    rm "$OUTPUT_FILE"
    echo "[-] Archivo anterior eliminado."
fi

# 2. Empaquetado optimizado para OJS 3.3 (usa Migraciones PHP, no XML)
echo "[+] Comprimiendo '$FOLDER_NAME'..."

tar --exclude=".git*" \
    --exclude="make_release.sh" \
    --exclude="schema.sql" \
    --exclude="README*.md" \
    -cvzf "$OUTPUT_FILE" "$FOLDER_NAME/"

if [ $? -eq 0 ]; then
    echo ""
    echo "===================================================="
    echo "  ¡ÉXITO! Paquete listo para instalar."
    echo "  Ruta: $PARENT_DIR/$OUTPUT_FILE"
    echo "===================================================="
    echo "Instrucciones: Sube este archivo al gestor de plugins"
    echo "de OJS. La base de datos se creará automáticamente."
    echo "===================================================="
else
    echo "ERR: Fallo al crear el archivo comprimido."
    exit 1
fi
