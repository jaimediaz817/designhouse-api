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


