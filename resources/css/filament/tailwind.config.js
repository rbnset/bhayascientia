export default {
    content: [
        './resources/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/diogogpinto/filament-auth-ui-enhancer/resources/**/*.blade.php',
        "./resources/**/*.js",
        "./resources/**/*.vue",
    ],
    theme: {
        extend: {
            screens: {
                'xs': '370px',
            },
        },
    },
    plugins: [],
}
