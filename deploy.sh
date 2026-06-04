#!/bin/bash
# ─────────────────────────────────────────────
# deploy.sh
# Despliega todo el stack en Kubernetes
# ─────────────────────────────────────────────

echo "🚀 Desplegando Novasoft en Kubernetes..."

# Aplicar todos los manifiestos en orden
kubectl apply -f k8s/00-configmap.yaml
echo "✅ ConfigMap y Secret aplicados"

kubectl apply -f k8s/01-mysql.yaml
echo "✅ MySQL desplegado"

echo "⏳ Esperando que MySQL esté listo (30s)..."
sleep 30

kubectl apply -f k8s/02-auth-service.yaml
echo "✅ auth-service desplegado"

kubectl apply -f k8s/03-clients-service.yaml
echo "✅ clients-service desplegado"

kubectl apply -f k8s/04-frontend.yaml
echo "✅ frontend desplegado"

kubectl apply -f k8s/05-ingress.yaml
echo "✅ Ingress/LoadBalancer aplicado"

echo ""
echo "📊 Estado del cluster:"
kubectl get pods
echo ""
kubectl get services
echo ""
kubectl get ingress

echo ""
echo "✅ Despliegue completo."
echo "👉 Agrega esto a /etc/hosts:  127.0.0.1  novasoft.local"
echo "👉 Abre: http://novasoft.local"
