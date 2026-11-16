// Fitur: PBI-12 Ringkasan Pola Respons Anak
// Grafik Chart.js untuk halaman ringkasan pola respons.
// Data berasal dari <script type="application/json" id="data-grafik-pola"> yang dibuat server.
import {
    BarController,
    BarElement,
    CategoryScale,
    Chart,
    Legend,
    LinearScale,
    Tooltip,
} from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip, Legend);

const font = { family: "'Plus Jakarta Sans', sans-serif", size: 12 };
const kurangiGerak = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

Chart.defaults.font = font;
Chart.defaults.color = '#4B5563';
if (kurangiGerak) {
    Chart.defaults.animation = false;
}

const sumbuJumlah = {
    beginAtZero: true,
    ticks: { precision: 0 },
    grid: { color: '#EEF1F0' },
    border: { display: false },
};

function grafikKategori(kanvas, data) {
    return new Chart(kanvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: 'Jumlah catatan',
                data: data.data,
                backgroundColor: data.warna,
                borderRadius: 6,
                maxBarThickness: 56,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: { callbacks: { label: (konteks) => `${konteks.parsed.y} catatan` } },
            },
            scales: {
                x: { grid: { display: false }, ticks: { autoSkip: false, maxRotation: 0, callback(nilai) {
                    // Label panjang dipecah dua baris agar tidak miring.
                    const teks = this.getLabelForValue(nilai);
                    return teks.length > 14 ? teks.split(' ').reduce((baris, kata) => {
                        const akhir = baris[baris.length - 1];
                        if (akhir && (akhir + ' ' + kata).length <= 14) {
                            baris[baris.length - 1] = akhir + ' ' + kata;
                        } else {
                            baris.push(kata);
                        }
                        return baris;
                    }, []) : teks;
                } } },
                y: sumbuJumlah,
            },
        },
    });
}

function grafikKelompok(kanvas, data) {
    return new Chart(kanvas, {
        type: 'bar',
        data: salinData(data),
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'bottom', labels: { usePointStyle: true, pointStyle: 'circle', boxWidth: 8, boxHeight: 8 } },
                tooltip: { callbacks: { label: (konteks) => `${konteks.dataset.label}: ${konteks.parsed.x} catatan` } },
            },
            scales: {
                x: { ...sumbuJumlah, stacked: true },
                y: { stacked: true, grid: { display: false }, border: { display: false } },
            },
        },
    });
}

// Salinan supaya Chart.js tidak mengubah data asli saat berganti tab.
function salinData(data) {
    return {
        labels: [...data.labels],
        datasets: data.datasets.map((dataset) => ({
            label: dataset.label,
            data: [...dataset.data],
            backgroundColor: dataset.backgroundColor,
            borderRadius: 4,
            maxBarThickness: 28,
        })),
    };
}

export function pasangGrafikPolaRespons() {
    const sumber = document.getElementById('data-grafik-pola');
    if (!sumber) {
        return;
    }

    const data = JSON.parse(sumber.textContent);
    const kanvasKategori = document.getElementById('grafik-kategori-respons');
    const kanvasKelompok = document.getElementById('grafik-pola-kelompok');

    if (kanvasKategori) {
        grafikKategori(kanvasKategori, data.kategori);
        kanvasKategori.dataset.siap = '1';
    }

    if (!kanvasKelompok) {
        return;
    }

    const tombolTab = [...document.querySelectorAll('[data-tab-kelompok]')];
    const tabel = [...document.querySelectorAll('[data-tabel-kelompok]')];
    let aktif = tombolTab[0]?.dataset.tabKelompok ?? 'aspek';
    const grafik = grafikKelompok(kanvasKelompok, data.kelompok[aktif]);
    kanvasKelompok.dataset.siap = '1';
    kanvasKelompok.dataset.kelompok = aktif;

    // Tanpa JavaScript semua tabel tampil. Dengan JavaScript hanya tabel tab aktif.
    const tampilkan = (jenis) => {
        aktif = jenis;
        tombolTab.forEach((tombol) => tombol.setAttribute('aria-selected', String(tombol.dataset.tabKelompok === jenis)));
        tabel.forEach((elemen) => { elemen.hidden = elemen.dataset.tabelKelompok !== jenis; });

        const baru = salinData(data.kelompok[jenis]);
        grafik.data.labels = baru.labels;
        grafik.data.datasets = baru.datasets;
        grafik.update();
        kanvasKelompok.dataset.kelompok = jenis;
    };

    tombolTab.forEach((tombol) => {
        tombol.addEventListener('click', () => tampilkan(tombol.dataset.tabKelompok));
    });

    tampilkan(aktif);
}
