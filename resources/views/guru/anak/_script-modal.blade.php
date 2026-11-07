{{-- Hanya untuk tampilan: buka/tutup modal dan ganti tab, tanpa logic data. --}}
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

        var tab = e.target.closest('[data-tab]');
        if (tab) {
            document.querySelectorAll('[data-tab]').forEach(function (t) {
                var aktif = t === tab;
                t.classList.toggle('bg-indigo-900', aktif);
                t.classList.toggle('text-white', aktif);
                t.classList.toggle('bg-white', ! aktif);
            });
            document.querySelectorAll('[data-panel]').forEach(function (p) {
                p.classList.toggle('hidden', p.dataset.panel !== tab.dataset.tab);
            });
        }
    });
</script>
