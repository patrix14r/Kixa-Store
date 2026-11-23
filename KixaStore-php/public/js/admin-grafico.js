// Gráficos del dashboard admin (Chart.js 4 por CDN): barras "Productos por Marca" y área "Estadísticas de Ingresos" con pestañas 7 / 30 días.
document.addEventListener("DOMContentLoaded", () => {
    if (typeof Chart === "undefined") return;

    const acento = "#6366f1";
    const texto = "#a1a1aa";
    const rejilla = "rgba(63, 63, 70, 0.4)";
    const tooltip = { backgroundColor: "#121215", borderColor: "#27272a", borderWidth: 1, titleColor: "#fafafa", bodyColor: texto, padding: 10 };

    // 1) Barras redondeadas: conteo real de productos por marca (data-marcas viene de AdminController).
    const lienzoMarcas = document.getElementById("graficoMarcas");
    if (lienzoMarcas) {
        const marcas = JSON.parse(lienzoMarcas.dataset.marcas);
        new Chart(lienzoMarcas, {
            type: "bar",
            data: {
                labels: marcas.map((m) => m.marca),
                datasets: [{ label: "Productos", data: marcas.map((m) => m.cantidad), backgroundColor: acento, hoverBackgroundColor: "#818cf8", borderRadius: 8, borderSkipped: false, maxBarThickness: 36 }],
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip },
                scales: {
                    x: { ticks: { color: texto }, grid: { display: false }, border: { display: false } },
                    y: { beginAtZero: true, ticks: { color: texto, precision: 0 }, grid: { color: rejilla }, border: { display: false } },
                },
            },
        });
    }

    // 2) Área suave de ingresos: el controlador envía 30 días y aquí se muestran los últimos 7 o los 30.
    const lienzoVentas = document.getElementById("graficoVentas");
    if (!lienzoVentas) return;

    const datos = JSON.parse(lienzoVentas.dataset.grafico);
    const recortar = (dias) => datos.slice(-dias);

    const contexto = lienzoVentas.getContext("2d");
    const degradado = contexto.createLinearGradient(0, 0, 0, lienzoVentas.parentElement.clientHeight || 288);
    degradado.addColorStop(0, "rgba(99, 102, 241, 0.35)");
    degradado.addColorStop(1, "rgba(99, 102, 241, 0)");

    const inicial = recortar(7);
    const grafico = new Chart(lienzoVentas, {
        type: "line",
        data: {
            labels: inicial.map((d) => d.dia),
            datasets: [{ label: "Ingresos (S/)", data: inicial.map((d) => d.ingresos), borderColor: acento, backgroundColor: degradado, fill: true, tension: 0.4, borderWidth: 2, pointRadius: 0, pointHoverRadius: 5, pointBackgroundColor: acento }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: { legend: { display: false }, tooltip },
            scales: {
                x: { ticks: { color: texto, maxTicksLimit: 10 }, grid: { display: false }, border: { display: false } },
                y: { beginAtZero: true, ticks: { color: texto }, grid: { color: rejilla }, border: { display: false } },
            },
        },
    });

    // Pestañas: cambian el rango visible sin recargar la página.
    document.querySelectorAll("[data-rango]").forEach((boton) => {
        boton.addEventListener("click", () => {
            const rango = recortar(Number(boton.dataset.rango));
            grafico.data.labels = rango.map((d) => d.dia);
            grafico.data.datasets[0].data = rango.map((d) => d.ingresos);
            grafico.update();

            document.querySelectorAll("[data-rango]").forEach((b) => b.classList.toggle("kx-tab-activa", b === boton));
        });
    });
});
