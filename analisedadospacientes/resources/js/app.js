import './bootstrap';
import * as bootstrap from 'bootstrap';

const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
[...tooltipTriggerList].forEach((tooltipTriggerEl) => {
    new bootstrap.Tooltip(tooltipTriggerEl);
});
