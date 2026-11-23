// Gráfico lineal del dashboard admin (Chart.js): ingresos de los últimos 7 días (una sola serie).
document.addEventListener("DOMContentLoaded", () => {
    const lienzo = document.getElementById("graficoVentas");
    if (!lienzo || typeof Chart === "undefined") return;

    const datos = JSON.parse(lienzo.dataset.grafico);
    const texto = "#a1a1aa";
    const rejilla = "rgba(63, 63, 70, 0.5)";

    new Chart(lienzo, {
        type: "line",
        data: {
            labels: datos.map((d) => d.dia),
            datasets: [
                {
                    label: "Ingresos (S/)",
                    data: datos.map((d) => d.ingresos),
                    borderColor: "#6366f1",
                    backgroundColor: "rgba(99, 102, 241, 0.15)",
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: "#6366f1",
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: "index", intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: { backgroundColor: "#121215", borderColor: "#27272a", borderWidth: 1, titleColor: "#fafafa", bodyColor: texto },
            },
            scales: {
                x: { ticks: { color: texto }, grid: { color: rejilla } },
                y: { beginAtZero: true, ticks: { color: texto }, grid: { color: rejilla } },
            },
        },
    });
});
