require('github-buttons');
require('jquery');
require('popper.js');
require('./bootstrap');
require('alpinejs');
Chart = require('chart.js/dist/chart.min');
require('shards-ui');
// require('./shards-dashboards.1.1.0.min');
// require('./app/app-blog-overview.1.1.0.min');
require('bootstrap-select');
window.Cookies = require('js-cookie');

QRCode = require('qrcode');

$.ajaxSetup({headers: {'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')}});




