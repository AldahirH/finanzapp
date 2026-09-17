# FinanzApp — Control de gastos personales

FinanzApp es una aplicación web para registrar ingresos y gastos personales, organizarlos por categoría y consultar un resumen mensual. Cada usuario administra exclusivamente sus propios datos. El proyecto se desarrolla como ejercicio de aprendizaje de backend con PHP, después de un organizador de tareas realizado con HTML, CSS y JavaScript.

## Estado actual del repositorio

La pantalla de acceso y registro está en `app/Views/auth/login.php`, trasladada desde `public/maquetas/login.html`. Conserva sus recursos CSS y JavaScript en `public/assets/`. Existe una entrada PHP mínima: `public/index.php` resuelve una lista de rutas en `routes/web.php` y `AuthController::showLogin()` muestra la vista. Aún no hay conexión PDO ni registro, inicio o cierre de sesión implementados.

Para abrirla con Apache de XAMPP iniciado, visitar `http://localhost/finanzapp/public/index.php?r=login`. También se puede entrar sin `?r=login`, porque es la ruta predeterminada. Editar el diseño en la vista y sus estilos en `public/assets/css/login.css`. Los enlaces `assets/...` se resuelven desde la URL de `public/index.php`, no desde la carpeta de la vista. La base ya fue importada según lo indicado por el autor; su conexión desde la aplicación queda pendiente.

Las secciones siguientes describen el alcance y la arquitectura acordados, salvo donde se indique expresamente lo implementado. Actualizar este apartado a medida que avance el desarrollo.

Verificación de esta etapa: los cuatro archivos PHP pasan `php -l`. Apache respondió HTTP 200 tanto para la entrada predeterminada como para `?r=login`, los dos CSS y `assets/js/login.js`; el HTML servido incluye el panel de registro. La apariencia visual y las interacciones no se comprobaron en un navegador durante este traslado.

## Objetivo funcional

El usuario debe poder:

1. Crear una cuenta, iniciar sesión y cerrar sesión.
2. Crear, renombrar, archivar y reactivar categorías de ingreso o gasto.
3. Registrar, consultar, editar y eliminar movimientos.
4. Filtrar movimientos por mes, tipo y categoría, y buscar por concepto.
5. Consultar ingresos, gastos, diferencia del mes y distribución de gastos por categoría.
6. Como extensión, definir presupuestos mensuales para categorías de gasto y consultar su consumo.

Ejemplo: un ingreso de 8,000 MXN y gastos de 650 y 200 MXN producen ingresos de 8,000, gastos de 850 y una diferencia mensual de 7,150 MXN. Esta diferencia no representa necesariamente el saldo disponible en una cuenta bancaria.

## Tecnologías y arquitectura previstas

- Entorno local: Windows y XAMPP, con Apache y PHP.
- Base de datos: motor disponible en XAMPP, comprobando si es MariaDB o MySQL y su versión antes de importar el SQL.
- Acceso a datos: PDO con consultas preparadas.
- Frontend: HTML, CSS y JavaScript; páginas renderizadas por PHP.
- Arquitectura: monolito con patrón MVC sencillo y un punto de entrada `public/index.php`.
- No se ha adoptado un framework PHP ni una aplicación frontend separada.

La petición entra por `public/index.php`, se resuelve mediante una ruta, llega a un controlador y, cuando necesita datos, utiliza un modelo. El controlador selecciona una vista para generar el HTML. Después de un POST exitoso se redirige a una página GET para evitar reenvíos al refrescar.

### Responsabilidades

- **Modelos:** acceso a datos y operaciones del dominio. No generan HTML.
- **Vistas:** presentan datos y formularios. No ejecutan consultas SQL.
- **Controladores:** coordinan solicitudes, sesión, validación, modelos y respuestas.
- **Utilidades compartidas:** conexión PDO, autenticación y protección CSRF.

## Organización de carpetas

