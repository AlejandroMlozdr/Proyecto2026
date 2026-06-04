from fastapi import FastAPI, HTTPException, Request
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import Response
from passlib.context import CryptContext
from jose import jwt
import os
import socket
from dotenv import load_dotenv
from database import get_connection, init_db
from models import RegisterRequest, LoginRequest, UserResponse

load_dotenv()

app = FastAPI(title="Auth Service")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["*"],
    allow_headers=["*"],
)

pwd_context = CryptContext(schemes=["bcrypt"], deprecated="auto")
SECRET_KEY = os.getenv("SECRET_KEY", "secreto")

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
    return {"status": "ok", "service": "auth-service", "host": socket.gethostname()}

# ─── Registro ────────────────────────────────────────────────────
@app.post("/auth/register", status_code=201)
def register(data: RegisterRequest):
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(
                "SELECT id FROM usuarios WHERE username=%s OR correo=%s",
                (data.username, data.correo)
            )
            if cursor.fetchone():
                raise HTTPException(status_code=400, detail="Username o correo ya registrado")
            hashed = pwd_context.hash(data.contrasena)
            cursor.execute(
                "INSERT INTO usuarios (username, nombre, correo, contrasena, celular) VALUES (%s,%s,%s,%s,%s)",
                (data.username, data.nombre, data.correo, hashed, data.celular)
            )
        conn.commit()
        return {"message": "Usuario registrado exitosamente"}
    finally:
        conn.close()

# ─── Login ───────────────────────────────────────────────────────
@app.post("/auth/login")
def login(data: LoginRequest):
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute("SELECT * FROM usuarios WHERE username=%s", (data.username,))
            user = cursor.fetchone()
        if not user or not pwd_context.verify(data.password, user["contrasena"]):
            raise HTTPException(status_code=401, detail="Credenciales incorrectas")
        token = jwt.encode(
            {"sub": str(user["id"]), "username": user["username"]},
            SECRET_KEY,
            algorithm="HS256"
        )
        return {
            "token": token,
            "user": {
                "id": user["id"],
                "username": user["username"],
                "nombre": user["nombre"],
                "correo": user["correo"],
                "celular": user["celular"]
            }
        }
    finally:
        conn.close()

# ─── Verificar token ─────────────────────────────────────────────
@app.get("/auth/verify")
def verify_token(token: str):
    try:
        payload = jwt.decode(token, SECRET_KEY, algorithms=["HS256"])
        return {"valid": True, "user_id": payload["sub"], "username": payload["username"]}
    except Exception:
        raise HTTPException(status_code=401, detail="Token inválido")
