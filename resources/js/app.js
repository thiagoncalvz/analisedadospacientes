import './bootstrap';
import 'bootstrap';

const searchInput = document.getElementById('searchInput');
const generalTable = document.getElementById('generalTable');

if (searchInput && generalTable) {
    searchInput.addEventListener('input', (event) => {
        const query = event.target.value.toLowerCase();
        const rows = generalTable.querySelectorAll('tbody tr');

        rows.forEach((row) => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });
    });
}
