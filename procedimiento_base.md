### COMANDOS GENERAL [UTILIDADES]
- limpiar cache:
[COMANDO]:
php artisan cache:clear
php artisan config:cache


### CONFIGURACIÓN DE HOST VIRTUAL

### CREAMOS UNA CARPETA MODELS
- dentro de Models, agregamos la entidad User
- modificamos el namespace de la entidad User:
namespace App\Models;

- Routes: ir a api.php
- Tenemos que eliminar el contenido que por defecto está allí escrito

### POSTMAN
- Creamos una variable base que represente el host principal del proyecto
haciendo click en el botón de configuración.


### CONFIGURANDO EL FICHERO .env (host)
APP_URL=http://designhouse.test

- Creamos la base de datos: desinghouse en el cliente MYSQL de preferencia.
- Seteamos dichos valores en el fichero .env
- para probar la conexión:
[COMANDO]:
php artisan migrate

[ERROR]:
para resolver el error del tamaño del campo, se abre el archivo AppServiceProvider.php
y en el método boot agregar lo siguiente:
[SOURCE]:
    // TODO: @jdiaz - add
    Schema::defaultStringLength(191);
    JsonResource::withoutWrapping();  


### SECCIÓN #3 ........................................................

## jwt auth
[COMANDO]:
composer require tymon/jwt-auth

- Manualmente, agregar esto en el fichero composer.json (DENTRO DE REQUIRE):
[SOURCE]:
    "tymon/jwt-auth": "^1.0.0-rc.5"

- Luego procedemos a escribir el comando para configurar el sel provider respectivo el paquete descargado:
php artisan vendor:publish --provider="Tymon\JWTAuth\Providers\LaravelServiceProvider"

- Luego nos dirijimos a: 
config/jwt.php

- Generar la llave:
php artisan jwt:secret

- Luego hacer que el modelo user implemente la clase: JWTSubject

[SOURCE]:
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }   

- Posteriormente ir al fichero auth dentro de config y modificar:

[SOURCE]:
    'guards' => [
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        'api' => [
            'driver' => 'jwt', // <-----
            'provider' => 'users',
            'hash' => false,
        ],
    ],

[SOURCE]:
    'defaults' => [
        'guard' => 'api',  // <----
        'passwords' => 'users',
    ],

### INSTALANDO AUTH LARAVEL 7:
- Ejecutar el comando:
[COMANDO]:
composer require laravel/ui
php artisan ui vue --auth

- Instalar mysql spatial:
[COMANDO]:
composer require grimzy/laravel-mysql-spatial

- Aplicar las configuraciones anotadas con //TODO: add en:
la migración de usuarios para agregar algunas columnas, y en el model User


### VERIFICANDO A TRAVÉS DEL EMAIL AL REGISTRARSE.

- Una vez implementado lo propio en VerificationController y agregar la ruta respectiva en api.php ejecutar el comando de migración para vaciar los datos de ba BD:
[COMANDO]:
php artisan migrate:fresh

- asegurarse de los parametros en .env para el servicio de mailtrap:
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=1f53c6d3d55206
MAIL_PASSWORD=c6b76f0cd69c1a
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=me@example.com
MAIL_FROM_NAME="${APP_NAME}"


- El archivo responsable de pintar el contenido del email está en:
verifyEmail.php

- Implementamos este método en el modelo de User:
[SOURCE]:
        // TODO:add
        public function sendEmailVerificationNotification()
        {
            $this->notify(new VerifyEmail);
        }

- Creamos a través de artisan una notificacion:
php artisan make:notification VerifyEmail

- Una vez creada la notificación VerifyEmail, se procede a borrar la clase de la que extiende
y en su lugar, extendemos del trait, para poder sobreescribir el método "verificationUrl"

- Ejecutar un refresco de la migración:
[COMANDO]:
php artisan migrate:fresh



### LOGIN PROCESS
- agregar la ruta en api.php

- Ir al controlador del Login

- Buscar el trait del Core:
AuthenticatesUsers.php

- Una vez testeado con POSTMAN todo el flujo de:
1. se registra el usuario
2. se toma desde el correo la path:
[PATH]:
?expires=1587896906&signature=5781e8d60722f144c72708354ca609cf006cbe54729e0db5220ec1e3ff0e4808

- previamente debe escribirse el ID del usuario en cuestión
3. Se ejecuta la petición: 
para verificar el email y activarse
4. Se intenta iniciar sesión obteniendo el TOKEN.

## NUEVA RUTA EN api.php
- 
- en postman agregar esto en la pestaña tests:
[SOURCE]:
pm.environment.set("TOKEN", pm.response.json().token)

- Posteriormente se configura en el entorno global del proyecto de las APIS en postman
agregando el mismo parametro {{TOKEN}} EN LA PARTE GENERAL, [...] menú de puntos, editar.


### CREANDO EL CONTROLADOR ME.CONTROLLER
- Crear nuevo controlador
[COMANDO]:
php artisan make:controller User\\MeController

### CREANDO UN NUEVO RECURSO USER RESOURCE:
- Ejecutar el comando:
php artisan make:resource UserResource


### RECUPERACIÓN DE PASSWORD:
- Se agrega en API:
[SOURCE]:
    Route::post('password/email', 'Auth\ForgotPasswordController@sendLinkEmail');
    Route::post('password/reset', 'Auth\ResetPasswordController@reset');

- Creamos una nueva notificación:
php artisan make:notification ResetPassword




