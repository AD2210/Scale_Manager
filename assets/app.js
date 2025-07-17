// Importation des styles
import './styles/global.scss';

// Import de jQuery (nécessaire pour Bootstrap)
import $ from 'jquery';
global.$ = global.jQuery = $;

// Import des contrôleurs Stimulus
import './bootstrap.js';

// Rendre Bootstrap disponible globalement
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;
