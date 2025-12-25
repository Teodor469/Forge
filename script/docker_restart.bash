#!/bin/bash
echo "Recovering Forge"

echo "Stopping Docker"
sudo systemctl stop docker
sudo systemctl stop docker.socket

echo "Cleaning iptables"
sudo iptables -t nat -F
sudo iptables -t filter -F

echo "Starting Docker"
sudo systemctl start docker

echo "Waiting for Docker to start and auto-restart containers"
sleep 15

echo "Checking container status:"
cd ~/Storage/Projects/Forge
docker-compose ps

echo ""
echo "Laravel http://localhost:8000"#!/bin/bash