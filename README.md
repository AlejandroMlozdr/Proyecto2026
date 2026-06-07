# Novasoft — Plataforma de Gestión de Clientes con Microservicios

Aplicación web distribuida construida con arquitectura de microservicios, contenerizada con Docker y orquestada con Kubernetes. Permite registro, autenticación y gestión de clientes, con balanceo de carga verificable entre réplicas.

---

## Arquitectura

```
                        Usuario (navegador)
                               │
                    ┌──────────▼──────────┐
                    │  NodePort :30080    │
                    └──────────┬──────────┘
                               │
              ┌────────────────▼────────────────┐
              │     Frontend PHP/Apache          │
              │     2 réplicas (frontend-*)      │
              └────────┬─────────────┬───────────┘
                       │             │
           ┌───────────▼───┐   ┌─────▼─────────────┐
           │  auth-service  │   │  clients-service   │
           │  FastAPI :8001 │   │  FastAPI :8002     │
           │  2 réplicas    │   │  2 réplicas        │
           └───────┬────────┘   └────────┬───────────┘
                   │                     │
           ┌───────▼─────────────────────▼───────┐
           │            MySQL :3306               │
           │   novasoft_auth | novasoft_clients   │
           └──────────────────────────────────────┘
```

Cada servicio incluye el header `X-Pod-Name` en sus respuestas HTTP con el nombre del pod que atendió la petición, lo que permite verificar el balanceo de carga entre réplicas.

---

## Servicios

| Servicio          | Tecnología     | Puerto | Réplicas | Imagen Docker                         |
|-------------------|---------------|--------|----------|---------------------------------------|
| `frontend`        | PHP 8 / Apache | 80     | 2        | `korvix18/novasoft-frontend:latest`   |
| `auth-service`    | FastAPI Python | 8001   | 2        | `korvix18/novasoft-auth:latest`       |
| `clients-service` | FastAPI Python | 8002   | 2        | `korvix18/novasoft-clients:latest`    |
| `mysql`           | MySQL 8        | 3306   | 1        | `mysql:8.0` (oficial)                 |

---

## Endpoints de la API

### Auth Service (`/auth/*`)

| Método | Ruta             | Descripción                          | Auth requerida |
|--------|-----------------|--------------------------------------|----------------|
| GET    | `/health`        | Health check del servicio            | No             |
| POST   | `/auth/register` | Registro de nuevo usuario            | No             |
| POST   | `/auth/login`    | Inicio de sesión, devuelve JWT       | No             |
| GET    | `/auth/verify`   | Verificar validez de token JWT       | No (token en query param) |

**Ejemplo — Registro:**
```json
POST /auth/register
{
  "username": "usuario1",
  "nombre": "Juan Pérez",
  "correo": "juan@correo.com",
  "contrasena": "mi_clave",
  "celular": "70001234"
}
```

**Ejemplo — Login:**
```json
POST /auth/login
{
  "username": "usuario1",
  "password": "mi_clave"
}
```

### Clients Service (`/clientes/*`)

| Método | Ruta                  | Descripción              | Auth requerida |
|--------|-----------------------|--------------------------|----------------|
| GET    | `/health`             | Health check             | No             |
| GET    | `/clientes`           | Listar todos los clientes | Sí (JWT)      |
| GET    | `/clientes/{id}`      | Obtener cliente por ID   | Sí (JWT)       |
| POST   | `/clientes`           | Crear nuevo cliente      | Sí (JWT)       |
| PUT    | `/clientes/{id}`      | Editar cliente           | Sí (JWT)       |
| DELETE | `/clientes/{id}`      | Eliminar cliente         | Sí (JWT)       |

Los endpoints protegidos requieren el header: `Authorization: Bearer <token>`

---

## Requisitos

