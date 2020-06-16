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

- Bug de mysql 10 - mariadb 10:
- Crear la siguiente función
[BUG]:
[SOURCE]:
    CREATE DEFINER=`root`@`localhost` FUNCTION `st_distance_sphere`(`pt1` POINT, `pt2` POINT) RETURNS decimal(10,2)
    BEGIN
    return 6371000 * 2 * ASIN(SQRT(
    POWER(SIN((ST_Y(pt2) - ST_Y(pt1)) * pi()/180 / 2),
    2) + COS(ST_Y(pt1) * pi()/180 ) * COS(ST_Y(pt2) *
    pi()/180) * POWER(SIN((ST_X(pt2) - ST_X(pt1)) *
    pi()/180 / 2), 2) ));
    END





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

- Para poder que funcionen las imágenes alojadas en el disco local, es necesario crear un
enlace simbólico:

[COMANDO]:
php artisan storage:link

[TEST]:
http://designhouse.test/storage/uploads/designs/thumbnail/1588555574_mes_que_un_club.jpg




## DELETE design image
- Se agrega la misma politica de seguridad implementada en update, ahora en el método delete
- Se procede a definir otra excepción (OTRO IF), dentro de la politica creada en pasos anteriores


### AGREGANDO TAGS 
- Se logra accediendo a esta web:
[URL]:
https://github.com/cviebrock/eloquent-taggable

[COMANDO]:
composer require cviebrock/eloquent-taggable

- Luego se procede a publicar el archivo de configuración:
[COMANDO]:
php artisan vendor:publish --provider="Cviebrock\EloquentTaggable\ServiceProvider" --tag "config"

- Posteriormente publicar las migraciones:
[COMANDO]:
php artisan vendor:publish --provider="Cviebrock\EloquentTaggable\ServiceProvider" --tag "migrations"

- Luego se procede a ejecutar las migraciones:
[COMANDO]:
composer dump-autoload
php artisan migrate



### SECCION PATRON REPOSITORIO 
- Creamos un nuevo controlador:
[COMANDO]:
php artisan make:controller User\\UserController
- en el proyecto dentro de la carpeta App, se creauna carpeta:

[FOLDER]
Repositories
    + Contracts
        +IDesign.php
    + Eloquent

## CREAMOS UN NUEVO PROVIDER:
[COMANDO]:
php artisan make:provider RepositoryServiceProvider

- Implementamos lo propio en Boot para Bindear la interfaz con el repositorio respectivo
- Posteriormente vamos a : config/app.php y registramos dicho service provider.

## CREAMOS NUEVA EXCEPCIÓN:  EXCEPCION MODEL
[COMANDO]:
php artisan make:exception ModelNotDefined

* NOTA: asegurarse desde POSTMAN tener configurado en las cabeceras:
    - Accept: JSON
    - Content-Type: JSON

## CREAR UNA NUEVA CARPETA DENTRO DE REPOSITORY:
[FOLDER]:
Criteria


[TAGS]: DEBUG, LARAVEL DEBUG
## INSTALAR DEBUG EN LARAVEL (DEBUG BAR - BROWSER DEBUG)
[COMANDO]:
composer require barryvdh/laravel-debugbar --dev

Copy the package config to your local config with the publish command:
[COMANDO]:
php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"


## CREAR MIDDLEWARE: PROFILE JSON RESPONSE
[COMANDO]:
php artisan make:middleware ProfileJsonResponse

- Luego lo registramos en el Kernel:
[SOURCE]:
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Fruitcake\Cors\HandleCors::class,
        \App\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        // TODO: add:middleware
        \App\Http\Middleware\ProfileJsonResponse::class,
    ];

- Desde postman intentar acceder a esta URL:
[URL]:
{{BASE_URL}}/users?_debug=true


### SECCION 7 - LIKES Y COMENTARIOS
- Creamos un modelo comentario con su respectiva migración:
[COMANDO]:
php artisan make:model Models\\Comment -m

