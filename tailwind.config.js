/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./resources/**/*.blade.php",
        "./resources/**/*.js",
        "./resources/**/*.vue",
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
    ],
    theme: {
        extend: {
            colors: {
                brand: {
                    blue: '#00659e',
                    'blue-dark': '#005a8c',
                    'blue-accent': '#0089d7',
                    light: '#BDE6FF',
                },
            },
        },
    },
    plugins: [],
}

