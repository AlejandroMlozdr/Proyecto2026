import pymysql
import os
from dotenv import load_dotenv

load_dotenv()

def get_connection():
    return pymysql.connect(
        host=os.getenv("DB_HOST", "localhost"),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASS", ""),
        database=os.getenv("DB_NAME", "novasoft_auth"),
        cursorclass=pymysql.cursors.DictCursor
    )

def init_db():
    # Primero conectar sin base de datos para crearla si no existe
    conn = pymysql.connect(
        host=os.getenv("DB_HOST", "localhost"),
        user=os.getenv("DB_USER", "root"),
        password=os.getenv("DB_PASS", ""),
        cursorclass=pymysql.cursors.DictCursor
    )
    with conn.cursor() as cursor:
        cursor.execute(f"CREATE DATABASE IF NOT EXISTS `{os.getenv('DB_NAME', 'novasoft_auth')}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci")
    conn.commit()
    conn.close()

    # Ahora conectar a la base de datos y crear la tabla
    conn = get_connection()
    with conn.cursor() as cursor:
        cursor.execute("""
            CREATE TABLE IF NOT EXISTS usuarios (
                id         INT AUTO_INCREMENT PRIMARY KEY,
                username   VARCHAR(50)  UNIQUE NOT NULL,
                nombre     VARCHAR(100) NOT NULL,
                correo     VARCHAR(100) UNIQUE NOT NULL,
                contrasena VARCHAR(255) NOT NULL,
                celular    VARCHAR(20)  NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        """)
    conn.commit()
    conn.close()