- Una vez diligenciados los campos entre ellos el polimórfico "Comentarios", 
se ejecuta la migración:
[COMANDO]:
php artisan migrate

- Crea el respectivo controlador de comentario:
[COMANDO]:
php artisan make:controller Designs\\CommentController

- Creamos el repositorio Comentario:
- Creamos el contrato para el Repositorio del Comentario.
- Registramos en el provider Repository service la nueva entidad

- Luego creamos el recurso:
[COMANDO]:
php artisan make:resource CommentResource

- Procedemos a crear una politica para actualización de comentarios:
[COMANDO]:
php artisan make:policy CommentPolicy --model=Models\\Comment

### FUNCIONALIDAD DE LIKES
- Creamos un nuevo modelo con migración
[COMANDO]:
php artisan make:model Models\\Like -m

- Desde diseños, relacionamos con la tabla polimórfica: Likes

## ¡OJO!, en Comment (MODELO), también hacemos uso del Trait creado: Likeable:
[SOURCE]:
use Likeable

## CREAR EL MODELO DE TEAM
[COMANDO]
php artisan make:model Models\\Team -m

- Creamos luego el controlador
[COMANDO]:
php artisan make:controller Teams\\TeamsController --resource

- Creo el contrato y repositorio para TEAM
- Posterirmente registro dicho contrato y repositorio (Team) en el provider

- Creamos en el model de Team la propiedad estática boot para capturar cuando se
realice una inserción y poblar la tabla puente: team_user

- Posteriormente se crea un recurso para team:
[COMANDO]:
php artisan make:resource TeamResource

- Generamos una politica para el modelo Teams responsable de vigilar las actualizaciones
y posibles borrados de datos:
[COMANDO]:
php artisan make:policy TeamPolicy --model=Models\\Team

- en el método update:
[SOURCE]
    return $team->isOwnerOfTeam($team);

- Luego registrar en el serviceProvider la politica

## MODIFICANDO MIGRACIÓN DE TEAM PARA AGREGAR EL ID A LOS DISEÑOS:
[COMANDO]:
php artisan make:migration add_team_id_to_designs --table=designs

- una vez agregado la columna: team_id dentro de Team migración, se ejecuta la migración:
[COMANDO]:
php artisan migrate


## crear MODELO DE INVITADOS - INVITACIONES - INVITAR USUARIO A GRUPO
- Se crea el modelo:
[COMANDO]:
php artisan make:model Models\\Invitation -m

- creamos la propiedad protegida con:
ceripient_email
sender_id
team_id
token

- Una vez se definen las relaciones en teams, users e invitations, se procede a ejeutar el comando:
[COMANDO]:
php artisan make:controller Teams\\InvitationsController

- Luego ira la migración:
[COMANDO]
php artisan migrate

## CREAMOS EL REPOSITORIO PARA LAS INVITACIONES
- una vez creado contrato + implementación en la carpeta eloquent, se procede a configurar
el provider del Repository

- En el controlador escribimos la lógica necesaria para crear una invitación
de usuario para unirse a un grupo

- Creamos :
[COMANDO]:
php artisan make:mail SendInvitationToJoinTeam --markdown=emails.invitations.invite-new-user


- Corregimos el tipo de dato de email_recipient
php artisan make:migration change_field_to_invitations --table=invitations

- Instalar este paquete de composer:
composer require doctrine/dbal


## CREANDO POLITICA PARA LAS INVITACIONES CON MODELO DE INVITATION
[COMANDO]:
php artisan make:policy InvitationPolicy --model=Models\\Invitation

- Registramos la nueva politica en el service provider AuthServiceProvider.php



### SECCION # 9 (CHAT - MODEL - MODELO,  )
- Creamos el modelo para el chat y el modelo para Mensajes "Message"
[COMANDO]:
php artisan make:model Models\\Chat -m
php artisan make:model Models\\Message -m

la relación es la siguiente:

| Chat |------<| Participants |>-----| User |
  id_chat        id_chat               id_user
                 id_user



