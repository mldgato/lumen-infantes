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

    'title' => 'Clemente Martínez Rojas',
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

    'use_ico_only' => true,
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

    'logo'             => env('APP_INSTITUTION_LOGO', '<b>App</b>'),
    'logo_img'         => 'vendor/adminlte/dist/img/LumenLogo.png',
    'logo_img_class'   => 'brand-image img-circle',
    'logo_img_xl'      => null,
    'logo_img_xl_class' => 'brand-image-xs',
    'logo_img_alt'     => env('APP_INSTITUTION_NAME', 'Logo'),

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
            'path' => 'vendor/adminlte/dist/img/LumenLogo.png',
            'alt' => 'Lumen Logo',
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
        'mode' => 'cwrapper',
        'img' => [
            'path' => 'vendor/adminlte/dist/img/LogoGif.gif',
            'alt' => 'Lumen Preloader Image',
            'effect' => 'animation__shake',
            'width' => 60,
            'height' => 60,
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
    'usermenu_header' => true,
    'usermenu_header_class' => 'bg-primary',
    'usermenu_image' => true,
    'usermenu_desc' => true,
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
    'layout_fixed_sidebar' => null,
    'layout_fixed_navbar' => null,
    'layout_fixed_footer' => null,
    'layout_dark_mode' => null,

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

    'classes_body' => '',
    'classes_brand' => '',
    'classes_brand_text' => '',
    'classes_content_wrapper' => 'background-svg',
    'classes_content_header' => '',
    'classes_content' => '',
    'classes_sidebar' => 'sidebar-dark-danger elevation-4',
    'classes_sidebar_nav' => '',
    'classes_topnav' => 'navbar-white navbar-light',
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
    'right_sidebar_theme' => 'dark',
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
    'dashboard_url' => 'dashboard',
    'logout_url' => 'logout',
    'login_url' => 'login',
    'register_url' => false,
    'password_reset_url' => 'password/reset',
    'password_email_url' => 'password/email',
    'profile_url' => false,
    'disable_darkmode_routes' => false,

    /*
    |--------------------------------------------------------------------------
    | Laravel Asset Bundling
    |--------------------------------------------------------------------------
    |
    | Here we can enable the Laravel Asset Bundling option for the admin panel.
    | Currently, the next modes are supported: 'mix', 'vite' and 'vite_js_only'.
    | When using 'vite_js_only', it's expected that your CSS is imported using
    | JavaScript. Typically, in your application's 'resources/js/app.js' file.
    | If you are not using any of these, leave it as 'false'.
    |
    | For detailed instructions you can look the asset bundling section here:
    | https://github.com/jeroennoten/Laravel-AdminLTE/wiki/Other-Configuration
    |
    */

    'laravel_asset_bundling' => false,
    'laravel_css_path' => 'css/app.css',
    'laravel_js_path' => 'js/app.js',
    'stylesheets' => [
        'css/custom.css',
    ],

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
        [
            'type' => 'navbar-search',
            'text' => 'Email del usuario',
            'topnav_right' => true,
            'route' => 'admin.user.loginUser',
            'method' => 'post',
            'input_name' => 'searchVal',
            'id' => 'navbarSearch',
            'can' => 'admin.user.loginUser',
        ],
        [
            'type'         => 'fullscreen-widget',
            'topnav_right' => false,
        ],
        ['header' => 'account_settings'],
        [
            'text'  => 'Perfil',
            'route' => 'profile',
            'icon'  => 'fas fa-fw fa-user',
        ],

        // ==========================================
        // ADMINISTRACIÓN
        // ==========================================
        [
            'header' => 'ADMINISTRACIÓN',
            'can'   => 'admin.menu.encabezado',
        ],

        // Gestión de Personal
        [
            'text'    => 'Gestión de Personal',
            'icon'    => 'fas fa-fw fa-users-cog',
            'can'     => 'admin.menu.personal',
            'submenu' => [
                [
                    'text'    => 'Roles y Permisos',
                    'icon'    => 'fas fa-fw fa-user-shield',
                    'can'     => 'admin.roles.index',
                    'submenu' => [
                        [
                            'text'  => 'Roles',
                            'route' => 'admin.roles.index',
                            'icon'  => 'fas fa-user-tag',
                            'can'   => 'admin.roles.index',
                        ],
                        [
                            'text'  => 'Permisos',
                            'route' => 'admin.permissions.index',
                            'icon'  => 'fas fa-key',
                            'can'   => 'admin.permissions.index',
                        ],
                    ],
                ],
                [
                    'text'  => 'Personal Administrativo',
                    'route' => 'admin.users.index',
                    'icon'  => 'fas fa-fw fa-id-badge',
                    'can'   => 'admin.users.index',
                ],
            ],
        ],

        // Gestión Estudiantil
        [
            'text'    => 'Gestión Estudiantil',
            'icon'    => 'fas fa-fw fa-user-graduate',
            'can'     => 'admin.menu.estudiantil',
            'submenu' => [
                [
                    'text'  => 'Inscripciones',
                    'route' => 'admin.students.enrollments.index',
                    'icon'  => 'fas fa-fw fa-child',
                    'can'   => 'admin.students.enrollments.index',
                ],
                [
                    'text'  => 'Estudiantes',
                    'route' => 'admin.students.index',
                    'icon'  => 'fas fa-fw fa-users',
                    'can'   => 'admin.students.index',
                ],
            ],
        ],

        // Gestión Académica
        [
            'text'    => 'Gestión Académica',
            'icon'    => 'fas fa-fw fa-school',
            'can'     => 'admin.menu.academica',
            'submenu' => [
                [
                    'text'    => 'Grados y Secciones',
                    'icon'    => 'fas fa-fw fa-th-list',
                    'can'     => 'admin.levels.index',
                    'submenu' => [
                        [
                            'text'  => 'Niveles',
                            'route' => 'admin.levels.index',
                            'icon'  => 'fas fa-fw fa-layer-group',
                            'can'   => 'admin.levels.index',
                        ],
                        [
                            'text'  => 'Grados',
                            'route' => 'admin.grades.index',
                            'icon'  => 'fas fa-fw fa-graduation-cap',
                            'can'   => 'admin.grades.index',
                        ],
                        [
                            'text'  => 'Secciones',
                            'route' => 'admin.sections.index',
                            'icon'  => 'fas fa-fw fa-tags',
                            'can'   => 'admin.sections.index',
                        ],
                        [
                            'text'  => 'Aulas',
                            'route' => 'admin.classrooms.index',
                            'icon'  => 'fas fa-fw fa-chalkboard',
                            'can'   => 'admin.classrooms.index',
                        ],
                    ],
                ],
                [
                    'text'    => 'Cursos y Pénsum',
                    'icon'    => 'fas fa-fw fa-book',
                    'can'     => 'admin.courses.index',
                    'submenu' => [
                        [
                            'text'  => 'Cursos',
                            'route' => 'admin.courses.index',
                            'icon'  => 'fas fa-fw fa-file-alt',
                            'can'   => 'admin.courses.index',
                        ],
                        [
                            'text'  => 'Pénsum',
                            'route' => 'admin.pensums.index',
                            'icon'  => 'fas fa-fw fa-list-alt',
                            'can'   => 'admin.pensums.index',
                        ],
                    ],
                ],
                [
                    'text'  => 'Asignación de Profesores',
                    'route' => 'admin.classroom-course-assignments.index',
                    'icon'  => 'fas fa-fw fa-chalkboard-teacher',
                    'can'   => 'admin.classroom-course-assignments.index',
                ],
                [
                    'text'  => 'Conf. Académica',
                    'route' => 'admin.academic-configurations.index',
                    'icon'  => 'fas fa-fw fa-cogs',
                    'can'   => 'admin.academic-configurations.index',
                ],
            ],
        ],

        // Cuadros y Calificaciones
        [
            'text'    => 'Cuadros y Calificaciones',
            'icon'    => 'fas fa-fw fa-book-open',
            'can'     => 'admin.menu.cuadros',
            'submenu' => [
                [
                    'text'  => 'Validación de Cuadros',
                    'route' => 'admin.grade-books.index',
                    'icon'  => 'fas fa-fw fa-check-square',
                    'can'   => 'admin.grade-books.index',
                ],
                [
                    'text'  => 'Cambios de Notas',
                    'route' => 'admin.grade-change-requests.index',
                    'icon'  => 'fas fa-fw fa-clipboard-check',
                    'can'   => 'admin.grade-change-requests.index',
                ],
            ],
        ],

        // Reportes Admin
        [
            'text'    => 'Reportes',
            'icon'    => 'fas fa-fw fa-chart-bar',
            'can'     => 'admin.menu.reportes',
            'submenu' => [
                [
                    'text'    => 'Sábanas',
                    'icon'    => 'fas fa-fw fa-table',
                    'can'     => 'admin.reports.sabana-unidad',
                    'submenu' => [
                        [
                            'text'  => 'Sábana por Unidad',
                            'route' => 'admin.reports.sabana-unidad.index',
                            'icon'  => 'fas fa-file-excel',
                            'can'   => 'admin.reports.sabana-unidad',
                        ],
                        [
                            'text'  => 'Sábana General',
                            'route' => 'admin.reports.sabana-general.index',
                            'icon'  => 'fas fa-file-excel',
                            'can'   => 'admin.reports.sabana-general',
                        ],
                        [
                            'text'  => 'Sábana Promedio Final',
                            'route' => 'admin.reports.sabana-promedio.index',
                            'icon'  => 'fas fa-file-excel',
                            'can'   => 'admin.reports.sabana-promedio',
                        ],
                    ],
                ],
                [
                    'text'  => 'Cuadros por Aula',
                    'route' => 'admin.reports.cuadros-classroom.index',
                    'icon'  => 'fas fa-fw fa-file-pdf',
                    'can'   => 'admin.reports.cuadros-classroom',
                ],
                [
                    'text'    => 'Listados',
                    'icon'    => 'fas fa-fw fa-list',
                    'can'     => 'admin.reports.student-list',
                    'submenu' => [
                        [
                            'text'  => 'Listado PDF',
                            'route' => 'admin.reports.student-list.index',
                            'icon'  => 'fas fa-file-pdf',
                            'can'   => 'admin.reports.student-list',
                        ],
                        [
                            'text'  => 'Listado Excel',
                            'route' => 'admin.reports.student-list-excel.index',
                            'icon'  => 'fas fa-file-excel',
                            'can'   => 'admin.reports.student-list-excel',
                        ],
                        [
                            'text'  => 'Profesores y Cursos',
                            'route' => 'admin.reports.professor-courses.index',
                            'icon'  => 'fas fa-file-excel',
                            'can'   => 'admin.reports.professor-courses',
                        ],
                    ],
                ],
                [
                    'text'  => 'Boletas de Calificaciones',
                    'route' => 'admin.reports.report-cards.index',
                    'icon'  => 'fas fa-fw fa-file-pdf',
                    'can'   => 'admin.reports.report-cards',
                ],
                [
                    'text'  => 'Actividades No Entregadas',
                    'route' => 'admin.reports.missing-activities.index',
                    'icon'  => 'fas fa-fw fa-tasks',
                    'can'   => 'admin.reports.missing-activities',
                ],
                [
                    'text'  => 'Asistencia',
                    'route' => 'admin.reports.attendance.index',
                    'icon'  => 'fas fa-fw fa-user-check',
                    'can'   => 'admin.reports.attendance',
                ],
                [
                    'text'  => 'Avance de Notas',
                    'route' => 'admin.reports.grade-progress.index',
                    'icon'  => 'fas fa-fw fa-chart-line',
                    'can'   => 'admin.reports.grade-progress',
                ],
            ],
        ],

        // Sistema
        [
            'text'    => 'Sistema',
            'icon'    => 'fas fa-fw fa-server',
            'can'     => 'admin.menu.sistema',
            'submenu' => [
                [
                    'text'  => 'Auditoría',
                    'route' => 'admin.audit.index',
                    'icon'  => 'fas fa-fw fa-history',
                    'can'   => 'admin.audit.index',
                ],
            ],
        ],

        // ==========================================
        // PROFESOR
        // ==========================================
        [
            'header' => 'DOCENTE',
            'can'   => 'profesor.menu.cuadros',
        ],

        // Mis Cuadros
        [
            'text'    => 'Mis Cuadros',
            'icon'    => 'fas fa-fw fa-book-open',
            'can'     => 'profesor.menu.cuadros',
            'submenu' => [
                [
                    'text'  => 'Mis Cuadros',
                    'route' => 'profesor.grade-books.index',
                    'icon'  => 'fas fa-fw fa-book',
                    'can'   => 'profesor.grade-books.index',
                ],
                [
                    'text'  => 'Solicitar Cambio de Notas',
                    'route' => 'profesor.grade-change-requests.index',
                    'icon'  => 'fas fa-fw fa-edit',
                    'can'   => 'profesor.grade-change-requests.create',
                ],
                [
                    'text'  => 'Asistencia',
                    'route' => 'profesor.attendance.index',
                    'icon'  => 'fas fa-fw fa-user-check',
                    'can'   => 'profesor.attendance.index',
                ],
            ],
        ],

        // Reportes Profesor
        [
            'text'    => 'Reportes y Documentos',
            'icon'    => 'fas fa-fw fa-chart-bar',
            'can'     => 'profesor.menu.reportes',
            'submenu' => [
                [
                    'text'  => 'Acumulado',
                    'route' => 'profesor.reports.sabana-promedio.index',
                    'icon'  => 'fas fa-fw fa-file-pdf',
                    'can'   => 'profesor.reports.sabana-promedio',
                ],
                [
                    'text'  => 'Mis Cuadros por Unidad',
                    'route' => 'profesor.reports.cuadros-unidad.index',
                    'icon'  => 'fas fa-fw fa-file-pdf',
                    'can'   => 'profesor.reports.cuadros-unidad',
                ],
                [
                    'text'  => 'Cuadro Vacío',
                    'route' => 'profesor.reports.cuadro-vacio.index',
                    'icon'  => 'fas fa-fw fa-print',
                    'can'   => 'profesor.reports.cuadro-vacio',
                ],
                [
                    'text'  => 'Listado de Estudiantes PDF',
                    'route' => 'profesor.reports.student-list.index',
                    'icon'  => 'fas fa-fw fa-file-pdf',
                    'can'   => 'profesor.reports.student-list',
                ],
                [
                    'text'  => 'Listado de Estudiantes Excel',
                    'route' => 'profesor.reports.student-list-excel.index',
                    'icon'  => 'fas fa-fw fa-file-excel',
                    'can'   => 'profesor.reports.student-list-excel',
                ],
                [
                    'text'  => 'Actividades No Entregadas',
                    'route' => 'profesor.reports.missing-activities.index',
                    'icon'  => 'fas fa-fw fa-tasks',
                    'can'   => 'profesor.reports.missing-activities',
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
            'active' => false,
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
            'active' => true,
            'files' => [
                [
                    'type'     => 'js',
                    'asset'    => false,
                    'location' => '//cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js',
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

    'livewire' => true,
];
