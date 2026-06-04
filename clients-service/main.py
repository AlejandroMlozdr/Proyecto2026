from fastapi import FastAPI, HTTPException, Header, Request
from fastapi.middleware.cors import CORSMiddleware
from pydantic import BaseModel
from jose import jwt
import os
import socket
from dotenv import load_dotenv
from database import get_connection, init_db

load_dotenv()

app = FastAPI(title="Clients Service")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

SECRET_KEY = os.getenv("SECRET_KEY", "secreto")

# ─── Modelos ─────────────────────────────────────────────────────
class ClienteCreate(BaseModel):
    username: str
    nombre: str
    correo: str
    celular: str

class ClienteUpdate(BaseModel):
    nombre: str
    correo: str
    celular: str

# ─── Helpers ─────────────────────────────────────────────────────
def verify_token(authorization: str = ""):
    try:
        token = authorization.replace("Bearer ", "")
        payload = jwt.decode(token, SECRET_KEY, algorithms=["HS256"])
        return payload
    except Exception:
        raise HTTPException(status_code=401, detail="No autorizado")

@app.on_event("startup")
def startup():
    init_db()

# Middleware: agrega X-Pod-Name en cada respuesta para identificar la réplica
@app.middleware("http")
async def add_pod_header(request: Request, call_next):
    response = await call_next(request)
    response.headers["X-Pod-Name"] = socket.gethostname()
    return response

# ─── Health check ────────────────────────────────────────────────
@app.get("/health")
def health():
    return {"status": "ok", "service": "clients-service", "host": socket.gethostname()}

# ─── GET todos los clientes ──────────────────────────────────────
@app.get("/clientes")
def get_clientes(authorization: str = Header(default="")):
    verify_token(authorization)
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute("SELECT id, username, nombre, correo, celular, created_at FROM clientes ORDER BY id")
            return cursor.fetchall()
    finally:
        conn.close()

# ─── GET un cliente ──────────────────────────────────────────────
@app.get("/clientes/{cliente_id}")
def get_cliente(cliente_id: int, authorization: str = Header(default="")):
    verify_token(authorization)
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute("SELECT id, username, nombre, correo, celular FROM clientes WHERE id=%s", (cliente_id,))
            cliente = cursor.fetchone()
        if not cliente:
            raise HTTPException(status_code=404, detail="Cliente no encontrado")
        return cliente
    finally:
        conn.close()

# ─── POST crear cliente ──────────────────────────────────────────
@app.post("/clientes", status_code=201)
def create_cliente(data: ClienteCreate, authorization: str = Header(default="")):
    verify_token(authorization)
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(
                "INSERT INTO clientes (username, nombre, correo, celular) VALUES (%s,%s,%s,%s)",
                (data.username, data.nombre, data.correo, data.celular)
            )
            new_id = cursor.lastrowid
        conn.commit()
        return {"message": "Cliente creado", "id": new_id}
    finally:
        conn.close()

# ─── PUT editar cliente ──────────────────────────────────────────
@app.put("/clientes/{cliente_id}")
def update_cliente(cliente_id: int, data: ClienteUpdate, authorization: str = Header(default="")):
    verify_token(authorization)
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(
                "UPDATE clientes SET nombre=%s, correo=%s, celular=%s WHERE id=%s",
                (data.nombre, data.correo, data.celular, cliente_id)
            )
            if cursor.rowcount == 0:
                raise HTTPException(status_code=404, detail="Cliente no encontrado")
        conn.commit()
        return {"message": "Cliente actualizado"}
    finally:
        conn.close()

# ─── DELETE cliente ──────────────────────────────────────────────
@app.delete("/clientes/{cliente_id}")
def delete_cliente(cliente_id: int, authorization: str = Header(default="")):
    verify_token(authorization)
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute("DELETE FROM clientes WHERE id=%s", (cliente_id,))
            if cursor.rowcount == 0:
                raise HTTPException(status_code=404, detail="Cliente no encontrado")
        conn.commit()
        return {"message": "Cliente eliminado"}
    finally:
        conn.close()