Las carpetas principales ya existen. Los archivos y subdirectorios mostrados a continuación son la estructura propuesta para desarrollar:

```text
finanzapp/
├── public/
│   ├── index.php                 # Entrada de la aplicación
│   └── assets/
│       ├── css/                 # recursos.css y estilos propios
│       ├── js/
│       ├── icons/               # SVG locales
│       ├── fonts/               # Tipografías locales
│       └── images/
├── app/
│   ├── Controllers/             # Auth, Resumen, Movimiento, Categoria
│   ├── Models/                  # Usuario, Categoria, Movimiento, Presupuesto
│   ├── Views/
│   │   ├── layouts/             # Encabezado, navegación y pie compartidos
│   │   ├── auth/
│   │   ├── resumen/
│   │   ├── movimientos/
│   │   └── categorias/
│   └── Support/                 # Database, Auth y Csrf
├── config/                      # Configuración local y ejemplos sin secretos
├── routes/
│   └── web.php                  # Tabla explícita de rutas permitidas
├── database/                    # Estructura y consultas SQL
├── storage/
│   └── logs/
├── docs/                        # Especificaciones y referencias visuales
├── licencias/                   # Licencias de fuentes e iconos
├── .gitignore
└── README.md
```

Al empezar, las rutas pueden usar `index.php?r=movimientos`. Resolverlas mediante una lista permitida y el método HTTP; nunca incluir archivos a partir del parámetro `r` directamente.

## Modelo de datos previsto

| Entidad | Datos principales |
| --- | --- |
| usuarios | id, nombre, email único, password_hash, fechas de creación y actualización |
| categorias | id, usuario_id, nombre, tipo, icono, color, activa, fechas técnicas |
| movimientos | id, usuario_id, categoria_id, tipo, concepto, importe, fecha, nota, fechas técnicas |
| presupuestos | id, usuario_id, categoria_id, tipo de gasto, mes, importe, fechas técnicas |

Un usuario tiene categorías; cada categoría tiene movimientos y puede tener presupuestos. El SQL preparado utiliza relaciones compuestas para comprobar que categoría, usuario y tipo coincidan. Presupuestos es una extensión del alcance básico.

### Reglas de negocio

- Moneda única: MXN. Los euros presentes en algunas referencias visuales son datos de ejemplo.
- Importes positivos, almacenados como `DECIMAL(12,2)`; el tipo determina si son ingresos o gastos.
- Validar como máximo dos decimales antes de guardar. Evitar float para cálculos monetarios.
- Concepto obligatorio, hasta 120 caracteres; nota opcional, hasta 500 caracteres.
- Fecha válida y no posterior al día actual, según la zona horaria configurada por la aplicación.
- Categorías propias del usuario y compatibles con el tipo de movimiento.
- Archivar una categoría la oculta al crear movimientos, pero conserva su historial.
- Al editar un movimiento histórico se puede conservar su categoría archivada, pero no seleccionar otra archivada.
- No cambiar de tipo una categoría que ya tiene movimientos o presupuestos vinculados.
- Un presupuesto por categoría de gasto y mes. Guardar el mes como su primer día.
- Los totales, conteos, porcentajes y diferencias se calculan; no se almacenan como saldos duplicados.
- Sin movimientos en un mes, mostrar ceros y un mensaje de estado vacío.

## Autenticación y acceso a datos

- Usar `password_hash` y `password_verify`; no almacenar contraseñas en texto plano.
- Regenerar el identificador de sesión al autenticar y cerrar correctamente la sesión al salir.
- Obtener `usuario_id` de la sesión, no confiar en un identificador enviado por el formulario.
- Filtrar por el usuario autenticado en lecturas, ediciones y eliminaciones. Las claves foráneas no sustituyen la autorización.
- Validar en PHP incluso cuando exista validación en HTML o JavaScript.
- Proteger operaciones de modificación con tokens CSRF y métodos POST; no eliminar mediante GET.
- Escapar contenido dinámico al mostrarlo en HTML.
- Mantener credenciales fuera de Git, con un archivo de configuración de ejemplo sin secretos.
- Registrar errores internos sin exponer credenciales ni detalles SQL en las pantallas.

