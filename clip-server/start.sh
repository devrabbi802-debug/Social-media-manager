#!/bin/bash

# CLIP Server Startup Script
# This script starts the CLIP image embedding server

echo "Starting CLIP Image Embedding Server..."

# Check if Python is installed
if ! command -v python3 &> /dev/null; then
    echo "Error: Python3 is not installed"
    exit 1
fi

# Check if requirements are installed
if ! python3 -c "import fastapi" &> /dev/null; then
    echo "Installing requirements..."
    pip3 install -r requirements.txt
fi

# Start the server
echo "Starting CLIP server on port 8089..."
python3 main.py
