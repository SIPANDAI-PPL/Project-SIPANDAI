import './bootstrap';

// PBI-12: Chart.js dimuat hanya di halaman yang punya data grafik pola respons.
if (document.getElementById('data-grafik-pola')) {
    import('./grafik-pola-respons').then(({ pasangGrafikPolaRespons }) => pasangGrafikPolaRespons());
}
