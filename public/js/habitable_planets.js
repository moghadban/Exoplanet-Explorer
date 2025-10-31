document.addEventListener('DOMContentLoaded', () => {
    // -----------------------------
    // Theme colors for dark background
    // -----------------------------
    const textColor = '#f5f5f5';
    // Use a slight white tint for grid lines for visibility on dark theme
    const gridColor = 'rgba(255, 255, 255, 0.2)';
    const axisBorderColor = '#e0e0e0';
    const legendLabelColor = '#ffffff';
    // FIX: Use dark, semi-transparent background for tooltips
    const tooltipBg = 'rgba(40, 40, 40, 0.95)';
    const tooltipBorder = '#aaaaaa';

    // Chart Colors
    // Pie Chart Colors (Habitable vs Non-Habitable)
    const habitableColor = '#14b8a6'; // Deep Teal
    const nonHabitableColor = '#ec4899'; // Vivid Orange

    // Bar Chart Colors (Radius Categories: Small, Medium, Large)
    const radiusColors = ['#38bdf8', '#facc15', '#ec4899']; // Blue, Gold, Pink

    // -----------------------------
    // Helper: normalize label (Kept, but not actively used in this file)
    // -----------------------------
    function normalizeLabel(label) {
        return label.trim();
    }

    // -----------------------------
    // Pie Chart: Habitable vs Non-Habitable
    // -----------------------------
    const pieCanvas = document.getElementById('habitablePie');
    if (pieCanvas) {
        const habitableCount = parseInt(pieCanvas.dataset.habitable);
        const nonHabitableCount = parseInt(pieCanvas.dataset.nonhabitable);

        if (window.habitablePieChart) window.habitablePieChart.destroy();

        window.habitablePieChart = new Chart(pieCanvas, {
            type: 'pie',
            data: {
                labels: ['Habitable', 'Non-Habitable'],
                datasets: [{
                    data: [habitableCount, nonHabitableCount],
                    // Use new custom colors
                    backgroundColor: [habitableColor, nonHabitableColor],
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverBorderColor: '#ffffcc',
                    hoverBorderWidth: 3,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: {color: legendLabelColor}
                    },
                    tooltip: {
                        // Apply dark theme variables
                        backgroundColor: tooltipBg,
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: tooltipBorder,
                        borderWidth: 1,
                        callbacks: {
                            label: ctx => `${ctx.label}: ${ctx.parsed} planets`
                        }
                    }
                },
                elements: {
                    arc: {
                        borderColor: '#ffffff',
                        borderWidth: 1.5,
                        shadowColor: '#ffffff40',
                        shadowBlur: 6
                    }
                }
            }
        });
    }

    // -----------------------------
    // Bar Chart: Planets by Radius Category
    // -----------------------------
    const barCanvas = document.getElementById('radiusBar');
    if (barCanvas) {
        if (window.radiusBarChart) window.radiusBarChart.destroy();

        const categories = JSON.parse(barCanvas.dataset.categories);

        window.radiusBarChart = new Chart(barCanvas, {
            type: 'bar',
            data: {
                labels: Object.keys(categories),
                datasets: [{
                    label: 'Number of Planets',
                    data: Object.values(categories),
                    // Use new custom colors
                    backgroundColor: radiusColors,
                    borderColor: '#ffffff',
                    borderWidth: 2,
                    hoverBorderColor: '#ffffcc',
                    hoverBorderWidth: 2.5
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {display: false},
                    tooltip: {
                        // Apply dark theme variables
                        backgroundColor: tooltipBg,
                        titleColor: textColor,
                        bodyColor: textColor,
                        borderColor: tooltipBorder,
                        borderWidth: 1,
                        callbacks: {
                            label: ctx => `${ctx.dataset.label}: ${ctx.parsed.y}`
                        }
                    }
                },
                scales: {
                    x: {
                        ticks: {color: textColor},
                        grid: {
                            color: gridColor,
                            borderColor: axisBorderColor,
                            lineWidth: 1.2
                        },
                        border: {color: axisBorderColor}
                    },
                    y: {
                        beginAtZero: true,
                        title: {display: true, text: 'Planets Count', color: textColor},
                        ticks: {color: textColor},
                        grid: {
                            color: gridColor,
                            borderColor: axisBorderColor,
                            lineWidth: 1.2
                        },
                        border: {color: axisBorderColor}
                    }
                },
                layout: {padding: 8}
            }
        });
    }

    // -----------------------------
    // Export Buttons Styling
    // -----------------------------
    function styleExportButton(btn) {
        btn.style.backgroundColor = '#2563eb';
        btn.style.color = '#fff';
        btn.style.border = '1px solid #aaa';
        btn.style.borderRadius = '5px';
        btn.style.padding = '6px 12px';
        btn.style.marginRight = '6px';
        btn.style.cursor = 'pointer';
        btn.style.fontSize = '0.9rem';
        btn.style.transition = 'background-color 0.3s ease';
        btn.onmouseover = () => btn.style.backgroundColor = '#3b82f6';
        btn.onmouseout = () => btn.style.backgroundColor = '#2563eb';
    }

    // -----------------------------
    // Pie Chart Exports
    // -----------------------------
    const pieExport = document.getElementById('habitableExports');
    if (pieExport && pieCanvas) {
        pieExport.innerHTML = '';
        const habitableCount = parseInt(pieCanvas.dataset.habitable);
        const nonHabitableCount = parseInt(pieCanvas.dataset.nonhabitable);


        const pngBtn = document.createElement('button');
        pngBtn.textContent = 'Download PNG';
        styleExportButton(pngBtn);
        pngBtn.onclick = () => {
            const link = document.createElement('a');
            link.href = pieCanvas.toDataURL('image/png');
            link.download = 'habitable_pie.png';
            link.click();
        };
        pieExport.appendChild(pngBtn);

        const csvBtn = document.createElement('button');
        csvBtn.textContent = 'Download CSV';
        styleExportButton(csvBtn);
        csvBtn.onclick = () => {
            const csv = `Category,Count\nHabitable,${habitableCount}\nNon-Habitable,${nonHabitableCount}`;
            const blob = new Blob([csv], {type: 'text/csv'});
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'habitable_pie.csv';
            link.click();
        };
        pieExport.appendChild(csvBtn);
    }

    // -----------------------------
    // Bar Chart Exports
    // -----------------------------
    const barExport = document.getElementById('radiusExports');
    if (barExport && barCanvas) {
        barExport.innerHTML = '';

        const barPngBtn = document.createElement('button');
        barPngBtn.textContent = 'Download PNG';
        styleExportButton(barPngBtn);
        barPngBtn.onclick = () => {
            const link = document.createElement('a');
            link.href = barCanvas.toDataURL('image/png');
            link.download = 'radius_bar.png';
            link.click();
        };
        barExport.appendChild(barPngBtn);

        const barCsvBtn = document.createElement('button');
        barCsvBtn.textContent = 'Download CSV';
        styleExportButton(barCsvBtn);
        barCsvBtn.onclick = () => {
            let csv = 'Category,Count\n';
            for (const [cat, val] of Object.entries(JSON.parse(barCanvas.dataset.categories))) {
                csv += `${cat},${val}\n`;
            }
            const blob = new Blob([csv], {type: 'text/csv'});
            const url = URL.createObjectURL(blob);
            const link = document.createElement('a');
            link.href = url;
            link.download = 'radius_bar.csv';
            link.click();
        };
        barExport.appendChild(barCsvBtn);
    }
});
