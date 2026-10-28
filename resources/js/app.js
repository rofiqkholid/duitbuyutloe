import './bootstrap';
import { createIcons, icons } from 'lucide';
import Viewer from 'viewerjs';

window.createIcons = createIcons;
window.lucideIcons = icons;
window.Viewer = Viewer;

document.addEventListener('DOMContentLoaded', () => {
    createIcons({ icons });
});
