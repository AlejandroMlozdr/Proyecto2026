from pydantic import BaseModel, EmailStr

class RegisterRequest(BaseModel):
    username: str
    nombre: str
    correo: str
    contrasena: str
    celular: str

class LoginRequest(BaseModel):
    username: str
    password: str

class UserResponse(BaseModel):
    id: int
    username: str
    nombre: str
    correo: str
    celular: str
