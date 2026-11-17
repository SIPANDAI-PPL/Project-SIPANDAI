{{-- Buka/tutup modal. Simpan data lewat form biasa (POST); tab memakai tautan. --}}
<script>
    document.addEventListener('click', function (e) {
        var buka = e.target.closest('[data-modal-buka]');
        if (buka) {
            e.preventDefault();
            document.querySelectorAll('[data-modal]').forEach(function (m) { m.classList.add('hidden'); });
            document.getElementById(buka.dataset.modalBuka).classList.remove('hidden');
            return;
        }

        var tutup = e.target.closest('[data-modal-tutup]');
        if (tutup) {
            e.preventDefault();
            tutup.closest('[data-modal]').classList.add('hidden');
            return;
        }
    });
</script>
