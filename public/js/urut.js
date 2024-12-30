document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', () => {
            const table = header.closest('table');
            const tbody = table.querySelector('tbody');
            const index = Array.from(header.parentNode.children).indexOf(header);
            const order = header.classList.contains('asc') ? 'desc' : 'asc';

            // Reset semua header
            document.querySelectorAll('.sortable').forEach(th => th.classList.remove('asc', 'desc'));

            // Tambahkan kelas untuk sorting saat ini
            header.classList.add(order);

            const rows = Array.from(tbody.querySelectorAll('tr'));

            rows.sort((rowA, rowB) => {
                const cellA = rowA.children[index].innerText.toLowerCase();
                const cellB = rowB.children[index].innerText.toLowerCase();

                if (cellA < cellB) {
                    return order === 'asc' ? -1 : 1;
                } else if (cellA > cellB) {
                    return order === 'asc' ? 1 : -1;
                } else {
                    return 0;
                }
            });

            // Hapus semua baris dan tambahkan kembali dalam urutan yang benar
            rows.forEach(row => tbody.appendChild(row));
        });
    });
});