### PERFIL DE USUARIO (sección #4)
- Agregamos unas rutas básicas en api.php y posteriormente ejecutamos el
comando para crear un controlador nuevo:
[COMANDO]:
php artisan make:controller User\\SettingsController

- Creamos una nueva regla:
[COMANDO]:
php artisan make:rule MatchOldPassword
php artisan make:rule CheckSamePassword

- Con las reglas anteriores podemos facilmente separar responsabilidades
del lado de las validaciones para tener un mejor orden para los mensajes
respectivos y las reglas requeridas (checkear password actual y que no sea la misma 
que pretende cambiar).


### MODELO DE DESIGN Y MIGRACIONES (Sección #5)

- Creación del modelo:
[COMANDO]:
php artisan make:model Models\\Design -m

- ajustamos los campos necesarios para proceder a ejecutar la migración:
[COMANDO]:
php artisan migrate

- Ahora se procede a crear el controlador
[COMANDO]:
php artisan make:controller Designs\\UploadController

- Configuramos una ruta para las subidas - uploads
- Después de configurar la validación (con validate), se procede a crear un fichero
nuevo dentro de la carpeta conf:
[FILE]
site.php

- Modificamos la migración de Designs para agregar nuevos campos:
[COMANDO]:
php artisan make:migration add_fields_to_designs --table=designs

- Una vez se ha agregado la columna se procede a configurar el Storage en laravel:

Se modifica el fichero:
[ARCHIVO]
filesSystems.php dentro de la carpeta CONF

- Dentro de la carpeta STORAGE, se crea una carpeta:
[CARPETA]:
uploads

- Dentro de uploas, se crean 3 sub carpetas más.

- Después de probar en POSTMAN la correcta subida del archivo temporal, procederemos a crear un JOB:
[COMANDO]:
php artisan make:job UploadImage

- Luego procedemos a instalar una librería para la gestión del tamaño de imágenes:
[URL]:
image.intervention.io/getting_started/installation

- Instalación:
[COMANDO]:
composer require intervention/image

- Implementar lo propio en el Job creado en pasos anteriores y revisar muy bien la 
composición de las rutas relativas para acceder a las imágenes.


### GUARDAR - CONFIGURAR SERVICIO DE AMAZON WEB SERVICES (AWS FILESYSTEM)
- debemos ejecutar el siguiente comando:
[COMANDO]:
composer require league/flysystem-aws-s3-v3

- Desde amazon, se procede a obtener la clave secreta para ingresarlas en el fichero .env

- Después del proceso de creación de la politica:
[CODIGO]
[SOURCE]:
    {
    "Id": "Policy1588489761326",
    "Version": "2012-10-17",
    "Statement": [
        {
        "Sid": "Stmt1588489739031",
        "Action": [
            "s3:DeleteObject",
            "s3:GetObject"
        ],
        "Effect": "Allow",
        "Resource": "arn:aws:s3:::trilogicprojects",
        "Principal": "*"
        }
    ]
    }


### CONFIGURANDO QUEUES LARAVEL:
-   Lo primero que tenemos que hacer es identificar la propiedad en .ENV:
QUEUE_CONNECTION=sync
QUEUE_CONNECTION=database (SE DEJA ASÍ)

- Creamos una Queue a partir de una tabla (generamos la migración):
[COMANDO]:
php artisan queue:table
php artisan migrate

- volvemos a dejar en el fichero .ENV, la siguiente propiedad modificada en pasos anteriores:
QUEUE_CONNECTION=sync (SE DEJA ASÍ)
QUEUE_CONNECTION=database

- Ahora procedemos a crear un Jork del job:
[COMANDO]:
php artisan queue:work 

## FLUJO DE PRUEBA:
1. en .ENV, se coloca:
QUEUE_CONNECTION=sync (SE DEJA ASÍ)

2. Se ejecutan los comandos para borrar la caché. (2 comandos):
[COMANDO]:
php artisan cache:clear
php artisan config:cache

3. Se apreciará cierto retardo al ejecutar la petición con POSTMAN

4. Nuevamente se modifica el fichero .ENV:
QUEUE_CONNECTION=database (SE DEJA ASÍ)

5. Nuevamente se borra la caché
6. Se ejecuta la petición y ésta responderá más rápido.
7. Verificar en la tabla de jobs una pendiente por ejecutar en la cola
8. Se ejecuta el comando:
[COMANDO]:
php artisan queue:work

9. Volver a realizar una petición en POSTMAN y notará que se ejecutará
nuevamente el proceso en la terminal como causa de haber ejecutado el work anterior.


### ACTUALIZAR DESIGNS - UPDATE DESIGNS
- Creamos la ruta en api.php y elcontrolador:
[PATH]:
Route::put('designs/{id}', 'Designs\DesignController@update');

[COMANDO]:
php artisan make:controller Designs\\DesignController

- Se actualiza la información del Diseño sin repetir ningún campo 'title'

### Crear politica de seguridad para el proceso actualización:
- crear politica:
[COMANDO]:
php artisan make:policy DesignPolicy --model=Models\\Design

- Después de crear la politica, se procede a registrarla en el provider:
AuthServiceProvider

- Después de modificar en el controlador Design y método update, se cambia el orden
de la consulta para aplicar el método authorize.

- Se implementa validación en:  App/Exceptions/Handler

### Creando recurso para Design:
- Se crea un recurso de la siguiente manera:
[COMANDO]:
php artisan make:resource DesignResource

- Luego de crear el recurso para Designs, hacemos un paréntesis para agregar al
modelo DESIGN un atributo get:

- 