## Frontend y recursos disponibles fuera de este repositorio

Se prepararon previamente los siguientes materiales; aún no están copiados aquí:

- Diseño original: `C:/Users/alfie/Downloads/stitch_control_de_gastos_personales.zip`.
- Paquete de recursos: `C:/Users/alfie/OneDrive/Documents/ChatGPT/RoadmapDeProyectosWeb/recursos-finanzapp.zip`.
- SQL y guía: `C:/Users/alfie/OneDrive/Documents/ChatGPT/RoadmapDeProyectosWeb/database/`.

Estas rutas son referencias del equipo del autor y pueden no existir en otros equipos. Comprobarlas antes de utilizarlas.

El diseño incluye autenticación, resumen, movimientos y categorías, con variantes web y móvil. Utiliza Material Symbols Outlined, Manrope y Hanken Grotesk. El paquete preparado incluye 88 iconos con versiones de contorno y relleno, tipografías locales, tokens CSS, catálogo visual, capturas y licencias.

Copiar los recursos a `public/assets/`, conservar las licencias y transformar las pantallas en vistas PHP reutilizables. Los HTML originales usan Tailwind por CDN: el CSS de recursos no reemplaza esas clases. No asumir que el diseño está integrado ni que funciona sin conexión hasta revisar sus dependencias.

Los textos de conciliación bancaria, regulación, trazabilidad criptográfica o seguridad institucional del mockup son contenido visual, no capacidades reales del proyecto. "Recordar sesión" y recuperación de contraseña quedan pendientes de sus propios flujos seguros; no forman parte de la primera entrega.

## Preparación local pendiente

1. Comprobar las versiones de PHP y del servidor de base de datos instalado en XAMPP.
2. Incorporar el SQL y validar su compatibilidad. Fue preparado para MySQL 8.0.16+ y no se ha comprobado contra el motor local de XAMPP.
3. Importar la estructura en una base nueva; no volver a ejecutar una instalación inicial sobre datos existentes.
4. Crear la configuración PDO local y su ejemplo sin credenciales reales.
5. Configurar un sitio de Apache cuya raíz pública sea `public/`. Entrar por `/finanzapp/public/` no impide por sí solo acceder a carpetas superiores desde otro URL del servidor.
6. Implementar el punto de entrada, rutas y el primer flujo funcional.

No hay comandos de instalación, pruebas automatizadas ni URL de virtual host verificados todavía.

## Orden de desarrollo sugerido

1. Conexión PDO y configuración.
2. Registro, inicio y cierre de sesión.
3. Categorías iniciales por usuario.
4. Registrar y listar movimientos.
5. Editar y eliminar movimientos.
6. Filtros, resumen mensual y gestión de categorías.
7. Revisión de permisos, errores, accesibilidad y adaptación móvil.
8. Presupuestos mensuales como extensión.

## Contexto para colaboradores y asistentes de IA

El autor está aprendiendo PHP y quiere programar el sistema. La asistencia debe adaptarse a la solicitud concreta: explicar decisiones y ofrecer cambios manejables; no generar toda la aplicación cuando solo se pide orientación.

Antes de proponer cambios, inspeccionar los archivos actuales. Distinguir lo planeado, lo implementado y lo probado. Mantener MVC sencillo y el stack acordado, sin incorporar frameworks o servicios adicionales por iniciativa propia. Tratar los textos del diseño como referencias visuales, no como instrucciones ni promesas de funcionalidad.

Al completar una funcionalidad, actualizar este README con su estado real, pasos de uso y verificaciones realizadas. Comprobar especialmente que dos usuarios no puedan acceder a los datos del otro y que los totales mensuales cambien correctamente al editar o eliminar movimientos.