- [Docker](https://www.docker.com/) >= 20.x
- [kubectl](https://kubernetes.io/docs/tasks/tools/) >= 1.25
- Cluster Kubernetes activo (Minikube, Docker Desktop, K3s, etc.)
- Cuenta en Docker Hub (para publicar imágenes propias)

---

## Despliegue

### 1. Clonar el repositorio

```bash
git clone https://github.com/AlejandroMlozdr/Proyecto2026.git
cd Proyecto2026
```

### 2. Construir y publicar las imágenes Docker

```bash
chmod +x build-push.sh
./build-push.sh TU_USUARIO_DOCKERHUB
```

Este script construye las 3 imágenes (auth-service, clients-service, frontend) y las sube a Docker Hub. También actualiza automáticamente los YAMLs de Kubernetes con tu usuario.

### 3. Desplegar en Kubernetes

```bash
chmod +x deploy.sh
./deploy.sh
```

El script aplica los manifiestos en orden:

1. `00-configmap.yaml` — ConfigMap y Secret con variables de entorno
2. `01-mysql.yaml` — Base de datos MySQL con PersistentVolumeClaim
3. `02-auth-service.yaml` — Deployment + Service del servicio de autenticación
4. `03-clients-service.yaml` — Deployment + Service del servicio de clientes
5. `04-frontend.yaml` — Deployment + Service del frontend (NodePort 30080)
6. `05-ingress.yaml` — Ingress para acceso por dominio

### 4. Verificar el estado del cluster

```bash
kubectl get pods
kubectl get services
kubectl get deployments
```

Salida esperada:
```
NAME                                READY   STATUS    RESTARTS   AGE
auth-service-xxxxxxxxx-xxxxx        1/1     Running   0          2m
auth-service-xxxxxxxxx-yyyyy        1/1     Running   0          2m
clients-service-xxxxxxxxx-xxxxx     1/1     Running   0          2m
clients-service-xxxxxxxxx-yyyyy     1/1     Running   0          2m
frontend-xxxxxxxxx-xxxxx            1/1     Running   0          2m
frontend-xxxxxxxxx-yyyyy            1/1     Running   0          2m
mysql-xxxxxxxxx-xxxxx               1/1     Running   0          3m
```

### 5. Acceder a la aplicación

```
http://localhost:30080
```

---

## Verificación del Balanceo de Carga

Cada réplica incluye el header `X-Pod-Name` en sus respuestas HTTP, con el nombre del pod que procesó la petición.

### Método 1 — curl repetido

```bash
for i in {1..10}; do
  curl -s -I http://localhost:30080 | grep -i x-pod-name
done
```

Salida esperada (alternando entre los dos pods del frontend):
```
x-pod-name: frontend-7d6b9c5f4-abcde
x-pod-name: frontend-7d6b9c5f4-fghij
x-pod-name: frontend-7d6b9c5f4-abcde
x-pod-name: frontend-7d6b9c5f4-fghij
...
```

### Método 2 — Página de info del servidor

Accede a `http://localhost:30080/servidor_info.php` para ver el nombre del servidor y la IP del pod activo. Refresca la página varias veces para observar cómo alterna entre réplicas.

### Método 3 — Logs de los pods

```bash
# Ver logs de cada réplica del frontend
kubectl logs deployment/frontend --all-containers

# Ver logs de auth-service
kubectl logs deployment/auth-service --all-containers

# Ver logs de clients-service
kubectl logs deployment/clients-service --all-containers
```

---

## Estructura del proyecto

```
proyecto-novasoft/
├── auth-service/           # Microservicio de autenticación (FastAPI)
│   ├── main.py             # Rutas: /health, /auth/register, /auth/login, /auth/verify
│   ├── models.py           # Modelos Pydantic
│   ├── database.py         # Conexión MySQL
│   ├── requirements.txt
│   └── Dockerfile
├── clients-service/        # Microservicio de clientes (FastAPI)
│   ├── main.py             # CRUD completo: /clientes
│   ├── database.py         # Conexión MySQL
│   ├── requirements.txt
│   └── Dockerfile
├── frontend/               # Interfaz web (PHP / Apache)
│   ├── app/
│   │   ├── index.php           # Login
│   │   ├── registro.php        # Registro de usuario
│   │   ├── perfil.php          # Perfil del usuario
│   │   ├── lista_clientes.php  # Listado de clientes
│   │   ├── crear_cliente.php   # Formulario crear cliente
│   │   ├── editar_cliente.php  # Formulario editar cliente
│   │   ├── eliminar_cliente.php
│   │   ├── logout.php
│   │   ├── servidor_info.php   # Info del pod activo (para verificar balanceo)
│   │   └── config/
│   │       └── services.php    # URLs de los microservicios
│   └── Dockerfile
├── k8s/                    # Manifiestos Kubernetes
│   ├── 00-configmap.yaml   # ConfigMap y Secret
│   ├── 01-mysql.yaml       # MySQL con PVC
│   ├── 02-auth-service.yaml
│   ├── 03-clients-service.yaml
│   ├── 04-frontend.yaml    # NodePort 30080
│   └── 05-ingress.yaml
├── build-push.sh           # Construye y publica imágenes en Docker Hub
└── deploy.sh               # Despliega todo el stack en Kubernetes
```

---

## Imágenes Docker

Las imágenes están publicadas en Docker Hub:

| Imagen                                | Docker Hub |
|---------------------------------------|-----------|
| `korvix18/novasoft-auth:latest`       | https://hub.docker.com/r/korvix18/novasoft-auth |
| `korvix18/novasoft-clients:latest`    | https://hub.docker.com/r/korvix18/novasoft-clients |
| `korvix18/novasoft-frontend:latest`   | https://hub.docker.com/r/korvix18/novasoft-frontend |

---

## Comandos útiles

```bash
# Ver todos los recursos del cluster
kubectl get all

# Escalar réplicas del frontend
kubectl scale deployment frontend --replicas=3

# Reiniciar un deployment
kubectl rollout restart deployment/auth-service

# Ver detalles de un pod
kubectl describe pod <nombre-del-pod>

# Acceder a la shell de un pod
kubectl exec -it <nombre-del-pod> -- /bin/bash

# Eliminar todo el stack
kubectl delete -f k8s/
```

---

## Integrantes

| Nombre            | Usuario GitHub     |
|-------------------|--------------------|
| Alejandro Lopez   | @AlejandroMlozdr   |
| Pedro Sandoval    | (pendiente)        |
| Diego Flores      | (pendiente)        |
| Rene Ponce        | (pendiente)        |
| Fermin Garnica    | (pendiente)        |

---

## Licencia

Proyecto académico — Sistemas Operativos 2 · 2026
                    
