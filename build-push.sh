#!/bin/bash
# ─────────────────────────────────────────────
# build-push.sh
# Construye y sube las 3 imágenes a Docker Hub
# Uso: ./build-push.sh TU_USUARIO_DOCKERHUB
# ─────────────────────────────────────────────

DOCKER_USER=${1:-"TU_USUARIO"}

echo "🔨 Construyendo imágenes para: $DOCKER_USER"

# Auth service
echo ""
echo "▶ auth-service..."
docker build -t $DOCKER_USER/novasoft-auth:latest ./auth-service
docker push $DOCKER_USER/novasoft-auth:latest

# Clients service
echo ""
echo "▶ clients-service..."
docker build -t $DOCKER_USER/novasoft-clients:latest ./clients-service
docker push $DOCKER_USER/novasoft-clients:latest

# Frontend
echo ""
echo "▶ frontend..."
docker build -t $DOCKER_USER/novasoft-frontend:latest ./frontend
docker push $DOCKER_USER/novasoft-frontend:latest

echo ""
echo "✅ Imágenes subidas a Docker Hub:"
echo "   $DOCKER_USER/novasoft-auth:latest"
echo "   $DOCKER_USER/novasoft-clients:latest"
echo "   $DOCKER_USER/novasoft-frontend:latest"

# Actualizar los YAMLs con el usuario correcto
echo ""
echo "📝 Actualizando YAMLs con tu usuario..."
sed -i "s|TU_USUARIO|$DOCKER_USER|g" k8s/02-auth-service.yaml
sed -i "s|TU_USUARIO|$DOCKER_USER|g" k8s/03-clients-service.yaml
sed -i "s|TU_USUARIO|$DOCKER_USER|g" k8s/04-frontend.yaml
echo "✅ YAMLs actualizados"
