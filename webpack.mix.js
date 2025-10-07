const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management - Imprimeindo
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

// Configuración de Mix para Imprimeindo
mix
    // JavaScript principal
    .js('resources/js/app.js', 'public/js')
    .js('resources/js/qr-generator.js', 'public/js')
    
    // CSS principal con TailwindCSS
    .css('resources/css/app.css', 'public/css')
    .css('resources/css/variables.css', 'public/css')
    
    // PostCSS con TailwindCSS y Autoprefixer
    .options({
        postCss: [
            require('tailwindcss'),
            require('autoprefixer'),
        ]
    })
    
    // Configuración de desarrollo
    .sourceMaps(true, 'source-map')
    
    // Configuración de producción
    .version()
    
    // Configuración del servidor de desarrollo
    .browserSync({
        proxy: 'localhost:8000',
        files: [
            'app/**/*.php',
            'resources/views/**/*.php',
            'resources/js/**/*.js',
            'resources/css/**/*.css'
        ],
        watchOptions: {
            usePolling: true,
            interval: 1000
        }
    })
    
    // Configuración de Webpack personalizada
    .webpackConfig({
        resolve: {
            alias: {
                '@': path.resolve('resources/js'),
                '~': path.resolve('resources/css')
            }
        },
        module: {
            rules: [
                {
                    test: /\.js$/,
                    exclude: /node_modules/,
                    use: {
                        loader: 'babel-loader',
                        options: {
                            presets: ['@babel/preset-env']
                        }
                    }
                }
            ]
        }
    })
    
    // Configuración de notificaciones
    .disableNotifications();

// Configuración específica para desarrollo
if (mix.inProduction()) {
    mix.version();
} else {
    mix.sourceMaps();
}

// Configuración de archivos estáticos
mix.copyDirectory('resources/images', 'public/images');

// Configuración de limpieza
mix.then(() => {
    console.log('✅ Compilación de assets completada para Imprimeindo');
});