- En el modelo se define entonces:
[SOURCE]:
    public function participants()
    {
        return $this->belongsToMany(User::class, 'participants')
    }

    public function messages()
    {
        return $this->hasMany(Message::class);        
    }

    // Helper
        - Dentro del modelo Message:
        [SOURCE]:

            protected $fillable = [
            'user_id', 'chat_id', 'body', 'last_read'
        ];

        public function chat()
        {
            return $this->belongsTo(Chat::class);
        }

        public function sender()
        {
            return $this->belongsTo(User::class, 'user_id');
        }

- Creamos el controlador para chats:
[COMANDO]:
php artisan make:controller Chats\\ChatController

- Se procede entonces a crear los contratos y repositorios para:
Chat, Messages
- Se registran en el serviceProvider del repositorio.
- Se crean los recursos para gestionar respuestas:
[COMANDO]:
php artisan make:resource ChatResource
php artisan make:resource MessageResource

- Luego se agregan los campos respectivos en las migraciones
- Finalmente, ejecutar las migraciones:
[COMANDO]:
php artisan migrate

- Después de abundar en la implementación de los métodos del controlador para el Chat,
crearemos una politica para el caso de: Messages
[COMANDO]:
php artisan make:policy MessagePolicy --model=Models\\Message

- Luego registrar esta politica en el ServiceProvider respectivo: 

## RUTAS PARA BÚSQUEDAS DE DISEÑOS
- 



### SECTION #10
Funciones para análisis espacial

- Lo primero será agregar una Ruta en api.php:
[PATH]:
[SOURCE]:
Route::get('search/designers', 'User\userController@search');


### SECCION 11:
- AVATAR CON GRAVATAR:
[URL]:
http://www.gravatar.com/avatar/jaimeivan0017@gmail.com

- Configurar CORS:
[LINK]:
https://github.com/fruitcake/laravel-cors

- instalar a través de composer:
[COMANDO]:
composer require fruitcake/laravel-cors

- Posteriormente hay que definir un Middleware en el KERNEL de la app
- To allow CORS for all your routes, add the HandleCors middleware in the $middleware property of app/Http/Kernel.php class:

[SOURCE]:
    protected $middleware = [
        // ...
        \Fruitcake\Cors\HandleCors::class,
    ];

- Posteriormente, se crea configuración de cors:
- The defaults are set in config/cors.php. Publish the config to copy the file to your own config:
[COMANDO]:
php artisan vendor:publish --tag="cors"

- Una vez creado el fichero de configuración de cors en config de la app, se procede a agregar esta regla:

[SOURCE]:
'paths' => ['api/*'],


































### FRONTEND - Seccion - 12

- NVM
- para listar las versiones de node:
[COMANDO]:
nvm list

- ver todas las versiones:
[COMANDO]:
nvm list available

- instalar:
[COMANDO]:
nvm install 12.16.3

- usar una versión:
[COMANDO]:
nvm use 12.16.03

- IMPORTANTE: Si node da problemas al intentar ve la versión instalada, simplemente dentro
de archivos de programa en C, se cambia de nombre a la carpeta node para reintentar asignar
a través de NVM la versión a usar en el sistema operativo.

NPX
=> Crear proyecto FRONT:
[COMANDO]:
npx create-nuxt-app designhouse-client

- Una vez seleccionado los parametros iniciales de configuración, se procede a accedera la
carpeta del proyecto cliente y ejecutarlo:
[PATH-IMAGE]:
paramsConfig.png

[COMANDO]:
npm run dev

- En el fichero .env del cliente agregar:
[SOURCE]:
    BASE_URL=http://designhouse.test:3000
    API_BASE=http://designhouse.test/api

- Configuramos luego en la KEY axios, la baseUrl (nuxt.config)

- Posteriormente dentro del objeto head, definimos las paths de los scripts relativos a (nuxt.conf):
1. jquery
2. Bootstrap
3. Fontawesome

- procedemos a instalar en la consola:
npm i node-sass sass-loader --save

