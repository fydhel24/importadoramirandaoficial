<?php


return [

    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    |
    | Here you can change the default title of your admin panel.
    |
    | For detailed instructions you can look the title section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'title' => 'IMPORTADORA MIRANDA',
    'title_prefix' => '',
    'title_postfix' => '',

    /*
    |--------------------------------------------------------------------------
    | Favicon
    |--------------------------------------------------------------------------
    |
    | Here you can activate the favicon.
    |
    | For detailed instructions you can look the favicon section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_ico_only' => false,
    'use_full_favicon' => false,

    /*
    |--------------------------------------------------------------------------
    | Google Fonts
    |--------------------------------------------------------------------------
    |
    | Here you can allow or not the use of external google fonts. Disabling the
    | google fonts may be useful if your admin panel internet access is
    | restricted somehow.
    |
    | For detailed instructions you can look the google fonts section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'google_fonts' => [
        'allowed' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Logo
    |--------------------------------------------------------------------------
    |
    | Here you can change the logo of your admin panel.
    |
    | For detailed instructions you can look the logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'logo' => '<b class="logo-importadora">Importadora</b> <b class="logo-miranda">Miranda</b>',



    'logo_img' => 'images/logo.png',
    'logo_img_class' => 'brand-image img-circle elevation-3',
    'logo_img_xl' => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt' => 'Admin Logo',

    /*
    |--------------------------------------------------------------------------
    | Authentication Logo
    |--------------------------------------------------------------------------
    |
    | Here you can setup an alternative logo to use on your login and register
    | screens. When disabled, the admin panel logo will be used instead.
    |
    | For detailed instructions you can look the auth logo section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'auth_logo' => [
        'enabled' => false,
        'img' => [
            'path' => 'images/logo.png',
            'alt' => 'Auth Logo',
            'class' => '',
            'width' => 50,
            'height' => 50,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Preloader Animation
    |--------------------------------------------------------------------------
    |
    | Here you can change the preloader animation configuration. Currently, two
    | modes are supported: 'fullscreen' for a fullscreen preloader animation
    | and 'cwrapper' to attach the preloader animation into the content-wrapper
    | element and avoid overlapping it with the sidebars and the top navbar.
    |
    | For detailed instructions you can look the preloader section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'preloader' => [
        'enabled' => true,
        'mode' => 'fullscreen',
        'img' => [

            'path' => 'images/logo.png', // Cambia la ruta aquí
            'alt' => 'AdminLTE Preloader Image',
            'effect' => 'animation__shake',
            'width' => 200,
            'height' => 200,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Menu
    |--------------------------------------------------------------------------
    |
    | Here you can activate and change the user menu.
    |
    | For detailed instructions you can look the user menu section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'usermenu_enabled' => true,
    'usermenu_header' => false,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => false,
    'usermenu_desc' => false,
    'usermenu_profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Layout
    |--------------------------------------------------------------------------
    |
    | Here we change the layout of your admin panel.
    |
    | For detailed instructions you can look the layout section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'layout_topnav' => null,
    'layout_boxed' => null,
    'layout_fixed_sidebar' => true,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => false,

    /*
    |--------------------------------------------------------------------------
    | Authentication Views Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the authentication views.
    |
    | For detailed instructions you can look the auth classes section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'classes_auth_card' => 'card-outline card-primary',
    'classes_auth_header' => '',
    'classes_auth_body' => '',
    'classes_auth_footer' => '',
    'classes_auth_icon' => '',
    'classes_auth_btn' => 'btn-flat btn-primary',

    /*
    |--------------------------------------------------------------------------
    | Admin Panel Classes
    |--------------------------------------------------------------------------
    |
    | Here you can change the look and behavior of the admin panel.
    |
    | For detailed instructions you can look the admin panel classes here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */
    /* .sidebar-gradient {
    background: rgb(238, 174, 202);
    background: radial-gradient(circle, rgba(238, 174, 202, 1) 0%, rgba(148, 161, 233, 1) 100%);esto en carpeta css/app.css
}
<link rel="stylesheet" href="{{ asset('css/app.css') }}"> esto en master de vendor*/


    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => '',
    'classes_content_header' => '',
    'classes_content' => '',

     //cambio
    'classes_sidebar' => 'sidebar-purple elevation-4 sidebar-gradient',

    'classes_sidebar_nav' => 'sidebar-letter-white',
    'classes_topnav' => 'sidebar-purple elevation-4 ',
    'classes_topnav_nav' => 'navbar-expand',
    'classes_topnav_container' => 'container',

    /*
    |--------------------------------------------------------------------------
    | Sidebar
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar of the admin panel.
    |
    | For detailed instructions you can look the sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'sidebar_mini' => 'lg',
    'sidebar_collapse' => false,
    'sidebar_collapse_auto_size' => false,
    'sidebar_collapse_remember' => false,
    'sidebar_collapse_remember_no_transition' => true,
    'sidebar_scrollbar_theme' => 'os-theme-light',
    'sidebar_scrollbar_auto_hide' => 'l',
    'sidebar_nav_accordion' => true,
    'sidebar_nav_animation_speed' => 300,

    /*
    |--------------------------------------------------------------------------
    | Control Sidebar (Right Sidebar)
    |--------------------------------------------------------------------------
    |
    | Here we can modify the right sidebar aka control sidebar of the admin panel.
    |
    | For detailed instructions you can look the right sidebar section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Layout-and-Styling-Configuration
    |
    */

    'right_sidebar' => false,
    'right_sidebar_icon' => 'fas fa-cogs',
    'right_sidebar_theme' => 'light',
    'right_sidebar_slide' => true,
    'right_sidebar_push' => true,
    'right_sidebar_scrollbar_theme' => 'os-theme-light',
    'right_sidebar_scrollbar_auto_hide' => 'l',
    /*
    |--------------------------------------------------------------------------
    | URLs
    |--------------------------------------------------------------------------
    |
    | Here we can modify the url settings of the admin panel.
    |
    | For detailed instructions you can look the urls section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Basic-Configuration
    |
    */

    'use_route_url' => false,
    'dashboard_url' => 'home',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => 'register',
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Mix
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Mix option for the admin panel.
    |
    | For detailed instructions you can look the laravel mix section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'enabled_laravel_mix' => false,
    'laravel_mix_css_path' => 'css/app.css',
    'laravel_mix_js_path' => 'js/app.js',

    /*
    |--------------------------------------------------------------------------
    | Menu Items
    |--------------------------------------------------------------------------
    |
    | Here we can modify the sidebar/top navigation of the admin panel.
    |
    | For detailed instructions you can look here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */
    'menu' => [
        // Navbar items:
        [
            'text' => '',
            'url'  => '/analisis',
            'icon' => 'fas fa-chart-bar',
            'topnav_right' => true,
            'can' =>  'users.index',
        ],
        [
            'text'    => '',
            'url'     => 'registros',
            'icon' => 'fas fa-book-open',// Ícono de la cámara (puedes cambiarlo)
            'icon_color' => 'primary', // Color del ícono
            'topnav_right' => true,
            'can' => 'pedidos.index',
        ],
          [
            'text' => '',
            'url'  => '/planilla-pagos',
            'icon' => 'fas fa-credit-card',
            'topnav_right' => true,
             'can' => 'users.index'

        ],
          [
            'text'    => '',
            'url'     => 'carpetas',
            'icon' => 'fas fa-camera-retro',// Ícono de la cámara (puedes cambiarlo)
            'icon_color' => 'primary', // Color del ícono
            'topnav_right' => true,
            'can' => 'pedidos.index',
        ],
        [
            'text' => '',
            'url'  => '/precioproductos', // Usa la ruta que configuraste en routes/web.php
            'icon' => 'fas fa-money-check-alt',
            'topnav_right' => true
        ],
        [
            'text' => '',
            'url'  => '/notas',
           'icon' => 'fas fa-calendar-alt',
            'topnav_right' => true
        ],
        [
            'text' => '',
            'url'  => '/change-password',
           'icon' => 'fas fa-lock-open',
            'topnav_right' => true
        ],

        [
            'text' => '',
            'url'  => '/proveedores',
            'icon' => 'fas fa-user-circle',
            'can' => 'proveedores.index',
            'topnav_right' => true
        ],
        
       [
    'text' => '',
    'url'  => '/solicitudes',
    'icon' => 'fas fa-file-signature',
    'topnav_right' => true,
    'can' => 'pedidos.index',
],


        // Sidebar items:
        [
            'type' => 'sidebar-menu-search',
            'text' => 'search',
        ],
        [
            'text' => 'blog',
            'url' => 'admin/blog',
            'can' => 'manage-blog',
        ],

        [
            'text' => 'Reporte Caja',
            'url'  => '/caja-sucursal',
            'icon' => 'fas fa-fw fa-share',
            'can' => 'caja_sucursal.index'
        ],
        [
            'text' => 'Cuaderno sucursal',
            'url'  => '/envioscuadernosucursal',
            'icon' => 'fas fa-book-open',
            'can' =>  'pedidos.index',
        ],
        [
            'text' => 'Gestion de Usuarios',
            'icon' => 'fas fa-users',
            'submenu' => [
                [
                    'text' => 'Roles',
                    'url'  => 'roles',
                    'icon' => 'fas fa-user',
                    'can' => 'roles.index'
                ],
                [
                    'text' => 'Usuarios',
                    'url'  => 'users',
                    'icon' => 'fas fa-user',
                    'can' => 'users.index'
                ],

            ],
        ],
        
        
        [
            'text' => 'Cuaderno',
            'icon' => 'fas fa-book',
            'submenu' => [
                [
                    'text' => 'Cuaderno Completo',
                    'url'  => '/envioscuaderno', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-book-open', // Icono de libro
                    'can' => 'pedidos.index',
                ],
                [
                    'text' => 'Cuaderno Sin Marcados',
                    'url'  => '/envioscuaderno/sinmarcados', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-book-open', // Icono de libro
                    'can' => 'pedidos.index',
                ],
                [
                    'text' => 'Cuaderno Sin La Paz',
                    'url'  => '/envioscuaderno/sinlapaz', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-map-marker-alt', // Icono de ubicación
                    'can' => 'pedidos.index',
                ],
                [
                    'text' => 'Cuaderno Sin enviado ni la paz',
                    'url'  => '/envioscuaderno/sinlapazyenviados', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-exclamation-triangle', // Icono de advertencia
                    'can' => 'pedidos.index',
                ],
                [
                    'text' => 'Cuaderno Solo La Paz',
                    'url'  => '/envioscuaderno/sololapaz', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-globe', // Icono de globo (para algo relacionado con el mundo, como la paz)
                    'can' => 'pedidos.index',
                ],
                [
                    'text' => 'La Paz Confirmados',
                    'url'  => '/envioscuaderno/confirmados', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-check-circle', // Icono de confirmación
                    'can' => 'pedidos.index',
                ],
                [
                    'text' => 'La Paz Pendientes',
                    'url'  => '/envioscuaderno/pendientes', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-hourglass-half', // Icono de pendiente o espera
                    'can' => 'pedidos.index',
                ],
                
 [
    'text' => 'Cuaderno Pendientes',
    'url'  => '/envios/faltante', // Usa la ruta que configuraste en routes/web.php
    'icon' => 'fas fa-globe', // Icono de globo (para algo relacionado con el mundo, como la paz)
    'can' => 'pedidos.index',
],


                [
                    'text' => 'Cuaderno Listo para Enviar',
                    'url'  => '/envios/extra1', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-globe', // Icono de globo (para algo relacionado con el mundo, como la paz)
                    'can' => 'pedidos.index',
 ],
            ],

        ],
        [
            'text' => 'PEDIDOS',
            'icon' => 'fas fa-plane',
            'submenu' => [

                [
                    'text' => 'PEDIDOS',
                    'url'  => 'pedidos', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-plane',
                    'can' => 'pedidos.index'
                ],
                [
                    'text' => 'DIAS DE PEDIDO',
                    'url'  => 'semanas',
                    'icon' => 'fas fa-paper-plane',
                    'can' => 'semanas.index'
                ],

                // Aquí añadimos el nuevo botón
                [
                    'text' => 'VER DIAS',
                    'url'  => '/orden', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-sun',
                    'can' => 'orden.index'
                ]
            ],
        ],

        [
            'text' => 'Solicitud y Envios de Productos',
            'icon' => 'fas fa-plane-arrival',
            'submenu' => [

                [
                    'text' => 'Envio de Productos',
                    'url'  => 'envios', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-calendar-week',
                    'can' => 'envios.index'
                ],
                [
                    'text' => 'Historial de Envios',
                    'url'  => '/envios/historial', // Usa la ruta configurada en web.php
                    'icon' => 'fas fa-history',
                    'can' => 'envios.historial'
                ],
                [
                    'text' => 'Solicitud de Productos',
                    'url'  => '/envios/solicitud', // Usa la ruta configurada en web.php
                    'icon' => 'fas fa-clipboard-check',
                    'can' => 'envios.solicitud'
                ],
            ],
        ],


        [
            'text' => 'Administrar Stock',
            'url'  => 'reportestockedit',
            'icon' => 'fas fa-cubes',
            'can' => 'report.stock'
        ],
        [
            'text' => 'Reporte de stock',
            'url'  => 'reportestock',
            'icon' => 'fas fa-calendar-week',
            'can' => 'report.stock'
        ],

        [
            'text' => 'Gestión de Almacén',
            'icon' => 'fas fa-warehouse',
            'submenu' => [
                [
                    'text' => 'Almacén',
                    'url'  => 'productos',
                    'icon' => 'fas fa-store',
                    'can'  => 'productos.index'
                ],
                [
                    'text' => 'Productos en mal estado',
                    'url'  => '/envios/productos-mal-estado',
                    'icon' => 'fas fa-exclamation-triangle',
                    'can'  => 'productos.index'
                ],
                [
                    'text' => 'Recepción a Almacén',
                    'url'  => '/envios/productos-almacen',
                    'icon' => 'fas fa-dolly',
                    'can'  => 'productos.index'
                ]
            ]
        ],
        [
            'text' => 'Detalles de productos',
            'icon' => 'fas fa-info',
            'submenu' => [
                [
                    'text' => 'Inventarios',
                    'url' => 'inventarios',
                    'icon' => 'fas fa-solid fa-warehouse',
                    'label_color' => 'success',
                    'can' => 'inventarios.index'
                ],

                [
                    'text' => 'Sucursales',
                    'url' => 'sucursales',
                    'icon' => 'fas fa-solid fa-horse',
                    'label_color' => 'success',
                    'can' => 'sucursales.index'
                ],
                [
                    'text' => 'Marcas',
                    'url' => 'marcas',
                    'icon' => 'fas fa-solid fa-copyright',
                    'label_color' => 'success',
                    'can' => 'marcas.index'
                ],
                [
                    'text' => 'Categorias',
                    'url' => 'categorias',
                    'icon' => 'fas fa-solid fa-list',
                    'label_color' => 'success',
                    'can' => 'categorias.index'
                ],
                [
                    'text' => 'Tipos',
                    'url' => 'tipos',
                    'icon' => 'fas fa-fw fa-file',
                    'label_color' => 'success',
                    'can' => 'tipos.index'
                ],

                [
                    'text' => 'CUPONES',
                    'url' => 'cupos',
                    'icon' => 'fas fa-receipt',
                    'label_color' => 'success',
                    'can' => 'cupos.index'
                ],
            ],
        ],

        [
            'text' => 'Cajas Sucursales',
            'url'  => '/cajas-sucursales',
            'icon' => 'fas fa-fw fa-share',
            'can'  => 'productos.index'
        ],


        [
             'text' => 'Ventas Caja',
            'icon' => 'fas fa-cash-register', // Icono de caja registradora
            'submenu' => [
                [
                    'text' => 'Ventas de Recojo',
                    'url'  => '/ventarecojomoderno',
                    'icon' => 'fas fa-hand-holding-box', // Icono de recoger una caja
                    'can'  => 'control.index'
                ],
                [
                    'text' => 'Ventas de Recojo Cola',
                    'url'  => '/ventarecojomodernocola',
                    'icon' => 'fas fa-solid fa-cash-register', // Icono de recoger una caja
                    'can'  => 'control.index'
                ],
                 [
                    'text' => 'Venta Rápida Moderna',
                    'url'  => '/ventarapidamoderna', // Usa la ruta correcta aquí
                    'icon' => 'fas fa-bolt', // Icono de rayo (rápido, ágil)
                ],

            ],
        ],

        // Agrupamos los tres elementos de ventas en un solo submenu
        [
            'text' => 'Ventas',
            'icon' => 'fas fa-solid fa-cash-register',
            'submenu' => [
                [
                    'text' => 'Venta Rápida',
                    'url'  => 'ventarapida', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-solid fa-cash-register',
                ],
                [
                    'text' => 'Cancelar Venta',
                    'url'  => 'cancelarventa', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-solid fa-plane-slash',
                ],
                [
                    'text' => 'Cancelar Venta Semana',
                    'url'  => '/cancelarventa/ultimasemana', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-solid fa-strikethrough',
                ],
                [
                    'text' => 'Venta por Sucursales',
                    'url'  => '/control', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-solid fa-horse',
                    'can' => 'control.index'
                ],
               
            ],
        ],
        
        
        
        
         [
            'text' => 'Ventas Promocion',
            'icon' => 'fas fa-solid fa-cash-register',
            'submenu' => [
                /* [
                    'text' => 'Ventas de recojo',
                    'url'  => '/ventarecojo',
                    'icon' => 'fas fa-fw fa-share',
                    'can' => 'control.index'

                ], */
                 [
                    'text' => 'PROMOCIONES',
                    'url'  => 'promociones', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-solid fa-hourglass-start',
                    'can' => 'promociones.index'
                ],
                /* [
                    'text' => 'Venta Rápida Moderna',
                    'url'  => 'ventarapidamoderna', // Usa la ruta que configuraste en routes/web.php
                    'icon' => 'fas fa-bolt', // Ícono de rayo (rápido, ágil)

                ], */
                
            ],
        ],





        // Restauramos "Envio de Productos"
        [
            'text' => 'Reporte de Ventas',
            'icon' => 'fas fa-chart-line',
            'submenu' => [
                [
                    'text' => 'Reporte de ventas',
                    'url' => '/reporte-ventas', // Enlace a la nueva vista
                    'icon' => 'fas fa-file-invoice-dollar',
                    'label_color' => 'success',
                    'can' => 'report.ventas'
                ],
                [
                    'text' => 'Ventas Canceladas',
                    'url'  => '/ventas-canceladas', // Ruta configurada en routes/web.php
                    'icon' => 'fas fa-ban', // Icono representativo
                    'can' => 'users.index'
                ],
                [
                    'text' => 'Por Día',
                    'url'  => '/sales-report',
                    'icon' => 'fas fa-calendar-day',
                    'can' => 'roles.index'
                ],
                [
                    'text' => 'Por Semana',
                    'url'  => '/sales-report/week',
                    'icon' => 'fas fa-calendar-week',
                    'can' => 'roles.index'
                ],
                [
                    'text' => 'Por Mes',
                    'url'  => '/sales-report/month',
                    'icon' => 'fas fa-calendar-alt',
                    'can' => 'roles.index'
                ],
            ],
        ],

        [
            'text' => 'GENERAR REPORTES',
            'icon' => 'fas fa-fw fa-chart-pie',
            'submenu' => [

                
                [
                    'text' => 'Reporte de ventas del usuario',
                    'url' => '/reporte-usuario-ventas', // Enlace a la nueva vista para las ventas del usuario autenticado
                    'icon' => 'fas fa-user-tag', // Icono representativo
                    'label_color' => 'info', // Color del label
                    'can' => 'report.user.ventas'
                ],
                [
                    'text' => 'Reporte de inventarios',
                    'url' => '/reporte/inventario', // Cambia la URL para que coincida con la ruta
                    'icon' => 'fas fa-boxes',
                    'label_color' => 'success',
                    'can' => 'report.inventario'
                ],

                [
                    'text' => 'Reporte de pedidos',
                    'url' => '/reporte/pedidos',
                    'icon' => 'fas fa-clipboard-list',
                    'label_color' => 'success',
                    'can' => 'reporte.pedidos'
                ],
                [
                    'text' => 'Reporte de pedido productos',
                    'url' => '/reporte/pedidos_producto',
                    'icon' => 'fas fa-truck-loading',
                    'label_color' => 'success',
                    'can' => 'reporte.pedidos_producto'
                ],
                [
                    'text' => 'Reporte Por Producto',
                    'url'  => 'reportes/productos',
                    'icon' => 'fas fa-fw fa-share',
                    'can' => 'reportes.productos.form'
                ],
            ],
        ],

    ],
    /*
    |--------------------------------------------------------------------------
    | Menu Filters
    |--------------------------------------------------------------------------
    |
    | Here we can modify the menu filters of the admin panel.
    |
    | For detailed instructions you can look the menu filters section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Menu-Configuration
    |
    */

    'filters' => [
        JeroenNoten\LaravelAdminLte\Menu\Filters\GateFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\HrefFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\SearchFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ActiveFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\ClassesFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\LangFilter::class,
        JeroenNoten\LaravelAdminLte\Menu\Filters\DataFilter::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Plugins Initialization
    |--------------------------------------------------------------------------
    |
    | Here we can modify the plugins used inside the admin panel.
    |
    | For detailed instructions you can look the plugins section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Plugins-Configuration
    |
    */

    'plugins' => [
        'Datatables' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css',
                ],
            ],
        ],
        'Select2' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/js/select2.min.js',
                ],
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/select2/4.0.3/css/select2.css',
                ],
            ],
        ],
        'Chartjs' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.0/Chart.bundle.min.js',
                ],
            ],
        ],
        'Sweetalert2' => [
            'active' => true,
            'files' => [
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdn.jsdelivr.net/npm/sweetalert2@11',
                ],
            ],
        ],
        'Pace' => [
            'active' => false,
            'files' => [
                [
                    'type' => 'css',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/themes/blue/pace-theme-center-radar.min.css',
                ],
                [
                    'type' => 'js',
                    'asset' => false,
                    'location' => '//cdnjs.cloudflare.com/ajax/libs/pace/1.0.2/pace.min.js',
                ],
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | IFrame
    |--------------------------------------------------------------------------
    |
    | Here we change the IFrame mode configuration. Note these changes will
    | only apply to the view that extends and enable the IFrame mode.
    |
    | For detailed instructions you can look the iframe mode section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/IFrame-Mode-Configuration
    |
    */

    'iframe' => [
        'default_tab' => [
            'url' => null,
            'title' => null,
        ],
        'buttons' => [
            'close' => true,
            'close_all' => true,
            'close_all_other' => true,
            'scroll_left' => true,
            'scroll_right' => true,
            'fullscreen' => true,
        ],
        'options' => [
            'loading_screen' => 1000,
            'auto_show_new_tab' => true,
            'use_navbar_items' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Livewire
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Livewire support.
    |
    | For detailed instructions you can look the livewire here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'livewire' => false,
];
