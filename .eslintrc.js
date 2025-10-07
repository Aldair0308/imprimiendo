module.exports = {
    env: {
        browser: true,
        es2021: true,
        node: true
    },
    extends: [
        'eslint:recommended'
    ],
    parserOptions: {
        ecmaVersion: 'latest',
        sourceType: 'module'
    },
    globals: {
        // Variables globales de Laravel
        'Laravel': 'readonly',
        'route': 'readonly',
        'trans': 'readonly',
        'trans_choice': 'readonly',
        
        // Variables globales de Imprimeindo
        'Imprimeindo': 'writable',
        'QRGenerator': 'readonly',
        'qrGenerator': 'writable',
        'QRManager': 'readonly',
        'PrinterManager': 'readonly',
        'FileManager': 'readonly',
        'Utils': 'readonly',
        
        // Variables del DOM
        'document': 'readonly',
        'window': 'readonly',
        'console': 'readonly',
        'fetch': 'readonly',
        'navigator': 'readonly',
        'localStorage': 'readonly',
        'sessionStorage': 'readonly',
        'setTimeout': 'readonly',
        'setInterval': 'readonly',
        'clearTimeout': 'readonly',
        'clearInterval': 'readonly'
    },
    rules: {
        // Reglas de estilo
        'indent': ['error', 4],
        'linebreak-style': ['error', 'unix'],
        'quotes': ['error', 'single'],
        'semi': ['error', 'always'],
        
        // Reglas de calidad de código
        'no-unused-vars': ['warn', { 
            'argsIgnorePattern': '^_',
            'varsIgnorePattern': '^_'
        }],
        'no-console': 'off', // Permitir console.log en desarrollo
        'no-debugger': 'warn',
        'no-alert': 'warn',
        
        // Reglas de mejores prácticas
        'eqeqeq': ['error', 'always'],
        'curly': ['error', 'all'],
        'no-eval': 'error',
        'no-implied-eval': 'error',
        'no-new-func': 'error',
        'no-script-url': 'error',
        
        // Reglas de variables
        'no-undef': 'error',
        'no-global-assign': 'error',
        'no-implicit-globals': 'error',
        
        // Reglas de funciones
        'no-empty-function': 'warn',
        'consistent-return': 'warn',
        'default-case': 'warn',
        
        // Reglas de arrays y objetos
        'no-sparse-arrays': 'error',
        'no-array-constructor': 'error',
        'no-new-object': 'error',
        
        // Reglas de espaciado y formato
        'space-before-function-paren': ['error', {
            'anonymous': 'never',
            'named': 'never',
            'asyncArrow': 'always'
        }],
        'space-in-parens': ['error', 'never'],
        'space-before-blocks': 'error',
        'keyword-spacing': 'error',
        'comma-spacing': 'error',
        'key-spacing': 'error',
        'object-curly-spacing': ['error', 'always'],
        'array-bracket-spacing': ['error', 'never'],
        
        // Reglas de líneas
        'max-len': ['warn', { 
            'code': 120,
            'ignoreUrls': true,
            'ignoreStrings': true,
            'ignoreTemplateLiterals': true
        }],
        'max-lines': ['warn', {
            'max': 500,
            'skipBlankLines': true,
            'skipComments': true
        }],
        
        // Reglas específicas para el proyecto
        'camelcase': ['error', { 
            'properties': 'never',
            'ignoreDestructuring': true
        }]
    },
    overrides: [
        {
            // Configuración específica para archivos de configuración
            files: ['*.config.js', 'webpack.mix.js', 'tailwind.config.js'],
            rules: {
                'no-undef': 'off'
            }
        },
        {
            // Configuración específica para archivos de test
            files: ['**/*.test.js', '**/*.spec.js'],
            env: {
                jest: true
            },
            rules: {
                'no-unused-expressions': 'off'
            }
        }
    ]
};