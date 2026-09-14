export default [
    {
        ignores: ['node_modules/**', 'public/build/**', 'vendor/**'],
    },
    {
        files: ['resources/js/**/*.js'],
        languageOptions: {
            ecmaVersion: 'latest',
            sourceType: 'module',
            globals: {
                window: 'readonly',
                document: 'readonly',
                fetch: 'readonly',
                console: 'readonly',
                setTimeout: 'readonly',
                setInterval: 'readonly',
                clearInterval: 'readonly',
                Echo: 'writable',
                Pusher: 'writable',
                Alpine: 'readonly',
                Livewire: 'readonly',
                localStorage: 'readonly',
                CustomEvent: 'readonly',
            },
        },
        rules: {
            'no-unused-vars': 'warn',
            'no-undef': 'error',
            eqeqeq: 'warn',
            'prefer-const': 'warn',
            'no-var': 'warn',
        },
    },
];
