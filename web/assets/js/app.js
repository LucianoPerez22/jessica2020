// require jQuery normally
const $ = require('jquery');
const moment = require('moment');
const bootstrap = require('bootstrap');

// create global $ and jQuery variables
global.$ = global.jQuery = $;
global.moment = moment;
global.bootstrap = bootstrap;
