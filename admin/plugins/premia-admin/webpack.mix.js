// webpack.mix.js

let mix = require( 'laravel-mix' );

mix.js( 'src/index.js', 'dist' ).setPublicPath( 'dist' );
